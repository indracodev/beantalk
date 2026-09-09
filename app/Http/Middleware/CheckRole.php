<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request by verifying user role
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user() ?: ($request->header('X-User-Id') ? \App\Models\User::find($request->header('X-User-Id')) : null);

        if ($user && !$request->user()) {
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        // Jika endpoint dipanggil tanpa otentikasi user
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Otentikasi diperlukan untuk mengakses resource ini.',
                ]
            ], 401);
        }

        // Cek apakah role user ada di dalam daftar roles yang diizinkan
        if (!empty($roles) && !$user->hasRole($roles)) {
            $requiredRoles = implode(', ', $roles);
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => "Akses ditolak. Tindakan ini memerlukan hak akses: [{$requiredRoles}]. Peran Anda saat ini: '{$user->role}'.",
                ]
            ], 403);
        }

        return $next($request);
    }
}
