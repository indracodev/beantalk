<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIntegrationRequest;
use App\Models\Project;
use App\Services\ActivityLogger;
use App\Services\IntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /** GET /api/v1/admin/integrations */
    public function index(Request $request): JsonResponse
    {
        $projects = Project::where('tenant_id', $request->user()->tenant_id)->with(['domains', 'activeApiKey', 'widgetSetting'])->get();

        return $this->ok(['integrations' => $projects->map(fn (Project $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'domain' => $p->domains->first()?->domain,
            'public_key' => $p->activeApiKey?->public_key,
            'primary_color' => $p->widgetSetting?->primary_color ?? '#1E1E1E',
            'is_active' => $p->is_active,
            'embed_script' => $p->activeApiKey ? IntegrationService::embedScript($p->activeApiKey->public_key) : null,
        ])]);
    }

    /** POST /api/v1/admin/integrations (superadmin) */
    public function store(StoreIntegrationRequest $request, IntegrationService $integrations): JsonResponse
    {
        ['project' => $project, 'domain' => $domain, 'api_key' => $key, 'setting' => $setting]
            = $integrations->create($request->user()->tenant_id, $request->validated());

        ActivityLogger::log(
            'integration.created',
            "Membuat integrasi website baru: {$project->name} ({$domain}) dengan warna {$setting->primary_color}",
            $project,
            ['domain' => $domain, 'public_key' => $key->public_key, 'primary_color' => $setting->primary_color],
        );

        return $this->ok([
            'project_id' => $project->id,
            'name' => $project->name,
            'domain' => $domain,
            'public_key' => $key->public_key,
            'primary_color' => $setting->primary_color,
            'embed_script' => IntegrationService::embedScript($key->public_key),
        ], 201);
    }
}
