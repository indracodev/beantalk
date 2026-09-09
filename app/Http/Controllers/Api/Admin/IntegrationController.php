<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Project;
use App\Models\ProjectDomain;
use App\Models\Tenant;
use App\Models\WidgetSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    /**
     * Lists all integrated websites and their public keys
     * GET /api/v1/admin/integrations
     */
    public function index(Request $request)
    {
        $projects = Project::with(['domains', 'apiKeys', 'widgetSetting'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'integrations' => $projects->map(function ($project) {
                    $activeKey = $project->apiKeys->where('is_active', true)->first();
                    $domain = $project->domains->first();
                    $setting = $project->widgetSetting;

                    return [
                        'id'            => $project->id,
                        'name'          => $project->name,
                        'slug'          => $project->slug,
                        'domain'        => $domain ? $domain->domain : null,
                        'public_key'    => $activeKey ? $activeKey->public_key : null,
                        'primary_color' => $setting ? $setting->primary_color : '#1E1E1E',
                        'is_active'     => $project->is_active,
                        'embed_script'  => $activeKey 
                            ? "<script src=\"" . url('/chat.js') . "\" data-key=\"{$activeKey->public_key}\" async></script>"
                            : null,
                    ];
                })
            ]
        ]);
    }

    /**
     * Creates a new website integration and returns embed script tag
     * POST /api/v1/admin/integrations
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:100',
            'domain'            => 'required|string|max:255',
            'primary_color'     => 'nullable|string|max:10',
            'greeting_title'    => 'nullable|string|max:100',
            'greeting_subtitle' => 'nullable|string|max:255',
        ]);

        // Default to first tenant if not explicitly set (Single tenant / multi project)
        $tenantId = (app()->bound('current_tenant_id') ? app('current_tenant_id') : null) ?? Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Organization', 'plan' => 'enterprise']
        )->id;

        $slug = Str::slug($request->input('name'));
        if (Project::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        // 1. Buat Project
        $project = Project::create([
            'tenant_id' => $tenantId,
            'name'      => $request->input('name'),
            'slug'      => $slug,
            'is_active' => true,
        ]);

        // 2. Daftarkan Domain Whitelist
        $domain = preg_replace('#^https?://#', '', rtrim($request->input('domain'), '/'));
        ProjectDomain::create([
            'project_id'  => $project->id,
            'domain'      => $domain,
            'is_verified' => true,
        ]);

        // 3. Generate Public Key
        $domainClean = preg_replace('/[^a-z0-9]/', '', strtolower($domain));
        $publicKey = 'pk_live_' . substr($domainClean, 0, 10) . '_' . mt_rand(1000, 9999);
        
        $apiKey = ApiKey::create([
            'tenant_id'  => $tenantId,
            'project_id' => $project->id,
            'public_key' => $publicKey,
            'name'       => 'Default Production Key',
            'is_active'  => true,
        ]);

        // 4. Buat Widget Settings
        $setting = WidgetSetting::create([
            'project_id'        => $project->id,
            'primary_color'     => $request->input('primary_color', '#1E1E1E'),
            'greeting_title'    => $request->input('greeting_title', 'Hallo!'),
            'greeting_subtitle' => $request->input('greeting_subtitle', 'Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!'),
            'is_online'         => true,
        ]);

        $embedScript = "<script src=\"" . url('/chat.js') . "\" data-key=\"{$publicKey}\" async></script>";

        \App\Services\ActivityLogger::log(
            'integration.created',
            "Membuat integrasi website baru: {$project->name} ({$domain}) dengan warna {$setting->primary_color}",
            $project,
            ['domain' => $domain, 'public_key' => $publicKey, 'primary_color' => $setting->primary_color]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'project_id'    => $project->id,
                'name'          => $project->name,
                'domain'        => $domain,
                'public_key'    => $publicKey,
                'primary_color' => $setting->primary_color,
                'embed_script'  => $embedScript,
            ]
        ], 201);
    }
}
