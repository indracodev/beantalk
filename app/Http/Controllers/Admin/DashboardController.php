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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Master 3-Column Admin Inbox
     * GET /admin or GET /admin/inbox or GET /admin/inbox/{id}
     */
    public function inbox(Request $request, $id = null): View
    {
        $tenantId = $request->user()->tenant_id;

        // Ambil semua project milik tenant
        $projects = Project::where('tenant_id', $tenantId)->orderBy('name', 'asc')->get();

        // Query percakapan dengan filter (eager load semua relasi untuk menghindari N+1 query)
        $query = Conversation::where('tenant_id', $tenantId)
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
            $query->whereHas('visitor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $conversations = $query->get();

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
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
            $activeConversation->load(['messages.user']);
        }

        // Ambil daftar agen/staff untuk penugasan
        $staffMembers = User::where('tenant_id', $tenantId)->orderBy('name', 'asc')->get();

        // Statistik ringkas dalam 1 query agregasi tunggal (menghindari multiple roundtrip counts)
        $statusCounts = Conversation::where('tenant_id', $tenantId)
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

        return view('admin.inbox', compact(
            'projects',
            'conversations',
            'activeConversation',
            'staffMembers',
            'counts'
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
            ->with(['domains', 'apiKeys', 'widgetSetting'])
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

        $members = User::where('tenant_id', $tenantId)
            ->withCount(['conversations'])
            ->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.team', compact('members'));
    }

    /**
     * Activity Logs & Audit Trail
     * GET /admin/logs
     */
    public function logs(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $query = ActivityLog::where('tenant_id', $tenantId)->with('user')->orderBy('id', 'desc');

        if ($request->filled('role')) {
            $query->where('user_role', $request->input('role'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
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
                'id'          => $msg->id,
                'sender_type' => 'agent',
                'sender_name' => $user->name,
                'content'     => $msg->content,
                'created_at'  => $msg->created_at ? $msg->created_at->format('H:i') : '-',
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

        $visitorName = $conversation->visitor->name ?? 'Pengunjung Web';

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

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $formatted,
                'last_id'  => $newMessages->isNotEmpty() ? $newMessages->last()->id : $afterId,
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
            "Menugaskan percakapan #{$conversation->id} kepada {$assigneeName}",
            $conversation,
            ['assigned_user_id' => $conversation->assigned_user_id]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'assigned_user_id'   => $conversation->assigned_user_id,
                'assigned_user_name' => $assigneeName,
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
}
