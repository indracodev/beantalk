<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\WidgetSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pembuatan integrasi website (project + domain + API key + widget setting).
 * Satu sumber untuk dashboard web & REST API.
 */
class IntegrationService
{
    /**
     * @param  array{name: string, domain: string, primary_color?: ?string, greeting_title?: ?string, greeting_subtitle?: ?string}  $data
     * @return array{project: Project, domain: string, api_key: ApiKey, setting: WidgetSetting}
     */
    public function create(int $tenantId, array $data, string $defaultColor = '#1E1E1E'): array
    {
        return DB::transaction(function () use ($tenantId, $data, $defaultColor) {
            $slug = Str::slug($data['name']) ?: 'website';
            if (Project::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
                $slug .= '-'.Str::lower(Str::random(4));
            }

            $project = Project::create([
                'tenant_id' => $tenantId,
                'name' => $data['name'],
                'slug' => $slug,
                'is_active' => true,
            ]);

            $domain = self::normalizeDomain($data['domain']);
            $project->domains()->create(['domain' => $domain, 'is_verified' => true]);

            $apiKey = ApiKey::create([
                'tenant_id' => $tenantId,
                'project_id' => $project->id,
                'public_key' => ApiKey::generatePublicKey($domain),
                'name' => 'Default Production Key',
                'is_active' => true,
            ]);

            $setting = WidgetSetting::create([
                'project_id' => $project->id,
                'primary_color' => $data['primary_color'] ?? $defaultColor,
                'greeting_title' => $data['greeting_title'] ?? 'Hallo!',
                'greeting_subtitle' => $data['greeting_subtitle'] ?? 'Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
                'is_online' => true,
            ]);

            return ['project' => $project, 'domain' => $domain, 'api_key' => $apiKey, 'setting' => $setting];
        });
    }

    /** Nonaktifkan semua key lama lalu terbitkan key baru. */
    public function regenerateKey(Project $project): ApiKey
    {
        return DB::transaction(function () use ($project) {
            ApiKey::where('project_id', $project->id)->update(['is_active' => false]);

            return ApiKey::create([
                'tenant_id' => $project->tenant_id,
                'project_id' => $project->id,
                'public_key' => ApiKey::generatePublicKey($project->domains()->value('domain') ?? $project->name),
                'name' => 'Regenerated Key',
                'is_active' => true,
            ]);
        });
    }

    public static function embedScript(string $publicKey): string
    {
        return '<script src="'.url('/chat.js').'" data-key="'.e($publicKey).'" async></script>';
    }

    /** "https://Shop.com/" → "shop.com" */
    public static function normalizeDomain(string $domain): string
    {
        return strtolower(rtrim(preg_replace('#^https?://#i', '', trim($domain)), '/'));
    }
}
