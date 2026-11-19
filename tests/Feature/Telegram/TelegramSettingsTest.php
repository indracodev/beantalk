<?php

namespace Tests\Feature\Telegram;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\WidgetSetting;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;

class TelegramSettingsTest extends TelegramTestCase
{
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
        $this->assertTrue((bool) $settings->telegram_notifications_enabled);
        $this->assertTrue((bool) $settings->telegram_topic_mode_enabled);
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

        $service = new TelegramService;
        $service->sendNewTicketAlert($conv, $msg);

        $conv->refresh();
        $this->assertTrue((bool) $conv->telegram_notif_sent);

        // Call again -> should NOT send second alert (1-time only guarantee)
        Http::assertSentCount(1);
        $service->sendNewTicketAlert($conv, $msg);
        Http::assertSentCount(1);
    }
}
