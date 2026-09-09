<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Get paginated audit activity logs
     * GET /api/v1/admin/activity-logs
     */
    public function index(Request $request)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;

        $query = ActivityLog::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('user_role', $request->input('role'));
        }

        // Filter by specific user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by action keyword
        if ($request->filled('action')) {
            $query->where('action', 'LIKE', '%' . $request->input('action') . '%');
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $logs = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'logs' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'per_page'     => $logs->perPage(),
                    'total'        => $logs->total(),
                    'last_page'    => $logs->lastPage(),
                ]
            ]
        ]);
    }
}
