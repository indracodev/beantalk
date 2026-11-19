<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;

class IntegrationPageTest extends AdminTestCase
{
    /**
     * Test Authenticated User can view Integrations Hub
     */
    public function test_authenticated_user_can_view_integrations()
    {
        $response = $this->actingAs($this->agent)->get('/admin/integrations');

        $response->assertStatus(200)
            ->assertSee('Integrasi Web')
            ->assertSee('Test Dashboard Project');
    }

    /**
     * Test Admin can store new integration project
     */
    public function test_admin_can_store_new_integration()
    {
        $response = $this->actingAs($this->superadmin)->post('/admin/integrations', [
            'name' => 'Toko Cabang Surabaya',
            'domain' => 'surabaya.tokokita.com',
            'primary_color' => '#3B82F6',
            'greeting_title' => 'Selamat Datang!',
            'greeting_subtitle' => 'Customer Service Surabaya siap melayani Anda.',
        ]);

        $response->assertRedirect(route('admin.integrations'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Toko Cabang Surabaya',
        ]);

        $this->assertDatabaseHas('project_domains', [
            'domain' => 'surabaya.tokokita.com',
        ]);

        $this->assertDatabaseHas('widget_settings', [
            'primary_color' => '#3B82F6',
        ]);
    }

    /**
     * Test Authenticated Admin can view dedicated integration detail page
     */
    public function test_authenticated_user_can_view_integration_detail()
    {
        $response = $this->actingAs($this->superadmin)->get("/admin/integrations/{$this->project->id}");

        $response->assertStatus(200)
            ->assertSee($this->project->name)
            ->assertSee('Smart Bot &amp; FAQ Rules', false)
            ->assertSee('Tampilan &amp; Branding', false)
            ->assertSee('Saluran Sosial &amp; Marketplace', false);
    }

    /**
     * Test Admin can update integration name and domains whitelist
     */
    public function test_admin_can_update_integration_name_and_domains()
    {
        $response = $this->actingAs($this->superadmin)->put("/admin/integrations/{$this->project->id}/settings", [
            'name' => 'Updated Brand Name',
            'domains' => 'https://newdomain.com/, www.newdomain.com, api.newdomain.com',
            'primary_color' => '#0071E3',
        ]);

        $response->assertRedirect("/admin/integrations/{$this->project->id}");

        $this->project->refresh();
        $this->assertEquals('Updated Brand Name', $this->project->name);

        $domains = $this->project->domains()->pluck('domain')->toArray();
        $this->assertContains('newdomain.com', $domains);
        $this->assertContains('www.newdomain.com', $domains);
        $this->assertContains('api.newdomain.com', $domains);
    }

    /**
     * Test Admin can assign multiple agents or default to all
     */
    public function test_admin_can_assign_multiple_agents_to_project()
    {
        // 1. Assign specific agent
        $response = $this->actingAs($this->superadmin)->put("/admin/integrations/{$this->project->id}/settings", [
            'name' => $this->project->name,
            'primary_color' => '#0071E3',
            'agent_scope' => 'selected',
            'agent_ids' => [$this->agent->id],
        ]);

        $response->assertRedirect("/admin/integrations/{$this->project->id}");
        $this->project->refresh();
        $this->assertCount(1, $this->project->assignedUsers);
        $this->assertEquals($this->agent->id, $this->project->assignedUsers->first()->id);

        // 2. Set back to all agents (default)
        $responseAll = $this->actingAs($this->superadmin)->put("/admin/integrations/{$this->project->id}/settings", [
            'name' => $this->project->name,
            'primary_color' => '#0071E3',
            'agent_scope' => 'all',
        ]);

        $responseAll->assertRedirect("/admin/integrations/{$this->project->id}");
        $this->project->refresh();
        $this->assertCount(0, $this->project->assignedUsers);
        $this->assertNull($this->project->assigned_user_id);
    }
}
