<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use Database\Seeders\IndracoStoreChatbotSeeder;
use Tests\TestCase;

class IndracoStoreChatbotTest extends TestCase
{
    protected $apiKey;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the INDRACO Chatbot Seeder
        $this->seed(IndracoStoreChatbotSeeder::class);

        $this->project = Project::where('slug', 'indracostore')->first();
        $this->apiKey = ApiKey::where('project_id', $this->project->id)->first();
    }

    public function testIndracoStoreSeederSuccessfullyCreatesProjectAndKey()
    {
        $this->assertNotNull($this->project);
        $this->assertEquals('INDRACO Store', $this->project->name);
        $this->assertNotNull($this->apiKey);
        $this->assertEquals('pk_live_indracostore_prod', $this->apiKey->public_key);

        $setting = $this->project->widgetSetting;
        $this->assertNotNull($setting);
        $this->assertTrue((bool)$setting->bot_enabled);
        $this->assertEquals('INDRACO Assistant', $setting->bot_name);
        $this->assertIsArray($setting->bot_rules);
        $this->assertGreaterThan(70, count($setting->bot_rules));

        // Verify option labels do not contain numbering or (YA)
        foreach ($setting->bot_welcome_options as $opt) {
            $this->assertFalse((bool)preg_match('/^\D*\d+[\.\)]\s*/', $opt['label']), "Option {$opt['label']} should not have numbers");
            $this->assertStringNotContainsString('(YA)', $opt['label']);
        }
    }

    public function testFirstInboundMessageReceivesIndracoWelcomeMenu()
    {
        $visitorUuid = 'indraco-test-vis-' . uniqid();

        $res = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Halo selamat siang',
            'client_message_id' => 'msg-indraco-01-' . uniqid(),
        ]);

        $res->assertStatus(201);
        $conversationId = $res->json('data.conversation_id');

        $botMsg = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->first();

        $this->assertNotNull($botMsg);
        $this->assertStringContainsString('INDRACO Store', $botMsg->content);
        $this->assertStringContainsString('Pembelian Produk', $botMsg->content);
        $this->assertStringContainsString('Informasi & Kerjasama', $botMsg->content);
        $this->assertStringContainsString('Kendala Pembelian di Toko Online', $botMsg->content);
    }

    public function testVisitorSelectingMenuOneGetsProductCategories()
    {
        $visitorUuid = 'indraco-test-vis-' . uniqid();

        // 1. Initial message
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Halo',
            'client_message_id' => 'msg-indraco-menu-01-' . uniqid(),
        ]);

        $conv = Conversation::where('project_id', $this->project->id)->latest('id')->first();

        // 2. Visitor chooses "1"
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/{$conv->id}/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => '1',
            'client_message_id' => 'msg-indraco-menu-02-' . uniqid(),
        ]);

        $lastBotMsg = Message::where('conversation_id', $conv->id)
            ->where('sender_type', 'bot')
            ->latest('id')
            ->first();

        $this->assertNotNull($lastBotMsg);
        $this->assertStringContainsString('Kategori Produk INDRACO Store', $lastBotMsg->content);
        $this->assertStringContainsString('*Kopi*', $lastBotMsg->content);
        $this->assertStringContainsString('Produk Non Kopi', $lastBotMsg->content);
    }

    public function testVisitorInquiringAboutDistributorOrReseller()
    {
        $visitorUuid = 'indraco-test-vis-' . uniqid();

        // Direct inquiry: "reseller"
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Bagaimana cara jadi reseller kopi?',
            'client_message_id' => 'msg-indraco-reseller-' . uniqid(),
        ]);

        $conv = Conversation::where('project_id', $this->project->id)->latest('id')->first();
        $botMsg = Message::where('conversation_id', $conv->id)->where('sender_type', 'bot')->first();

        $this->assertNotNull($botMsg);
        $this->assertStringContainsString('Syarat Menjadi Reseller', $botMsg->content);
    }

    public function testTypingYaTriggersHandoffToHumanCs()
    {
        $visitorUuid = 'indraco-test-vis-' . uniqid();

        // 1. Initial message
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Halo',
            'client_message_id' => 'msg-indraco-ya-01-' . uniqid(),
        ]);

        $conv = Conversation::where('project_id', $this->project->id)->latest('id')->first();
        $this->assertTrue((bool)$conv->is_bot_active);

        // 2. Visitor types "YA"
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/{$conv->id}/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'YA',
            'client_message_id' => 'msg-indraco-ya-02-' . uniqid(),
        ]);

        $conv->refresh();
        $this->assertFalse((bool)$conv->is_bot_active);
        $this->assertNotNull($conv->bot_handoff_at);

        $lastBotMsg = Message::where('conversation_id', $conv->id)
            ->where('sender_type', 'bot')
            ->latest('id')
            ->first();

        $this->assertStringContainsString('Customer Service', $lastBotMsg->content);
    }
}
