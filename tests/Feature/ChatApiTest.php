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
                         'widget' => ['language', 'primary_color', 'greeting_title', 'greeting_subtitle', 'support_title', 'find_us_title', 'social_channels'],
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
        $conv = \App\Models\Conversation::where('tenant_id', $admin->tenant_id)->first();
        if (!$conv) {
            $project = \App\Models\Project::where('tenant_id', $admin->tenant_id)->first() ?? \App\Models\Project::first();
            $visitor = \App\Models\Visitor::first();
            $conv = \App\Models\Conversation::create([
                'tenant_id' => $admin->tenant_id,
                'project_id' => $project->id,
                'visitor_id' => $visitor->id,
                'channel_type' => 'widget',
                'status' => 'open',
            ]);
        }

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/conversations/{$conv->id}/reply", [
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

    /**
     * Test 10: Session init does NOT create empty conversation in database
     */
    public function testSessionInitDoesNotCreateEmptyConversation()
    {
        $uniqueUuid = 'fresh-visitor-' . uniqid();
        $convCountBefore = \App\Models\Conversation::withoutGlobalScopes()->count();

        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => $uniqueUuid,
            'name'         => 'Visitor Tanpa Chat',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.conversation', null);

        $convCountAfter = \App\Models\Conversation::withoutGlobalScopes()->count();
        $this->assertEquals($convCountBefore, $convCountAfter, 'Session init should not create empty conversation rows.');
    }

    /**
     * Test 11: First customer message creates conversation lazily
     */
    public function testFirstMessageCreatesConversationLazily()
    {
        $uniqueUuid = 'chatting-visitor-' . uniqid();

        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uniqueUuid,
            'sender_name'  => 'Ibu Maya',
            'content'      => 'Halo kak, apakah ada promo diskon hari ini?',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sender_name', 'Ibu Maya')
            ->assertJsonPath('data.content', 'Halo kak, apakah ada promo diskon hari ini?');

        $convId = $res->json('data.conversation_id');
        $this->assertNotNull($convId);
        $this->assertGreaterThan(0, $convId);

        $conv = \App\Models\Conversation::find($convId);
        $this->assertNotNull($conv);
        $this->assertEquals('Halo kak, apakah ada promo diskon hari ini?', $conv->last_message_preview);

        // Next page load / session init must return this active conversation and messages directly
        $initAfterChat = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => $uniqueUuid,
        ]);
        $initAfterChat->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.conversation.id', $convId)
            ->assertJsonPath('data.conversation.status', 'open');
        $this->assertNotEmpty($initAfterChat->json('data.conversation.messages'));
    }

    /**
     * Test 12: Customer can resolve ticket, view ticket history, and start a fresh ticket
     */
    public function testCustomerCanResolveConversationAndStartNewTicket()
    {
        $uuid = 'visitor-multi-ticket-' . uniqid();

        // 1. Kirim pesan pertama untuk membuat tiket #1
        $res1 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uuid,
            'sender_name'  => 'Pelanggan 1',
            'content'      => 'Pertanyaan tiket pertama',
        ]);
        $res1->assertStatus(201);
        $conv1Id = $res1->json('data.conversation_id');

        // 2. Pelanggan menyelesaikan tiket #1
        $resolveRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson("/api/v1/client/conversations/{$conv1Id}/resolve", [
            'visitor_uuid' => $uuid,
        ]);
        $resolveRes->assertStatus(200)
                   ->assertJsonPath('success', true)
                   ->assertJsonPath('data.status', 'closed');

        $this->assertEquals('closed', \App\Models\Conversation::find($conv1Id)->status);

        // 3. Mengirim pesan ke tiket yang sudah closed harus ditolak
        $closedRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson("/api/v1/client/conversations/{$conv1Id}/messages", [
            'visitor_uuid' => $uuid,
            'content'      => 'Pesan di tiket yang sudah tutup',
        ]);
        $closedRes->assertStatus(422)
                  ->assertJsonPath('error.code', 'CONVERSATION_CLOSED');

        // 4. Pelanggan membuat tiket baru (id: 0)
        $res2 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uuid,
            'sender_name'  => 'Pelanggan 1',
            'content'      => 'Pertanyaan tiket kedua yang fresh',
        ]);
        $res2->assertStatus(201);
        $conv2Id = $res2->json('data.conversation_id');

        $this->assertNotEquals($conv1Id, $conv2Id, 'Harus membuat tiket percakapan baru yang terpisah.');
        $this->assertEquals('open', \App\Models\Conversation::find($conv2Id)->status);

        // 5. Cek daftar riwayat tiket pelanggan
        $historyRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->getJson("/api/v1/client/conversations?visitor_uuid={$uuid}");

        $historyRes->assertStatus(200)
                   ->assertJsonPath('success', true);

        $tickets = $historyRes->json('data.conversations');
        $this->assertCount(2, $tickets);
        $statuses = array_column($tickets, 'status');
        $this->assertContains('closed', $statuses);
        $this->assertContains('open', $statuses);
    }

    /**
     * Test 13: Admin can resolve ticket with closing greeting template
     */
    public function testAdminCanResolveConversationWithClosingMessageTemplate()
    {
        $uuid = 'visitor-admin-resolve-' . uniqid();

        // Buat percakapan
        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uuid,
            'sender_name'  => 'Ibu Siska',
            'content'      => 'Tanya informasi garansi produk',
        ]);
        $convId = $res->json('data.conversation_id');

        $user = \App\Models\User::first();
        $conv = \App\Models\Conversation::find($convId);
        $conv->update(['tenant_id' => $user->tenant_id]);

        $closingText = 'Terima kasih telah menghubungi kami. Semoga harimu menyenangkan! Jika ada pertanyaan lain, jangan ragu untuk chat kembali.';

        $statusRes = $this->actingAs($user)
            ->putJson("/admin/inbox/{$convId}/status", [
                'status'          => 'closed',
                'closing_message' => $closingText,
            ]);

        $statusRes->assertStatus(200)
                  ->assertJsonPath('success', true)
                  ->assertJsonPath('data.status', 'closed')
                  ->assertJsonPath('data.closing_message.content', $closingText);

        $conv = \App\Models\Conversation::find($convId);
        $this->assertEquals('closed', $conv->status);

        // Pastikan pesan penutup tersimpan di database sebagai pesan dari agen
        $lastMsg = $conv->latestMessage;
        $this->assertEquals('agent', $lastMsg->sender_type);
        $this->assertEquals($closingText, $lastMsg->content);
    }

    /**
     * Test 15: Widget language configuration and session init integration
     */
    public function testWidgetLanguageConfiguration()
    {
        $project = \App\Models\Project::where('slug', 'supresso')->first();
        $user = \App\Models\User::first();
        $user->update(['tenant_id' => $project->tenant_id]);

        // 1. Admin sets language to 'en'
        $response = $this->actingAs($user)->put("/admin/integrations/{$project->id}/settings", [
            'language'      => 'en',
            'primary_color' => '#0071E3',
        ]);
        $response->assertRedirect();

        // Verify setting in database
        $setting = \App\Models\WidgetSetting::where('project_id', $project->id)->first();
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
            'language'      => 'id',
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
    public function testLauncherMascotConfigurationAndSessionInit()
    {
        $project = \App\Models\Project::where('slug', 'supresso')->first();
        $user = \App\Models\User::first();
        $user->update(['tenant_id' => $project->tenant_id]);

        // 1. Admin saves settings with mascot launcher
        $response = $this->actingAs($user)->put("/admin/integrations/{$project->id}/settings", [
            'primary_color'   => '#0071E3',
            'launcher_type'   => 'mascot',
            'mascot_id'       => 'panda',
            'mascot_size'     => 80,
            'mascot_tracking' => '1',
        ]);
        $response->assertRedirect();

        // Verify database state
        $setting = \App\Models\WidgetSetting::where('project_id', $project->id)->first();
        $this->assertEquals('mascot', $setting->launcher_type);
        $this->assertEquals('panda', $setting->mascot_id);
        $this->assertEquals(80, $setting->mascot_size);
        $this->assertTrue((bool)$setting->mascot_tracking);

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

