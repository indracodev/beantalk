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
}
