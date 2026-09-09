<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Initializes or resumes a chat session for a website visitor
     * POST /api/v1/client/session/init
     */
    public function init(Request $request)
    {
        $request->validate([
            'visitor_uuid' => 'nullable|string|max:36',
            'page_url'     => 'nullable|string|max:500',
            'page_title'   => 'nullable|string|max:255',
        ]);

        /** @var Project $project */
        $project = $request->attributes->get('project');

        // 1. Dapatkan atau generate visitor UUID lokal
        $visitorUuid = $request->input('visitor_uuid') ?: (string) Str::uuid();

        // 2. Resolve or create visitor entity
        $visitor = $this->conversationService->getOrCreateVisitor(
            $project,
            $visitorUuid,
            $request->ip(),
            $request->userAgent()
        );

        // 3. Resolve or create active conversation
        $conversation = $this->conversationService->getOrCreateConversation($project, $visitor, [
            'page_url'   => $request->input('page_url'),
            'page_title' => $request->input('page_title'),
        ]);

        // 4. Widget settings (Warna core & teks)
        $widgetSetting = $project->widgetSetting;

        return response()->json([
            'success' => true,
            'data' => [
                'visitor' => [
                    'uuid' => $visitor->visitor_uuid,
                ],
                'conversation' => [
                    'id'                  => $conversation->id,
                    'status'              => $conversation->status,
                    'last_message_at'     => $conversation->last_message_at ? $conversation->last_message_at->toIso8601String() : null,
                    'unread_visitor_count'=> $conversation->unread_visitor_count,
                ],
                'project' => [
                    'id'   => $project->id,
                    'name' => $project->name,
                ],
                'widget' => [
                    'primary_color'     => $widgetSetting ? $widgetSetting->primary_color : '#1E1E1E',
                    'accent_color'      => $widgetSetting ? $widgetSetting->accent_color : '#FFFFFF',
                    'position'          => $widgetSetting ? $widgetSetting->position : 'bottom-right',
                    'greeting_title'    => $widgetSetting ? $widgetSetting->greeting_title : 'Hallo!',
                    'greeting_subtitle' => $widgetSetting ? $widgetSetting->greeting_subtitle : 'Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
                    'is_online'         => $widgetSetting ? $widgetSetting->is_online : true,
                ]
            ]
        ]);
    }
}
