<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\WidgetSetting;
use Illuminate\Database\Seeder;

/**
 * Basis seeder chatbot per website. Data bot (sambutan, opsi, rules, tree) ada di
 * database/seeders/data/{slug}.json; subclass hanya mendeskripsikan project-nya.
 * Idempoten: aman dijalankan ulang tanpa menimpa warna/kanal/jam kerja yang sudah diatur.
 */
abstract class ChatbotSeeder extends Seeder
{
    /** Nama berkas JSON di folder data/ (tanpa ekstensi). */
    abstract protected function dataFile(): string;

    abstract protected function resolveProject(Tenant $tenant): Project;

    abstract protected function resolveApiKey(Tenant $tenant, Project $project): ApiKey;

    /** @return string[] */
    abstract protected function domains(): array;

    /** Atribut awal widget bila belum ada. */
    abstract protected function widgetDefaults(): array;

    abstract protected function botName(?string $current): string;

    abstract protected function offlineMessage(): string;

    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(['slug' => 'indraco'], ['name' => 'PT Indraco Jaya Perkasa', 'plan' => 'enterprise', 'is_active' => true]);
        app()->instance('current_tenant_id', $tenant->id);

        $project = $this->resolveProject($tenant);
        foreach ($this->domains() as $domain) {
            $project->domains()->firstOrCreate(['domain' => $domain], ['is_verified' => true]);
        }
        $apiKey = $this->resolveApiKey($tenant, $project);

        $data = json_decode(file_get_contents(__DIR__."/data/{$this->dataFile()}.json"), true, flags: JSON_THROW_ON_ERROR);

        $ws = WidgetSetting::firstOrNew(['project_id' => $project->id]);
        if (! $ws->exists) {
            $ws->fill($this->widgetDefaults());
        }
        $ws->fill([
            'bot_enabled' => true,
            'bot_mode_query' => true,
            'bot_mode_options' => true,
            'bot_name' => $this->botName($ws->bot_name),
            'bot_welcome_message' => $data['welcome_message'],
            'bot_welcome_options' => $data['welcome_options'],
            'bot_rules' => $data['rules'],
            'bot_tree' => $data['tree'],
            'bot_offline_message' => $ws->bot_offline_message ?: $this->offlineMessage(),
        ])->save();

        $this->command?->info("Chatbot {$project->name} siap: key {$apiKey->public_key}, ".count($data['rules']).' aturan.');
    }
}
