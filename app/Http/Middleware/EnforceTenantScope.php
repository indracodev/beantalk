<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceTenantScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user() ?: ($request->header('X-User-Id') ? \App\Models\User::find($request->header('X-User-Id')) : null);

        if ($user && !$request->user()) {
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        if ($user && $user->tenant_id) {
            app()->instance('current_tenant_id', $user->tenant_id);
            $request->attributes->set('tenant_id', $user->tenant_id);
        }

        return $next($request);
    }
}
