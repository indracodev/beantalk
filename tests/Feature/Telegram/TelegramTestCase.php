<?php

namespace Tests\Feature\Telegram;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Services\TelegramService;
use Tests\TestCase;

/** Fixture Telegram: tenant, project + widget setting Telegram, admin & agent ber-username Telegram. */
abstract class TelegramTestCase extends TestCase
{
    protected $tenant;

    protected $admin;

    protected $project;

    protected $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'telegram-test-tenant'],
            ['name' => 'Telegram Test Tenant', 'plan' => 'enterprise']
        );

        $this->admin = User::firstOrCreate(
            ['username' => 'teleadmin'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Telegram Admin',
                'email' => 'teleadmin@indraco.com',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'status' => 'online',
            ]
        );
        $this->admin->tenant_id = $this->tenant->id;
        $this->admin->save();

        $this->project = Project::firstOrCreate(
            ['project_key' => 'PK-TELEGRAMTEST123'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Toko Sepatu',
                'slug' => 'toko-sepatu',
                'domain' => 'tokosepatu.com',
                'status' => 'active',
            ]
        );
        $this->project->tenant_id = $this->tenant->id;
        $this->project->save();

        $this->visitor = Visitor::firstOrCreate(
            ['visitor_uuid' => 'VT-TELEGRAM-12345'],
            [
                'project_id' => $this->project->id,
                'name' => 'Budi Santoso',
                'customer_code' => 'CUS-77889',
            ]
        );
        $this->visitor->project_id = $this->project->id;
        $this->visitor->save();
    }

    /** Header secret token yang didaftarkan setWebhook untuk bot uji (B3). */
    protected function secretHeader(): array
    {
        return ['X-Telegram-Bot-Api-Secret-Token' => TelegramService::webhookSecret('123456:TEST_TOKEN')];
    }
}
