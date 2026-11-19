<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\Client\Concerns\ResolvesVisitorConversation;
use App\Http\Controllers\Controller;
use App\Mail\ChatTranscriptMail;
use App\Models\Message;
use App\Models\Visitor;
use App\Services\ActivityLogger;
use App\Services\BotService;
use App\Services\ConversationService;
use App\Services\PollingService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MessageController extends Controller
{
    use ResolvesVisitorConversation;

    private const ANONYMOUS_NAMES = ['pengunjung', 'visitor', 'anda', 'guest'];

    public function __construct(
        private ConversationService $conversations,
        private PollingService $polling,
        private BotService $bot,
        private TelegramService $telegram,
    ) {}

    /** GET /api/v1/client/conversations — daftar tiket milik pengunjung. */
    public function listConversations(Request $request): JsonResponse
    {
        $uuid = $this->visitorUuid($request);
        if (! $uuid) {
            return $this->fail('VISITOR_REQUIRED', 'visitor_uuid parameter is required.', 422);
        }

        $project = $this->project($request);
        $visitor = Visitor::where('project_id', $project->id)->where('visitor_uuid', $uuid)->first();

        $list = $visitor
            ? $this->conversations->getVisitorConversations($project, $visitor)->map(fn ($c) => $this->conversations->toWidgetSummary($c))
            : [];

        return $this->ok(['conversations' => $list]);
    }

    /** POST /api/v1/client/conversations/{id}/resolve — pengunjung menyelesaikan tiket. */
    public function resolve(Request $request, string $id): JsonResponse
    {
        $conversation = $this->ownedConversation($request, $id);
        if (! $conversation) {
            return $this->conversationNotFound();
        }

        $this->conversations->setStatus($conversation, 'closed');
        ChatTranscriptMail::sendTo($conversation);

        ActivityLogger::log(
            'conversation.status_updated',
            "Pengunjung menandai percakapan #{$conversation->id} selesai",
            $conversation,
            ['status' => 'closed', 'resolved_by' => 'customer'],
        );

        return $this->ok(['id' => $conversation->id, 'status' => 'closed']);
    }

    /** GET /api/v1/client/conversations/{id}/messages?after_id= — polling range scan. */
    public function index(Request $request, string $id): JsonResponse
    {
        $afterId = (int) $request->query('after_id', 0);

        // ponytail: masa transisi widget lama ter-cache (polling tanpa uuid) → daftar kosong. Jadikan 404 setelah ±2 minggu sejak deploy.
        if ($this->visitorUuid($request) === null) {
            return $this->ok(['messages' => [], 'last_id' => $afterId]);
        }

        $conversation = $this->ownedConversation($request, $id);
        if (! $conversation) {
            return $this->conversationNotFound();
        }

        $messages = $this->polling->pollMessages($conversation, $afterId, min((int) $request->query('limit', 50), 100));

        if ($conversation->unread_visitor_count > 0 && $messages->isNotEmpty()) {
            $conversation->update(['unread_visitor_count' => 0]);
        }

        return $this->ok([
            'messages' => $messages->map(fn (Message $m) => $m->toWidgetArray()),
            'last_id' => $messages->last()?->id ?? $afterId,
        ]);
    }

    /** POST /api/v1/client/conversations/{id}/messages — kirim pesan (idempoten). */
    public function store(Request $request, string $id): JsonResponse
    {
        if (! $request->has('content') && $request->has('message')) {
            $request->merge(['content' => $request->input('message')]);
        }

        $data = $request->validate([
            'content' => 'required|string|max:5000',
            'client_message_id' => 'nullable|string|max:64',
            'content_type' => 'nullable|string|in:text,image,file,card',
            'metadata' => 'nullable|array',
            'sender_name' => 'nullable|string|max:100',
            'page_url' => 'nullable|string|max:500',
            'page_title' => 'nullable|string|max:255',
        ]);

        $project = $this->project($request);
        $conversation = null;

        if (is_numeric($id) && (int) $id > 0) {
            $conversation = $this->ownedConversation($request, $id);
            if (! $conversation) {
                return $this->conversationNotFound();
            }
            if ($conversation->status === 'closed') {
                return $this->fail('CONVERSATION_CLOSED', 'Tiket percakapan ini telah selesai. Silakan buat tiket baru.', 422);
            }
        }

        // Pesan pertama dari widget: buat visitor + percakapan on-demand.
        if (! $conversation) {
            $uuid = $request->input('visitor_uuid');
            if (! is_string($uuid) || $uuid === '') {
                return $this->fail('CONVERSATION_NOT_FOUND', 'Percakapan tidak ditemukan untuk project ini.', 404);
            }
            $visitor = $this->conversations->getOrCreateVisitor($project, $uuid, $request->ip(), $request->userAgent(), $data['sender_name'] ?? null);
            $conversation = $this->conversations->getOrCreateConversation($project, $visitor, [
                'page_url' => $data['page_url'] ?? null,
                'page_title' => $data['page_title'] ?? null,
            ]);
        }

        $result = $this->conversations->appendMessage($conversation, [
            'sender_type' => 'visitor',
            'sender_name' => $this->resolveSenderName($conversation->visitor, $data['sender_name'] ?? ''),
            'content' => $data['content'],
            'client_message_id' => $data['client_message_id'] ?? null,
            'content_type' => $data['content_type'] ?? 'text',
            'metadata' => $data['metadata'] ?? null,
        ]);

        $msg = $result['message'];
        if (! $result['is_duplicate']) {
            $this->afterVisitorMessage($conversation, $msg);
        }

        return $this->ok(
            $msg->toWidgetArray() + ['conversation_id' => $conversation->id, 'is_duplicate' => $result['is_duplicate']],
            $result['is_duplicate'] ? 200 : 201,
        );
    }

    /** Nama pengirim: input eksplisit (bukan "Pengunjung" dsb.) atau nama tersimpan. */
    private function resolveSenderName(Visitor $visitor, string $input): string
    {
        $name = strip_tags(trim($input));
        if ($name !== '' && ! in_array(strtolower($name), self::ANONYMOUS_NAMES, true)) {
            if (empty($visitor->name)) {
                $visitor->update(['name' => $name]);
            }

            return $name;
        }

        return $visitor->name ?: $visitor->display_name;
    }

    /** Bot auto-responder + sinkron Telegram; kegagalan integrasi tidak menggagalkan kirim pesan. */
    private function afterVisitorMessage($conversation, Message $msg): void
    {
        try {
            $this->bot->processInboundMessage($conversation, $msg);
        } catch (Throwable $e) {
            Log::warning('[BotService] '.$e->getMessage());
        }

        try {
            $this->telegram->sendNewTicketAlert($conversation, $msg);
            $this->telegram->forwardVisitorMessage($conversation, $msg);
        } catch (Throwable $e) {
            Log::warning('[TelegramService] '.$e->getMessage());
        }
    }
}
