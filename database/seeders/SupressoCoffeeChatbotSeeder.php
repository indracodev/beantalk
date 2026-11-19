<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\Tenant;

/**
 * Chatbot Supresso Coffee SG (supresso.com). Data: data/supresso-coffee.json
 * Hanya menyentuh project 'supresso' — tidak pernah 'supressocoid' (supresso.co.id).
 */
class SupressoCoffeeChatbotSeeder extends ChatbotSeeder
{
    protected function dataFile(): string
    {
        return 'supresso-coffee';
    }

    protected function resolveProject(Tenant $tenant): Project
    {
        return Project::where('slug', 'supresso')
            ->orWhere(fn ($q) => $q->where('name', 'SUPRESSO')->where('slug', '!=', 'supressocoid'))
            ->first()
            ?? Project::create(['tenant_id' => $tenant->id, 'name' => 'SUPRESSO', 'slug' => 'supresso', 'is_active' => true]);
    }

    /** Pertahankan key yang sudah ada (mis. pk_live_supressoco_1970). */
    protected function resolveApiKey(Tenant $tenant, Project $project): ApiKey
    {
        return ApiKey::where('project_id', $project->id)->first() ?? ApiKey::create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'name' => 'SUPRESSO Web Widget Key',
            'public_key' => 'pk_live_supressoco_1970',
            'is_active' => true,
        ]);
    }

    protected function domains(): array
    {
        return ['supresso.com', 'www.supresso.com', 'beantalk.indracoglobal.com', 'localhost', '127.0.0.1'];
    }

    protected function widgetDefaults(): array
    {
        return [
            'primary_color' => '#ff5000',
            'accent_color' => '#FFFFFF',
            'position' => 'bottom-right',
            'language' => 'en',
            'greeting_title' => 'Supresso Coffee',
            'greeting_subtitle' => 'How may we assist you today? Feel free to ask!',
            'support_title' => 'Customer Support',
            'is_online' => true,
        ];
    }

    protected function botName(?string $current): string
    {
        return $current && $current !== 'BeanBot' ? $current : 'Supresso Assistant';
    }

    protected function offlineMessage(): string
    {
        return 'Thank you for reaching out to Supresso Coffee. Our team is currently offline. Please leave your message and contact details, and we will get back to you shortly!';
    }
}
