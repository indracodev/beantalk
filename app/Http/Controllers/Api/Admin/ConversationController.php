<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ChatTranscriptMail;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ActivityLogger;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Inbox CS via REST (tenant di-scope oleh middleware tenant.scope + global scope). */
class ConversationController extends Controller
{
    public function __construct(private ConversationService $conversations) {}

    /** GET /api/v1/admin/conversations?project_id=&status= */
    public function index(Request $request): JsonResponse
    {
        $page = Conversation::where('tenant_id', $request->user()->tenant_id)
            ->whereHas('messages')
            ->with(['visitor', 'contact', 'project', 'assignedUser'])
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return $this->ok([
            'conversations' => $page->getCollection()->map(fn (Conversation $c) => [
                'id' => $c->id,
                'project_name' => $c->project?->name ?? 'Unknown',
                'customer_name' => $c->contact?->name ?? 'Visitor '.substr($c->visitor?->visitor_uuid ?? '0000', 0, 8),
                'customer_email' => $c->visitor?->email ?? $c->contact?->email,
                'page_url' => $c->page_url,
                'page_title' => $c->page_title,
                'status' => $c->status,
                'unread_agent_count' => $c->unread_agent_count,
                'last_message_preview' => $c->last_message_preview,
                'last_message_at' => $c->last_message_at?->toIso8601String(),
                'assigned_agent' => $c->assignedUser?->name ?? 'Belum Ditugaskan',
            ]),
            'pagination' => [
                'current_page' => $page->currentPage(),
                'total_pages' => $page->lastPage(),
                'total_items' => $page->total(),
            ],
        ]);
    }

    /** GET /api/v1/admin/conversations/{id} — thread + konteks pelanggan (tandai dibaca). */
    public function show(Request $request, string $id): JsonResponse
    {
        $c = $this->find($request, $id)->load(['visitor', 'contact', 'project', 'messages']);

        if ($c->unread_agent_count > 0) {
            $c->update(['unread_agent_count' => 0]);
        }

        return $this->ok(['conversation' => [
            'id' => $c->id,
            'status' => $c->status,
            'project' => ['id' => $c->project->id, 'name' => $c->project->name],
            'customer' => [
                'name' => $c->visitor?->display_name ?? $c->contact?->name ?? 'Visitor',
                'email' => $c->visitor?->email ?? $c->contact?->email,
                'ip' => $c->visitor?->ip_address,
                'source' => $c->page_url,
                'product' => $c->page_title,
            ],
            'messages' => $c->messages->map(fn (Message $m) => $m->toWidgetArray()),
        ]]);
    }

    /** POST /api/v1/admin/conversations/{id}/reply */
    public function reply(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['content' => 'required|string|max:5000']);
        $conversation = $this->find($request, $id);
        $agent = $request->user();

        $msg = $this->conversations->appendMessage($conversation, [
            'sender_type' => 'agent',
            'sender_id' => $agent->id,
            'sender_name' => $agent->name,
            'content' => $data['content'],
        ])['message'];

        ActivityLogger::log('message.replied', "Membalas pesan di percakapan #{$conversation->id} ke pengunjung", $msg, [
            'conversation_id' => $conversation->id,
            'message_id' => $msg->id,
            'preview' => mb_substr($msg->content, 0, 100),
        ]);

        return $this->ok($msg->only(['id', 'sender_type', 'sender_name', 'content']) + [
            'created_at' => $msg->created_at?->toIso8601String(),
        ], 201);
    }

    /** POST /api/v1/admin/conversations/{id}/close — tutup + kirim transkrip otomatis. */
    public function close(Request $request, string $id): JsonResponse
    {
        $conversation = $this->find($request, $id);
        if ($conversation->status === 'closed') {
            return $this->fail('ALREADY_CLOSED', 'Percakapan sudah ditutup.', 422);
        }

        $this->conversations->setStatus($conversation, 'closed');
        $emailed = ChatTranscriptMail::sendTo($conversation);

        ActivityLogger::log(
            'conversation.closed',
            "CS menutup percakapan #{$conversation->id}".($emailed ? ' (transkrip email terkirim)' : ''),
            $conversation,
            ['status' => 'closed', 'transcript_emailed' => $emailed],
        );

        return $this->ok(['id' => $conversation->id, 'status' => 'closed', 'transcript_emailed' => $emailed]);
    }

    /** POST /api/v1/admin/conversations/{id}/email-transcript */
    public function emailTranscript(Request $request, string $id): JsonResponse
    {
        $conversation = $this->find($request, $id);

        if (! ChatTranscriptMail::sendTo($conversation)) {
            return $this->fail('NO_EMAIL', 'Pengunjung belum memiliki email. Riwayat chat tidak dapat dikirim.', 422);
        }

        ActivityLogger::log('transcript.emailed', "Mengirim riwayat chat percakapan #{$conversation->id} ke email pengunjung", $conversation, [
            'conversation_id' => $conversation->id,
            'email' => $conversation->visitor?->email,
        ]);

        return $this->ok(['message' => 'Riwayat chat berhasil dikirim ke email pengunjung.']);
    }

    /** Percakapan dalam tenant staf (404 untuk tenant lain), tidak hanya mengandalkan global scope. */
    private function find(Request $request, string $id): Conversation
    {
        return Conversation::where('tenant_id', $request->user()->tenant_id)->findOrFail($id);
    }
}
