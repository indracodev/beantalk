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

    public function __construct(
        ConversationService $conversationService,
        PollingService $pollingService
    ) {
        $this->conversationService = $conversationService;
        $this->pollingService = $pollingService;
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

        return response()->json([
            'success' => true,
            'data' => [
                'id'                => $msg->id,
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
