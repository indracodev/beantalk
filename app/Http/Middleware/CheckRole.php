<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC endpoint API: role:superadmin,agent. User HANYA dari guard sesi —
 * header X-User-Id dari klien tidak pernah dipercaya.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Otentikasi diperlukan untuk mengakses resource ini.',
                ],
            ], 401);
        }

        if ($roles !== [] && ! $user->hasRole($roles)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Akses ditolak. Tindakan ini memerlukan hak akses: ['.implode(', ', $roles)."]. Peran Anda saat ini: '{$user->role}'.",
                ],
            ], 403);
        }

        return $next($request);
    }
}
