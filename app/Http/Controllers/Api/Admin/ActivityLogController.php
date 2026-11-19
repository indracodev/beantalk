<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /** GET /api/v1/admin/activity-logs?role=&user_id=&action=&per_page= */
    public function index(Request $request): JsonResponse
    {
        $logs = ActivityLog::where('tenant_id', $request->user()->tenant_id)
            ->when($request->input('role'), fn ($q, $v) => $q->where('user_role', $v))
            ->when($request->input('user_id'), fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->input('action'), fn ($q, $v) => $q->whereLike('action', '%'.$v.'%'))
            ->orderByDesc('id')
            ->paginate(min((int) $request->input('per_page', 25), 100));

        return $this->ok([
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
