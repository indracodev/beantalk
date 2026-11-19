<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class RbacSecurityTest extends TestCase
{
    protected $tenant;

    protected $superadmin;

    protected $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'internal-test-tenant'],
            ['name' => 'Internal BeanTalk Group', 'plan' => 'enterprise']
        );

        $this->superadmin = User::updateOrCreate(
            ['email' => 'test-superadmin@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Test Superadmin',
                'username' => 'test-superadmin',
                'password' => bcrypt('secret123'),
                'role' => 'superadmin',
            ]
        );

        $this->agent = User::updateOrCreate(
            ['email' => 'test-agent@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Test Agent',
                'username' => 'test-agent',
                'password' => bcrypt('secret123'),
                'role' => 'agent',
            ]
        );
    }

    /**
     * Test 1: Unauthenticated request to role-protected endpoint is rejected (401)
     */
    public function test_unauthenticated_request_to_role_protected_endpoint_fails()
    {
        $response = $this->postJson('/api/v1/admin/integrations', [
            'name' => 'Unauthorized Store',
            'domain' => 'unauth.com',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    /**
     * Test 2: Agent cannot create integration (403 Forbidden)
     */
    public function test_agent_cannot_create_integration()
    {
        $response = $this->actingAs($this->agent)
            ->postJson('/api/v1/admin/integrations', [
                'name' => 'Agent Store Attempt',
                'domain' => 'agentattempt.com',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    /**
     * Test 3: Superadmin CAN create integration (201 Created)
     */
    public function test_superadmin_can_create_integration()
    {
        $response = $this->actingAs($this->superadmin)
            ->postJson('/api/v1/admin/integrations', [
                'name' => 'Master Official Store',
                'domain' => 'masterstore.com',
                'primary_color' => '#C59B27',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Master Official Store');
    }

    /**
     * Test 4: Agent CAN view team list and conversations
     */
    public function test_agent_can_view_team_and_conversations()
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
     * Test 5: Agent cannot invite new team members (403 Forbidden)
     */
    public function test_agent_cannot_invite_team_members()
    {
        $response = $this->actingAs($this->agent)
            ->postJson('/api/v1/admin/team', [
                'name' => 'New Rogue Member',
                'email' => 'rogue@example.com',
                'role' => 'agent',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    /**
     * Test 6: Superadmin CAN invite new agent team member (201 Created)
     */
    public function test_superadmin_can_invite_team_member()
    {
        $unique = uniqid();
        $response = $this->actingAs($this->superadmin)
            ->postJson('/api/v1/admin/team', [
                'name' => 'Staff Baru',
                'username' => 'staff_'.$unique,
                'email' => 'staff_'.$unique.'@example.com',
                'role' => 'agent',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'agent');
    }

    /**
     * Test 7: Superadmin CAN promote agent to superadmin (200 OK)
     */
    public function test_superadmin_can_change_role()
    {
        $response = $this->actingAs($this->superadmin)
            ->putJson("/api/v1/admin/team/{$this->agent->id}/role", [
                'role' => 'superadmin',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'superadmin');
    }

    /**
     * Test 8: Agent CANNOT change team member role (403 Forbidden)
     */
    public function test_agent_cannot_change_role()
    {
        $this->agent->update(['role' => 'agent']);

        $response = $this->actingAs($this->agent)
            ->putJson("/api/v1/admin/team/{$this->superadmin->id}/role", [
                'role' => 'agent',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
