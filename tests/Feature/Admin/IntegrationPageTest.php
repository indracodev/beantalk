<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use App\Models\WidgetSetting;

class IntegrationPageTest extends AdminTestCase
{
    /**
     * Test Authenticated User can view Integrations Hub
     */
    public function test_authenticated_user_can_view_integrations()
    {
        $response = $this->actingAs($this->agent)->get('/admin/integrations');

        $response->assertStatus(200)
            ->assertSee('Integrasi Web')
            ->assertSee('Test Dashboard Project');
    }

    /**
     * Test Admin can store new integration project
     */
    public function test_admin_can_store_new_integration()
    {
        $response = $this->actingAs($this->superadmin)->post('/admin/integrations', [
            'name' => 'Toko Cabang Surabaya',
            'domain' => 'surabaya.tokokita.com',
            'primary_color' => '#3B82F6',
            'greeting_title' => 'Selamat Datang!',
            'greeting_subtitle' => 'Customer Service Surabaya siap melayani Anda.',
        ]);

        $response->assertRedirect(route('admin.integrations'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Toko Cabang Surabaya',
        ]);

        $this->assertDatabaseHas('project_domains', [
            'domain' => 'surabaya.tokokita.com',
        ]);

        $this->assertDatabaseHas('widget_settings', [
            'primary_color' => '#3B82F6',
        ]);
    }

    /**
     * Test Admin can update widget settings including Find Us Somewhere Else social channels
     */
    public function test_admin_can_update_widget_and_social_channel_settings()
    {
        $payload = [
            'primary_color' => '#8a2332',
            'greeting_title' => 'Halo Pelanggan Setia!',
            'greeting_subtitle' => 'Ada yang bisa kami bantu?',
            'support_title' => 'Customer Care Indraco',
            'find_us_title' => 'Temukan Kami di Platform Lain',
            'channels' => [
                'whatsapp' => ['enabled' => '1', 'url' => '0812-3456-7890'],
                'instagram' => ['enabled' => '1', 'url' => '@indraco_coffee'],
                'telegram' => ['enabled' => '1', 'url' => 'indracocare'],
                'shopee' => ['enabled' => '1', 'url' => 'https://shopee.co.id/indraco'],
            ],
        ];

        $response = $this->actingAs($this->superadmin)
            ->put("/admin/integrations/{$this->project->id}/settings", $payload);

        $response->assertSessionHas('success');

        $setting = WidgetSetting::where('project_id', $this->project->id)->first();
        $this->assertNotNull($setting);
        $this->assertEquals('#8a2332', $setting->primary_color);
        $this->assertEquals('Customer Care Indraco', $setting->support_title);
        $this->assertEquals('Temukan Kami di Platform Lain', $setting->find_us_title);

        $channels = $setting->social_channels;
        $this->assertIsArray($channels);
        $this->assertCount(8, $channels);

        $activeChannels = array_filter($channels, function ($c) {
            return ! empty($c['enabled']);
        });
        $this->assertCount(4, $activeChannels);

        // Check WA formatted with wa.me and cleaned country code
        $wa = collect($channels)->firstWhere('id', 'whatsapp');
        $this->assertNotNull($wa);
        $this->assertTrue($wa['enabled']);
        $this->assertEquals('https://wa.me/6281234567890', $wa['url']);

        // Check IG formatted with instagram.com
        $ig = collect($channels)->firstWhere('id', 'instagram');
        $this->assertNotNull($ig);
        $this->assertTrue($ig['enabled']);
        $this->assertEquals('https://instagram.com/indraco_coffee', $ig['url']);

        // Check TG formatted with t.me
        $tg = collect($channels)->firstWhere('id', 'telegram');
        $this->assertNotNull($tg);
        $this->assertTrue($tg['enabled']);
        $this->assertEquals('https://t.me/indracocare', $tg['url']);
    }

    /**
     * Test Admin can save multiple contacts of the same platform (e.g. multiple WhatsApp numbers)
     */
    public function test_admin_can_save_multi_instance_social_channels()
    {
        $payload = [
            'primary_color' => '#0071E3',
            'find_us_title' => 'Hubungi Kami',
            'social_channels' => json_encode([
                [
                    'id' => 'wa_sales',
                    'platform' => 'whatsapp',
                    'name' => 'CS 1 - Sales & Pemesanan',
                    'url' => '081234567890',
                    'enabled' => true,
                ],
                [
                    'id' => 'wa_retur',
                    'platform' => 'whatsapp',
                    'name' => 'CS 2 - Retur & Klaim',
                    'url' => '089876543210',
                    'enabled' => true,
                ],
                [
                    'id' => 'ig_official',
                    'platform' => 'instagram',
                    'name' => 'Instagram Official',
                    'url' => '@supresso_coffee',
                    'enabled' => true,
                ],
            ]),
        ];

        $response = $this->actingAs($this->superadmin)
            ->put("/admin/integrations/{$this->project->id}/settings", $payload);

        $response->assertSessionHas('success');

        $setting = WidgetSetting::where('project_id', $this->project->id)->first();
        $this->assertNotNull($setting);

        $channels = $setting->social_channels;
        $this->assertIsArray($channels);
        $this->assertCount(3, $channels);

        // Verify both WA numbers are preserved and normalized
        $waList = array_values(array_filter($channels, function ($c) {
            return $c['platform'] === 'whatsapp';
        }));
        $this->assertCount(2, $waList);
        $this->assertEquals('CS 1 - Sales & Pemesanan', $waList[0]['name']);
        $this->assertEquals('https://wa.me/6281234567890', $waList[0]['url']);
        $this->assertEquals('CS 2 - Retur & Klaim', $waList[1]['name']);
        $this->assertEquals('https://wa.me/6289876543210', $waList[1]['url']);

        $ig = collect($channels)->firstWhere('platform', 'instagram');
        $this->assertNotNull($ig);
        $this->assertEquals('https://instagram.com/supresso_coffee', $ig['url']);
    }

    /**
     * Test Admin can save TikTok, YouTube, Facebook, and Custom Icon social channels
     */
    public function test_admin_can_save_tik_tok_you_tube_facebook_and_custom_icon_channels()
    {
        $payload = [
            'primary_color' => '#0071E3',
            'find_us_title' => 'Ikuti Kami di Media Sosial',
            'social_channels' => json_encode([
                [
                    'id' => 'fb_1',
                    'platform' => 'facebook',
                    'name' => 'Facebook Resmi',
                    'url' => 'supressocoffee',
                    'enabled' => true,
                    'icon_type' => 'default',
                    'custom_icon' => '',
                ],
                [
                    'id' => 'tt_1',
                    'platform' => 'tiktok',
                    'name' => 'TikTok Official',
                    'url' => '@supresso.id',
                    'enabled' => true,
                    'icon_type' => 'default',
                    'custom_icon' => '',
                ],
                [
                    'id' => 'yt_1',
                    'platform' => 'youtube',
                    'name' => 'YouTube Channel',
                    'url' => '@SupressoCoffee',
                    'enabled' => true,
                    'icon_type' => 'default',
                    'custom_icon' => '',
                ],
                [
                    'id' => 'custom_1',
                    'platform' => 'custom',
                    'name' => 'Katalog Produk Spesial',
                    'url' => 'https://supresso.com/catalog',
                    'enabled' => true,
                    'icon_type' => 'custom',
                    'custom_icon' => 'https://supresso.com/images/catalog-icon.svg',
                ],
            ]),
        ];

        $response = $this->actingAs($this->superadmin)
            ->put("/admin/integrations/{$this->project->id}/settings", $payload);

        $response->assertSessionHas('success');

        $setting = WidgetSetting::where('project_id', $this->project->id)->first();
        $this->assertNotNull($setting);

        $channels = $setting->social_channels;
        $this->assertIsArray($channels);
        $this->assertCount(4, $channels);

        // Verify Facebook
        $fb = collect($channels)->firstWhere('platform', 'facebook');
        $this->assertNotNull($fb);
        $this->assertEquals('Facebook Resmi', $fb['name']);
        $this->assertEquals('https://facebook.com/supressocoffee', $fb['url']);
        $this->assertEquals('default', $fb['icon_type']);

        // Verify TikTok
        $tt = collect($channels)->firstWhere('platform', 'tiktok');
        $this->assertNotNull($tt);
        $this->assertEquals('TikTok Official', $tt['name']);
        $this->assertEquals('https://tiktok.com/@supresso.id', $tt['url']);

        // Verify YouTube
        $yt = collect($channels)->firstWhere('platform', 'youtube');
        $this->assertNotNull($yt);
        $this->assertEquals('YouTube Channel', $yt['name']);
        $this->assertEquals('https://youtube.com/@SupressoCoffee', $yt['url']);

        // Verify Custom Channel with custom_icon
        $custom = collect($channels)->firstWhere('platform', 'custom');
        $this->assertNotNull($custom);
        $this->assertEquals('custom', $custom['icon_type']);
        $this->assertEquals('https://supresso.com/images/catalog-icon.svg', $custom['custom_icon']);
    }

    /**
     * Test Authenticated Admin can view dedicated integration detail page
     */
    public function test_authenticated_user_can_view_integration_detail()
    {
        $response = $this->actingAs($this->superadmin)->get("/admin/integrations/{$this->project->id}");

        $response->assertStatus(200)
            ->assertSee($this->project->name)
            ->assertSee('Smart Bot &amp; FAQ Rules', false)
            ->assertSee('Tampilan &amp; Branding', false)
            ->assertSee('Saluran Sosial &amp; Marketplace', false);
    }

    /**
     * Test Admin can update integration name and domains whitelist
     */
    public function test_admin_can_update_integration_name_and_domains()
    {
        $response = $this->actingAs($this->superadmin)->put("/admin/integrations/{$this->project->id}/settings", [
            'name' => 'Updated Brand Name',
            'domains' => 'https://newdomain.com/, www.newdomain.com, api.newdomain.com',
            'primary_color' => '#0071E3',
        ]);

        $response->assertRedirect("/admin/integrations/{$this->project->id}");

        $this->project->refresh();
        $this->assertEquals('Updated Brand Name', $this->project->name);

        $domains = $this->project->domains()->pluck('domain')->toArray();
        $this->assertContains('newdomain.com', $domains);
        $this->assertContains('www.newdomain.com', $domains);
        $this->assertContains('api.newdomain.com', $domains);
    }

    /**
     * Test Admin can assign multiple agents or default to all
     */
    public function test_admin_can_assign_multiple_agents_to_project()
    {
        // 1. Assign specific agent
        $response = $this->actingAs($this->superadmin)->put("/admin/integrations/{$this->project->id}/settings", [
            'name' => $this->project->name,
            'primary_color' => '#0071E3',
            'agent_scope' => 'selected',
            'agent_ids' => [$this->agent->id],
        ]);

        $response->assertRedirect("/admin/integrations/{$this->project->id}");
        $this->project->refresh();
        $this->assertCount(1, $this->project->assignedUsers);
        $this->assertEquals($this->agent->id, $this->project->assignedUsers->first()->id);

        // 2. Set back to all agents (default)
        $responseAll = $this->actingAs($this->superadmin)->put("/admin/integrations/{$this->project->id}/settings", [
            'name' => $this->project->name,
            'primary_color' => '#0071E3',
            'agent_scope' => 'all',
        ]);

        $responseAll->assertRedirect("/admin/integrations/{$this->project->id}");
        $this->project->refresh();
        $this->assertCount(0, $this->project->assignedUsers);
        $this->assertNull($this->project->assigned_user_id);
    }
}
