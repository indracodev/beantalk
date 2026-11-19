<?php

namespace Tests\Feature\Telegram;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\WidgetSetting;

class TelegramWebhookTest extends TelegramTestCase
{
    public function test_telegram_webhook_receives_registered_agent_reply(): void
    {
        $uniqueTopicId = (int) (time() + rand(100000, 999999));

        // Register agent with telegram_user_id and telegram_username
        $agent = User::firstOrCreate(
            ['username' => 'siti_cs'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Siti Rahma',
                'email' => 'siti@indraco.com',
                'password' => bcrypt('password'),
                'role' => 'agent',
                'telegram_user_id' => '99999',
                'telegram_username' => 'sitics',
            ]
        );

        WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'tenant_id' => $this->tenant->id,
                'telegram_bot_token' => '123456:TEST_TOKEN',
                'telegram_chat_id' => '-1001234567890',
                'telegram_notifications_enabled' => true,
                'telegram_topic_mode_enabled' => true,
            ]
        );

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $this->visitor->id,
            'status' => 'open',
            'telegram_topic_id' => $uniqueTopicId,
            'is_bot_active' => true,
        ]);

        // Agent replies in Telegram topic thread $uniqueTopicId
        $payload = [
            'message' => [
                'message_id' => 888,
                'message_thread_id' => $uniqueTopicId,
                'chat' => [
                    'id' => -1001234567890,
                    'type' => 'supergroup',
                ],
                'from' => [
                    'id' => 99999,
                    'is_bot' => false,
                    'first_name' => 'Siti',
                    'username' => 'sitics',
                ],
                'text' => 'Halo Kak Budi, ready ya size 42!',
            ],
        ];

        $response = $this->postJson(route('api.v1.telegram.webhook'), $payload, $this->secretHeader());
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
        $this->assertFalse((bool) $conv->is_bot_active);
    }

    public function test_telegram_webhook_rejects_unregistered_telegram_user(): void
    {
        $uniqueTopicId = (int) (time() + rand(200000, 999999));

        WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'tenant_id' => $this->tenant->id,
                'telegram_bot_token' => '123456:TEST_TOKEN',
                'telegram_chat_id' => '-1001234567890',
                'telegram_notifications_enabled' => true,
                'telegram_topic_mode_enabled' => true,
            ]
        );

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $this->visitor->id,
            'status' => 'open',
            'telegram_topic_id' => $uniqueTopicId,
            'is_bot_active' => true,
        ]);

        // Unregistered user in Telegram supergroup replies
        $payload = [
            'message' => [
                'message_id' => 889,
                'message_thread_id' => $uniqueTopicId,
                'chat' => [
                    'id' => -1001234567890,
                    'type' => 'supergroup',
                ],
                'from' => [
                    'id' => 7777777, // Unregistered ID
                    'is_bot' => false,
                    'first_name' => 'Stranger',
                    'username' => 'random_stranger_user',
                ],
                'text' => 'Pesan dari orang luar',
            ],
        ];

        $response = $this->postJson(route('api.v1.telegram.webhook'), $payload, $this->secretHeader());
        $response->assertOk();
        $response->assertJson(['ok' => true, 'note' => 'unauthorized_telegram_sender']);

        // Assert NO message was created for the customer
        $msgCount = Message::where('conversation_id', $conv->id)->count();
        $this->assertEquals(0, $msgCount);
    }

    public function test_telegram_webhook_ignored_when_toggle_is_disabled(): void
    {
        $uniqueTopicId = (int) (microtime(true) * 1000) % 1000000000;
        Conversation::where('telegram_topic_id', $uniqueTopicId)->delete();

        // Turn OFF Telegram notifications and topic mode
        WidgetSetting::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'tenant_id' => $this->tenant->id,
                'telegram_bot_token' => '123456:TEST_TOKEN',
                'telegram_chat_id' => '-1001234567890',
                'telegram_notifications_enabled' => false,
                'telegram_topic_mode_enabled' => false,
            ]
        );

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $this->visitor->id,
            'status' => 'open',
            'telegram_topic_id' => $uniqueTopicId,
            'is_bot_active' => true,
        ]);

        $payload = [
            'message' => [
                'message_id' => 890,
                'message_thread_id' => $uniqueTopicId,
                'chat' => [
                    'id' => -1001234567890,
                    'type' => 'supergroup',
                ],
                'from' => [
                    'id' => 99999,
                    'is_bot' => false,
                    'first_name' => 'Siti',
                    'username' => 'sitics',
                ],
                'text' => 'Halo Kak Budi!',
            ],
        ];

        $response = $this->postJson(route('api.v1.telegram.webhook'), $payload);
        $response->assertOk();
        $response->assertJson(['ok' => true, 'note' => 'telegram_mode_disabled']);
    }
}
