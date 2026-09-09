<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogger
{
    /**
     * Record an audit activity log entry
     *
     * @param string $action e.g. 'integration.created', 'role.updated', 'message.replied'
     * @param string $description Human-readable description
     * @param mixed $subject Associated Eloquent model or null
     * @param array $properties Additional context or parameters
     * @param User|null $user The user performing the action, or null to auto-resolve
     * @return ActivityLog
     */
    public static function log(
        string $action,
        string $description,
        $subject = null,
        array $properties = [],
        ?User $user = null
    ): ActivityLog {
        if (!$user && request()) {
            $user = request()->user() ?: (request()->header('X-User-Id') ? User::find(request()->header('X-User-Id')) : null);
        }

        $tenantId = $user ? $user->tenant_id : (app()->bound('current_tenant_id') ? app('current_tenant_id') : ($subject && isset($subject->tenant_id) ? $subject->tenant_id : 1));

        $role = $user ? $user->role : ($properties['role'] ?? 'system');
        $userName = $user ? $user->name : ($properties['user_name'] ?? 'System / Automated');

        return ActivityLog::create([
            'tenant_id'    => $tenantId,
            'user_id'      => $user ? $user->id : null,
            'user_name'    => $userName,
            'user_role'    => $role,
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject ? $subject->getKey() : null,
            'properties'   => !empty($properties) ? $properties : null,
            'ip_address'   => request() ? request()->ip() : '127.0.0.1',
            'user_agent'   => request() ? substr(request()->userAgent() ?? '', 0, 500) : 'CLI/System',
            'created_at'   => now(),
        ]);
    }
}
