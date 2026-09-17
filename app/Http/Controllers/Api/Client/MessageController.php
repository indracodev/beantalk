<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Project;
use App\Services\ConversationService;
use App\Services\PollingService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $conversationService;
    protected $pollingService;
    protected $botService;

    public function __construct(
        ConversationService $conversationService,
        PollingService $pollingService,
        \App\Services\BotService $botService
    ) {
        $this->conversationService = $conversationService;
        $this->pollingService = $pollingService;
        $this->botService = $botService;
    }

    /**
     * Lists conversation tickets for a visitor
     * GET /api/v1/client/conversations
     */
    public function listConversations(Request $request)
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        $visitorUuid = $request->input('visitor_uuid') ?: $request->header('X-Visitor-Uuid');
        if (!$visitorUuid) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VISITOR_REQUIRED',
                    'message' => 'visitor_uuid parameter is required.'
                ]
            ], 422);
        }

        $visitor = \App\Models\Visitor::where('project_id', $project->id)
            ->where('visitor_uuid', $visitorUuid)
            ->first();

        if (!$visitor) {
            return response()->json([
                'success' => true,
                'data' => [
                    'conversations' => []
                ]
            ]);
        }

        $conversations = $this->conversationService->getVisitorConversations($project, $visitor);

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => $conversations->map(function ($c) {
                    return [
                        'id'                   => $c->id,
                        'status'               => $c->status,
                        'channel'              => $c->channel,
                        'channel_label'        => $c->channel_label,
                        'last_message_preview' => $c->last_message_preview ?? ($c->latestMessage ? mb_substr(strip_tags($c->latestMessage->content), 0, 75) : null),
                        'last_message_at'      => $c->last_message_at ? $c->last_message_at->toIso8601String() : ($c->created_at ? $c->created_at->toIso8601String() : null),
                        'unread_visitor_count' => (int) $c->unread_visitor_count,
                        'created_at'           => $c->created_at ? $c->created_at->toIso8601String() : null,
                    ];
                })
            ]
        ]);
    }

    /**
     * Customer resolves conversation directly from widget
     * POST /api/v1/client/conversations/{id}/resolve
     */
    public function resolve(Request $request, $conversationId)
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        $conversation = Conversation::where('project_id', $project->id)
            ->where('id', $conversationId)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CONVERSATION_NOT_FOUND',
                    'message' => 'Percakapan tidak ditemukan.'
                ]
            ], 404);
        }

        // Optional verify visitor ownership if visitor_uuid provided
        $visitorUuid = $request->input('visitor_uuid') ?: $request->header('X-Visitor-Uuid');
        if ($visitorUuid && $conversation->visitor && $conversation->visitor->visitor_uuid !== $visitorUuid) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Anda tidak memiliki akses ke percakapan ini.'
                ]
            ], 403);
        }

        $conversation->update(['status' => 'closed']);

        try {
            $telegramService = app(\App\Services\TelegramService::class);
            $telegramService->closeForumTopic($conversation);
        } catch (\Throwable $e) {}

        // Auto-send transcript email to visitor
        $conversation->load(['visitor', 'project']);
        $visitor = $conversation->visitor;
        $visitorEmail = $visitor ? $visitor->email : null;
        if ($visitorEmail) {
            try {
                $projectName = $conversation->project ? $conversation->project->name : 'BeanTalk';
                $visitorName = $visitor->name ?: ($visitor->display_name ?? 'Pengunjung');
                \Illuminate\Support\Facades\Mail::to($visitorEmail)->send(
                    new \App\Mail\ChatTranscriptMail($conversation, $projectName, $visitorName)
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[ChatTranscript] Self-resolve email gagal: {$e->getMessage()}");
            }
        }

        \App\Services\ActivityLogger::log(
            'conversation.status_updated',
            "Pengunjung menandai percakapan #{$conversation->id} selesai",
            $conversation,
            ['status' => 'closed', 'resolved_by' => 'customer']
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id'     => $conversation->id,
                'status' => 'closed',
            ]
        ]);
    }

    /**
     * Polls messages with index scan: WHERE conversation_id = ? AND id > ?
     * GET /api/v1/client/conversations/{id}/messages?after_id=123
     */
    public function index(Request $request, $conversationId)
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        // Pastikan percakapan milik project ini
        $conversation = Conversation::where('project_id', $project->id)
            ->where('id', $conversationId)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CONVERSATION_NOT_FOUND',
                    'message' => 'Percakapan tidak ditemukan untuk project ini.',
                ]
            ], 404);
        }

        $afterId = (int) $request->query('after_id', 0);
        $limit = min((int) $request->query('limit', 50), 100);

        $messages = $this->pollingService->pollMessages($conversation, $afterId, $limit);

        // Jika visitor menarik pesan, reset unread_visitor_count
        if ($conversation->unread_visitor_count > 0 && $messages->isNotEmpty()) {
            $conversation->update(['unread_visitor_count' => 0]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages->map(function ($msg) {
                    return [
                        'id'                => $msg->id,
                        'client_message_id' => $msg->client_message_id,
                        'sender_type'       => $msg->sender_type,
                        'sender_name'       => $msg->sender_name,
                        'content'           => $msg->content,
                        'content_type'      => $msg->content_type,
                        'metadata'          => $msg->metadata,
                        'status'            => $msg->status,
                        'created_at'        => $msg->created_at ? $msg->created_at->toIso8601String() : null,
                    ];
                }),
                'last_id' => $messages->isNotEmpty() ? $messages->last()->id : $afterId,
            ]
        ]);
    }

    /**
     * Appends a message from the visitor with idempotency guarantee
     * POST /api/v1/client/conversations/{id}/messages
     */
    public function store(Request $request, $conversationId)
    {
        if (!$request->has('content') && $request->has('message')) {
            $request->merge(['content' => $request->input('message')]);
        }

        $request->validate([
            'content'           => 'required|string|max:5000',
            'client_message_id' => 'nullable|string|max:64',
            'content_type'      => 'nullable|string|in:text,image,file,card',
            'metadata'          => 'nullable|array',
        ]);

        /** @var Project $project */
        $project = $request->attributes->get('project');

        $conversation = null;
        if (!empty($conversationId) && is_numeric($conversationId) && (int)$conversationId > 0) {
            $conversation = Conversation::where('project_id', $project->id)
                ->where('id', $conversationId)
                ->first();
        }

        // Jika percakapan lama sudah berstatus 'closed', jangan izinkan kirim ke tiket lama ini
        if ($conversation && $conversation->status === 'closed') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CONVERSATION_CLOSED',
                    'message' => 'Tiket percakapan ini telah selesai. Silakan buat tiket baru.',
                ]
            ], 422);
        }

        // Jika conversation belum ada (misal pesan pertama dari widget), resolve/create on demand!
        if (!$conversation) {
            $visitorUuid = $request->input('visitor_uuid');
            if ($visitorUuid) {
                $visitor = $this->conversationService->getOrCreateVisitor(
                    $project,
                    $visitorUuid,
                    $request->ip(),
                    $request->userAgent(),
                    $request->input('sender_name')
                );
            } else {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CONVERSATION_NOT_FOUND',
                        'message' => 'Percakapan tidak ditemukan untuk project ini.',
                    ]
                ], 404);
            }

            $conversation = $this->conversationService->getOrCreateConversation($project, $visitor, [
                'page_url'   => $request->input('page_url'),
                'page_title' => $request->input('page_title'),
            ]);
        }

        $rawSenderName = strip_tags(trim($request->input('sender_name', '')));
        if ($rawSenderName && !in_array(strtolower($rawSenderName), ['pengunjung', 'visitor', 'anda', 'guest'])) {
            if ($conversation->visitor && empty($conversation->visitor->name)) {
                $conversation->visitor->update(['name' => $rawSenderName]);
            }
            $finalSenderName = $rawSenderName;
        } else {
            $finalSenderName = $conversation->visitor->name ?: ($conversation->visitor->display_name ?? 'Tamu');
        }

        $result = $this->conversationService->appendMessage($conversation, [
            'sender_type'       => 'visitor',
            'sender_name'       => $finalSenderName,
            'content'           => $request->input('content'),
            'client_message_id' => $request->input('client_message_id'),
            'content_type'      => $request->input('content_type', 'text'),
            'metadata'          => $request->input('metadata'),
        ]);

        $msg = $result['message'];

        // Trigger Bot Auto-Responder & Telegram Sync jika pesan bukan duplikasi
        if (!$result['is_duplicate']) {
            try {
                $this->botService->processInboundMessage($conversation, $msg);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[BotService] Inbound processing warning: ' . $e->getMessage());
            }

            try {
                $telegramService = app(\App\Services\TelegramService::class);
                $telegramService->sendNewTicketAlert($conversation, $msg);
                $telegramService->forwardVisitorMessage($conversation, $msg);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[TelegramService] Message forward warning: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'                => $msg->id,
                'conversation_id'   => $conversation->id,
                'client_message_id' => $msg->client_message_id,
                'sender_type'       => $msg->sender_type,
                'sender_name'       => $msg->sender_name,
                'content'           => $msg->content,
                'content_type'      => $msg->content_type,
                'metadata'          => $msg->metadata,
                'created_at'        => $msg->created_at ? $msg->created_at->toIso8601String() : null,
                'is_duplicate'      => $result['is_duplicate'],
            ]
        ], $result['is_duplicate'] ? 200 : 201);
    }
}
