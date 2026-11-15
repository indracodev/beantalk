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
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Celah B1–B3 (audit ekstraksi Ryuzen, 15 Nov 2026):
 * B1 polling pesan tanpa cek pemilik, B2 kirim/selesaikan/unggah tanpa cek pemilik wajib,
 * B3 webhook Telegram tanpa verifikasi secret token.
 */
class VisitorOwnershipSecurityTest extends TestCase
{
    const KEY = 'pk_live_ownership_test';
    const PEMILIK = '11111111-1111-4111-8111-111111111111';
    const PENYUSUP = '22222222-2222-4222-8222-222222222222';

    protected $tenant;
    protected $project;
    protected $conv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(['slug' => 'uji-pemilik'], ['name' => 'Uji Pemilik', 'is_active' => true]);
        $this->project = Project::firstOrCreate(['tenant_id' => $this->tenant->id, 'slug' => 'uji-pemilik'], ['name' => 'Uji Pemilik', 'is_active' => true]);
        ApiKey::firstOrCreate(['public_key' => self::KEY], ['tenant_id' => $this->tenant->id, 'project_id' => $this->project->id, 'name' => 'Uji', 'is_active' => true]);
        WidgetSetting::updateOrCreate(['project_id' => $this->project->id], ['bot_enabled' => false, 'is_online' => true]);

        $pemilik = Visitor::firstOrCreate(['project_id' => $this->project->id, 'visitor_uuid' => self::PEMILIK], ['name' => 'Pemilik']);
        Visitor::firstOrCreate(['project_id' => $this->project->id, 'visitor_uuid' => self::PENYUSUP], ['name' => 'Penyusup']);

        Conversation::where('visitor_id', $pemilik->id)->delete();
        $this->conv = Conversation::create([
            'tenant_id' => $this->tenant->id, 'project_id' => $this->project->id, 'visitor_id' => $pemilik->id,
            'status' => 'open', 'channel' => 'widget',
        ]);
        Message::create([
            'conversation_id' => $this->conv->id, 'tenant_id' => $this->tenant->id, 'sender_type' => 'visitor',
            'sender_name' => 'Pemilik', 'content' => 'Alamat saya Jl. Mawar 1', 'content_type' => 'text', 'status' => 'sent',
        ]);
    }

    protected function sebagai($uuid = null)
    {
        $headers = ['X-Project-Key' => self::KEY];
        if ($uuid) {
            $headers['X-Visitor-Uuid'] = $uuid;
        }

        return $this->withHeaders($headers);
    }

    public function test_b1_pengunjung_lain_tidak_bisa_membaca_pesan()
    {
        $this->sebagai(self::PENYUSUP)->getJson("/api/v1/client/conversations/{$this->conv->id}/messages")->assertStatus(404);
        $this->sebagai()->getJson("/api/v1/client/conversations/{$this->conv->id}/messages")->assertStatus(404);

        $this->sebagai(self::PEMILIK)->getJson("/api/v1/client/conversations/{$this->conv->id}/messages")
            ->assertOk()->assertJsonPath('data.messages.0.content', 'Alamat saya Jl. Mawar 1');
    }

    public function test_b2_pengunjung_lain_tidak_bisa_menyisipkan_pesan()
    {
        $this->sebagai(self::PENYUSUP)->postJson("/api/v1/client/conversations/{$this->conv->id}/messages", [
            'visitor_uuid' => self::PENYUSUP, 'content' => 'Pesan palsu', 'client_message_id' => 'x1',
        ])->assertStatus(404);

        $this->assertSame(0, Message::where('conversation_id', $this->conv->id)->where('content', 'Pesan palsu')->count());
    }

    public function test_b2_selesaikan_dan_unggah_wajib_pemilik()
    {
        $this->sebagai()->postJson("/api/v1/client/conversations/{$this->conv->id}/resolve")->assertStatus(404);
        $this->sebagai(self::PENYUSUP)->postJson("/api/v1/client/conversations/{$this->conv->id}/resolve")->assertStatus(404);
        $this->assertSame('open', $this->conv->fresh()->status);

        $this->sebagai()->postJson("/api/v1/client/conversations/{$this->conv->id}/upload", [])->assertStatus(404);

        $this->sebagai(self::PEMILIK)->postJson("/api/v1/client/conversations/{$this->conv->id}/resolve")->assertOk();
        $this->assertSame('closed', $this->conv->fresh()->status);
    }

    public function test_catatan_internal_tidak_ikut_ke_widget_saat_init()
    {
        Message::create([
            'conversation_id' => $this->conv->id, 'tenant_id' => $this->tenant->id, 'sender_type' => 'agent',
            'sender_name' => 'CS', 'content' => 'Catatan internal rahasia', 'content_type' => 'text', 'status' => 'sent', 'is_internal_note' => true,
        ]);

        $res = $this->sebagai(self::PEMILIK)->postJson('/api/v1/client/session/init', ['visitor_uuid' => self::PEMILIK])->assertOk();
        $this->assertStringNotContainsString('Catatan internal rahasia', $res->getContent());
    }

    protected function siapkanTelegram()
    {
        WidgetSetting::updateOrCreate(['project_id' => $this->project->id], [
            'telegram_bot_token' => '123456:UJI_TOKEN', 'telegram_chat_id' => '-100123',
            'telegram_notifications_enabled' => true, 'telegram_topic_mode_enabled' => true,
        ]);
        User::firstOrCreate(['username' => 'cs_uji_tg'], [
            'tenant_id' => $this->tenant->id, 'name' => 'CS Uji', 'email' => 'cs-uji-tg@example.com',
            'password' => bcrypt('uji-rahasia'), 'role' => 'agent', 'telegram_user_id' => '777', 'telegram_username' => 'csuji',
        ]);
        $topik = (int) (time() + rand(1000000, 9999999));
        $this->conv->update(['telegram_topic_id' => $topik]);

        return [
            'message' => [
                'message_id' => 5, 'message_thread_id' => $topik, 'chat' => ['id' => -100123, 'type' => 'supergroup'],
                'from' => ['id' => 777, 'is_bot' => false, 'username' => 'csuji'], 'text' => 'Balasan dari Telegram',
            ],
        ];
    }

    public function test_b3_webhook_telegram_tanpa_secret_diabaikan()
    {
        $payload = $this->siapkanTelegram();

        $this->postJson(route('api.v1.telegram.webhook'), $payload)->assertOk();
        $this->postJson(route('api.v1.telegram.webhook'), $payload, ['X-Telegram-Bot-Api-Secret-Token' => 'tebakan'])->assertOk();
        $this->assertSame(0, Message::where('conversation_id', $this->conv->id)->where('content', 'Balasan dari Telegram')->count());

        $secret = TelegramService::webhookSecret('123456:UJI_TOKEN');
        $this->postJson(route('api.v1.telegram.webhook'), $payload, ['X-Telegram-Bot-Api-Secret-Token' => $secret])->assertOk();
        $this->assertSame(1, Message::where('conversation_id', $this->conv->id)->where('content', 'Balasan dari Telegram')->count());
    }

    public function test_b3_set_webhook_mendaftarkan_secret_token()
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'description' => 'Webhook was set'])]);

        app(TelegramService::class)->setWebhook('123456:UJI_TOKEN', 'https://chat.example.com/api/v1/telegram/webhook');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/setWebhook')
                && hash_equals(TelegramService::webhookSecret('123456:UJI_TOKEN'), (string) $request['secret_token']);
        });
    }
}
