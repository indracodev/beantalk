<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi widget: X-Project-Key (atau ?project_key) + whitelist domain Origin/Referer.
 */
class ResolveProjectKey
{
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1', 'chat-me.test'];

    public function handle(Request $request, Closure $next): Response
    {
        $publicKey = $request->header('X-Project-Key') ?? $request->input('project_key') ?? $request->input('key');

        if (! is_string($publicKey) || $publicKey === '') {
            return $this->deny('MISSING_PROJECT_KEY', 'Header X-Project-Key atau parameter project_key diperlukan.', 401);
        }

        $apiKey = ApiKey::resolveActive($publicKey);
        $project = $apiKey?->project;
        if (! $project || ! $project->is_active) {
            return $this->deny('INVALID_PROJECT_KEY', 'Project Public Key tidak valid atau project tidak aktif.', 401);
        }

        // Whitelist domain (localhost/dev hanya dilewati di luar production).
        $origin = $request->header('Origin') ?? $request->header('Referer');
        $host = $origin ? parse_url($origin, PHP_URL_HOST) : null;
        $isLocal = ! app()->isProduction() && in_array($host, self::LOCAL_HOSTS, true);

        if ($host && ! $isLocal && $project->domains->isNotEmpty()
            && ! $project->domains->contains(fn ($d) => $d->is_verified && $d->matchesHost($host))) {
            return $this->deny('UNAUTHORIZED_DOMAIN', "Domain '{$host}' tidak diizinkan untuk project ini.", 403);
        }

        app()->instance('current_project', $project);
        app()->instance('current_tenant_id', $apiKey->tenant_id);
        $request->attributes->set('project', $project);
        $request->attributes->set('tenant_id', $apiKey->tenant_id);

        return $next($request);
    }

    private function deny(string $code, string $message, int $status): Response
    {
        return response()->json(['success' => false, 'error' => compact('code', 'message')], $status);
    }
}
