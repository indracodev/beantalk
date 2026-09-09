<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\Request;

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
        $query = Conversation::with(['visitor', 'contact', 'project', 'assignedUser'])
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
                        'customer_email'       => $conv->contact ? $conv->contact->email : null,
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
                        'name'    => $conversation->contact ? $conversation->contact->name : 'Jane Doe (Visitor)',
                        'email'   => $conversation->contact ? $conversation->contact->email : 'visitor@browser.local',
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
}
