<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use Database\Seeders\SupressoCoffeeChatbotSeeder;
use Tests\TestCase;

class SupressoCoffeeChatbotTest extends TestCase
{
    protected $apiKey;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the Supresso Chatbot Seeder
        $this->seed(SupressoCoffeeChatbotSeeder::class);

        $this->project = Project::where('slug', 'supresso')->first();
        $this->apiKey = ApiKey::where('project_id', $this->project->id)->first();
    }

    public function testSupressoCoffeeSeederSuccessfullyCreatesOrUpdatesProjectAndKey()
    {
        $this->assertNotNull($this->project);
        $this->assertContains($this->project->name, ['SUPRESSO', 'Supresso Coffee']);
        $this->assertEquals('supresso', $this->project->slug);
        $this->assertNotNull($this->apiKey);

        $setting = $this->project->widgetSetting;
        $this->assertNotNull($setting);
        $this->assertTrue((bool)$setting->bot_enabled);
        $this->assertEquals('Supresso Assistant', $setting->bot_name);
        $this->assertIsArray($setting->bot_rules);
        $this->assertGreaterThan(30, count($setting->bot_rules));

        // Verify options do NOT contain numbering like "1.", "1️⃣", etc.
        foreach ($setting->bot_welcome_options as $opt) {
            $this->assertFalse((bool)preg_match('/^\d+[\.\)]\s*/', $opt['label']), "Option {$opt['label']} should not start with numbers");
            $this->assertStringNotContainsString('1️⃣', $opt['label']);
            $this->assertStringNotContainsString('2️⃣', $opt['label']);
        }
    }

    public function testFirstInboundMessageReceivesSupressoWelcomeMenu()
    {
        $visitorUuid = 'supresso-test-vis-' . uniqid();

        $res = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Hello',
            'client_message_id' => 'msg-supresso-01-' . uniqid(),
        ]);

        $res->assertStatus(201);
        $conversationId = $res->json('data.conversation_id');

        $botMsg = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->first();

        $this->assertNotNull($botMsg);
        $this->assertStringContainsString('Supresso Coffee', $botMsg->content);

        $options = $botMsg->metadata['options'] ?? [];
        $this->assertNotEmpty($options);

        // Check options are direct without numbers
        $labels = array_column($options, 'label');
        $this->assertContains('Order', $labels);
        $this->assertContains('Product', $labels);
        $this->assertContains('Membership', $labels);
        $this->assertContains('Global Shipping', $labels);
        $this->assertNotContains('1. Order', $labels);
    }

    public function testVisitorSelectingOrderGetsOrderAssistance()
    {
        $visitorUuid = 'supresso-test-vis-' . uniqid();

        // 1. Initial message
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Hi',
            'client_message_id' => 'msg-sup-order-01-' . uniqid(),
        ]);

        $conv = Conversation::where('project_id', $this->project->id)->latest('id')->first();

        // 2. Visitor clicks "Order" option
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/{$conv->id}/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Order',
            'client_message_id' => 'msg-sup-order-02-' . uniqid(),
        ]);

        $lastBotMsg = Message::where('conversation_id', $conv->id)
            ->where('sender_type', 'bot')
            ->latest('id')
            ->first();

        $this->assertNotNull($lastBotMsg);
        $this->assertStringContainsString('Order Assistance', $lastBotMsg->content);

        $options = $lastBotMsg->metadata['options'] ?? [];
        $labels = array_column($options, 'label');
        $this->assertContains('Order Status', $labels);
        $this->assertContains('Cancellation', $labels);
        $this->assertContains('Return Policy', $labels);
        $this->assertNotContains('1.1 Order status', $labels);
    }

    public function testVisitorSelectingShippingOptionsGetsShippingDetails()
    {
        $visitorUuid = 'supresso-test-vis-' . uniqid();

        // 1. Initial message
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Hi',
            'client_message_id' => 'msg-sup-ship-01-' . uniqid(),
        ]);

        $conv = Conversation::where('project_id', $this->project->id)->latest('id')->first();

        // 2. Visitor sends "Shipping Options"
        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/{$conv->id}/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'Shipping Options',
            'client_message_id' => 'msg-sup-ship-02-' . uniqid(),
        ]);

        $lastBotMsg = Message::where('conversation_id', $conv->id)
            ->where('sender_type', 'bot')
            ->latest('id')
            ->first();

        $this->assertNotNull($lastBotMsg);
        $this->assertStringContainsString('Complimentary Shipping', $lastBotMsg->content);
        $this->assertStringContainsString('Ninjavan', $lastBotMsg->content);
    }

    public function testVisitorRequestingCSGetsHandoff()
    {
        $visitorUuid = 'supresso-test-vis-' . uniqid();

        $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson("/api/v1/client/conversations/0/messages", [
            'visitor_uuid'      => $visitorUuid,
            'content'           => 'CS',
            'client_message_id' => 'msg-sup-cs-' . uniqid(),
        ]);

        $conv = Conversation::where('project_id', $this->project->id)->latest('id')->first();
        $this->assertFalse((bool)$conv->is_bot_active);
    }

    public function testSeederDoesNotDuplicateOrTouchSupressoCoId()
    {
        // Create supresso.co.id project to simulate database state
        $tenant = $this->project->tenant;
        $idProject = Project::create([
            'tenant_id' => $tenant->id,
            'name'      => 'supresso.co.id',
            'slug'      => 'supressocoid',
            'is_active' => true,
        ]);

        // Re-run seeder
        $this->seed(SupressoCoffeeChatbotSeeder::class);

        // Verify supresso project remains single
        $this->assertEquals(1, Project::where('slug', 'supresso')->count());

        // Verify supresso.co.id was untouched
        $reloadedIdProject = Project::find($idProject->id);
        $this->assertEquals('supresso.co.id', $reloadedIdProject->name);
        $this->assertEquals('supressocoid', $reloadedIdProject->slug);
    }
}
