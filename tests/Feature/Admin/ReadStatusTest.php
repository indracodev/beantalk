<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Visitor;
use App\Services\ConversationService;

/** Status baca per-user: superadmin tidak tercatat membaca; CS lain tetap melihat "baru"; balasan menutup untuk semua. */
class ReadStatusTest extends AdminTestCase
{
    private User $agentB;

    private Conversation $conv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agentB = User::create(['tenant_id' => $this->tenant->id, 'name' => 'CS B', 'email' => 'csb@read.test', 'password' => 'secret123', 'role' => 'agent']);
        $visitor = Visitor::create(['project_id' => $this->project->id, 'visitor_uuid' => 'v-read']);
        $this->conv = Conversation::create(['tenant_id' => $this->tenant->id, 'project_id' => $this->project->id, 'visitor_id' => $visitor->id]);
        $this->visitorSays('Halo, mau tanya');
    }

    private function visitorSays(string $text): void
    {
        app(ConversationService::class)->appendMessage($this->conv->fresh(), ['sender_type' => 'visitor', 'sender_name' => 'V', 'content' => $text]);
    }

    private function unreadFor(User $user): bool
    {
        return Conversation::unreadIdsFor($user, [$this->conv->id])->contains($this->conv->id);
    }

    public function test_agent_opening_marks_read_only_for_that_agent(): void
    {
        $this->actingAs($this->agent)->get("/admin/inbox/{$this->conv->id}")->assertOk();

        $this->assertFalse($this->unreadFor($this->agent));
        $this->assertTrue($this->unreadFor($this->agentB));
        $this->assertTrue($this->unreadFor($this->superadmin));
    }

    public function test_superadmin_opening_does_not_mark_read(): void
    {
        $this->actingAs($this->superadmin)->get("/admin/inbox/{$this->conv->id}")->assertOk();
        $this->actingAs($this->superadmin)->getJson("/admin/inbox/{$this->conv->id}/messages")->assertOk();

        $this->assertTrue($this->unreadFor($this->superadmin));
        $this->assertTrue($this->unreadFor($this->agent));
        $this->assertGreaterThan(0, $this->conv->fresh()->unread_agent_count);
    }

    public function test_conversation_taken_by_another_cs_is_not_unread_for_others(): void
    {
        $this->conv->update(['assigned_user_id' => $this->agent->id]);

        $this->assertTrue($this->unreadFor($this->agent));
        $this->assertFalse($this->unreadFor($this->agentB));
    }

    public function test_reply_marks_read_for_everyone(): void
    {
        $this->actingAs($this->agentB)->postJson("/admin/inbox/{$this->conv->id}/reply", ['content' => 'Siap, dibantu'])->assertCreated();

        foreach ([$this->agent, $this->agentB, $this->superadmin] as $user) {
            $this->assertFalse($this->unreadFor($user), $user->name);
        }
    }

    public function test_new_visitor_message_becomes_unread_again_after_reading(): void
    {
        $this->actingAs($this->agent)->getJson("/admin/inbox/{$this->conv->id}/messages")->assertOk();
        $this->assertFalse($this->unreadFor($this->agent));

        $this->visitorSays('Halo? masih ada?');

        $this->assertTrue($this->unreadFor($this->agent));
    }

    public function test_feed_and_badge_reflect_per_user_status(): void
    {
        $this->actingAs($this->agent)->get("/admin/inbox/{$this->conv->id}")->assertOk();

        $mine = $this->actingAs($this->agent)->getJson('/admin/inbox/feed/updates')->json('data');
        $other = $this->actingAs($this->agentB)->getJson('/admin/inbox/feed/updates')->json('data');

        $this->assertSame(0, $mine['unread_total']);
        $this->assertSame(1, $other['unread_total']);
        $this->assertFalse(collect($mine['conversations'])->firstWhere('id', $this->conv->id)['is_unread']);
        $this->assertTrue(collect($other['conversations'])->firstWhere('id', $this->conv->id)['is_unread']);
        $this->assertSame(0, Message::where('conversation_id', $this->conv->id)->where('sender_type', 'agent')->count());
    }
}
