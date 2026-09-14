<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class ResolveProjectKey
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Ambil Public Key dari Header X-Project-Key atau query parameter
        $publicKey = $request->header('X-Project-Key') 
            ?? $request->input('project_key') 
            ?? $request->input('key');

        if (!$publicKey) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_PROJECT_KEY',
                    'message' => 'Header X-Project-Key atau parameter project_key diperlukan.',
                ]
            ], 401);
        }

        // 2. Lookup API Key aktif beserta relasi Project & Widget Settings
        $apiKey = ApiKey::withoutGlobalScopes()
            ->with(['project.widgetSetting', 'project.domains'])
            ->where('public_key', $publicKey)
            ->where('is_active', true)
            ->first();

        if (!$apiKey || !$apiKey->project || !$apiKey->project->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_PROJECT_KEY',
                    'message' => 'Project Public Key tidak valid atau project tidak aktif.',
                ]
            ], 401);
        }

        $project = $apiKey->project;

        // 3. Domain Whitelist Verification (Origin / Referer)
        $origin = $request->header('Origin') ?? $request->header('Referer');
        if ($origin && $project->domains->isNotEmpty()) {
            $host = parse_url($origin, PHP_URL_HOST);
            
            // Lewatkan localhost / local development domains
            $isLocal = in_array($host, ['localhost', '127.0.0.1', 'chat-me.test']) || empty($host);
            
            if (!$isLocal) {
                $cleanHost = preg_replace('/^www\./i', '', $host);
                $isAllowed = $project->domains->contains(function ($item) use ($host, $cleanHost) {
                    $cleanItemDomain = preg_replace('/^www\./i', '', $item->domain);
                    return $item->is_verified && (
                        strcasecmp($item->domain, $host) === 0 ||
                        strcasecmp($cleanItemDomain, $cleanHost) === 0 ||
                        str_ends_with(strtolower($host), '.' . strtolower($cleanItemDomain)) ||
                        str_ends_with(strtolower($cleanHost), '.' . strtolower($cleanItemDomain))
                    );
                });

                if (!$isAllowed) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'UNAUTHORIZED_DOMAIN',
                            'message' => "Domain '{$host}' tidak diizinkan untuk project ini.",
                        ]
                    ], 403);
                }
            }
        }

        // 4. Bind Project dan Tenant ID ke Container Lifecycle
        app()->instance('current_project', $project);
        app()->instance('current_tenant_id', $apiKey->tenant_id);

        // Bind attributes ke request agar mudah diakses controller
        $request->attributes->set('project', $project);
        $request->attributes->set('tenant_id', $apiKey->tenant_id);

        return $next($request);
    }
}
