<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WidgetSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SoundSettingsIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;
    protected User $admin;
    protected Project $project;
    protected WidgetSetting $widgetSetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'   => 'Sound Tenant',
            'slug'   => 'sound-tenant-' . uniqid(),
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Sound Admin',
            'email'     => 'soundadmin_' . uniqid() . '@example.com',
            'password'  => bcrypt('password123'),
            'role'      => 'superadmin',
        ]);

        $this->project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Sound Store',
            'slug'      => 'sound-store-' . uniqid(),
            'status'    => 'active',
        ]);

        $this->widgetSetting = WidgetSetting::create([
            'project_id'     => $this->project->id,
            'primary_color'  => '#0071E3',
            'sound_enabled'  => true,
            'sound_type'     => 'pedestrian',
            'sound_duration' => 15,
        ]);
    }

    public function test_integration_detail_view_contains_sound_settings_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.integrations.detail', $this->project->id));

        $response->assertStatus(200);
        $response->assertSee('tab-btn-sound', false);
        $response->assertSee('tab-pane-sound', false);
        $response->assertSee('Lampu Merah Penyeberangan', false);
        $response->assertSee('Suara Ambulans', false);
        $response->assertSee('Mobil Dinas / Patwal', false);
        $response->assertSee('btnTestPlaySound', false);
        $response->assertSee('btnTestStopSound', false);
    }

    public function test_admin_can_update_sound_settings(): void
    {
        $payload = [
            'primary_color'  => '#E11D48',
            'sound_enabled'  => '1',
            'sound_type'     => 'ambulance',
            'sound_duration' => 30,
        ];

        $response = $this->actingAs($this->admin)->put(
            route('admin.integrations.settings', $this->project->id),
            $payload
        );

        $response->assertRedirect();
        $this->widgetSetting->refresh();

        $this->assertTrue($this->widgetSetting->sound_enabled);
        $this->assertEquals('ambulance', $this->widgetSetting->sound_type);
        $this->assertEquals(30, $this->widgetSetting->sound_duration);
    }

    public function test_admin_can_upload_custom_sound_file(): void
    {
        Storage::fake('public');

        $fakeMp3 = UploadedFile::fake()->createWithContent('custom-chime.mp3', 'fake mp3 sound file data');

        $payload = [
            'primary_color'     => '#0071E3',
            'sound_enabled'     => '1',
            'sound_type'        => 'custom',
            'sound_duration'    => 20,
            'sound_custom_file' => $fakeMp3,
        ];

        $response = $this->actingAs($this->admin)->put(
            route('admin.integrations.settings', $this->project->id),
            $payload
        );

        $response->assertRedirect();
        $this->widgetSetting->refresh();

        $this->assertEquals('custom', $this->widgetSetting->sound_type);
        $this->assertEquals(20, $this->widgetSetting->sound_duration);
        $this->assertNotNull($this->widgetSetting->sound_custom_url);
        $this->assertStringContainsString('/storage/sounds/', $this->widgetSetting->sound_custom_url);
    }

    public function test_dashboard_poll_updates_includes_sound_payload_for_latest_incoming(): void
    {
        $visitor = Visitor::create([
            'project_id'       => $this->project->id,
            'visitor_uuid'     => 'vis-' . uniqid(),
            'name'             => 'Customer Sound Test',
            'last_seen_at'     => now(),
        ]);

        $conversation = Conversation::create([
            'tenant_id'          => $this->tenant->id,
            'project_id'         => $this->project->id,
            'visitor_id'         => $visitor->id,
            'status'             => 'open',
            'unread_agent_count' => 1,
        ]);

        $msg = Message::create([
            'tenant_id'       => $this->tenant->id,
            'conversation_id' => $conversation->id,
            'sender_type'     => 'visitor',
            'sender_name'     => 'Customer Sound Test',
            'content'         => 'Halo apakah toko buka?',
        ]);

        $response = $this->actingAs($this->admin)->getJson(
            route('admin.inbox.updates', ['since_message_id' => $msg->id - 1])
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'latest_incoming' => [
                    'message_id',
                    'conversation_id',
                    'sound_enabled',
                    'sound_type',
                    'sound_duration',
                    'sound_custom_url',
                ],
            ],
        ]);

        $json = $response->json();
        $this->assertTrue($json['data']['latest_incoming']['sound_enabled']);
        $this->assertEquals('pedestrian', $json['data']['latest_incoming']['sound_type']);
        $this->assertEquals(15, $json['data']['latest_incoming']['sound_duration']);
    }

    public function test_admin_can_update_customer_widget_sound_settings(): void
    {
        $payload = [
            'primary_color'         => '#0071E3',
            'widget_sound_enabled'  => '1',
            'widget_sound_type'     => 'pop',
        ];

        $response = $this->actingAs($this->admin)->put(
            route('admin.integrations.settings', $this->project->id),
            $payload
        );

        $response->assertRedirect();
        $this->widgetSetting->refresh();

        $this->assertTrue($this->widgetSetting->widget_sound_enabled);
        $this->assertEquals('pop', $this->widgetSetting->widget_sound_type);
    }

    public function test_session_init_returns_customer_widget_sound_settings(): void
    {
        $this->widgetSetting->update([
            'widget_sound_enabled' => true,
            'widget_sound_type'    => 'ding',
        ]);

        $apiKey = \App\Models\ApiKey::create([
            'tenant_id'  => $this->tenant->id,
            'project_id' => $this->project->id,
            'public_key' => 'pk_live_test_sound_' . uniqid(),
            'name'       => 'Test Key',
            'is_active'  => true,
        ]);

        $response = $this->withHeaders([
            'X-Project-Key' => $apiKey->public_key,
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => 'vis-uuid-' . uniqid(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.widget.sound_enabled', true);
        $response->assertJsonPath('data.widget.sound_type', 'ding');
    }
}
