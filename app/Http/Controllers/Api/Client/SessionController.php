<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\BusinessHoursService;
use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    protected $conversationService;
    protected $businessHoursService;

    public function __construct(ConversationService $conversationService, BusinessHoursService $businessHoursService)
    {
        $this->conversationService = $conversationService;
        $this->businessHoursService = $businessHoursService;
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
            'email'        => 'nullable|email|max:255',
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
            $request->input('name'),
            $request->input('email')
        );

        // 3. Resolve active conversation ONLY if one already exists with messages and is active
        $conversation = $this->conversationService->getActiveConversation($project, $visitor);

        // 3.5. Fetch all customer conversations/tickets (open & closed)
        $pastConversations = $this->conversationService->getVisitorConversations($project, $visitor);
        $conversationsData = $pastConversations->map(function ($c) {
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
        });

        // 4. Widget settings (Warna core & teks)
        $widgetSetting = $project->widgetSetting;

        $conversationData = null;
        if ($conversation) {
            $recentMessages = $conversation->messages()
                ->orderBy('id', 'asc')
                ->take(100)
                ->get()
                ->map(function ($msg) {
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
                });

            $conversationData = [
                'id'                  => $conversation->id,
                'status'              => $conversation->status,
                'channel'             => $conversation->channel,
                'channel_label'       => $conversation->channel_label,
                'last_message_at'     => $conversation->last_message_at ? $conversation->last_message_at->toIso8601String() : null,
                'unread_visitor_count'=> $conversation->unread_visitor_count,
                'messages'            => $recentMessages,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'visitor' => [
                    'uuid'          => $visitor->visitor_uuid,
                    'name'          => $visitor->name,
                    'email'         => $visitor->email,
                    'customer_code' => $visitor->customer_code_formatted,
                    'display_name'  => $visitor->display_name,
                ],
                'conversation'  => $conversationData,
                'conversations' => $conversationsData,
                'project' => [
                    'id'   => $project->id,
                    'name' => $project->name,
                ],
                'widget' => [
                    'language'          => $widgetSetting ? ($widgetSetting->language ?: 'id') : 'id',
                    'primary_color'     => $widgetSetting ? $widgetSetting->primary_color : '#1E1E1E',
                    'accent_color'      => $widgetSetting ? $widgetSetting->accent_color : '#FFFFFF',
                    'position'          => $widgetSetting ? $widgetSetting->position : 'bottom-right',
                    'greeting_title'    => $widgetSetting ? $widgetSetting->greeting_title : 'Hallo!',
                    'greeting_subtitle' => $widgetSetting ? $widgetSetting->greeting_subtitle : 'Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
                    'support_title'     => $widgetSetting ? ($widgetSetting->support_title ?: 'Customer Support') : 'Customer Support',
                    'is_online'         => $widgetSetting ? $widgetSetting->is_online : true,
                    'find_us_title'       => $widgetSetting ? ($widgetSetting->find_us_title ?: 'Reach Us Anywhere Else') : 'Reach Us Anywhere Else',
                    'social_channels'     => $widgetSetting && is_array($widgetSetting->social_channels) ? $widgetSetting->social_channels : [],
                    'bot_enabled'         => $widgetSetting ? (bool) $widgetSetting->bot_enabled : false,
                    'bot_name'            => $widgetSetting ? $widgetSetting->bot_name : 'BeanBot',
                    'bot_welcome_message' => $widgetSetting ? $widgetSetting->bot_welcome_message : null,
                    'bot_mode_query'      => $widgetSetting ? (bool) $widgetSetting->bot_mode_query : true,
                    'bot_mode_options'    => $widgetSetting ? (bool) $widgetSetting->bot_mode_options : true,
                    'bot_welcome_options' => $widgetSetting ? $widgetSetting->bot_welcome_options : null,
                    'sound_enabled'       => $widgetSetting ? (bool) ($widgetSetting->widget_sound_enabled ?? true) : true,
                    'sound_type'          => $widgetSetting ? ($widgetSetting->widget_sound_type ?: 'chime') : 'chime',
                    'sound_custom_url'    => $widgetSetting ? $widgetSetting->widget_sound_custom_url : null,
                ],
                'business_hours' => $widgetSetting
                    ? $this->businessHoursService->getScheduleSummary($widgetSetting)
                    : ['enabled' => false],
                'is_within_business_hours' => $widgetSetting
                    ? $this->businessHoursService->isWithinBusinessHours($widgetSetting)
                    : true,
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
            'name'         => 'nullable|string|max:100',
            'email'        => 'nullable|email|max:255',
        ]);

        if (!$request->filled('name') && !$request->filled('email')) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Setidaknya nama atau email harus diisi.',
                ]
            ], 422);
        }

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

        $updates = [];
        if ($request->filled('name')) {
            $updates['name'] = strip_tags(trim($request->input('name')));
        }
        if ($request->filled('email')) {
            $updates['email'] = strtolower(trim($request->input('email')));
        }
        $visitor->update($updates);

        // Sync email to linked contact if exists
        if (isset($updates['email']) && $visitor->contact_id && $visitor->contact) {
            $visitor->contact->update(['email' => $updates['email']]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'visitor' => [
                    'uuid'          => $visitor->visitor_uuid,
                    'name'          => $visitor->name,
                    'email'         => $visitor->email,
                    'customer_code' => $visitor->customer_code_formatted,
                    'display_name'  => $visitor->display_name,
                ]
            ]
        ]);
    }
}
