<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bind tenant aktif dari user sesi (bukan dari input klien) agar global scope
 * BelongsToTenant menyaring seluruh query.
 */
class EnforceTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->is('api/v1/admin*') && ! $user?->tenant_id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Autentikasi admin/staff diperlukan untuk mengakses endpoint ini.',
                ],
            ], 401);
        }

        if ($user?->tenant_id) {
            app()->instance('current_tenant_id', $user->tenant_id);
            $request->attributes->set('tenant_id', $user->tenant_id);
        }

        return $next($request);
    }
}
