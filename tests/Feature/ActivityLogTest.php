<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ActivityLogTest extends TestCase
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
            ['slug' => 'audit-test-tenant'],
            ['name' => 'Audit Tenant Corp', 'plan' => 'enterprise']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'audit-owner@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Audit Owner Hendri',
                'username'  => 'audit-owner',
                'password'  => bcrypt('secret123'),
                'role'      => 'owner',
            ]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'audit-admin@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Audit Admin Bob',
                'username'  => 'audit-admin',
                'password'  => bcrypt('secret123'),
                'role'      => 'admin',
            ]
        );

        $this->agent = User::firstOrCreate(
            ['email' => 'audit-agent@example.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name'      => 'Audit Agent Sarah',
                'username'  => 'audit-agent',
                'password'  => bcrypt('secret123'),
                'role'      => 'agent',
            ]
        );
    }

    /**
     * Test 1: Direct activity logging works across roles
     */
    public function testDirectActivityLoggerRecordsEntry()
    {
        $log = ActivityLogger::log(
            'system.maintenance',
            'Sistem melakukan verifikasi integritas database',
            null,
            ['type' => 'automated_check'],
            $this->owner
        );

        $this->assertDatabaseHas('activity_logs', [
            'id'        => $log->id,
            'user_role' => 'owner',
            'action'    => 'system.maintenance',
        ]);
    }

    /**
     * Test 2: Admin creating integration generates activity log
     */
    public function testCreatingIntegrationLogsActivity()
    {
        $response = $this->actingAs($this->admin)
                         ->postJson('/api/v1/admin/integrations', [
                             'name'          => 'Audit Tested Store',
                             'domain'        => 'audittest.com',
                             'primary_color' => '#6366F1',
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('activity_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->admin->id,
            'user_role' => 'admin',
            'action'    => 'integration.created',
        ]);
    }

    /**
     * Test 3: Inviting team member generates activity log
     */
    public function testInvitingTeamMemberLogsActivity()
    {
        $email = 'new-recruit-' . uniqid() . '@example.com';

        $response = $this->actingAs($this->owner)
                         ->postJson('/api/v1/admin/team', [
                             'name'  => 'New Agent Recruit',
                             'email' => $email,
                             'role'  => 'agent',
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('activity_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->owner->id,
            'user_role' => 'owner',
            'action'    => 'team.invited',
        ]);
    }

    /**
     * Test 4: Updating member role generates activity log
     */
    public function testUpdatingRoleLogsActivity()
    {
        $response = $this->actingAs($this->owner)
                         ->putJson("/api/v1/admin/team/{$this->agent->id}/role", [
                             'role' => 'admin',
                         ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->owner->id,
            'user_role' => 'owner',
            'action'    => 'role.updated',
        ]);
    }

    /**
     * Test 5: Fetch activity logs via REST API endpoint with role filtering
     */
    public function testFetchActivityLogsWithRoleFilter()
    {
        // Seed an agent activity
        ActivityLogger::log('chat.assigned', 'Agent Sarah ditugaskan tiket baru', null, [], $this->agent);
        // Seed an owner activity
        ActivityLogger::log('billing.upgrade', 'Owner Hendri mengupgrade kuota', null, [], $this->owner);

        // 1. Fetch all
        $resAll = $this->actingAs($this->admin)->getJson('/api/v1/admin/activity-logs');
        $resAll->assertStatus(200)
               ->assertJsonPath('success', true)
               ->assertJsonStructure(['data' => ['logs', 'pagination']]);

        // 2. Filter by role=agent
        $resAgent = $this->actingAs($this->admin)->getJson('/api/v1/admin/activity-logs?role=agent');
        $resAgent->assertStatus(200);
        foreach ($resAgent->json('data.logs') as $item) {
            $this->assertEquals('agent', $item['user_role']);
        }

        // 3. Filter by role=owner
        $resOwner = $this->actingAs($this->admin)->getJson('/api/v1/admin/activity-logs?role=owner');
        $resOwner->assertStatus(200);
        foreach ($resOwner->json('data.logs') as $item) {
            $this->assertEquals('owner', $item['user_role']);
        }
    }
}
