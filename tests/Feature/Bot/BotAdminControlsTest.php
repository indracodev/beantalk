<?php

namespace Tests\Feature\Bot;

use App\Models\Conversation;
use App\Models\Visitor;

class BotAdminControlsTest extends BotTestCase
{
    /**
     * Test 5: CS Agent can manually toggle bot on/off via Inbox
     */
    public function test_agent_can_toggle_bot_state_in_inbox()
    {
        $visitor = Visitor::create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'visitor_uuid' => 'bot-test-toggle-'.uniqid(),
            'name' => 'Toggle Tester',
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
            'is_bot_active' => false,
        ]);

        $responseOff->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_bot_active', false);

        $conversation->refresh();
        $this->assertFalse((bool) $conversation->is_bot_active);

        // Toggle Bot ON
        $responseOn = $this->actingAs($this->adminUser)->postJson("/admin/inbox/{$conversation->id}/toggle-bot", [
            'is_bot_active' => true,
        ]);

        $responseOn->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_bot_active', true);

        $conversation->refresh();
        $this->assertTrue((bool) $conversation->is_bot_active);
    }

    /**
     * Test 6: Agent reply auto-yields bot
     */
    public function test_agent_reply_auto_yields_bot()
    {
        $visitor = Visitor::create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'visitor_uuid' => 'bot-test-autoyield-'.uniqid(),
            'name' => 'AutoYield Tester',
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
            'content' => 'Halo Kak, ada yang bisa kami bantu secara langsung?',
        ]);

        $replyRes->assertSuccessful()
            ->assertJsonPath('success', true);

        $conversation->refresh();
        $this->assertFalse((bool) $conversation->is_bot_active);
        $this->assertNotNull($conversation->bot_handoff_at);
    }

    /**
     * Test 9: Admin can save bot modes and dynamic welcome options
     */
    public function test_admin_can_save_bot_modes_and_welcome_options()
    {
        $welcomeOptions = [
            ['label' => '📦 Beli Kopi', 'value' => 'kopi'],
            ['label' => '💬 Tanya CS', 'value' => 'cs'],
        ];

        $res = $this->actingAs($this->adminUser)->put("/admin/integrations/{$this->project->id}/settings", [
            'primary_color' => '#222222',
            'bot_enabled' => '1',
            'bot_mode_query' => '1',
            'bot_mode_options' => '1',
            'bot_name' => 'SuperBot',
            'bot_welcome_message' => 'Halo dari SuperBot!',
            'bot_welcome_options' => json_encode($welcomeOptions),
        ]);

        $res->assertRedirect();

        $this->widgetSetting->refresh();
        $this->assertTrue((bool) $this->widgetSetting->bot_mode_query);
        $this->assertTrue((bool) $this->widgetSetting->bot_mode_options);
        $this->assertEquals($welcomeOptions, $this->widgetSetting->bot_welcome_options);
        $this->assertEquals('SuperBot', $this->widgetSetting->bot_name);
    }

    /**
     * Test 10: Admin can save hierarchical decision tree (bot_tree)
     */
    public function test_admin_can_save_bot_tree_structure()
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
                        'children' => [],
                    ],
                ],
            ],
        ];

        $res = $this->actingAs($this->adminUser)->put("/admin/integrations/{$this->project->id}/settings", [
            'primary_color' => '#222222',
            'bot_enabled' => '1',
            'bot_mode_query' => '1',
            'bot_mode_options' => '1',
            'bot_tree' => json_encode($botTree),
        ]);

        $res->assertRedirect();

        $this->widgetSetting->refresh();
        $this->assertIsArray($this->widgetSetting->bot_tree);
        $this->assertEquals('node_root_1', $this->widgetSetting->bot_tree[0]['id']);
        $this->assertEquals('☕ 1. Kopi', $this->widgetSetting->bot_tree[0]['label']);
        $this->assertEquals('node_child_1_1', $this->widgetSetting->bot_tree[0]['children'][0]['id']);
    }
}
