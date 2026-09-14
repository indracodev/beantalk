<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceTenantScope
{
    public function handle(Request $request, Closure $next)
    {
        // ponytail: hanya ambil user dari session auth, JANGAN PERNAH trust X-User-Id header dari client
        $user = $request->user();

        // ponytail: Untuk endpoint API Admin, autentikasi user tenant wajib ada
        if ($request->is('api/v1/admin*')) {
            if (!$user || !$user->tenant_id) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code'    => 'UNAUTHENTICATED',
                        'message' => 'Autentikasi admin/staff diperlukan untuk mengakses endpoint ini.',
                    ]
                ], 401);
            }
        }

        if ($user && $user->tenant_id) {
            app()->instance('current_tenant_id', $user->tenant_id);
            $request->attributes->set('tenant_id', $user->tenant_id);
        }

        return $next($request);
    }
}

