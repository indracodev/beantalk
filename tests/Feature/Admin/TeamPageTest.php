<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Hash;

class TeamPageTest extends AdminTestCase
{
    /**
     * Test Superadmin can view Team Management page
     */
    public function test_superadmin_can_view_team_page()
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
    public function test_agent_cannot_view_team_page()
    {
        $response = $this->actingAs($this->agent)->get('/admin/team');

        $response->assertStatus(403);
    }

    /**
     * Test Superadmin can store new team member
     */
    public function test_superadmin_can_store_new_team_member()
    {
        $response = $this->actingAs($this->superadmin)->post('/admin/team', [
            'name' => 'CS Budi Santoso',
            'username' => 'budi_cs_'.uniqid(),
            'email' => 'budi_'.uniqid().'@indraco.com',
            'role' => 'agent',
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
    public function test_agent_cannot_store_new_team_member()
    {
        $response = $this->actingAs($this->agent)->post('/admin/team', [
            'name' => 'CS Hacker',
            'email' => 'hacker@indraco.com',
            'role' => 'superadmin',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test Superadmin can impersonate (Login As) an agent
     */
    public function test_superadmin_can_impersonate_agent()
    {
        $response = $this->actingAs($this->superadmin)
            ->post("/admin/team/{$this->agent->id}/impersonate");

        $response->assertRedirect('/admin/inbox');
        $this->assertEquals($this->agent->id, auth()->id());
        $this->assertEquals($this->superadmin->id, session('impersonator_id'));
    }

    /**
     * Test Impersonated session can leave and return to original superadmin
     */
    public function test_impersonated_user_can_leave_impersonation()
    {
        // Masuk sebagai agent terlebih dahulu
        $this->actingAs($this->superadmin)
            ->post("/admin/team/{$this->agent->id}/impersonate");

        $this->assertEquals($this->agent->id, auth()->id());

        // Kembali ke akun superadmin asli
        $response = $this->post('/admin/impersonate/leave');

        $response->assertRedirect('/admin/team');
        $this->assertEquals($this->superadmin->id, auth()->id());
        $this->assertFalse(session()->has('impersonator_id'));
    }

    /**
     * Test Agent cannot impersonate other users (403 Forbidden)
     */
    public function test_agent_cannot_impersonate()
    {
        $response = $this->actingAs($this->agent)
            ->post("/admin/team/{$this->superadmin->id}/impersonate");

        $response->assertStatus(403);
    }

    /**
     * Test Admin can update staff password in team management
     */
    public function test_admin_can_update_staff_password()
    {
        $response = $this->actingAs($this->superadmin)->put("/admin/team/{$this->agent->id}", [
            'name' => $this->agent->name,
            'email' => $this->agent->email,
            'role' => 'agent',
            'password' => 'new-agent-secret',
        ]);

        $response->assertSessionHas('success');
        $this->agent->refresh();
        $this->assertTrue(Hash::check('new-agent-secret', $this->agent->password));
    }
}
