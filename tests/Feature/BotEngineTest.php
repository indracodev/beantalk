<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WidgetSetting;
use Tests\TestCase;

class BotEngineTest extends TestCase
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
                'is_active' => true
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
                        'response' => 'Pengiriman kami menggunakan JNE, J&T, dan SiCepat.'
                    ],
                    [
                        'keywords' => 'jam buka, operasional',
                        'response' => 'Jam operasional kami adalah Senin - Jumat pukul 08:00 - 17:00 WIB.'
                    ]
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
                'is_active' => true
            ]
        );
    }

    /**
     * Test 1: Bot does NOT respond when globally disabled
     */
    public function testBotDoesNotRespondWhenGloballyDisabled()
    {
        $this->widgetSetting->update(['bot_enabled' => false]);

        $visitorUuid = 'bot-test-visitor-disabled-' . uniqid();

        // 1. Send Message with visitor_uuid on conv 0
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Berapa ongkir ke Surabaya?',
            'client_message_id' => 'msg-bot-01-' . uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        // Ensure NO bot messages exist
        $botMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->count();

        $this->assertEquals(0, $botMessages);
    }

    /**
     * Test 2: Bot sends welcome message on first generic message
     */
    public function testBotSendsWelcomeMessageOnFirstGenericMessage()
    {
        $this->widgetSetting->update(['bot_enabled' => true]);

        $visitorUuid = 'bot-test-visitor-welcome-' . uniqid();

        // Send First Message without keyword match
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Selamat pagi!',
            'client_message_id' => 'msg-bot-02-' . uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $botMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->get();

        $this->assertCount(1, $botMessages);
        $this->assertStringContainsString('Halo! Saya BeanBot', $botMessages[0]->content);
    }

    /**
     * Test 3: Bot answers with keyword FAQ rule
     */
    public function testBotAnswersWithKeywordFaqRule()
    {
        $this->widgetSetting->update(['bot_enabled' => true]);

        $visitorUuid = 'bot-test-visitor-faq-' . uniqid();

        // Send Message matching keyword 'ongkir'
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Berapa ongkir pengiriman ke Bandung?',
            'client_message_id' => 'msg-bot-03-' . uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $botMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->get();

        $this->assertCount(1, $botMessages);
        $this->assertStringContainsString('Pengiriman kami menggunakan JNE', $botMessages[0]->content);
    }

    /**
     * Test 4: Bot smart handoff when visitor requests human CS
     */
    public function testBotSmartHandoffToHumanAgent()
    {
        $this->widgetSetting->update(['bot_enabled' => true]);

        $visitorUuid = 'bot-test-visitor-handoff-' . uniqid();

        // Customer requests CS
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Saya mau bicara dengan CS manusia tolong',
            'client_message_id' => 'msg-bot-04-' . uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $conversation = Conversation::find($conversationId);

        $this->assertFalse((bool)$conversation->is_bot_active);
        $this->assertNotNull($conversation->bot_handoff_at);

        $lastBotMsg = Message::where('conversation_id', $conversation->id)
            ->where('sender_type', 'bot')
            ->latest('id')
            ->first();

        $this->assertNotNull($lastBotMsg);
        $this->assertStringContainsString('staf Customer Service', $lastBotMsg->content);
    }

    /**
     * Test 5: CS Agent can manually toggle bot on/off via Inbox
     */
    public function testAgentCanToggleBotStateInInbox()
    {
        $visitor = Visitor::create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'visitor_uuid' => 'bot-test-toggle-' . uniqid(),
            'name' => 'Toggle Tester'
        ]);

        $conversation = Conversation::create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
            'is_bot_active' => true,
            'unread_agent_count' => 0,
            'unread_visitor_count' => 0,
        ]);

        // Toggle Bot OFF
        $responseOff = $this->actingAs($this->adminUser)->postJson("/admin/inbox/{$conversation->id}/toggle-bot", [
            'is_bot_active' => false
        ]);

        $responseOff->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_bot_active', false);

        $conversation->refresh();
        $this->assertFalse((bool)$conversation->is_bot_active);

        // Toggle Bot ON
        $responseOn = $this->actingAs($this->adminUser)->postJson("/admin/inbox/{$conversation->id}/toggle-bot", [
            'is_bot_active' => true
        ]);

        $responseOn->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_bot_active', true);

        $conversation->refresh();
        $this->assertTrue((bool)$conversation->is_bot_active);
    }

    /**
     * Test 6: Agent reply auto-yields bot
     */
    public function testAgentReplyAutoYieldsBot()
    {
        $visitor = Visitor::create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'visitor_uuid' => 'bot-test-autoyield-' . uniqid(),
            'name' => 'AutoYield Tester'
        ]);

        $conversation = Conversation::create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
            'is_bot_active' => true,
            'unread_agent_count' => 0,
            'unread_visitor_count' => 0,
        ]);

        $replyRes = $this->actingAs($this->adminUser)->postJson("/admin/inbox/{$conversation->id}/reply", [
            'content' => 'Halo Kak, ada yang bisa kami bantu secara langsung?'
        ]);

        $replyRes->assertSuccessful()
            ->assertJsonPath('success', true);

        $conversation->refresh();
        $this->assertFalse((bool)$conversation->is_bot_active);
        $this->assertNotNull($conversation->bot_handoff_at);
    }

    /**
     * Test 7: Bot respects bot_mode_query toggle
     */
    public function testBotRespectsModeQueryToggle()
    {
        $this->widgetSetting->update([
            'bot_enabled'      => true,
            'bot_mode_query'   => false, // Query matching disabled
            'bot_mode_options' => true,
        ]);

        $visitorUuid = 'bot-test-visitor-noquery-' . uniqid();

        // Send loose query containing keyword 'ongkir'
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Berapa tarif ongkir ke Jakarta?',
            'client_message_id' => 'msg-bot-noquery-' . uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        // Since mode_query is false, it should NOT match the 'ongkir' rule via loose substring,
        // it falls back to welcome message on first message
        $botMsg = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->first();

        $this->assertNotNull($botMsg);
        $this->assertEquals($this->widgetSetting->bot_welcome_message, $botMsg->content);
    }

    /**
     * Test 8: Bot strips options when bot_mode_options is disabled
     */
    public function testBotStripsOptionsWhenModeOptionsDisabled()
    {
        $this->widgetSetting->update([
            'bot_enabled'         => true,
            'bot_mode_query'      => true,
            'bot_mode_options'    => false, // Options disabled
            'bot_welcome_options' => [
                ['label' => 'Tombol Test', 'value' => 'test']
            ]
        ]);

        $visitorUuid = 'bot-test-visitor-nooptions-' . uniqid();

        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Halo kak',
            'client_message_id' => 'msg-bot-noopt-' . uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $botMsg = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->first();

        $this->assertNotNull($botMsg);
        $this->assertNull($botMsg->metadata['options'] ?? null);
    }

    /**
     * Test 9: Admin can save bot modes and dynamic welcome options
     */
    public function testAdminCanSaveBotModesAndWelcomeOptions()
    {
        $welcomeOptions = [
            ['label' => '📦 Beli Kopi', 'value' => 'kopi'],
            ['label' => '💬 Tanya CS', 'value' => 'cs'],
        ];

        $res = $this->actingAs($this->adminUser)->put("/admin/integrations/{$this->project->id}/settings", [
            'primary_color'       => '#222222',
            'bot_enabled'         => '1',
            'bot_mode_query'      => '1',
            'bot_mode_options'    => '1',
            'bot_name'            => 'SuperBot',
            'bot_welcome_message' => 'Halo dari SuperBot!',
            'bot_welcome_options' => json_encode($welcomeOptions),
        ]);

        $res->assertRedirect();

        $this->widgetSetting->refresh();
        $this->assertTrue((bool)$this->widgetSetting->bot_mode_query);
        $this->assertTrue((bool)$this->widgetSetting->bot_mode_options);
        $this->assertEquals($welcomeOptions, $this->widgetSetting->bot_welcome_options);
        $this->assertEquals('SuperBot', $this->widgetSetting->bot_name);
    }

    /**
     * Test 10: Admin can save hierarchical decision tree (bot_tree)
     */
    public function testAdminCanSaveBotTreeStructure()
    {
        $botTree = [
            [
                'id' => 'node_root_1',
                'label' => '☕ 1. Kopi',
                'value' => '1',
                'response' => 'Pilih kategori kopi:',
                'children' => [
                    [
                        'id' => 'node_child_1_1',
                        'label' => '1.1 Robusta',
                        'value' => '1.1',
                        'response' => 'Informasi kopi robusta...',
                        'children' => []
                    ]
                ]
            ]
        ];

        $res = $this->actingAs($this->adminUser)->put("/admin/integrations/{$this->project->id}/settings", [
            'primary_color'       => '#222222',
            'bot_enabled'         => '1',
            'bot_mode_query'      => '1',
            'bot_mode_options'    => '1',
            'bot_tree'            => json_encode($botTree),
        ]);

        $res->assertRedirect();

        $this->widgetSetting->refresh();
        $this->assertIsArray($this->widgetSetting->bot_tree);
        $this->assertEquals('node_root_1', $this->widgetSetting->bot_tree[0]['id']);
        $this->assertEquals('☕ 1. Kopi', $this->widgetSetting->bot_tree[0]['label']);
        $this->assertEquals('node_child_1_1', $this->widgetSetting->bot_tree[0]['children'][0]['id']);
    }
}
