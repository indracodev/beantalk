<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WidgetSetting;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramIntegrationTest extends TestCase
{
    use DatabaseTransactions;

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
                'name'      => 'Telegram Admin',
                'email'     => 'teleadmin@indraco.com',
                'password'  => bcrypt('password'),
                'role'      => 'superadmin',
                'status'    => 'online',
            ]
        );

        $this->project = Project::firstOrCreate(
            ['project_key' => 'PK-TELEGRAMTEST123'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Toko Sepatu',
                'slug'      => 'toko-sepatu',
                'domain'    => 'tokosepatu.com',
                'status'    => 'active',
            ]
        );

        $this->visitor = Visitor::firstOrCreate(
            ['visitor_uuid' => 'VT-TELEGRAM-12345'],
            [
                'tenant_id'     => $this->tenant->id,
                'project_id'    => $this->project->id,
                'name'          => 'Budi Santoso',
                'customer_code' => 'CUS-77889',
            ]
        );
    }

    public function test_telegram_settings_can_be_saved(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.integrations.settings', $this->project->id), [
            'primary_color' => '#10b981',
            'telegram_bot_token' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
            'telegram_chat_id' => '-1001234567890',
            'telegram_notifications_enabled' => '1',
            'telegram_topic_mode_enabled' => '1',
        ]);

        $response->assertRedirect();

        $settings = WidgetSetting::where('project_id', $this->project->id)->first();
        $this->assertNotNull($settings);
        $this->assertEquals('123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11', $settings->telegram_bot_token);
        $this->assertEquals('-1001234567890', $settings->telegram_chat_id);
        $this->assertTrue((bool)$settings->telegram_notifications_enabled);
        $this->assertTrue((bool)$settings->telegram_topic_mode_enabled);
    }

    public function test_telegram_test_connection_endpoint(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => [
                    'id' => 123456,
                    'is_bot' => true,
                    'first_name' => 'BeanTalk Support Bot',
                    'username' => 'beantalk_support_bot',
                    'message_id' => 999,
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.integrations.test-telegram', $this->project->id), [
            'telegram_bot_token' => '123456:TEST_TOKEN',
            'telegram_chat_id' => '-1001234567890',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'bot_username' => 'beantalk_support_bot',
        ]);
    }

    public function test_telegram_service_one_time_ticket_alert(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'tenant_id' => $this->tenant->id,
                'telegram_bot_token' => '123456:TEST_TOKEN',
                'telegram_chat_id' => '-1001234567890',
                'telegram_notifications_enabled' => true,
            ]
        );

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $this->visitor->id,
            'status' => 'open',
            'telegram_notif_sent' => false,
        ]);

        $msg = Message::create([
            'tenant_id' => $this->tenant->id,
            'conversation_id' => $conv->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Budi Santoso',
            'content' => 'Halo mau tanya stok size 42',
        ]);

        $service = new TelegramService();
        $service->sendNewTicketAlert($conv, $msg);

        $conv->refresh();
        $this->assertTrue((bool)$conv->telegram_notif_sent);

        // Call again -> should NOT send second alert (1-time only guarantee)
        Http::assertSentCount(1);
        $service->sendNewTicketAlert($conv, $msg);
        Http::assertSentCount(1);
    }

    public function test_telegram_webhook_receives_registered_agent_reply(): void
    {
        $uniqueTopicId = 90000 + rand(100, 9999);

        // Register agent with telegram_user_id and telegram_username
        $agent = User::firstOrCreate(
            ['username' => 'siti_cs'],
            [
                'tenant_id'         => $this->tenant->id,
                'name'              => 'Siti Rahma',
                'email'             => 'siti@indraco.com',
                'password'          => bcrypt('password'),
                'role'              => 'agent',
                'telegram_user_id'  => '99999',
                'telegram_username' => 'sitics',
            ]
        );

        WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'tenant_id'                      => $this->tenant->id,
                'telegram_bot_token'             => '123456:TEST_TOKEN',
                'telegram_chat_id'               => '-1001234567890',
                'telegram_notifications_enabled' => true,
                'telegram_topic_mode_enabled'    => true,
            ]
        );

        $conv = Conversation::create([
            'tenant_id'         => $this->tenant->id,
            'project_id'        => $this->project->id,
            'visitor_id'        => $this->visitor->id,
            'status'            => 'open',
            'telegram_topic_id' => $uniqueTopicId,
            'is_bot_active'     => true,
        ]);

        // Agent replies in Telegram topic thread $uniqueTopicId
        $payload = [
            'message' => [
                'message_id'        => 888,
                'message_thread_id' => $uniqueTopicId,
                'chat' => [
                    'id'   => -1001234567890,
                    'type' => 'supergroup',
                ],
                'from' => [
                    'id'         => 99999,
                    'is_bot'     => false,
                    'first_name' => 'Siti',
                    'username'   => 'sitics',
                ],
                'text' => 'Halo Kak Budi, ready ya size 42!',
            ],
        ];

        $response = $this->postJson(route('api.v1.telegram.webhook'), $payload);
        $response->assertOk();
        $response->assertJson(['ok' => true, 'conversation_id' => $conv->id, 'agent_name' => 'Siti Rahma']);

        // Assert message recorded in database with clean official name
        $msg = Message::where('conversation_id', $conv->id)->first();
        $this->assertNotNull($msg);
        $this->assertEquals('Halo Kak Budi, ready ya size 42!', $msg->content);
        $this->assertEquals('agent', $msg->sender_type);
        $this->assertEquals('Siti Rahma', $msg->sender_name);

        // Assert bot auto-yielded
        $conv->refresh();
        $this->assertFalse((bool)$conv->is_bot_active);
    }

    public function test_telegram_webhook_rejects_unregistered_telegram_user(): void
    {
        $uniqueTopicId = 91000 + rand(100, 9999);

        WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'tenant_id'                      => $this->tenant->id,
                'telegram_bot_token'             => '123456:TEST_TOKEN',
                'telegram_chat_id'               => '-1001234567890',
                'telegram_notifications_enabled' => true,
                'telegram_topic_mode_enabled'    => true,
            ]
        );

        $conv = Conversation::create([
            'tenant_id'         => $this->tenant->id,
            'project_id'        => $this->project->id,
            'visitor_id'        => $this->visitor->id,
            'status'            => 'open',
            'telegram_topic_id' => $uniqueTopicId,
            'is_bot_active'     => true,
        ]);

        // Unregistered user in Telegram supergroup replies
        $payload = [
            'message' => [
                'message_id'        => 889,
                'message_thread_id' => $uniqueTopicId,
                'chat' => [
                    'id'   => -1001234567890,
                    'type' => 'supergroup',
                ],
                'from' => [
                    'id'         => 7777777, // Unregistered ID
                    'is_bot'     => false,
                    'first_name' => 'Stranger',
                    'username'   => 'random_stranger_user',
                ],
                'text' => 'Pesan dari orang luar',
            ],
        ];

        $response = $this->postJson(route('api.v1.telegram.webhook'), $payload);
        $response->assertOk();
        $response->assertJson(['ok' => true, 'note' => 'unauthorized_telegram_sender']);

        // Assert NO message was created for the customer
        $msgCount = Message::where('conversation_id', $conv->id)->count();
        $this->assertEquals(0, $msgCount);
    }

    public function test_telegram_webhook_ignored_when_toggle_is_disabled(): void
    {
        $uniqueTopicId = 92000 + rand(100, 9999);

        // Turn OFF Telegram notifications and topic mode
        WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'tenant_id'                      => $this->tenant->id,
                'telegram_bot_token'             => '123456:TEST_TOKEN',
                'telegram_chat_id'               => '-1001234567890',
                'telegram_notifications_enabled' => false,
                'telegram_topic_mode_enabled'    => false,
            ]
        );

        $conv = Conversation::create([
            'tenant_id'         => $this->tenant->id,
            'project_id'        => $this->project->id,
            'visitor_id'        => $this->visitor->id,
            'status'            => 'open',
            'telegram_topic_id' => $uniqueTopicId,
            'is_bot_active'     => true,
        ]);

        $payload = [
            'message' => [
                'message_id'        => 890,
                'message_thread_id' => $uniqueTopicId,
                'chat' => [
                    'id'   => -1001234567890,
                    'type' => 'supergroup',
                ],
                'from' => [
                    'id'         => 99999,
                    'is_bot'     => false,
                    'first_name' => 'Siti',
                    'username'   => 'sitics',
                ],
                'text' => 'Halo Kak Budi!',
            ],
        ];

        $response = $this->postJson(route('api.v1.telegram.webhook'), $payload);
        $response->assertOk();
        $response->assertJson(['ok' => true, 'note' => 'telegram_mode_disabled']);
    }
}
