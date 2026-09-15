<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\ProjectDomain;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Services\ActivityLogger;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Executive Dashboard Overview
     * GET /admin or GET /admin/dashboard
     */
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $projects = Project::where('tenant_id', $tenantId)->orderBy('name', 'asc')->get();

        $totalUnreadConversations = Conversation::where('tenant_id', $tenantId)
            ->where('unread_agent_count', '>', 0)
            ->count();

        $period = $request->input('period', '7d');

        // Telemetry Data (Executive KPIs)
        $summary = [
            'period' => $period,
            'totalConversations' => [
                'value' => '1,482',
                'delta' => '+14.8%',
                'subtext' => 'vs 1,291 periode sebelumnya • 98.2% Terjawab'
            ],
            'medianFrt' => [
                'value' => '42s',
                'delta' => '-18s lebih cepat',
                'subtext' => 'Target SLA < 60s • 99.4% Tepat Waktu'
            ],
            'csat' => [
                'value' => '97.4%',
                'delta' => '4.92 / 5.0',
                'subtext' => 'Berdasarkan 824 ulasan • 581 Positif'
            ],
            'assistedRevenue' => [
                'value' => 'Rp 148.500.000',
                'delta' => '+22.1%',
                'subtext' => '134 konversi checkout • B2B + Retail'
            ],
        ];

        // 14 Days Time-Series Data (Inbound vs Resolved Velocity)
        $chartData = [
            ['date' => '28 Agu', 'label' => '28 Agu', 'inbound' => 65, 'resolved' => 58],
            ['date' => '29 Agu', 'label' => '29 Agu', 'inbound' => 78, 'resolved' => 70],
            ['date' => '30 Agu', 'label' => '30 Agu', 'inbound' => 72, 'resolved' => 68],
            ['date' => '31 Agu', 'label' => '31 Agu', 'inbound' => 95, 'resolved' => 88],
            ['date' => '01 Sep', 'label' => '01 Sep', 'inbound' => 110, 'resolved' => 102],
            ['date' => '02 Sep', 'label' => '02 Sep', 'inbound' => 88, 'resolved' => 82],
            ['date' => '03 Sep', 'label' => '03 Sep', 'inbound' => 125, 'resolved' => 118],
            ['date' => '04 Sep', 'label' => '04 Sep', 'inbound' => 140, 'resolved' => 132],
            ['date' => '05 Sep', 'label' => '05 Sep', 'inbound' => 112, 'resolved' => 106],
            ['date' => '06 Sep', 'label' => '06 Sep', 'inbound' => 155, 'resolved' => 148],
            ['date' => '07 Sep', 'label' => '07 Sep', 'inbound' => 168, 'resolved' => 160],
            ['date' => '08 Sep', 'label' => '08 Sep', 'inbound' => 135, 'resolved' => 130],
            ['date' => '09 Sep', 'label' => '09 Sep', 'inbound' => 182, 'resolved' => 176],
            ['date' => '10 Sep', 'label' => 'Hari ini', 'inbound' => 174, 'resolved' => 168],
        ];

        // Intent Distribution Breakdown
        $intentBreakdown = [
            [
                'label' => 'Product Consultation & Roast',
                'count' => 652,
                'percentage' => 44,
                'color' => '#0071E3',
                'bg' => 'bg-apple-blue',
            ],
            [
                'label' => 'B2B Wholesale & Export MOQ',
                'count' => 355,
                'percentage' => 24,
                'color' => '#6366F1',
                'bg' => 'bg-indigo-500',
            ],
            [
                'label' => 'Shipping & Pusat Logistics',
                'count' => 281,
                'percentage' => 19,
                'color' => '#F59E0B',
                'bg' => 'bg-amber-500',
            ],
            [
                'label' => 'Promo Vouchers & Payment',
                'count' => 194,
                'percentage' => 13,
                'color' => '#10B981',
                'bg' => 'bg-emerald-500',
            ],
        ];

        // Multi-Storefront Channels
        $channels = [
            [
                'name' => 'Supresso Coffee',
                'domain' => 'supresso.myshopify.com',
                'active_online' => 48,
                'chats_7d' => 684,
                'median_frt' => '34 detik',
                'conversion' => '14.2%',
                'revenue' => 'Rp 64.900.000',
                'status' => 'Aktif',
                'badge' => 'Shopify Online Store'
            ],
            [
                'name' => 'Indraco Store',
                'domain' => 'indracostore.com',
                'active_online' => 24,
                'chats_7d' => 412,
                'median_frt' => '48 detik',
                'conversion' => '11.8%',
                'revenue' => 'Rp 28.400.000',
                'status' => 'Aktif',
                'badge' => 'E-Commerce Portal'
            ],
            [
                'name' => 'Indraco Global B2B',
                'domain' => 'indracoglobal.com',
                'active_online' => 8,
                'chats_7d' => 248,
                'median_frt' => '52 detik',
                'conversion' => '28.5%',
                'revenue' => 'Rp 51.200.000 Leads',
                'status' => 'Aktif',
                'badge' => 'International B2B'
            ],
            [
                'name' => 'SDA Store Surabaya',
                'domain' => 'sdastore.id',
                'active_online' => 4,
                'chats_7d' => 138,
                'median_frt' => '41 detik',
                'conversion' => '9.4%',
                'revenue' => 'Rp 4.000.000',
                'status' => 'Aktif',
                'badge' => 'Regional Hub'
            ],
        ];

        // CS Specialist Leaderboard
        $specialists = [
            [
                'name' => 'Sarah',
                'role' => 'CS Specialist',
                'scope' => 'Supresso Shopify & Indraco Store',
                'resolved_count' => 548,
                'csat' => '★ 4.96',
                'sla' => '99.8%',
                'avatar_color' => 'bg-pink-100 text-pink-700',
                'initials' => 'SA',
            ],
            [
                'name' => 'Budi',
                'role' => 'B2B Specialist',
                'scope' => 'Indraco Global Export & Wholesale',
                'resolved_count' => 312,
                'csat' => '★ 4.88',
                'sla' => '98.4%',
                'avatar_color' => 'bg-blue-100 text-blue-700',
                'initials' => 'BU',
            ],
            [
                'name' => 'Hendri',
                'role' => 'Director / Escalation',
                'scope' => 'Executive & VIP Accounts',
                'resolved_count' => 42,
                'csat' => '★ 5.00',
                'sla' => '100%',
                'avatar_color' => 'bg-purple-100 text-purple-700',
                'initials' => 'HE',
            ],
        ];

        // High Intent Products
        $highIntentProducts = [
            [
                'title' => 'Supresso Sumatra Mandheling Capsule',
                'path' => '/products/sumatra-capsule',
                'meta' => 'Rp 95.000',
                'volume' => '248 inquiries',
                'result' => '18.4% Checkout Conversion',
                'icon' => '☕',
            ],
            [
                'title' => 'Sumatra Green Beans Grade 1 (20ft FCL)',
                'path' => '/export/green-beans',
                'meta' => 'Container Quote',
                'volume' => '82 inquiries',
                'result' => '32.9% Quotation Issued',
                'icon' => '🚢',
            ],
            [
                'title' => 'Kopi Jahe Kental 10 Sachet Family Pack',
                'path' => '/promo/kopi-jahe',
                'meta' => 'Rp 28.500',
                'volume' => '114 inquiries',
                'result' => '14.1% Checkout Conversion',
                'icon' => '📦',
            ],
        ];

        return view('admin.dashboard', compact(
            'projects',
            'totalUnreadConversations',
            'summary',
            'chartData',
            'intentBreakdown',
            'channels',
            'specialists',
            'highIntentProducts',
            'period'
        ));
    }

    /**
     * Master 3-Column Admin Inbox
     * GET /admin/inbox or GET /admin/inbox/{id}
     */
    public function inbox(Request $request, $id = null): View
    {
        $tenantId = $request->user()->tenant_id;

        // Ambil semua project milik tenant
        $projects = Project::where('tenant_id', $tenantId)->orderBy('name', 'asc')->get();

        // Query percakapan dengan filter (hanya tampilkan yang sudah memiliki pesan nyata, hindari tiket kosong)
        $query = Conversation::where('tenant_id', $tenantId)
            ->whereHas('messages')
            ->with(['visitor', 'project.widgetSetting', 'assignedUser', 'latestMessage'])
            ->orderBy('last_message_at', 'desc');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'mine') {
                $query->where('assigned_user_id', $request->user()->id);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($sub) use ($search) {
                $sub->whereHas('visitor', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('customer_code', 'like', "%{$search}%");
                })
                ->orWhereHas('contact', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhere('last_message_preview', 'like', "%{$search}%");
            });
        }

        $conversations = $query->get()->unique('visitor_id')->values();

        // Tentukan percakapan aktif yang sedang dibuka (reuse instance memori jika sudah dimuat)
        $activeConversation = null;
        if ($id) {
            $activeConversation = $conversations->firstWhere('id', (int) $id);
            if ($activeConversation) {
                $activeConversation->load(['messages.user']);
            } else {
                $activeConversation = Conversation::where('tenant_id', $tenantId)
                    ->with(['visitor', 'project.widgetSetting', 'assignedUser', 'messages.user'])
                    ->find($id);
            }
            // Tandai tiket aktif sudah dibaca HANYA saat agen secara eksplisit membuka ID chat tersebut
            if ($activeConversation && $activeConversation->unread_agent_count > 0) {
                $activeConversation->update(['unread_agent_count' => 0]);
            }
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
            $activeConversation->load(['messages.user']);
        }

        // Ambil daftar agen/staff untuk penugasan
        $staffMembers = User::where('tenant_id', $tenantId)->orderBy('name', 'asc')->get();

        // Statistik ringkas dalam 1 query agregasi tunggal (menghindari multiple roundtrip counts)
        $statusCounts = Conversation::where('tenant_id', $tenantId)
            ->whereHas('messages')
            ->selectRaw("
                COUNT(*) as total_all,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as total_open,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as total_closed,
                SUM(CASE WHEN assigned_user_id = ? THEN 1 ELSE 0 END) as total_mine
            ", [$request->user()->id])
            ->first();

        $counts = [
            'all'    => (int) ($statusCounts->total_all ?? 0),
            'open'   => (int) ($statusCounts->total_open ?? 0),
            'closed' => (int) ($statusCounts->total_closed ?? 0),
            'mine'   => (int) ($statusCounts->total_mine ?? 0),
        ];

        $maxMessageId = (int) ($conversations->pluck('latestMessage.id')->filter()->max() ?? 0);

        return view('admin.inbox', compact(
            'projects',
            'conversations',
            'activeConversation',
            'staffMembers',
            'counts',
            'maxMessageId'
        ));
    }

    /**
     * Multi-Site Integrations Hub
     * GET /admin/integrations
     */
    public function integrations(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $projects = Project::where('tenant_id', $tenantId)
            ->with(['domains', 'activeApiKey', 'widgetSetting'])
            ->withCount(['conversations', 'visitors'])
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.integrations', compact('projects'));
    }

    /**
     * Team & CS Staff Management
     * GET /admin/team
     */
    public function team(Request $request): View
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Akses terbatas hanya untuk Superadmin.');
        }

        $tenantId = $request->user()->tenant_id;

        $query = User::where('tenant_id', $tenantId)->withCount(['conversations']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $sort = $request->input('sort', 'role');
        $direction = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        
        if ($sort === 'conversations_count') {
            $query->orderBy('conversations_count', $direction);
        } elseif (in_array($sort, ['name', 'username', 'email', 'role', 'status', 'created_at'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('role', 'asc')->orderBy('name', 'asc');
        }

        $members = $query->paginate(15)->withQueryString();

        return view('admin.team', compact('members'));
    }

    /**
     * Activity Logs & Audit Trail
     * GET /admin/logs
     */
    public function logs(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $query = ActivityLog::where('tenant_id', $tenantId)->with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('user_role', $request->input('role'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        $sort = $request->input('sort', 'id');
        $direction = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (in_array($sort, ['id', 'created_at', 'user_role', 'action', 'ip_address'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.logs', compact('logs'));
    }

    /**
     * Send CS Agent Reply
     * POST /admin/inbox/{id}/reply
     */
    public function reply(Request $request, $id, ConversationService $conversationService): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $conversation = Conversation::where('tenant_id', $tenantId)->findOrFail($id);

        $content = $request->input('content') ?: $request->input('message');
        if (!$content) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Konten pesan balasan wajib diisi.']
            ], 422);
        }

        $user = $request->user();

        // Otomatis assign percakapan ke agen yang pertama kali membalas jika belum ditugaskan
        if (empty($conversation->assigned_user_id)) {
            $conversation->update(['assigned_user_id' => $user->id]);
        }

        $result = $conversationService->appendMessage($conversation, [
            'sender_type' => 'agent',
            'sender_id'   => $user->id,
            'sender_name' => $user->name,
            'content'     => $content,
        ]);

        $msg = $result['message'];

        ActivityLogger::log(
            'message.replied',
            "Membalas pesan di percakapan #{$conversation->id} ke pengunjung",
            $msg,
            ['conversation_id' => $conversation->id, 'message_id' => $msg->id]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id'                 => $msg->id,
                'sender_type'        => 'agent',
                'sender_name'        => $user->name,
                'content'            => $msg->content,
                'created_at'         => $msg->created_at ? $msg->created_at->format('H:i') : '-',
                'assigned_user_id'   => $conversation->assigned_user_id,
                'assigned_user_name' => $user->name,
            ]
        ], 201);
    }

    /**
     * Adaptive Polling for New Messages
     * GET /admin/inbox/{id}/messages?after_id=...
     */
    public function messages(Request $request, $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $conversation = Conversation::where('tenant_id', $tenantId)->with('visitor')->findOrFail($id);

        $afterId = (int) $request->query('after_id', 0);

        $newMessages = Message::where('conversation_id', $conversation->id)
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->with('user')
            ->get();

        if ($conversation->unread_agent_count > 0) {
            $conversation->update(['unread_agent_count' => 0]);
        }

        $visitorName = $conversation->visitor->display_name ?? 'Tamu';

        $formatted = $newMessages->map(function ($msg) use ($visitorName) {
            return [
                'id'          => $msg->id,
                'sender_type' => $msg->sender_type,
                'sender_name' => $msg->sender_type === 'visitor' 
                    ? $visitorName 
                    : ($msg->sender_name ?? ($msg->user->name ?? 'Staff CS')),
                'content'     => $msg->content,
                'created_at'  => $msg->created_at ? $msg->created_at->format('H:i') : '-',
            ];
        });

        $totalUnread = Conversation::where('tenant_id', $tenantId)
            ->where('unread_agent_count', '>', 0)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'messages'     => $formatted,
                'last_id'      => $newMessages->isNotEmpty() ? $newMessages->last()->id : $afterId,
                'unread_total' => $totalUnread,
            ]
        ]);
    }

    /**
     * Poll Conversation List Updates & Realtime Activity across Tenant
     * GET /admin/inbox/feed/updates
     */
    public function pollUpdates(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $maxMessageId = (int) (Message::where('tenant_id', $tenantId)->max('id') ?? 0);

        // Cari pesan baru dari visitor di seluruh tenant sejak id terakhir.
        // HANYA deteksi notifikasi jika request secara eksplisit menyertakan query parameter since_message_id.
        // Jika tidak ada since_message_id, request ini adalah handshake inisialisasi (baseline sync) agar tidak membunyikan chat lama.
        $newVisitorMessages = collect();
        if ($request->has('since_message_id')) {
            $sinceMessageId = (int) $request->query('since_message_id', 0);
            $newVisitorMessages = Message::where('tenant_id', $tenantId)
                ->where('id', '>', $sinceMessageId)
                ->where('sender_type', 'visitor')
                ->whereHas('conversation', function ($q) {
                    $q->where('unread_agent_count', '>', 0);
                })
                ->orderBy('id', 'asc')
                ->get();
        }

        // Query daftar percakapan aktif dengan filter yang sama (hanya percakapan dengan pesan)
        $query = Conversation::where('tenant_id', $tenantId)
            ->whereHas('messages')
            ->with(['visitor', 'project.widgetSetting', 'assignedUser', 'latestMessage'])
            ->orderBy('last_message_at', 'desc');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'mine') {
                $query->where('assigned_user_id', $request->user()->id);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($sub) use ($search) {
                $sub->whereHas('visitor', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('customer_code', 'like', "%{$search}%");
                })
                ->orWhereHas('contact', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhere('last_message_preview', 'like', "%{$search}%");
            });
        }

        $conversations = $query->take(50)->get()->unique('visitor_id')->values();

        $formatted = $conversations->map(function ($conv) {
            $projectColor = $conv->project && $conv->project->widgetSetting ? $conv->project->widgetSetting->primary_color : '#0071E3';
            return [
                'id'                   => $conv->id,
                'project_id'           => $conv->project_id,
                'project_color'        => $projectColor,
                'visitor_id'           => $conv->visitor_id,
                'customer_name'        => $conv->visitor ? $conv->visitor->display_name : 'Tamu',
                'customer_code'        => $conv->visitor ? $conv->visitor->customer_code : null,
                'initials'             => $conv->visitor ? $conv->visitor->initials : 'TM',
                'channel_label'        => $conv->channel_label,
                'project_name'         => $conv->project ? $conv->project->name : 'Website',
                'last_message_preview' => $conv->last_message_preview ?: 'Percakapan baru diinisialisasi...',
                'last_message_time'    => $conv->last_message_time,
                'unread_agent_count'   => (int) $conv->unread_agent_count,
                'is_unread'            => (bool) ($conv->unread_agent_count > 0),
                'status'               => $conv->status,
                'assigned_user_id'     => $conv->assigned_user_id,
            ];
        });

        $totalUnread = Conversation::where('tenant_id', $tenantId)
            ->whereHas('messages')
            ->where('unread_agent_count', '>', 0)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'conversations'      => $formatted,
                'unread_total'       => $totalUnread,
                'max_message_id'     => $maxMessageId,
                'has_new_incoming'   => $newVisitorMessages->isNotEmpty(),
                'new_incoming_count' => $newVisitorMessages->count(),
                'latest_incoming'    => $newVisitorMessages->last() ? [
                    'message_id'      => $newVisitorMessages->last()->id,
                    'conversation_id' => $newVisitorMessages->last()->conversation_id,
                    'sender_name'     => $newVisitorMessages->last()->sender_name,
                    'content'         => mb_substr(strip_tags($newVisitorMessages->last()->content), 0, 70),
                ] : null,
            ]
        ]);
    }

    /**
     * Update Conversation Status (Open / Closed)
     * PUT /admin/inbox/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $tenantId = $request->user()->tenant_id;
        $conversation = Conversation::where('tenant_id', $tenantId)->findOrFail($id);

        $newStatus = $request->input('status');
        if (!$newStatus) {
            $newStatus = ($conversation->status === 'open') ? 'closed' : 'open';
        }

        $conversation->update(['status' => $newStatus]);

        ActivityLogger::log(
            'conversation.status_updated',
            "Mengubah status percakapan #{$conversation->id} menjadi {$newStatus}",
            $conversation,
            ['status' => $newStatus]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => ['status' => $newStatus]
            ]);
        }

        return redirect()->back()->with('success', "Status tiket berhasil diubah menjadi '{$newStatus}'.");
    }

    /**
     * Assign Conversation to Staff CS
     * PUT /admin/inbox/{id}/assign
     */
    public function assign(Request $request, $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $conversation = Conversation::where('tenant_id', $tenantId)->findOrFail($id);

        $assignedUserId = $request->input('assigned_user_id');
        if ($assignedUserId) {
            $assignee = User::where('tenant_id', $tenantId)->findOrFail($assignedUserId);
            $conversation->update(['assigned_user_id' => $assignee->id]);
            $assigneeName = $assignee->name;
        } else {
            $conversation->update(['assigned_user_id' => null]);
            $assigneeName = 'Belum Ditugaskan';
        }

        ActivityLogger::log(
            'conversation.assigned',
            "Menugaskan percakapan #{$conversation->id} ke {$assigneeName}",
            $conversation,
            ['assigned_user_id' => $assignedUserId, 'assignee_name' => $assigneeName]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'assigned_user_id'   => $assignedUserId,
                'assigned_user_name' => $assigneeName,
                'assignee_name'      => $assigneeName,
            ]
        ]);
    }

    /**
     * Update Customer Display Name
     * PUT /admin/inbox/{id}/customer
     */
    public function updateCustomerName(Request $request, $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $conversation = Conversation::where('tenant_id', $tenantId)->with('visitor')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $cleanName = strip_tags(trim($request->input('name')));

        if ($conversation->visitor) {
            $conversation->visitor->update(['name' => $cleanName]);
        }

        ActivityLogger::log(
            'customer.renamed',
            "Mengubah nama pelanggan percakapan #{$conversation->id} menjadi: {$cleanName}",
            $conversation->visitor,
            ['conversation_id' => $conversation->id, 'new_name' => $cleanName]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'display_name'  => $conversation->visitor->display_name,
                'name'          => $cleanName,
                'customer_code' => $conversation->visitor->customer_code_formatted,
            ]
        ]);
    }

    /**
     * Store New Integration Project
     * POST /admin/integrations
     */
    public function storeIntegration(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:100',
            'domain'            => 'required|string|max:255',
            'primary_color'     => 'nullable|string|max:10',
            'greeting_title'    => 'nullable|string|max:100',
            'greeting_subtitle' => 'nullable|string|max:255',
        ]);

        $tenantId = $request->user()->tenant_id;

        $slug = Str::slug($request->input('name'));
        if (Project::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        $project = Project::create([
            'tenant_id' => $tenantId,
            'name'      => $request->input('name'),
            'slug'      => $slug,
            'is_active' => true,
        ]);

        $cleanDomain = preg_replace('#^https?://#', '', rtrim($request->input('domain'), '/'));
        ProjectDomain::create([
            'project_id'  => $project->id,
            'domain'      => $cleanDomain,
            'is_verified' => true,
        ]);

        $domainClean = preg_replace('/[^a-z0-9]/', '', strtolower($cleanDomain));
        $publicKey = 'pk_live_' . substr($domainClean, 0, 10) . '_' . mt_rand(1000, 9999);

        ApiKey::create([
            'tenant_id'  => $tenantId,
            'project_id' => $project->id,
            'public_key' => $publicKey,
            'name'       => 'Default Production Key',
            'is_active'  => true,
        ]);

        $setting = WidgetSetting::create([
            'project_id'        => $project->id,
            'primary_color'     => $request->input('primary_color', '#C59B27'),
            'greeting_title'    => $request->input('greeting_title', 'Hallo!'),
            'greeting_subtitle' => $request->input('greeting_subtitle', 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!'),
            'is_online'         => true,
        ]);

        ActivityLogger::log(
            'integration.created',
            "Membuat integrasi website baru: {$project->name} ({$cleanDomain})",
            $project,
            ['domain' => $cleanDomain, 'public_key' => $publicKey]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'project_id'    => $project->id,
                    'name'          => $project->name,
                    'domain'        => $cleanDomain,
                    'public_key'    => $publicKey,
                    'primary_color' => $setting->primary_color,
                ]
            ], 201);
        }

        return redirect()->route('admin.integrations')->with('success', "Integrasi toko '{$project->name}' berhasil ditambahkan!");
    }

    /**
     * Regenerate API Key for a Project
     * POST /admin/integrations/{id}/regenerate
     */
    public function regenerateKey(Request $request, $id): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $project = Project::where('tenant_id', $tenantId)->findOrFail($id);

        // Nonaktifkan semua key lama
        ApiKey::where('project_id', $project->id)->update(['is_active' => false]);

        // Generate key baru
        $domain = $project->domains->first();
        $domainClean = $domain 
            ? preg_replace('/[^a-z0-9]/', '', strtolower($domain->domain)) 
            : Str::slug($project->name, '');
        $publicKey = 'pk_live_' . substr($domainClean, 0, 10) . '_' . mt_rand(1000, 9999);

        ApiKey::create([
            'tenant_id'  => $tenantId,
            'project_id' => $project->id,
            'public_key' => $publicKey,
            'name'       => 'Regenerated Key',
            'is_active'  => true,
        ]);

        ActivityLogger::log(
            'integration.key_regenerated',
            "Regenerasi API key untuk project: {$project->name}",
            $project,
            ['old_keys_deactivated' => true, 'new_public_key' => $publicKey]
        );

        return redirect()->route('admin.integrations')->with('success', "API Key untuk '{$project->name}' berhasil di-regenerasi. Salin ulang kode embed baru.");
    }

    /**
     * Update Widget & Social Channels Settings for a Project
     * PUT /admin/integrations/{id}/settings
     */
    public function updateWidgetSettings(Request $request, $id): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $project = Project::where('tenant_id', $tenantId)->findOrFail($id);

        $request->validate([
            'primary_color'     => 'required|string|max:20',
            'greeting_title'    => 'nullable|string|max:100',
            'greeting_subtitle' => 'nullable|string|max:255',
            'support_title'     => 'nullable|string|max:100',
            'find_us_title'     => 'nullable|string|max:100',
        ]);

        $channelsInput = $request->input('channels', []);
        $formattedChannels = [];

        $availableChannels = [
            'whatsapp'  => ['name' => 'WhatsApp', 'icon' => 'whatsapp'],
            'instagram' => ['name' => 'Instagram', 'icon' => 'instagram'],
            'messenger' => ['name' => 'Facebook Messenger', 'icon' => 'messenger'],
            'telegram'  => ['name' => 'Telegram', 'icon' => 'telegram'],
            'shopee'    => ['name' => 'Shopee', 'icon' => 'shopee'],
            'tokopedia' => ['name' => 'Tokopedia', 'icon' => 'tokopedia'],
        ];

        foreach ($availableChannels as $key => $meta) {
            $enabled = !empty($channelsInput[$key]['enabled']);
            $url = trim($channelsInput[$key]['url'] ?? '');

            // Normalisasi URL WhatsApp jika hanya diisi nomor handphone
            if ($key === 'whatsapp' && $url && !str_starts_with($url, 'http')) {
                $cleanNumber = preg_replace('/[^0-9]/', '', $url);
                if (str_starts_with($cleanNumber, '0')) {
                    $cleanNumber = '62' . substr($cleanNumber, 1);
                }
                $url = 'https://wa.me/' . $cleanNumber;
            }

            if ($key === 'instagram' && $url && !str_starts_with($url, 'http')) {
                $url = 'https://instagram.com/' . ltrim($url, '@');
            }

            if ($key === 'telegram' && $url && !str_starts_with($url, 'http')) {
                $url = 'https://t.me/' . ltrim($url, '@');
            }

            if ($key === 'messenger' && $url && !str_starts_with($url, 'http')) {
                $url = 'https://m.me/' . ltrim($url, '/');
            }

            $formattedChannels[] = [
                'id'      => $key,
                'name'    => $meta['name'],
                'enabled' => $enabled && !empty($url),
                'url'     => $url,
                'icon'    => $meta['icon'],
            ];
        }

        $widgetSetting = WidgetSetting::firstOrCreate(
            ['project_id' => $project->id],
            [
                'primary_color'     => '#C59B27',
                'greeting_title'    => 'Hallo!',
                'greeting_subtitle' => 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
                'is_online'         => true,
            ]
        );

        $widgetSetting->update([
            'primary_color'     => $request->input('primary_color', $widgetSetting->primary_color),
            'greeting_title'    => $request->input('greeting_title', $widgetSetting->greeting_title),
            'greeting_subtitle' => $request->input('greeting_subtitle', $widgetSetting->greeting_subtitle),
            'support_title'     => $request->input('support_title', $widgetSetting->support_title ?: 'Support'),
            'find_us_title'     => $request->input('find_us_title', 'Reach Us Anywhere Else'),
            'social_channels'   => $formattedChannels,
        ]);

        ActivityLogger::log(
            'widget.updated',
            "Memperbarui pengaturan tampilan widget dan saluran sosial untuk: {$project->name}",
            $project,
            ['project_id' => $project->id, 'channels_active' => count(array_filter($formattedChannels, fn($c) => $c['enabled']))]
        );

        return redirect()->route('admin.integrations')->with('success', "Pengaturan widget & saluran untuk '{$project->name}' berhasil disimpan!");
    }

    /**
     * Store New Team Member
     * POST /admin/team
     */
    public function storeTeam(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Akses terbatas hanya untuk Superadmin.');
        }

        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'nullable|string|min:3|max:50|alpha_dash|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'role'     => 'required|in:superadmin,agent',
            'password' => 'required|string|min:6',
        ]);

        $tenantId = $request->user()->tenant_id;

        $username = $request->input('username');
        if (!$username) {
            $base = Str::slug(explode('@', $request->input('email'))[0], '_');
            $username = $base;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . '_' . $counter++;
            }
        }

        $user = User::create([
            'tenant_id'  => $tenantId,
            'name'       => $request->input('name'),
            'username'   => $username,
            'email'      => $request->input('email'),
            'role'       => $request->input('role'),
            'password'   => Hash::make($request->input('password')),
            'status'     => 'offline',
            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($request->input('name')) . '&background=random',
        ]);

        ActivityLogger::log(
            'team.invited',
            "Menambahkan anggota tim baru: {$user->name} ({$user->email}) sebagai {$user->role}",
            $user,
            ['invited_user_id' => $user->id, 'email' => $user->email, 'role' => $user->role]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ]
            ], 201);
        }

        return redirect()->route('admin.team')->with('success', "Staf CS '{$user->name}' berhasil didaftarkan sebagai {$user->role}!");
    }

    /**
     * Login As (Impersonate) Another Team Member
     * POST /admin/team/{id}/impersonate
     */
    public function impersonate(Request $request, $id): RedirectResponse
    {
        $currentUser = $request->user();

        // Hanya Superadmin atau user yang sedang impersonate yang boleh beralih akun
        if (!$currentUser->isSuperAdmin() && !session()->has('impersonator_id')) {
            abort(403, 'Akses terbatas hanya untuk Superadmin.');
        }

        // Cari target user dalam tenant yang sama
        $targetUser = User::where('tenant_id', $currentUser->tenant_id)->findOrFail($id);

        // Cegah impersonate akun sendiri
        if ($targetUser->id === $currentUser->id) {
            return redirect()->back()->with('error', 'Anda sudah berada di akun ini.');
        }

        // Simpan ID superadmin asli di session
        if (!session()->has('impersonator_id')) {
            session(['impersonator_id' => $currentUser->id]);
        }

        ActivityLogger::log(
            'user.impersonate',
            "Superadmin {$currentUser->name} melakukan Login As sebagai {$targetUser->name} ({$targetUser->role})",
            $targetUser,
            [
                'original_user_id' => session('impersonator_id'),
                'target_user_id'   => $targetUser->id,
                'target_role'      => $targetUser->role,
            ]
        );

        Auth::login($targetUser);

        return redirect()->route('admin.inbox')->with('success', "Mode Login As aktif! Anda sekarang bertindak sebagai {$targetUser->name} ({$targetUser->role}).");
    }

    /**
     * Leave Impersonation (Kembali ke Akun Asli)
     * POST|GET /admin/impersonate/leave
     */
    public function leaveImpersonation(Request $request): RedirectResponse
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('admin.inbox');
        }

        $originalUserId = session('impersonator_id');
        $originalUser = User::find($originalUserId);

        session()->forget('impersonator_id');

        if (!$originalUser) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Sesi akun asli tidak ditemukan. Silakan login kembali.');
        }

        $impersonatedUser = $request->user();
        Auth::login($originalUser);

        ActivityLogger::log(
            'user.impersonate_leave',
            "{$originalUser->name} keluar dari mode Login As ({$impersonatedUser->name}) dan kembali ke akun aslinya",
            $originalUser,
            [
                'original_user_id'     => $originalUser->id,
                'impersonated_user_id' => $impersonatedUser->id,
            ]
        );

        return redirect()->route('admin.team')->with('success', "Kembali ke akun asli ({$originalUser->name}).");
    }

    /**
     * Otomatis mengonsolidasi percakapan duplikat dari customer yang sama
     */
    protected function consolidateDuplicateConversations(int $tenantId): void
    {
        try {
            $duplicateVisitorIds = Conversation::where('tenant_id', $tenantId)
                ->whereIn('status', ['open', 'pending'])
                ->select('visitor_id')
                ->groupBy('visitor_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('visitor_id');

            foreach ($duplicateVisitorIds as $visitorId) {
                if (!$visitorId) continue;
                $convs = Conversation::where('tenant_id', $tenantId)
                    ->where('visitor_id', $visitorId)
                    ->whereIn('status', ['open', 'pending'])
                    ->orderBy('id', 'asc')
                    ->get();

                if ($convs->count() > 1) {
                    $primary = $convs->first();
                    $otherIds = $convs->slice(1)->pluck('id')->toArray();
                    $otherUnreadSum = $convs->slice(1)->sum('unread_agent_count');

                    Message::whereIn('conversation_id', $otherIds)->update(['conversation_id' => $primary->id]);

                    $primary->update([
                        'unread_agent_count'   => $primary->unread_agent_count + $otherUnreadSum,
                        'last_message_at'      => $convs->max('last_message_at') ?: now(),
                        'last_message_preview' => $convs->sortByDesc('last_message_at')->first()->last_message_preview,
                    ]);

                    Conversation::whereIn('id', $otherIds)->delete();
                }
            }
        } catch (\Throwable $e) {
            // Silently recover if query fails
        }
    }
}
