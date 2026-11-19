<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

/** Fixture bersama test halaman admin: tenant, superadmin, agent, project. */
abstract class AdminTestCase extends TestCase
{
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

        User::whereIn('username', ['dashsuper', 'dashagent'])
            ->orWhereIn('email', ['dashsuper@indraco.com', 'dashagent@indraco.com'])
            ->orWhere('tenant_id', $this->tenant->id)
            ->delete();

        $this->superadmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Dashboard Superadmin',
            'email' => 'dashsuper@indraco.com',
            'username' => 'dashsuper',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'status' => 'online',
        ]);

        $this->agent = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Dashboard Agent',
            'email' => 'dashagent@indraco.com',
            'username' => 'dashagent',
            'password' => bcrypt('password'),
            'role' => 'agent',
            'status' => 'online',
        ]);

        $this->project = Project::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'test-dash-project'],
            ['name' => 'Test Dashboard Project']
        );
        $this->project->update(['name' => 'Test Dashboard Project']);
    }
}
