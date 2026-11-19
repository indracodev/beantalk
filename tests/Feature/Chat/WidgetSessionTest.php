<?php

namespace Tests\Feature\Chat;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WidgetSetting;

class WidgetSessionTest extends ChatTestCase
{
    /**
     * Test 1: Client Session Init without key fails (401)
     */
    public function test_session_init_requires_project_key()
    {
        $response = $this->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'MISSING_PROJECT_KEY');
    }

    /**
     * Test 2: Client Session Init with valid public key succeeds (200)
     */
    public function test_session_init_with_valid_key()
    {
        $response = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'page_url' => 'https://supresso.myshopify.com/products/sumatra-capsule',
            'page_title' => 'Supresso Sumatra Mandheling Capsule',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'visitor' => ['uuid'],
                    'conversation' => ['id', 'status'],
                    'project' => ['id', 'name'],
                    'widget' => ['language', 'primary_color', 'greeting_title', 'greeting_subtitle', 'support_title', 'find_us_title', 'social_channels'],
                ],
            ]);
    }

    /**
     * Test 8: Session init generates customer code and accepts customer name
     */
    public function test_session_init_generates_customer_code_and_handles_name()
    {
        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => 'test-uuid-cust-ident-1234',
            'name' => 'Budi Santoso',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.visitor.name', 'Budi Santoso')
            ->assertJsonPath('data.visitor.display_name', 'Budi Santoso');

        $custCode = $res->json('data.visitor.customer_code');
        $this->assertNotNull($custCode);
        $this->assertStringStartsWith('CUS-', $custCode);
    }

    /**
     * Test 9: Visitor can update profile name
     */
    public function test_visitor_can_update_profile_name()
    {
        $uuid = 'test-uuid-update-prof-'.uniqid();

        // Init anonymous session first
        $initRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => $uuid,
        ]);

        $initRes->assertStatus(200);
        $this->assertNull($initRes->json('data.visitor.name'));
        $this->assertStringStartsWith('Tamu · CUS-', $initRes->json('data.visitor.display_name'));

        // Update name
        $updateRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/profile', [
            'visitor_uuid' => $uuid,
            'name' => 'Andi Wijaya',
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.visitor.name', 'Andi Wijaya')
            ->assertJsonPath('data.visitor.display_name', 'Andi Wijaya');
    }

    /**
     * Test 10: Session init does NOT create empty conversation in database
     */
    public function test_session_init_does_not_create_empty_conversation()
    {
        $uniqueUuid = 'fresh-visitor-'.uniqid();
        $convCountBefore = Conversation::withoutGlobalScopes()->count();

        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => $uniqueUuid,
            'name' => 'Visitor Tanpa Chat',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.conversation', null);

        $convCountAfter = Conversation::withoutGlobalScopes()->count();
        $this->assertEquals($convCountBefore, $convCountAfter, 'Session init should not create empty conversation rows.');
    }

    /**
     * Test 7: Public CDN JS endpoints are directly accessible
     */
    public function test_public_widget_scripts_are_accessible()
    {
        $this->get('/chat-widget.js')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');

        $this->get('/chat.js')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');

        $this->get('/widget.js')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    }

    /**
     * Test 15: Widget language configuration and session init integration
     */
    public function test_widget_language_configuration()
    {
        $project = Project::where('slug', 'supresso')->first();
        $user = User::first();
        $user->update(['tenant_id' => $project->tenant_id]);

        // 1. Admin sets language to 'en'
        $response = $this->actingAs($user)->put("/admin/integrations/{$project->id}/settings", [
            'language' => 'en',
            'primary_color' => '#0071E3',
        ]);
        $response->assertRedirect();

        // Verify setting in database
        $setting = WidgetSetting::where('project_id', $project->id)->first();
        $this->assertEquals('en', $setting->language);

        // Verify client session init returns language 'en'
        $initRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => 'visitor-test-lang-en',
        ]);
        $initRes->assertStatus(200)
            ->assertJsonPath('data.widget.language', 'en');

        // 2. Admin sets language back to 'id'
        $response = $this->actingAs($user)->put("/admin/integrations/{$project->id}/settings", [
            'language' => 'id',
            'primary_color' => '#0071E3',
        ]);
        $response->assertRedirect();

        $setting->refresh();
        $this->assertEquals('id', $setting->language);

        // Verify client session init returns language 'id'
        $initResId = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => 'visitor-test-lang-id',
        ]);
        $initResId->assertStatus(200)
            ->assertJsonPath('data.widget.language', 'id');
    }

    /**
     * Test 16: Launcher mascot configuration and session init
     */
    public function test_launcher_mascot_configuration_and_session_init()
    {
        $project = Project::where('slug', 'supresso')->first();
        $user = User::first();
        $user->update(['tenant_id' => $project->tenant_id]);

        // 1. Admin saves settings with mascot launcher
        $response = $this->actingAs($user)->put("/admin/integrations/{$project->id}/settings", [
            'primary_color' => '#0071E3',
            'launcher_type' => 'mascot',
            'mascot_id' => 'panda',
            'mascot_size' => 80,
            'mascot_tracking' => '1',
        ]);
        $response->assertRedirect();

        // Verify database state
        $setting = WidgetSetting::where('project_id', $project->id)->first();
        $this->assertEquals('mascot', $setting->launcher_type);
        $this->assertEquals('panda', $setting->mascot_id);
        $this->assertEquals(80, $setting->mascot_size);
        $this->assertTrue((bool) $setting->mascot_tracking);

        // Verify client session init returns mascot payload
        $initRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => 'visitor-test-mascot-1',
        ]);
        $initRes->assertStatus(200)
            ->assertJsonPath('data.widget.launcher_type', 'mascot')
            ->assertJsonPath('data.widget.mascot_id', 'panda')
            ->assertJsonPath('data.widget.mascot_size', 80)
            ->assertJsonPath('data.widget.mascot_tracking', true);

        // 2. Admin switches back to default balloon
        $response2 = $this->actingAs($user)->put("/admin/integrations/{$project->id}/settings", [
            'primary_color' => '#0071E3',
            'launcher_type' => 'default',
        ]);
        $response2->assertRedirect();

        $setting->refresh();
        $this->assertEquals('default', $setting->launcher_type);

        $initRes2 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => 'visitor-test-mascot-2',
        ]);
        $initRes2->assertStatus(200)
            ->assertJsonPath('data.widget.launcher_type', 'default');
    }
}
