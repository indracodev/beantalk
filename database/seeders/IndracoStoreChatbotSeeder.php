<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\Tenant;

/** Chatbot INDRACO Store (indracostore.com). Data: data/indraco-store.json */
class IndracoStoreChatbotSeeder extends ChatbotSeeder
{
    protected function dataFile(): string
    {
        return 'indraco-store';
    }

    protected function resolveProject(Tenant $tenant): Project
    {
        return Project::firstOrCreate(['tenant_id' => $tenant->id, 'slug' => 'indracostore'], ['name' => 'INDRACO Store', 'is_active' => true]);
    }

    protected function resolveApiKey(Tenant $tenant, Project $project): ApiKey
    {
        return ApiKey::firstOrCreate(['public_key' => 'pk_live_indracostore_prod'], [
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'name' => 'INDRACO Store Web Widget Key',
            'is_active' => true,
        ]);
    }

    protected function domains(): array
    {
        return ['indracostore.com', 'www.indracostore.com', 'beantalk.indracoglobal.com', 'localhost', '127.0.0.1'];
    }

    protected function widgetDefaults(): array
    {
        return [
            'primary_color' => '#585858',
            'accent_color' => '#D4AF37',
            'position' => 'bottom-right',
            'greeting_title' => 'INDRACO Store',
            'greeting_subtitle' => 'Layanan Pelanggan & Tanya Jawab Produk',
            'is_online' => true,
        ];
    }

    protected function botName(?string $current): string
    {
        return $current ?: 'INDRACO Assistant';
    }

    protected function offlineMessage(): string
    {
        return 'Halo! Layanan konsultasi CS kami saat ini di luar jam operasional. Anda tetap dapat menggunakan bot otomatis atau meninggalkan pesan & nomor WhatsApp. Tim kami akan segera menghubungi Anda kembali.';
    }
}
