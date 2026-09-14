<?php

namespace App\Exceptions;

use App\Models\ActivityLog;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        // Log silent errors ke activity_logs agar mudah dipantau dari admin dashboard
        $this->logSilentError($exception);

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // 429 Too Many Requests — kembalikan JSON envelope standar untuk API
        if ($exception instanceof ThrottleRequestsException) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'TOO_MANY_REQUESTS',
                        'message' => 'Terlalu banyak permintaan. Silakan tunggu sebentar.',
                        'retry_after' => $exception->getHeaders()['Retry-After'] ?? 60,
                    ]
                ], 429);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Log error-error "silent" yang biasanya tidak terlihat oleh admin:
     * - 429 Too Many Requests (rate limit tercapai)
     * - 500 Internal Server Error
     * - Database/Connection errors
     * 
     * Disimpan ke tabel activity_logs agar bisa dipantau dari halaman Activity Logs.
     */
    private function logSilentError(Throwable $exception): void
    {
        try {
            $request = request();
            $statusCode = $this->resolveStatusCode($exception);

            // Hanya catat error yang "penting" dan bersifat silent
            $loggableErrors = [
                429 => 'rate_limit_exceeded',
                500 => 'server_error',
                502 => 'bad_gateway',
                503 => 'service_unavailable',
            ];

            // Catat juga database/connection error tanpa memandang status code
            $isDbError = $exception instanceof \Illuminate\Database\QueryException
                || $exception instanceof \PDOException;

            $action = $loggableErrors[$statusCode] ?? null;

            if (!$action && $isDbError) {
                $action = 'database_error';
            }

            if (!$action) {
                return; // Bukan error yang perlu dicatat ke activity log
            }

            // Rate limit: jangan spam log sendiri (max 1 log per IP per 30 detik per action)
            $cacheKey = 'err_log_' . md5($action . ($request->ip() ?? '') . ($request->path() ?? ''));
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                return;
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 30);

            // Tentukan tenant_id dari user yang login atau dari request context
            $tenantId = null;
            $userId = null;
            $userName = 'System';
            $userRole = 'system';

            if ($request->user()) {
                $tenantId = $request->user()->tenant_id ?? null;
                $userId = $request->user()->id ?? null;
                $userName = $request->user()->name ?? 'Unknown';
                $userRole = $request->user()->role ?? 'unknown';
            }

            // Untuk error dari API client (widget), ambil tenant dari project key
            if (!$tenantId && $request->is('api/v1/client/*')) {
                $project = $request->attributes->get('project');
                $tenantId = $project->tenant_id ?? null;
            }

            $description = $this->buildErrorDescription($exception, $statusCode, $action);

            ActivityLog::create([
                'tenant_id'    => $tenantId,
                'user_id'      => $userId,
                'user_name'    => $userName,
                'user_role'    => $userRole,
                'action'       => $action,
                'description'  => $description,
                'subject_type' => 'error',
                'subject_id'   => null,
                'properties'   => [
                    'status_code'     => $statusCode,
                    'url'             => $request->fullUrl(),
                    'method'          => $request->method(),
                    'ip'              => $request->ip(),
                    'exception_class' => get_class($exception),
                    'message'         => mb_substr($exception->getMessage(), 0, 500),
                ],
                'ip_address'   => $request->ip(),
                'user_agent'   => mb_substr($request->userAgent() ?? '', 0, 255),
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai logging error menyebabkan error baru.
            // Fallback ke Laravel log biasa.
            \Illuminate\Support\Facades\Log::warning('[ErrorLogger] Gagal mencatat error ke activity_logs: ' . $e->getMessage());
        }
    }

    /**
     * Resolve HTTP status code from exception.
     */
    private function resolveStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }
        if ($exception instanceof ThrottleRequestsException) {
            return 429;
        }
        if ($exception instanceof \Illuminate\Database\QueryException || $exception instanceof \PDOException) {
            return 500;
        }
        return 500;
    }

    /**
     * Build human-readable error description for activity log.
     */
    private function buildErrorDescription(Throwable $exception, int $statusCode, string $action): string
    {
        $request = request();
        $path = $request->path() ?? 'unknown';
        $ip = $request->ip() ?? 'unknown';

        $descriptions = [
            'rate_limit_exceeded' => "⚠️ Rate limit tercapai (429) dari IP {$ip} pada {$path}",
            'server_error'        => "🔴 Server error (500) pada {$path}: " . mb_substr($exception->getMessage(), 0, 200),
            'bad_gateway'         => "🔴 Bad gateway (502) pada {$path}",
            'service_unavailable' => "🔴 Service unavailable (503) pada {$path}",
            'database_error'      => "🔴 Database error pada {$path}: " . mb_substr($exception->getMessage(), 0, 200),
        ];

        return $descriptions[$action] ?? "Error [{$statusCode}] pada {$path}";
    }
}
