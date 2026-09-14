<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tenant = \App\Models\Tenant::firstOrCreate(['slug' => 'indraco'], ['name' => 'PT Indraco Jaya Perkasa', 'is_active' => true]);
        $project = \App\Models\Project::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'supresso'],
            ['name' => 'Supresso Coffee', 'is_active' => true]
        );
        \App\Models\ApiKey::firstOrCreate(
            ['public_key' => 'pk_live_supresso_8819'],
            ['tenant_id' => $tenant->id, 'project_id' => $project->id, 'name' => 'Supresso Test Key', 'is_active' => true]
        );
        \App\Models\WidgetSetting::firstOrCreate(
            ['project_id' => $project->id],
            ['primary_color' => '#1E1E1E', 'accent_color' => '#FFFFFF', 'position' => 'bottom-right', 'greeting_title' => 'Hallo!', 'greeting_subtitle' => 'Ada yang bisa dibantu?', 'is_online' => true]
        );

        $visitor = \App\Models\Visitor::firstOrCreate(
            ['project_id' => $project->id, 'visitor_uuid' => '550e8400-e29b-41d4-a716-446655440000'],
            ['name' => 'Jane Doe', 'tenant_id' => $project->tenant_id]
        );

        \Illuminate\Support\Facades\DB::table('conversations')->updateOrInsert(
            ['id' => 1],
            [
                'project_id'           => $project->id,
                'tenant_id'            => $project->tenant_id,
                'visitor_id'           => $visitor->id,
                'status'               => 'open',
                'channel'              => 'widget',
                'unread_agent_count'   => 0,
                'unread_visitor_count' => 0,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]
        );
    }

    /**
     * Test 1: Client Session Init without key fails (401)
     */
    public function testSessionInitRequiresProjectKey()
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
    public function testSessionInitWithValidKey()
    {
        $response = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'page_url'     => 'https://supresso.myshopify.com/products/sumatra-capsule',
            'page_title'   => 'Supresso Sumatra Mandheling Capsule',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'visitor' => ['uuid'],
                         'conversation' => ['id', 'status'],
                         'project' => ['id', 'name'],
                         'widget' => ['primary_color', 'greeting_title', 'greeting_subtitle', 'support_title', 'find_us_title', 'social_channels'],
                     ]
                 ]);
    }

    /**
     * Test 3: Polling messages via range scan
     */
    public function testPollMessagesRangeScan()
    {
        $response = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->getJson('/api/v1/client/conversations/1/messages?after_id=0');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'messages',
                         'last_id',
                     ]
                 ]);
    }

    /**
     * Test 4: Send message with idempotency
     */
    public function testSendMessageWithIdempotency()
    {
        $clientMsgId = 'test_msg_idempotent_' . uniqid();

        // Send 1st time
        $res1 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/1/messages', [
            'client_message_id' => $clientMsgId,
            'content'           => 'Apakah kemasan ini ramah lingkungan?',
            'content_type'      => 'text',
        ]);

        $res1->assertStatus(201)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.is_duplicate', false);

        $msgId = $res1->json('data.id');

        // Send 2nd time (Retry with same client_message_id)
        $res2 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/1/messages', [
            'client_message_id' => $clientMsgId,
            'content'           => 'Apakah kemasan ini ramah lingkungan?',
            'content_type'      => 'text',
        ]);

        $res2->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.is_duplicate', true)
             ->assertJsonPath('data.id', $msgId); // Same message returned, no duplicate created!
    }

    /**
     * Test 5: Admin lists inbox conversations
     */
    public function testAdminListConversations()
    {
        $admin = \App\Models\User::first();
        $response = $this->actingAs($admin)->getJson('/api/v1/admin/conversations');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'conversations',
                         'pagination',
                     ]
                 ]);
    }

    /**
     * Test 6: Admin replies to conversation
     */
    public function testAdminReplyConversation()
    {
        $admin = \App\Models\User::first();
        $response = $this->actingAs($admin)->postJson('/api/v1/admin/conversations/1/reply', [
            'content' => 'Tentu kak, seluruh kapsul Supresso 100% aluminium daur ulang.',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.sender_type', 'agent');
    }

    /**
     * Test 6b: Unauthenticated request to admin conversations is rejected
     */
    public function testUnauthenticatedAdminAccessRejected()
    {
        $response = $this->getJson('/api/v1/admin/conversations');

        $response->assertStatus(401)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    /**
     * Test 7: Public CDN JS endpoints are directly accessible
     */
    public function testPublicWidgetScriptsAreAccessible()
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
     * Test 8: Session init generates customer code and accepts customer name
     */
    public function testSessionInitGeneratesCustomerCodeAndHandlesName()
    {
        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => 'test-uuid-cust-ident-1234',
            'name'         => 'Budi Santoso',
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
    public function testVisitorCanUpdateProfileName()
    {
        $uuid = 'test-uuid-update-prof-' . uniqid();

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
            'name'         => 'Andi Wijaya',
        ]);

        $updateRes->assertStatus(200)
                  ->assertJsonPath('success', true)
                  ->assertJsonPath('data.visitor.name', 'Andi Wijaya')
                  ->assertJsonPath('data.visitor.display_name', 'Andi Wijaya');
    }
}
