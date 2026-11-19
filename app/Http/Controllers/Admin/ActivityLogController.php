<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Halaman Activity Logs & Audit Trail. */
class ActivityLogController extends Controller
{
    private const SORTABLE = ['id', 'created_at', 'user_role', 'user_name', 'action', 'ip_address'];

    private const PER_PAGE = [10, 15, 25, 50, 100];

    /** GET /admin/logs */
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->input('search'));
        $action = (string) $request->input('action');
        $sort = in_array($request->input('sort'), self::SORTABLE, true) ? $request->input('sort') : 'created_at';
        $direction = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->input('per_page'), self::PER_PAGE, true) ? (int) $request->input('per_page') : 25;

        $logs = ActivityLog::where('tenant_id', $tenantId)
            ->with('user')
            ->when($search !== '', fn ($q) => $q->where(fn ($s) => $s->whereLike('description', "%{$search}%")
                ->orWhereLike('action', "%{$search}%")->orWhereLike('ip_address', "%{$search}%")
                ->orWhereLike('user_name', "%{$search}%")->orWhereLike('subject_type', "%{$search}%")))
            ->when($request->filled('role'), fn ($q) => $q->where('user_role', $request->input('role')))
            ->when($action !== '', fn ($q) => str_contains($action, '*')
                ? $q->whereLike('action', str_replace('*', '%', $action))
                : $q->where('action', $action))
            ->when(DateRange::since($request->input('date_range')), fn ($q, $since) => $q->where('created_at', '>=', $since))
            ->orderBy($sort, $direction)
            ->when($sort !== 'id', fn ($q) => $q->orderBy('id', $direction))
            ->paginate($perPage)
            ->withQueryString();

        $logStats = ActivityLog::where('tenant_id', $tenantId)
            ->selectRaw("COUNT(*) AS total_logs,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS today_logs,
                SUM(CASE WHEN user_role = 'superadmin' THEN 1 ELSE 0 END) AS superadmin_logs,
                SUM(CASE WHEN user_role = 'agent' THEN 1 ELSE 0 END) AS agent_logs,
                SUM(CASE WHEN action LIKE '%error%' OR action LIKE '%exceeded%' OR action LIKE '%UNAUTHORIZED%' THEN 1 ELSE 0 END) AS warning_logs", [today()])
            ->toBase()
            ->first();

        $actionList = ActivityLog::where('tenant_id', $tenantId)->distinct()->orderBy('action')->pluck('action');

        return view('admin.logs', compact('logs', 'logStats', 'actionList', 'perPage', 'sort', 'direction'));
    }
}
