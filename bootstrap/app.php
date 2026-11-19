<?php

use App\Exceptions\ErrorActivityLogger;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnforceTenantScope;
use App\Http\Middleware\ResolveProjectKey;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Header keamanan + anti-crawler untuk seluruh response (web, API, widget).
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'project.key' => ResolveProjectKey::class,
            'tenant.scope' => EnforceTenantScope::class,
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 429 → envelope standar API.
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOO_MANY_REQUESTS',
                    'message' => 'Terlalu banyak permintaan. Silakan tunggu sebentar.',
                    'retry_after' => (int) ($e->getHeaders()['Retry-After'] ?? 60),
                ],
            ], 429, $e->getHeaders());
        });

        // Error "senyap" (429/5xx/DB) dicatat ke activity_logs agar terlihat dari dashboard.
        // Dipasang di respond (bukan report) karena HttpException tidak pernah di-report oleh core.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            ErrorActivityLogger::record($e, $response->getStatusCode(), $request);

            return $response;
        });
    })->create();
