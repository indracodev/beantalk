<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RbacSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $tenant;
    protected $owner;
    protected $admin;
    protected $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-tenant'],
            ['name' => 'Test Tenant Corp', 'plan' => 'enterprise']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'test-owner@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Owner',
                'username'  => 'test-owner',
                'password'  => bcrypt('secret123'),
                'role'      => 'owner',
            ]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'test-admin@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Admin',
                'username'  => 'test-admin',
                'password'  => bcrypt('secret123'),
                'role'      => 'admin',
            ]
        );

        $this->agent = User::firstOrCreate(
            ['email' => 'test-agent@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Agent',
                'username'  => 'test-agent',
                'password'  => bcrypt('secret123'),
                'role'      => 'agent',
            ]
        );
    }

    /**
     * Test 1: Unauthenticated request to role-protected endpoint is rejected (401)
     */
    public function testUnauthenticatedRequestToRoleProtectedEndpointFails()
    {
        $response = $this->postJson('/api/v1/admin/integrations', [
            'name'   => 'Unauthorized Store',
            'domain' => 'unauth.com',
        ]);

        $response->assertStatus(401)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    /**
     * Test 2: Agent cannot create integration (403 Forbidden)
     */
    public function testAgentCannotCreateIntegration()
    {
        $response = $this->actingAs($this->agent)
                         ->postJson('/api/v1/admin/integrations', [
                             'name'   => 'Agent Store Attempt',
                             'domain' => 'agentattempt.com',
                         ]);

        $response->assertStatus(403)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    /**
     * Test 3: Admin CAN create integration (201 Created)
     */
    public function testAdminCanCreateIntegration()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/v1/admin/integrations', [
                             'name'          => 'Admin Official Store',
                             'domain'        => 'adminstore.com',
                             'primary_color' => '#3B82F6',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.name', 'Admin Official Store');
    }

    /**
     * Test 4: Owner CAN create integration (201 Created)
     */
    public function testOwnerCanCreateIntegration()
    {
        $response = $this->actingAs($this->owner)
                         ->postJson('/api/v1/admin/integrations', [
                             'name'          => 'Owner Master Store',
                             'domain'        => 'ownerstore.com',
                             'primary_color' => '#10B981',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);
    }

    /**
     * Test 5: Agent CAN view team list and conversations
     */
    public function testAgentCanViewTeamAndConversations()
    {
        $teamResponse = $this->actingAs($this->agent)
                             ->getJson('/api/v1/admin/team');

        $teamResponse->assertStatus(200)
                     ->assertJsonPath('success', true)
                     ->assertJsonStructure(['data' => ['team']]);

        $inboxResponse = $this->actingAs($this->agent)
                              ->getJson('/api/v1/admin/conversations');

        $inboxResponse->assertStatus(200)
                      ->assertJsonPath('success', true);
    }

    /**
     * Test 6: Agent cannot invite new team members (403 Forbidden)
     */
    public function testAgentCannotInviteTeamMembers()
    {
        $response = $this->actingAs($this->agent)
                         ->postJson('/api/v1/admin/team', [
                             'name'  => 'New Rogue Member',
                             'email' => 'rogue@example.com',
                             'role'  => 'agent',
                         ]);

        $response->assertStatus(403)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    /**
     * Test 7: Owner CAN change team member role (200 OK)
     */
    public function testOwnerCanChangeRole()
    {
        $response = $this->actingAs($this->owner)
                         ->putJson("/api/v1/admin/team/{$this->agent->id}/role", [
                             'role' => 'admin',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.user.role', 'admin');
    }

    /**
     * Test 8: Admin CANNOT change team member role (403 Forbidden)
     */
    public function testAdminCannotChangeRole()
    {
        $response = $this->actingAs($this->admin)
                         ->putJson("/api/v1/admin/team/{$this->agent->id}/role", [
                             'role' => 'owner',
                         ]);

        $response->assertStatus(403)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
