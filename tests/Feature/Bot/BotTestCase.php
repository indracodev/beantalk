<?php

namespace Tests\Feature\Bot;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WidgetSetting;
use Tests\TestCase;

/** Fixture bot: tenant, project dengan bot aktif, aturan FAQ, admin & agent. */
abstract class BotTestCase extends TestCase
{
    protected $tenant;

    protected $project;

    protected $apiKey;

    protected $widgetSetting;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'indraco'],
            ['name' => 'PT Indraco Jaya Perkasa', 'is_active' => true]
        );

        $this->project = Project::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'bot-test-project'],
            ['name' => 'Bot Test Project', 'is_active' => true]
        );

        $this->apiKey = ApiKey::firstOrCreate(
            ['public_key' => 'pk_live_bottest_9999'],
            [
                'tenant_id' => $this->tenant->id,
                'project_id' => $this->project->id,
                'name' => 'Bot Test Key',
                'is_active' => true,
            ]
        );

        $this->widgetSetting = WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'primary_color' => '#1E1E1E',
                'accent_color' => '#FFFFFF',
                'position' => 'bottom-right',
                'greeting_title' => 'Halo!',
                'greeting_subtitle' => 'Ada yang bisa dibantu?',
                'is_online' => true,
                'bot_enabled' => true,
                'bot_name' => 'BeanBot Assistant',
                'bot_welcome_message' => 'Halo! Saya BeanBot, asisten virtual Anda. Ada yang bisa saya bantu?',
                'bot_offline_message' => 'Maaf, CS kami sedang offline saat ini.',
                'bot_rules' => [
                    [
                        'keywords' => 'ongkir, pengiriman, kurir',
                        'response' => 'Pengiriman kami menggunakan JNE, J&T, dan SiCepat.',
                    ],
                    [
                        'keywords' => 'jam buka, operasional',
                        'response' => 'Jam operasional kami adalah Senin - Jumat pukul 08:00 - 17:00 WIB.',
                    ],
                ],
            ]
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_bot@beantalk.test'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Admin Bot Tester',
                'username' => 'admin_bot',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'is_active' => true,
            ]
        );
    }
}
