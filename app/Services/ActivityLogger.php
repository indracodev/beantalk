<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Catat audit trail. User default = user sesi (tidak pernah dari header klien).
     *
     * @param  string  $action  mis. 'integration.created', 'role.updated'
     */
    public static function log(string $action, string $description, ?Model $subject = null, array $properties = [], ?User $user = null): ActivityLog
    {
        $request = request();
        $user ??= $request->user();

        $tenantId = $user?->tenant_id
            ?? (app()->bound('current_tenant_id') ? app('current_tenant_id') : null)
            ?? $subject?->tenant_id
            ?? $subject?->project?->tenant_id;

        return ActivityLog::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? ($properties['user_name'] ?? 'System / Automated'),
            'user_role' => $user?->role ?? ($properties['role'] ?? 'system'),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }
}
