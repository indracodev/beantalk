<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Visitor;

class InboxActionsTest extends AdminTestCase
{
    /**
     * Test Admin can reply to a conversation via web endpoint
     */
    public function test_admin_can_reply_to_conversation()
    {
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-reply-test-'.uniqid(),
            'name' => 'Visitor Reply Test',
        ]);

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);

        $response = $this->actingAs($this->agent)->postJson("/admin/inbox/{$conv->id}/reply", [
            'content' => 'Halo ini balasan resmi CS dari admin inbox!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sender_type' => 'agent',
                    'sender_name' => $this->agent->name,
                    'content' => 'Halo ini balasan resmi CS dari admin inbox!',
                ],
            ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'sender_type' => 'agent',
            'content' => 'Halo ini balasan resmi CS dari admin inbox!',
        ]);
    }

    /**
     * Test Admin can poll new messages via adaptive polling endpoint
     */
    public function test_admin_can_poll_new_messages()
    {
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-poll-test-'.uniqid(),
            'name' => 'Visitor Poll Test',
        ]);

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);

        $msg1 = Message::create([
            'conversation_id' => $conv->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Visitor Poll Test',
            'content' => 'Pesan awal sebelum polling',
            'status' => 'delivered',
        ]);

        $msg2 = Message::create([
            'conversation_id' => $conv->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Visitor Poll Test',
            'content' => 'Pesan baru pengunjung yang di-poll',
            'status' => 'delivered',
        ]);

        $response = $this->actingAs($this->agent)->getJson("/admin/inbox/{$conv->id}/messages?after_id={$msg1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'last_id' => $msg2->id,
                ],
            ]);

        $this->assertCount(1, $response->json('data.messages'));
        $this->assertEquals('Pesan baru pengunjung yang di-poll', $response->json('data.messages.0.content'));
    }

    /**
     * Test Admin can toggle conversation status (open / closed)
     */
    public function test_admin_can_toggle_conversation_status()
    {
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-status-test-'.uniqid(),
        ]);

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);

        // Toggle to closed
        $response = $this->actingAs($this->agent)->putJson("/admin/inbox/{$conv->id}/status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['status' => 'closed'],
            ]);

        $this->assertEquals('closed', $conv->fresh()->status);

        // Toggle back to open
        $response2 = $this->actingAs($this->agent)->putJson("/admin/inbox/{$conv->id}/status");

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['status' => 'open'],
            ]);

        $this->assertEquals('open', $conv->fresh()->status);
    }

    /**
     * Test Admin can assign conversation to staff CS
     */
    public function test_admin_can_assign_conversation_to_staff()
    {
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-assign-test-'.uniqid(),
        ]);

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);

        $response = $this->actingAs($this->superadmin)->putJson("/admin/inbox/{$conv->id}/assign", [
            'assigned_user_id' => $this->agent->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'assigned_user_id' => $this->agent->id,
                    'assigned_user_name' => $this->agent->name,
                ],
            ]);

        $this->assertEquals($this->agent->id, $conv->fresh()->assigned_user_id);
    }

    /**
     * Test Admin can update customer display name from info drawer
     */
    public function test_admin_can_update_customer_name()
    {
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-name-update-'.uniqid(),
            'customer_code' => 'CUS-TEST',
            'name' => 'Initial Name',
        ]);

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);

        $response = $this->actingAs($this->agent)->putJson("/admin/inbox/{$conv->id}/customer", [
            'name' => 'Budi Sudarsono',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Budi Sudarsono')
            ->assertJsonPath('data.display_name', 'Budi Sudarsono');

        $this->assertDatabaseHas('visitors', [
            'id' => $visitor->id,
            'name' => 'Budi Sudarsono',
        ]);
    }

    /**
     * Test Admin can poll live conversation feed updates
     */
    public function test_admin_can_poll_conversation_feed_updates()
    {
        // 1. Initial handshake with since_message_id=0 must NEVER trigger false positive incoming notifications
        $response = $this->actingAs($this->agent)->getJson('/admin/inbox/feed/updates?since_message_id=0');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.has_new_incoming', false)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'conversations',
                    'unread_total',
                    'max_message_id',
                    'has_new_incoming',
                ],
            ]);

        $baselineMaxId = $response->json('data.max_message_id');

        // 2. Create new visitor message on an UNREAD conversation
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-unread-'.uniqid(),
            'name' => 'Jane Unread',
        ]);

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
            'unread_agent_count' => 1,
        ]);

        $msg = Message::create([
            'conversation_id' => $conv->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Jane Unread',
            'content' => 'Halo min, tolong bantuan',
            'status' => 'delivered',
        ]);

        // Polling with since_message_id = baselineMaxId should detect the incoming unread chat!
        $pollRes = $this->actingAs($this->agent)->getJson("/admin/inbox/feed/updates?since_message_id={$baselineMaxId}");
        $pollRes->assertStatus(200)
            ->assertJsonPath('data.has_new_incoming', true)
            ->assertJsonPath('data.latest_incoming.message_id', $msg->id)
            ->assertJsonPath('data.latest_incoming.conversation_id', $conv->id);

        // 3. Mark conversation as READ (unread_agent_count = 0)
        $conv->update(['unread_agent_count' => 0]);

        // Polling again should NOT trigger notification because conversation is now already read!
        $readPollRes = $this->actingAs($this->agent)->getJson("/admin/inbox/feed/updates?since_message_id={$baselineMaxId}");
        $readPollRes->assertStatus(200)
            ->assertJsonPath('data.has_new_incoming', false);
    }
}
