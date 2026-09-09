<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
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

        // Query percakapan dengan filter
        $query = Conversation::where('tenant_id', $tenantId)
            ->with(['visitor', 'project', 'assignedUser', 'latestMessage'])
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

        // Tentukan percakapan aktif yang sedang dibuka
        $activeConversation = null;
        if ($id) {
            $activeConversation = Conversation::where('tenant_id', $tenantId)
                ->with(['visitor', 'project.widgetSetting', 'assignedUser', 'messages' => function ($q) {
                    $q->orderBy('id', 'asc');
                }])
                ->find($id);
        }

        if (!$activeConversation && $conversations->isNotEmpty()) {
            $activeConversation = Conversation::where('tenant_id', $tenantId)
                ->with(['visitor', 'project.widgetSetting', 'assignedUser', 'messages' => function ($q) {
                    $q->orderBy('id', 'asc');
                }])
                ->find($conversations->first()->id);
        }

        // Ambil daftar agen/staff untuk penugasan
        $staffMembers = User::where('tenant_id', $tenantId)->orderBy('name', 'asc')->get();

        // Statistik ringkas
        $counts = [
            'all'    => Conversation::where('tenant_id', $tenantId)->count(),
            'open'   => Conversation::where('tenant_id', $tenantId)->where('status', 'open')->count(),
            'closed' => Conversation::where('tenant_id', $tenantId)->where('status', 'closed')->count(),
            'mine'   => Conversation::where('tenant_id', $tenantId)->where('assigned_user_id', $request->user()->id)->count(),
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

        $query = ActivityLog::where('tenant_id', $tenantId)->orderBy('id', 'desc');

        if ($request->filled('role')) {
            $query->where('user_role', $request->input('role'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.logs', compact('logs'));
    }
}
