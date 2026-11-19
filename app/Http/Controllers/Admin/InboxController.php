<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\BotService;
use App\Services\ConversationService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Inbox 3 kolom + endpoint AJAX/polling-nya. */
class InboxController extends Controller
{
    private const DEFAULT_SOUND = ['sound_enabled' => true, 'sound_type' => 'pedestrian', 'sound_duration' => 15, 'sound_custom_url' => null];

    public function __construct(
        private ConversationService $conversations,
        private TelegramService $telegram,
    ) {}

    /** GET /admin/inbox/{id?} */
    public function index(Request $request, ?string $id = null): View
    {
        $user = $request->user();
        $filters = $request->only(['status', 'project_id', 'search']);

        $conversations = Conversation::inbox($user, $filters)->take(50)->get();
        $active = $id ? $conversations->firstWhere('id', (int) $id) : $conversations->first();
        if ($id && ! $active) {
            $active = $this->find($request, $id)->load(['visitor', 'project.widgetSetting', 'assignedUser']);
        }

        if ($active) {
            $active->load('messages.user');
            // Tercatat dibaca hanya saat chat dibuka eksplisit, per-user (superadmin dikecualikan).
            if ($id) {
                $active->markReadBy($user);
            }
        }

        // Riwayat tiket pelanggan aktif dari koleksi yang sudah dimuat (tanpa query tambahan).
        $customerAllTickets = collect();
        if ($active) {
            $customerAllTickets = $conversations->where('visitor_id', $active->visitor_id)->values();
            if (! $customerAllTickets->contains('id', $active->id)) {
                $customerAllTickets->prepend($active);
            }
        }

        return view('admin.inbox', [
            'projects' => Project::where('tenant_id', $user->tenant_id)->with('widgetSetting')->orderBy('name')->get(),
            'conversations' => $conversations,
            'activeConversation' => $active,
            'staffMembers' => User::where('tenant_id', $user->tenant_id)->orderBy('name')->get(),
            'counts' => Conversation::inboxCounts($user, $filters['project_id'] ?? null),
            'currentStatus' => $filters['status'] ?? 'open',
            'maxMessageId' => (int) $conversations->pluck('latestMessage.id')->filter()->max(),
            'visitorTicketCounts' => $conversations->countBy('visitor_id'),
            'customerAllTickets' => $customerAllTickets,
            'unreadIds' => Conversation::unreadIdsFor($user, $conversations->modelKeys()),
        ]);
    }

    /** POST /admin/inbox/{id}/reply — balasan agen (auto-assign + jeda bot). */
    public function reply(Request $request, string $id): JsonResponse
    {
        $content = $request->input('content') ?: $request->input('message');
        if (! is_string($content) || trim($content) === '' || mb_strlen($content) > 5000) {
            return response()->json(['success' => false, 'error' => ['message' => 'Konten pesan balasan wajib diisi (maks 5000 karakter).']], 422);
        }

        $conversation = $this->find($request, $id);
        $user = $request->user();

        $conversation->fill([
            'assigned_user_id' => $conversation->assigned_user_id ?: $user->id,
            'is_bot_active' => false,
            'bot_handoff_at' => $conversation->is_bot_active ? now() : $conversation->bot_handoff_at,
        ])->save();

        $msg = $this->sendAgentMessage($conversation, $user, $content, "Membalas pesan di percakapan #{$conversation->id} ke pengunjung");

        return $this->ok([
            'id' => $msg->id,
            'sender_type' => 'agent',
            'sender_name' => $user->name,
            'content' => $msg->content,
            'created_at' => $msg->created_at?->toIso8601String(),
            'assigned_user_id' => $conversation->assigned_user_id,
            'assigned_user_name' => $user->name,
            'is_bot_active' => false,
        ], 201);
    }

    /** POST /admin/inbox/{id}/toggle-bot */
    public function toggleBot(Request $request, string $id, BotService $bot): JsonResponse
    {
        $conversation = $this->find($request, $id);
        $active = $request->has('active') ? $request->boolean('active') : ! ($conversation->is_bot_active ?? true);
        $bot->toggleBot($conversation, $active);

        $verb = $active ? 'Mengaktifkan kembali' : 'Menjeda';
        ActivityLogger::log('bot.toggle', "{$verb} bot pada percakapan #{$conversation->id}", $conversation, ['is_bot_active' => $active]);

        return $this->ok([
            'conversation_id' => $conversation->id,
            'is_bot_active' => $active,
            'message' => $active ? 'Bot berhasil diaktifkan kembali untuk tiket ini.' : 'Bot berhasil dijeda. Staf CS menangani percakapan ini.',
        ]);
    }

    /** GET /admin/inbox/{id}/messages?after_id= — polling thread aktif. */
    public function messages(Request $request, string $id): JsonResponse
    {
        $conversation = $this->find($request, $id)->load('visitor');
        $afterId = (int) $request->query('after_id', 0);

        $messages = Message::where('conversation_id', $conversation->id)
            ->where('id', '>', $afterId)
            ->with('user')
            ->orderBy('id')
            ->get();

        $conversation->markReadBy($request->user());

        $visitorName = $conversation->visitor?->display_name ?? 'Tamu';

        return $this->ok([
            'messages' => $messages->map(fn (Message $m) => [
                'id' => $m->id,
                'sender_type' => $m->sender_type,
                'sender_name' => $m->sender_type === 'visitor' ? $visitorName : ($m->sender_name ?? $m->user?->name ?? 'Staff CS'),
                'content' => $m->content,
                'created_at' => $m->created_at?->toIso8601String(),
                'date_key' => ($m->created_at ?? now())->format('Y-m-d'),
                'date_label' => 'Hari Ini',
            ]),
            'last_id' => $messages->last()?->id ?? $afterId,
            'unread_total' => $this->unreadTotal($request->user()),
            'is_bot_active' => (bool) ($conversation->is_bot_active ?? true),
        ]);
    }

    /** GET /admin/inbox/feed/updates — daftar percakapan + deteksi pesan masuk baru. */
    public function pollUpdates(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['status', 'project_id', 'search']);
        $conversations = Conversation::inbox($user, $filters)->take(50)->get();
        $ticketCounts = $conversations->countBy('visitor_id');
        $unreadIds = Conversation::unreadIdsFor($user, $conversations->modelKeys());

        // Tanpa since_message_id = handshake awal: jadikan baseline, jangan bunyikan chat lama.
        // Baseline 0 tetap sah (tenant baru tanpa pesan), jadi cek keberadaan parameter, bukan nilainya.
        $sinceId = (int) $request->query('since_message_id', 0);
        $incoming = $request->has('since_message_id')
            ? Message::where('tenant_id', $user->tenant_id)
                ->where('id', '>', $sinceId)
                ->where('sender_type', 'visitor')
                ->whereHas('conversation', fn ($q) => $q->unreadFor($user))
                ->with('conversation.project.widgetSetting')
                ->orderBy('id')
                ->get()
            : collect();
        $latest = $incoming->last();

        return $this->ok([
            'conversations' => $conversations->map(fn (Conversation $c) => $c->toInboxCard((int) ($ticketCounts[$c->visitor_id] ?? 1), $unreadIds->contains($c->id))),
            'unread_total' => $this->unreadTotal($user),
            'counts' => Conversation::inboxCounts($user, $filters['project_id'] ?? null),
            'max_message_id' => (int) Message::where('tenant_id', $user->tenant_id)->max('id'),
            'has_new_incoming' => $incoming->isNotEmpty(),
            'new_incoming_count' => $incoming->count(),
            'latest_incoming' => $latest ? [
                'message_id' => $latest->id,
                'conversation_id' => $latest->conversation_id,
                'sender_name' => $latest->sender_name,
                'content' => mb_substr(strip_tags($latest->content), 0, 70),
            ] + ($latest->conversation?->project?->widgetSetting?->adminSoundConfig() ?? self::DEFAULT_SOUND) : null,
        ]);
    }

    /** PUT /admin/inbox/{id}/status — open/closed (+ pesan penutup opsional). */
    public function updateStatus(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $conversation = $this->find($request, $id);
        $status = $request->validate(['status' => 'nullable|in:open,closed,pending'])['status']
            ?? ($conversation->status === 'open' ? 'closed' : 'open');

        $closing = null;
        $closingText = trim((string) $request->input('closing_message'));
        if ($status === 'closed' && $closingText !== '') {
            $closing = $this->sendAgentMessage($conversation, $request->user(), $closingText, "Mengirim pesan penutup tiket #{$conversation->id}");
        }

        $this->conversations->setStatus($conversation, $status);
        ActivityLogger::log('conversation.status_updated', "Mengubah status percakapan #{$conversation->id} menjadi {$status}", $conversation, ['status' => $status]);

        if (! $request->wantsJson()) {
            return back()->with('success', "Status tiket berhasil diubah menjadi {$status}.");
        }

        return $this->ok([
            'status' => $status,
            'closing_message' => $closing ? $closing->only(['id', 'content', 'sender_name']) + ['created_at' => $closing->created_at?->toIso8601String()] : null,
        ]);
    }

    /** PUT /admin/inbox/{id}/assign */
    public function assign(Request $request, string $id): JsonResponse
    {
        $conversation = $this->find($request, $id);
        $assigneeId = $request->input('assigned_user_id');
        $assignee = $assigneeId ? User::where('tenant_id', $request->user()->tenant_id)->findOrFail($assigneeId) : null;

        $conversation->update(['assigned_user_id' => $assignee?->id]);
        $name = $assignee?->name ?? 'Belum Ditugaskan';

        ActivityLogger::log('conversation.assigned', "Menugaskan percakapan #{$conversation->id} ke {$name}", $conversation, [
            'assigned_user_id' => $assignee?->id, 'assignee_name' => $name,
        ]);

        return $this->ok(['assigned_user_id' => $assignee?->id, 'assigned_user_name' => $name, 'assignee_name' => $name]);
    }

    /** PUT /admin/inbox/{id}/customer — ganti nama tampilan pelanggan. */
    public function updateCustomerName(Request $request, string $id): JsonResponse
    {
        $conversation = $this->find($request, $id)->load('visitor');
        $name = strip_tags(trim($request->validate(['name' => 'required|string|max:100'])['name']));

        $conversation->visitor?->update(['name' => $name]);

        ActivityLogger::log('customer.renamed', "Mengubah nama pelanggan percakapan #{$conversation->id} menjadi: {$name}", $conversation->visitor, [
            'conversation_id' => $conversation->id, 'new_name' => $name,
        ]);

        return $this->ok([
            'display_name' => $conversation->visitor?->display_name,
            'name' => $name,
            'customer_code' => $conversation->visitor?->customer_code_formatted,
        ]);
    }

    /** Percakapan milik tenant user (404 bila tenant lain). */
    private function find(Request $request, string $id): Conversation
    {
        return Conversation::where('tenant_id', $request->user()->tenant_id)->findOrFail($id);
    }

    private function unreadTotal(User $user): int
    {
        return Conversation::where('tenant_id', $user->tenant_id)->unreadFor($user)->count();
    }

    /** Simpan pesan agen, teruskan ke topik Telegram, dan catat audit log. */
    private function sendAgentMessage(Conversation $conversation, User $user, string $content, string $logText): Message
    {
        $msg = $this->conversations->appendMessage($conversation, [
            'sender_type' => 'agent',
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'content' => $content,
        ])['message'];

        $this->telegram->forwardAgentReply($conversation, $msg);
        ActivityLogger::log('message.replied', $logText, $msg, ['conversation_id' => $conversation->id, 'message_id' => $msg->id]);

        return $msg;
    }
}
