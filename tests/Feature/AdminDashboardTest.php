<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Conversation;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected $tenant;
    protected $superadmin;
    protected $agent;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'dashboard-test-tenant'],
            ['name' => 'Dashboard Test Tenant', 'plan' => 'enterprise']
        );

        $this->superadmin = User::firstOrCreate(
            ['email' => 'dashsuper@indraco.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Dashboard Superadmin',
                'username'  => 'dashsuper',
                'password'  => bcrypt('password'),
                'role'      => 'superadmin',
                'status'    => 'online',
            ]
        );

        $this->agent = User::firstOrCreate(
            ['email' => 'dashagent@indraco.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Dashboard Agent',
                'username'  => 'dashagent',
                'password'  => bcrypt('password'),
                'role'      => 'agent',
                'status'    => 'online',
            ]
        );

        $this->project = Project::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'test-dash-project'],
            ['name' => 'Test Dashboard Project']
        );
    }

    /**
     * Test Guest redirected to /login when accessing admin routes
     */
    public function testGuestIsRedirectedToLogin()
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/inbox')->assertRedirect('/login');
        $this->get('/admin/integrations')->assertRedirect('/login');
        $this->get('/admin/team')->assertRedirect('/login');
        $this->get('/admin/logs')->assertRedirect('/login');
    }

    /**
     * Test Authenticated User can view Live Inbox page
     */
    public function testAuthenticatedUserCanViewInbox()
    {
        $response = $this->actingAs($this->agent)->get('/admin/inbox');

        $response->assertStatus(200)
            ->assertSee('Live Inbox')
            ->assertSee('Inbox Percakapan');
    }

    /**
     * Test Authenticated User can view Integrations Hub
     */
    public function testAuthenticatedUserCanViewIntegrations()
    {
        $response = $this->actingAs($this->agent)->get('/admin/integrations');

        $response->assertStatus(200)
            ->assertSee('Integrasi Web')
            ->assertSee('Test Dashboard Project');
    }

    /**
     * Test Superadmin can view Team Management page
     */
    public function testSuperadminCanViewTeamPage()
    {
        $response = $this->actingAs($this->superadmin)->get('/admin/team');

        $response->assertStatus(200)
            ->assertSee('Manajemen Tim CS')
            ->assertSee('dashsuper')
            ->assertSee('dashagent');
    }

    /**
     * Test Agent receives 403 Forbidden when accessing Team Management
     */
    public function testAgentCannotViewTeamPage()
    {
        $response = $this->actingAs($this->agent)->get('/admin/team');

        $response->assertStatus(403);
    }

    /**
     * Test Authenticated User can view Activity Logs page
     */
    public function testAuthenticatedUserCanViewLogs()
    {
        // Seed an activity log
        ActivityLog::create([
            'tenant_id'   => $this->tenant->id,
            'user_id'     => $this->superadmin->id,
            'user_name'   => $this->superadmin->name,
            'user_role'   => 'superadmin',
            'action'      => 'auth.test_login',
            'description' => 'Admin test logged in successfully',
            'ip_address'  => '127.0.0.1',
            'created_at'  => now(),
        ]);

        $response = $this->actingAs($this->superadmin)->get('/admin/logs');

        $response->assertStatus(200)
            ->assertSee('Activity Logs &amp; Audit Trail', false)
            ->assertSee('auth.test_login')
            ->assertSee('Admin test logged in successfully');
    }

    /**
     * Test Inbox does NOT produce N+1 queries (Constant query count regardless of items)
     */
    public function testInboxDoesNotProduceNPlusOneQueries()
    {
        // Buat 5 percakapan dengan relasi lengkap
        for ($i = 1; $i <= 5; $i++) {
            $vis = Visitor::create([
                'tenant_id'    => $this->tenant->id,
                'project_id'   => $this->project->id,
                'visitor_uuid' => 'visitor-uuid-test-' . $i . '-' . uniqid(),
                'name'         => 'Pengunjung ' . $i,
                'email'        => "visitor{$i}@example.com",
            ]);

            $conv = Conversation::create([
                'tenant_id'            => $this->tenant->id,
                'project_id'           => $this->project->id,
                'visitor_id'           => $vis->id,
                'assigned_user_id'     => $this->agent->id,
                'status'               => 'open',
                'channel'              => 'widget',
                'last_message_at'      => now(),
                'last_message_preview' => 'Halo pesan ' . $i,
            ]);

            // Pesan dari pengunjung
            \App\Models\Message::create([
                'conversation_id' => $conv->id,
                'tenant_id'       => $this->tenant->id,
                'sender_type'     => 'visitor',
                'sender_id'       => null,
                'sender_name'     => 'Pengunjung ' . $i,
                'content'         => 'Pesan pengunjung ' . $i,
                'status'          => 'delivered',
            ]);

            // Pesan balasan dari agen
            \App\Models\Message::create([
                'conversation_id' => $conv->id,
                'tenant_id'       => $this->tenant->id,
                'sender_type'     => 'agent',
                'sender_id'       => $this->agent->id,
                'sender_name'     => $this->agent->name,
                'content'         => 'Balasan agen untuk ' . $i,
                'status'          => 'delivered',
            ]);
        }

        \Illuminate\Support\Facades\DB::flushQueryLog();
        \Illuminate\Support\Facades\DB::enableQueryLog();

        $response = $this->actingAs($this->superadmin)->get('/admin/inbox');

        $response->assertStatus(200);

        $queries = \Illuminate\Support\Facades\DB::getQueryLog();
        $queryCount = count($queries);

        // Dengan eager loading lengkap dan reuse model, total query tetap konstan O(1)
        // yaitu 11 query terindeks untuk merender seluruh 3-kolom inbox, terlepas dari berapapun jumlah pesan.
        $this->assertLessThanOrEqual(12, $queryCount, "Terdeteksi potensi N+1 query: Total {$queryCount} query dieksekusi.");
    }

    /**
     * Test Admin can reply to a conversation via web endpoint
     */
    public function testAdminCanReplyToConversation()
    {
        $visitor = Visitor::create([
            'tenant_id'    => $this->tenant->id,
            'project_id'   => $this->project->id,
            'visitor_uuid' => 'vis-reply-test-' . uniqid(),
            'name'         => 'Visitor Reply Test',
        ]);

        $conv = Conversation::create([
            'tenant_id'  => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status'     => 'open',
            'channel'    => 'widget',
        ]);

        $response = $this->actingAs($this->agent)->postJson("/admin/inbox/{$conv->id}/reply", [
            'content' => 'Halo ini balasan resmi CS dari admin inbox!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'sender_type' => 'agent',
                    'sender_name' => $this->agent->name,
                    'content'     => 'Halo ini balasan resmi CS dari admin inbox!',
                ]
            ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'sender_type'     => 'agent',
            'content'         => 'Halo ini balasan resmi CS dari admin inbox!',
        ]);
    }

    /**
     * Test Admin can poll new messages via adaptive polling endpoint
     */
    public function testAdminCanPollNewMessages()
    {
        $visitor = Visitor::create([
            'tenant_id'    => $this->tenant->id,
            'project_id'   => $this->project->id,
            'visitor_uuid' => 'vis-poll-test-' . uniqid(),
            'name'         => 'Visitor Poll Test',
        ]);

        $conv = Conversation::create([
            'tenant_id'  => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status'     => 'open',
            'channel'    => 'widget',
        ]);

        $msg1 = \App\Models\Message::create([
            'conversation_id' => $conv->id,
            'tenant_id'       => $this->tenant->id,
            'sender_type'     => 'visitor',
            'sender_name'     => 'Visitor Poll Test',
            'content'         => 'Pesan awal sebelum polling',
            'status'          => 'delivered',
        ]);

        $msg2 = \App\Models\Message::create([
            'conversation_id' => $conv->id,
            'tenant_id'       => $this->tenant->id,
            'sender_type'     => 'visitor',
            'sender_name'     => 'Visitor Poll Test',
            'content'         => 'Pesan baru pengunjung yang di-poll',
            'status'          => 'delivered',
        ]);

        $response = $this->actingAs($this->agent)->getJson("/admin/inbox/{$conv->id}/messages?after_id={$msg1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'last_id' => $msg2->id,
                ]
            ]);

        $this->assertCount(1, $response->json('data.messages'));
        $this->assertEquals('Pesan baru pengunjung yang di-poll', $response->json('data.messages.0.content'));
    }

    /**
     * Test Admin can toggle conversation status (open / closed)
     */
    public function testAdminCanToggleConversationStatus()
    {
        $visitor = Visitor::create([
            'tenant_id'    => $this->tenant->id,
            'project_id'   => $this->project->id,
            'visitor_uuid' => 'vis-status-test-' . uniqid(),
        ]);

        $conv = Conversation::create([
            'tenant_id'  => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status'     => 'open',
            'channel'    => 'widget',
        ]);

        // Toggle to closed
        $response = $this->actingAs($this->agent)->putJson("/admin/inbox/{$conv->id}/status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['status' => 'closed']
            ]);

        $this->assertEquals('closed', $conv->fresh()->status);

        // Toggle back to open
        $response2 = $this->actingAs($this->agent)->putJson("/admin/inbox/{$conv->id}/status");

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['status' => 'open']
            ]);

        $this->assertEquals('open', $conv->fresh()->status);
    }

    /**
     * Test Admin can assign conversation to staff CS
     */
    public function testAdminCanAssignConversationToStaff()
    {
        $visitor = Visitor::create([
            'tenant_id'    => $this->tenant->id,
            'project_id'   => $this->project->id,
            'visitor_uuid' => 'vis-assign-test-' . uniqid(),
        ]);

        $conv = Conversation::create([
            'tenant_id'  => $this->tenant->id,
            'project_id' => $this->project->id,
            'visitor_id' => $visitor->id,
            'status'     => 'open',
            'channel'    => 'widget',
        ]);

        $response = $this->actingAs($this->superadmin)->putJson("/admin/inbox/{$conv->id}/assign", [
            'assigned_user_id' => $this->agent->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'assigned_user_id'   => $this->agent->id,
                    'assigned_user_name' => $this->agent->name,
                ]
            ]);

        $this->assertEquals($this->agent->id, $conv->fresh()->assigned_user_id);
    }

    /**
     * Test Admin can store new integration project
     */
    public function testAdminCanStoreNewIntegration()
    {
        $response = $this->actingAs($this->superadmin)->post('/admin/integrations', [
            'name'              => 'Toko Cabang Surabaya',
            'domain'            => 'surabaya.tokokita.com',
            'primary_color'     => '#3B82F6',
            'greeting_title'    => 'Selamat Datang!',
            'greeting_subtitle' => 'Customer Service Surabaya siap melayani Anda.',
        ]);

        $response->assertRedirect(route('admin.integrations'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'tenant_id' => $this->tenant->id,
            'name'      => 'Toko Cabang Surabaya',
        ]);

        $this->assertDatabaseHas('project_domains', [
            'domain' => 'surabaya.tokokita.com',
        ]);

        $this->assertDatabaseHas('widget_settings', [
            'primary_color' => '#3B82F6',
        ]);
    }

    /**
     * Test Superadmin can store new team member
     */
    public function testSuperadminCanStoreNewTeamMember()
    {
        $response = $this->actingAs($this->superadmin)->post('/admin/team', [
            'name'     => 'CS Budi Santoso',
            'username' => 'budi_cs_' . uniqid(),
            'email'    => 'budi_' . uniqid() . '@indraco.com',
            'role'     => 'agent',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.team'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'CS Budi Santoso',
            'role' => 'agent',
        ]);
    }

    /**
     * Test Agent cannot store new team member (403 Forbidden)
     */
    public function testAgentCannotStoreNewTeamMember()
    {
        $response = $this->actingAs($this->agent)->post('/admin/team', [
            'name'     => 'CS Hacker',
            'email'    => 'hacker@indraco.com',
            'role'     => 'superadmin',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403);
    }
}
