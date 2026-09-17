<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ChatTranscriptMail;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ConversationController extends Controller
{
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Lists inbox conversations for CS agents
     * GET /api/v1/admin/conversations?project_id=1&status=open
     */
    public function index(Request $request)
    {
        $query = Conversation::whereHas('messages')
            ->with(['visitor', 'contact', 'project', 'assignedUser'])
            ->orderBy('last_message_at', 'desc');

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $conversations = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => $conversations->map(function ($conv) {
                    return [
                        'id'                   => $conv->id,
                        'project_name'         => $conv->project ? $conv->project->name : 'Unknown',
                        'customer_name'        => $conv->contact ? $conv->contact->name : 'Visitor ' . substr($conv->visitor->visitor_uuid ?? '0000', 0, 8),
                        'customer_email'       => $conv->visitor ? $conv->visitor->email : ($conv->contact ? $conv->contact->email : null),
                        'page_url'             => $conv->page_url,
                        'page_title'           => $conv->page_title,
                        'status'               => $conv->status,
                        'unread_agent_count'   => $conv->unread_agent_count,
                        'last_message_preview' => $conv->last_message_preview,
                        'last_message_at'      => $conv->last_message_at ? $conv->last_message_at->toIso8601String() : null,
                        'assigned_agent'       => $conv->assignedUser ? $conv->assignedUser->name : 'Belum Ditugaskan',
                    ];
                }),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'total_pages'  => $conversations->lastPage(),
                    'total_items'  => $conversations->total(),
                ]
            ]
        ]);
    }

    /**
     * Shows conversation thread and customer context
     * GET /api/v1/admin/conversations/{id}
     */
    public function show(Request $request, $id)
    {
        $conversation = Conversation::with(['visitor', 'contact', 'project', 'assignedUser', 'messages'])
            ->findOrFail($id);

        // Tandai sudah dibaca oleh CS agen
        if ($conversation->unread_agent_count > 0) {
            $conversation->update(['unread_agent_count' => 0]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => [
                    'id'              => $conversation->id,
                    'status'          => $conversation->status,
                    'project'         => [
                        'id'   => $conversation->project->id,
                        'name' => $conversation->project->name,
                    ],
                    'customer'        => [
                        'name'    => $conversation->visitor ? $conversation->visitor->display_name : ($conversation->contact ? $conversation->contact->name : 'Visitor'),
                        'email'   => $conversation->visitor ? $conversation->visitor->email : ($conversation->contact ? $conversation->contact->email : null),
                        'ip'      => $conversation->visitor ? $conversation->visitor->ip_address : null,
                        'source'  => $conversation->page_url,
                        'product' => $conversation->page_title,
                    ],
                    'messages'        => $conversation->messages->map(function ($msg) {
                        return [
                            'id'           => $msg->id,
                            'sender_type'  => $msg->sender_type,
                            'sender_name'  => $msg->sender_name,
                            'content'      => $msg->content,
                            'content_type' => $msg->content_type,
                            'metadata'     => $msg->metadata,
                            'status'       => $msg->status,
                            'created_at'   => $msg->created_at ? $msg->created_at->toIso8601String() : null,
                        ];
                    }),
                ]
            ]
        ]);
    }

    /**
     * Sends an agent reply to the conversation
     * POST /api/v1/admin/conversations/{id}/reply
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $conversation = Conversation::findOrFail($id);

        $agent = $request->user();
        $agentName = $agent ? $agent->name : 'Sarah (Support)';
        $agentId = $agent ? $agent->id : null;

        $result = $this->conversationService->appendMessage($conversation, [
            'sender_type' => 'agent',
            'sender_id'   => $agentId,
            'sender_name' => $agentName,
            'content'     => $request->input('content'),
        ]);

        $msg = $result['message'];

        \App\Services\ActivityLogger::log(
            'message.replied',
            "Membalas pesan di percakapan #{$conversation->id} ke pengunjung",
            $msg,
            ['conversation_id' => $conversation->id, 'message_id' => $msg->id, 'preview' => substr($msg->content, 0, 100)]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id'          => $msg->id,
                'sender_type' => $msg->sender_type,
                'sender_name' => $msg->sender_name,
                'content'     => $msg->content,
                'created_at'  => $msg->created_at ? $msg->created_at->toIso8601String() : null,
            ]
        ], 201);
    }

    /**
     * Closes/resolves a conversation from admin inbox.
     * Auto-sends chat transcript to visitor email if available.
     * POST /api/v1/admin/conversations/{id}/close
     */
    public function close(Request $request, $id)
    {
        $conversation = Conversation::with(['visitor', 'project'])->findOrFail($id);

        if ($conversation->status === 'closed') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code'    => 'ALREADY_CLOSED',
                    'message' => 'Percakapan sudah ditutup.',
                ]
            ], 422);
        }

        $conversation->update(['status' => 'closed']);

        try {
            $telegramService = app(\App\Services\TelegramService::class);
            $telegramService->closeForumTopic($conversation);
        } catch (\Throwable $e) {}

        // Auto-send transcript to visitor email
        $emailSent = $this->sendTranscriptEmail($conversation);

        \App\Services\ActivityLogger::log(
            'conversation.closed',
            "CS menutup percakapan #{$conversation->id}" . ($emailSent ? ' (transkrip email terkirim)' : ''),
            $conversation,
            ['status' => 'closed', 'transcript_emailed' => $emailSent]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id'                 => $conversation->id,
                'status'             => 'closed',
                'transcript_emailed' => $emailSent,
            ]
        ]);
    }

    /**
     * Manually sends chat transcript to visitor email.
     * POST /api/v1/admin/conversations/{id}/email-transcript
     */
    public function emailTranscript(Request $request, $id)
    {
        $conversation = Conversation::with(['visitor', 'project'])->findOrFail($id);

        $emailSent = $this->sendTranscriptEmail($conversation);

        if (!$emailSent) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code'    => 'NO_EMAIL',
                    'message' => 'Pengunjung belum memiliki email. Riwayat chat tidak dapat dikirim.',
                ]
            ], 422);
        }

        \App\Services\ActivityLogger::log(
            'transcript.emailed',
            "Mengirim riwayat chat percakapan #{$conversation->id} ke email pengunjung",
            $conversation,
            ['conversation_id' => $conversation->id, 'email' => $conversation->visitor->email ?? null]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Riwayat chat berhasil dikirim ke email pengunjung.',
            ]
        ]);
    }

    /**
     * Internal helper: sends transcript email to visitor.
     * Returns true if email was sent, false if no email available.
     */
    private function sendTranscriptEmail(Conversation $conversation): bool
    {
        $visitor = $conversation->visitor;
        $email = $visitor ? $visitor->email : null;

        if (!$email) {
            // Try from contact
            $contact = $conversation->contact;
            $email = $contact ? $contact->email : null;
        }

        if (!$email) {
            return false;
        }

        $projectName = $conversation->project ? $conversation->project->name : 'BeanTalk';
        $visitorName = $visitor ? ($visitor->name ?: $visitor->display_name) : 'Pengunjung';

        try {
            Mail::to($email)->send(new ChatTranscriptMail($conversation, $projectName, $visitorName));
            return true;
        } catch (\Throwable $e) {
            Log::warning("[ChatTranscript] Gagal mengirim email transkrip: {$e->getMessage()}", [
                'conversation_id' => $conversation->id,
                'email' => $email,
            ]);
            return false;
        }
    }
}
