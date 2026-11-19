<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class InboxPageTest extends AdminTestCase
{
    /**
     * Test Authenticated User can view Live Inbox page with default open filter and contextual sidebar submenu
     */
    public function test_authenticated_user_can_view_inbox()
    {
        $response = $this->actingAs($this->agent)->get('/admin/inbox');

        $response->assertStatus(200)
            ->assertSee('Live Inbox')
            ->assertSee('Inbox')
            ->assertSee('Chat Aktif')
            ->assertSee('sidebar-submenu');
    }

    /**
     * Test Non-inbox page hides sidebar submenu
     */
    public function test_non_inbox_page_hides_sidebar_submenu()
    {
        $response = $this->actingAs($this->superadmin)->get('/admin/team');

        $response->assertStatus(200)
            ->assertSee('Inbox')
            ->assertDontSee('sidebar-submenu');
    }

    /**
     * Test Inbox defaults to open chat filter and filters out closed conversations
     */
    public function test_inbox_defaults_to_open_chat_filter()
    {
        $visOpen = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-open-'.uniqid(),
            'name' => 'Open Customer',
        ]);
        $convOpen = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visOpen->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);
        Message::create([
            'conversation_id' => $convOpen->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Open Customer',
            'content' => 'Open message content',
        ]);

        $visClosed = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-closed-'.uniqid(),
            'name' => 'Closed Customer',
        ]);
        $convClosed = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visClosed->id,
            'status' => 'closed',
            'channel' => 'widget',
        ]);
        Message::create([
            'conversation_id' => $convClosed->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Closed Customer',
            'content' => 'Closed message content',
        ]);

        // Default: only open conversation should appear
        $resDefault = $this->actingAs($this->agent)->get('/admin/inbox');
        $resDefault->assertStatus(200)
            ->assertSee('Open Customer')
            ->assertDontSee('Closed Customer');

        // Status closed: only closed conversation should appear
        $resClosed = $this->actingAs($this->agent)->get('/admin/inbox?status=closed');
        $resClosed->assertStatus(200)
            ->assertSee('Closed Customer')
            ->assertDontSee('Open Customer');

        // Status all: both should appear
        $resAll = $this->actingAs($this->agent)->get('/admin/inbox?status=all');
        $resAll->assertStatus(200)
            ->assertSee('Open Customer')
            ->assertSee('Closed Customer');
    }

    /**
     * Test Inbox does NOT produce N+1 queries (Constant query count regardless of items)
     */
    public function test_inbox_does_not_produce_n_plus_one_queries()
    {
        // Buat 5 percakapan dengan relasi lengkap
        for ($i = 1; $i <= 5; $i++) {
            $vis = Visitor::create([
                'tenant_id' => $this->tenant->id,
                'project_id' => $this->project->id,
                'visitor_uuid' => 'visitor-uuid-test-'.$i.'-'.uniqid(),
                'name' => 'Pengunjung '.$i,
                'email' => "visitor{$i}@example.com",
            ]);

            $conv = Conversation::create([
                'tenant_id' => $this->tenant->id,
                'project_id' => $this->project->id,
                'visitor_id' => $vis->id,
                'assigned_user_id' => $this->agent->id,
                'status' => 'open',
                'channel' => 'widget',
                'last_message_at' => now(),
                'last_message_preview' => 'Halo pesan '.$i,
            ]);

            // Pesan dari pengunjung
            Message::create([
                'conversation_id' => $conv->id,
                'tenant_id' => $this->tenant->id,
                'sender_type' => 'visitor',
                'sender_id' => null,
                'sender_name' => 'Pengunjung '.$i,
                'content' => 'Pesan pengunjung '.$i,
                'status' => 'delivered',
            ]);

            // Pesan balasan dari agen
            Message::create([
                'conversation_id' => $conv->id,
                'tenant_id' => $this->tenant->id,
                'sender_type' => 'agent',
                'sender_id' => $this->agent->id,
                'sender_name' => $this->agent->name,
                'content' => 'Balasan agen untuk '.$i,
                'status' => 'delivered',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->superadmin)->get('/admin/inbox');

        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Dengan eager loading lengkap dan reuse model, total query tetap konstan O(1)
        // yaitu maksimal 13 query terindeks untuk merender seluruh 3-kolom inbox + multi-channel switcher, terlepas dari berapapun jumlah pesan.
        $this->assertLessThanOrEqual(13, $queryCount, "Terdeteksi potensi N+1 query: Total {$queryCount} query dieksekusi.");
    }

    /**
     * Test Admin can search inbox by customer code
     */
    public function test_search_by_customer_code()
    {
        $code = 'CUS-SRCH99';
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-srch-'.uniqid(),
            'customer_code' => $code,
            'name' => 'Customer Search Target',
        ]);

        $conv = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);

        Message::create([
            'conversation_id' => $conv->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Customer Search Target',
            'content' => 'Test search target message',
            'status' => 'delivered',
        ]);

        $response = $this->actingAs($this->agent)->get("/admin/inbox?search={$code}");
        $response->assertStatus(200)
            ->assertSee($code);
    }

    /**
     * Test filter status di inbox: Mine hanya menampilkan chat OPEN yang ditugaskan ke saya,
     * dan tiket CLOSED otomatis masuk ke Selesai serta counter terhitung tepat (Zero N+1).
     */
    public function test_inbox_status_filtering_and_mine_excludes_closed_conversations()
    {
        $visitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_uuid' => 'vis-status-'.uniqid(),
            'name' => 'Test Visitor Status',
        ]);

        // Tiket 1: Assigned to agent, status open
        $convOpen = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'assigned_user_id' => $this->agent->id,
            'status' => 'open',
            'channel' => 'widget',
        ]);
        Message::create([
            'conversation_id' => $convOpen->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Test Visitor Status',
            'content' => 'Pesan aktif saya',
            'status' => 'delivered',
        ]);

        // Tiket 2: Assigned to agent, status CLOSED (sudah di-resolve)
        $convClosed = Conversation::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'assigned_user_id' => $this->agent->id,
            'status' => 'closed',
            'channel' => 'widget',
        ]);
        Message::create([
            'conversation_id' => $convClosed->id,
            'tenant_id' => $this->tenant->id,
            'sender_type' => 'visitor',
            'sender_name' => 'Test Visitor Status',
            'content' => 'Pesan selesai saya',
            'status' => 'delivered',
        ]);

        // 1. GET /admin/inbox?status=mine harus memuat convOpen dan TIDAK memuat convClosed
        $responseMine = $this->actingAs($this->agent)->get('/admin/inbox?status=mine');
        $responseMine->assertStatus(200);
        $mineConversations = $responseMine->viewData('conversations');
        $this->assertTrue($mineConversations->contains('id', $convOpen->id));
        $this->assertFalse($mineConversations->contains('id', $convClosed->id));

        // Counter mine harus hanya menghitung status open (tidak menghitung closed)
        $counts = $responseMine->viewData('counts');
        $this->assertEquals(1, $counts['mine']);

        // 2. GET /admin/inbox?status=closed harus memuat convClosed
        $responseClosed = $this->actingAs($this->agent)->get('/admin/inbox?status=closed');
        $responseClosed->assertStatus(200);
        $closedConversations = $responseClosed->viewData('conversations');
        $this->assertTrue($closedConversations->contains('id', $convClosed->id));
        $this->assertFalse($closedConversations->contains('id', $convOpen->id));

        // 3. Polling feed /admin/inbox/feed/updates?status=mine harus mengembalikan data yang konsisten
        $pollMine = $this->actingAs($this->agent)->getJson('/admin/inbox/feed/updates?status=mine');
        $pollMine->assertStatus(200);
        $pollMineData = $pollMine->json('data.conversations');
        $pollMineIds = collect($pollMineData)->pluck('id')->all();
        $this->assertContains($convOpen->id, $pollMineIds);
        $this->assertNotContains($convClosed->id, $pollMineIds);
        $this->assertEquals(1, $pollMine->json('data.counts.mine'));
    }
}
