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
            'name'         => 'nullable|string|max:100',
            'page_url'     => 'nullable|string|max:500',
            'page_title'   => 'nullable|string|max:255',
        ]);

        /** @var Project $project */
        $project = $request->attributes->get('project');

        // 1. Dapatkan atau generate visitor UUID lokal
        $visitorUuid = $request->input('visitor_uuid') ?: (string) Str::uuid();

        // 2. Resolve or create visitor entity with customer identity
        $visitor = $this->conversationService->getOrCreateVisitor(
            $project,
            $visitorUuid,
            $request->ip(),
            $request->userAgent(),
            $request->input('name')
        );

        // 3. Resolve active conversation ONLY if one already exists with messages
        $conversation = $this->conversationService->getActiveConversation($project, $visitor);

        // 4. Widget settings (Warna core & teks)
        $widgetSetting = $project->widgetSetting;

        $conversationData = null;
        if ($conversation) {
            $conversationData = [
                'id'                  => $conversation->id,
                'status'              => $conversation->status,
                'channel'             => $conversation->channel,
                'channel_label'       => $conversation->channel_label,
                'last_message_at'     => $conversation->last_message_at ? $conversation->last_message_at->toIso8601String() : null,
                'unread_visitor_count'=> $conversation->unread_visitor_count,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'visitor' => [
                    'uuid'          => $visitor->visitor_uuid,
                    'name'          => $visitor->name,
                    'customer_code' => $visitor->customer_code_formatted,
                    'display_name'  => $visitor->display_name,
                ],
                'conversation' => $conversationData,
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
                    'support_title'     => $widgetSetting ? ($widgetSetting->support_title ?: 'Customer Support') : 'Customer Support',
                    'is_online'         => $widgetSetting ? $widgetSetting->is_online : true,
                    'find_us_title'     => $widgetSetting ? ($widgetSetting->find_us_title ?: 'Reach Us Anywhere Else') : 'Reach Us Anywhere Else',
                    'social_channels'   => $widgetSetting && is_array($widgetSetting->social_channels) ? $widgetSetting->social_channels : [],
                ]
            ]
        ]);
    }

    /**
     * Updates visitor profile name from the web chat widget
     * POST /api/v1/client/session/profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'visitor_uuid' => 'required|string|max:36',
            'name'         => 'required|string|max:100',
        ]);

        /** @var Project $project */
        $project = $request->attributes->get('project');

        $visitor = \App\Models\Visitor::where('project_id', $project->id)
            ->where('visitor_uuid', $request->input('visitor_uuid'))
            ->first();

        if (!$visitor) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VISITOR_NOT_FOUND',
                    'message' => 'Data pengunjung tidak ditemukan untuk project ini.',
                ]
            ], 404);
        }

        $cleanName = strip_tags(trim($request->input('name')));
        $visitor->update(['name' => $cleanName]);

        return response()->json([
            'success' => true,
            'data' => [
                'visitor' => [
                    'uuid'          => $visitor->visitor_uuid,
                    'name'          => $visitor->name,
                    'customer_code' => $visitor->customer_code_formatted,
                    'display_name'  => $visitor->display_name,
                ]
            ]
        ]);
    }
}
