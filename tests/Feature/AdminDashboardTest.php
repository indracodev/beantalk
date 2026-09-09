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
}
