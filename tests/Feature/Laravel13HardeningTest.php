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
use App\Services\ConversationService;
use App\Services\TelegramService;
use App\Support\SocialChannels;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Regresi untuk perbaikan keamanan/bug yang ditemukan saat rebuild Laravel 13. */
class Laravel13HardeningTest extends TestCase
{
    private Tenant $tenant;

    private User $admin;

    private User $agent;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::create(['name' => 'T', 'slug' => 't13']);
        $this->admin = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Admin', 'email' => 'a@t13.test', 'password' => 'secret123', 'role' => 'superadmin']);
        $this->agent = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Agent', 'email' => 'g@t13.test', 'password' => 'secret123', 'role' => 'agent']);
        $this->project = Project::create(['tenant_id' => $this->tenant->id, 'name' => 'Shop', 'slug' => 'shop']);
        WidgetSetting::create(['project_id' => $this->project->id]);
    }

    public function test_agent_cannot_mutate_integrations_via_web(): void
    {
        $this->actingAs($this->agent)->put("/admin/integrations/{$this->project->id}/settings", ['primary_color' => '#000000'])->assertForbidden();
        $this->actingAs($this->agent)->post("/admin/integrations/{$this->project->id}/regenerate")->assertForbidden();
        $this->actingAs($this->agent)->post('/admin/integrations', ['name' => 'X', 'domain' => 'x.com'])->assertForbidden();
    }

    public function test_x_user_id_header_is_never_trusted(): void
    {
        $this->withHeader('X-User-Id', (string) $this->admin->id)
            ->postJson('/api/v1/admin/integrations', ['name' => 'X', 'domain' => 'x.com'])
            ->assertUnauthorized();
    }

    public function test_social_channel_urls_reject_non_http_schemes(): void
    {
        $this->assertSame('', SocialChannels::normalizeUrl('custom', 'javascript:alert(1)'));
        $this->assertSame('', SocialChannels::normalizeUrl('custom', 'data:text/html,x'));
        $this->assertSame('https://wa.me/628123', SocialChannels::normalizeUrl('whatsapp', '08123'));
        $this->assertSame('https://instagram.com/brand', SocialChannels::normalizeUrl('instagram', '@brand'));

        $saved = SocialChannels::fromInput([['platform' => 'custom', 'url' => 'javascript:alert(1)', 'enabled' => true]]);
        $this->assertFalse($saved[0]['enabled']);
    }

    public function test_primary_color_must_be_hex(): void
    {
        $this->actingAs($this->admin)
            ->put("/admin/integrations/{$this->project->id}/settings", ['primary_color' => 'red;}body{display:none'])
            ->assertSessionHasErrors('primary_color');
    }

    public function test_telegram_token_is_encrypted_at_rest(): void
    {
        $this->project->widgetSetting->update(['telegram_bot_token' => '123:SECRET']);

        $raw = DB::table('widget_settings')->where('project_id', $this->project->id)->value('telegram_bot_token');
        $this->assertNotSame('123:SECRET', $raw);
        $this->assertSame('123:SECRET', $this->project->widgetSetting->fresh()->telegram_bot_token);
    }

    public function test_first_message_of_new_tenant_triggers_notification(): void
    {
        // Baseline handshake pada tenant tanpa pesan = 0, polling berikutnya tetap harus mendeteksi.
        $baseline = $this->actingAs($this->agent)->getJson('/admin/inbox/feed/updates')->json('data.max_message_id');
        $this->assertSame(0, $baseline);

        $visitor = Visitor::create(['project_id' => $this->project->id, 'visitor_uuid' => 'v-1']);
        $conv = Conversation::create(['tenant_id' => $this->tenant->id, 'project_id' => $this->project->id, 'visitor_id' => $visitor->id, 'unread_agent_count' => 1]);
        Message::create(['conversation_id' => $conv->id, 'tenant_id' => $this->tenant->id, 'sender_type' => 'visitor', 'sender_name' => 'V', 'content' => 'Halo']);

        $this->actingAs($this->agent)->getJson('/admin/inbox/feed/updates?since_message_id=0')
            ->assertJsonPath('data.has_new_incoming', true);
    }

    public function test_project_key_cache_is_invalidated_on_regenerate(): void
    {
        $key = ApiKey::create(['tenant_id' => $this->tenant->id, 'project_id' => $this->project->id, 'public_key' => 'pk_live_old', 'is_active' => true]);
        $this->postJson('/api/v1/client/session/init', [], ['X-Project-Key' => 'pk_live_old'])->assertOk();

        $this->actingAs($this->admin)->post("/admin/integrations/{$this->project->id}/regenerate")->assertRedirect();

        $this->postJson('/api/v1/client/session/init', [], ['X-Project-Key' => $key->public_key])->assertUnauthorized();
    }

    public function test_unread_counter_increments_atomically(): void
    {
        $visitor = Visitor::create(['project_id' => $this->project->id, 'visitor_uuid' => 'v-2']);
        $conv = Conversation::create(['tenant_id' => $this->tenant->id, 'project_id' => $this->project->id, 'visitor_id' => $visitor->id]);
        $stale = Conversation::find($conv->id); // instance basi, meniru request paralel

        $service = app(ConversationService::class);
        $service->appendMessage($conv, ['content' => 'a', 'sender_name' => 'V']);
        $service->appendMessage($stale, ['content' => 'b', 'sender_name' => 'V']);

        $this->assertSame(2, $conv->fresh()->unread_agent_count);
    }

    public function test_admin_api_cannot_touch_other_tenant(): void
    {
        $other = Tenant::create(['name' => 'O', 'slug' => 'other13']);
        $victim = User::create(['tenant_id' => $other->id, 'name' => 'V', 'email' => 'v@o13.test', 'password' => 'secret123', 'role' => 'agent']);
        $otherProject = Project::create(['tenant_id' => $other->id, 'name' => 'P', 'slug' => 'p']);
        $visitor = Visitor::create(['project_id' => $otherProject->id, 'visitor_uuid' => 'v-o']);
        $conv = Conversation::create(['tenant_id' => $other->id, 'project_id' => $otherProject->id, 'visitor_id' => $visitor->id]);

        $this->actingAs($this->admin)->putJson("/api/v1/admin/team/{$victim->id}/role", ['role' => 'superadmin'])->assertNotFound();
        $this->actingAs($this->admin)->deleteJson("/api/v1/admin/team/{$victim->id}")->assertNotFound();
        $this->actingAs($this->admin)->getJson("/api/v1/admin/conversations/{$conv->id}")->assertNotFound();
        $this->actingAs($this->admin)->postJson("/api/v1/admin/conversations/{$conv->id}/close")->assertNotFound();
        $this->assertSame('agent', $victim->fresh()->role);
        $this->assertNotContains($victim->id, collect($this->actingAs($this->admin)->getJson('/api/v1/admin/team')->json('data.team'))->pluck('id'));
    }

    public function test_telegram_reply_is_not_routed_across_groups_with_same_topic_id(): void
    {
        $this->project->widgetSetting->update(['telegram_bot_token' => '1:TOKEN', 'telegram_chat_id' => '-100111', 'telegram_topic_mode_enabled' => true]);
        $this->agent->update(['telegram_username' => 'cs_agent']);
        $visitor = Visitor::create(['project_id' => $this->project->id, 'visitor_uuid' => 'v-tg']);
        $conv = Conversation::create(['tenant_id' => $this->tenant->id, 'project_id' => $this->project->id, 'visitor_id' => $visitor->id, 'telegram_topic_id' => 7]);
        $payload = fn (int $chat) => ['message' => [
            'message_id' => 99, 'message_thread_id' => 7, 'text' => 'Halo',
            'chat' => ['id' => $chat], 'from' => ['id' => 5, 'username' => 'cs_agent', 'is_bot' => false],
        ]];
        $secret = ['X-Telegram-Bot-Api-Secret-Token' => TelegramService::webhookSecret('1:TOKEN')];

        $this->postJson('/api/v1/telegram/webhook', $payload(-100999), $secret)->assertJsonPath('note', 'no_matching_conversation');
        $this->assertSame(0, $conv->messages()->count());

        $this->postJson('/api/v1/telegram/webhook', $payload(-100111), $secret)->assertJsonPath('conversation_id', $conv->id);
        $this->assertSame(1, $conv->messages()->count());
    }
}
