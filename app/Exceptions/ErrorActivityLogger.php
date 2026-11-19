<?php

namespace App\Exceptions;

use App\Models\ActivityLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PDOException;
use Throwable;

/**
 * Mencatat error "senyap" (rate limit, 5xx, error database) ke activity_logs per tenant.
 */
class ErrorActivityLogger
{
    private const ACTIONS = [
        429 => 'rate_limit_exceeded',
        500 => 'server_error',
        502 => 'bad_gateway',
        503 => 'service_unavailable',
    ];

    public static function record(Throwable $e, int $status, Request $request): void
    {
        try {
            $isDbError = $e instanceof QueryException || $e instanceof PDOException;
            $action = self::ACTIONS[$status] ?? ($isDbError ? 'database_error' : null);
            if (! $action) {
                return;
            }

            $user = $request->user();
            $tenantId = $user?->tenant_id ?? $request->attributes->get('project')?->tenant_id;
            if (! $tenantId) {
                return; // activity_logs wajib tenant-scoped
            }

            // Anti-spam: maks 1 entri per action+IP+path per 30 detik.
            if (! Cache::add('err_log_'.md5($action.$request->ip().$request->path()), true, 30)) {
                return;
            }

            $path = $request->path();
            $message = mb_substr($e->getMessage(), 0, 200);
            $description = match ($action) {
                'rate_limit_exceeded' => "Rate limit tercapai (429) dari IP {$request->ip()} pada {$path}",
                'server_error' => "Server error (500) pada {$path}: {$message}",
                'database_error' => "Database error pada {$path}: {$message}",
                default => "Error [{$status}] pada {$path}",
            };

            ActivityLog::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'System',
                'user_role' => $user?->role ?? 'system',
                'action' => $action,
                'description' => $description,
                'subject_type' => 'error',
                'properties' => [
                    'status_code' => $status,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'exception_class' => $e::class,
                    'message' => mb_substr($e->getMessage(), 0, 500),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (Throwable $inner) {
            // Logging error tidak boleh memicu error baru.
            Log::warning('[ErrorActivityLogger] '.$inner->getMessage());
        }
    }
}
