<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DashboardPageTest extends AdminTestCase
{
    /**
     * Test Guest redirected to /login when accessing admin routes
     */
    public function test_guest_is_redirected_to_login()
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/inbox')->assertRedirect('/login');
        $this->get('/admin/integrations')->assertRedirect('/login');
        $this->get('/admin/team')->assertRedirect('/login');
        $this->get('/admin/logs')->assertRedirect('/login');
        $this->get('/admin/visitors')->assertRedirect('/login');
    }

    /**
     * Test Authenticated User can view Executive Dashboard page
     */
    public function test_authenticated_user_can_view_executive_dashboard()
    {
        $response = $this->actingAs($this->superadmin)->get('/admin');

        $response->assertStatus(200)
            ->assertSee('Executive Dashboard')
            ->assertSee('Streaming')
            ->assertSee('Total Conversations')
            ->assertSee('Volume &amp; Resolution Velocity', false);
    }

    /**
     * Test Authenticated User can export Dashboard CSV report
     */
    public function test_authenticated_user_can_export_dashboard_report()
    {
        $response = $this->actingAs($this->superadmin)->get('/admin/dashboard/export?period=7d');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test Authenticated User can filter Executive Dashboard by custom date range
     */
    public function test_authenticated_user_can_filter_dashboard_by_custom_date_range()
    {
        $start = now()->subDays(10)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = $this->actingAs($this->superadmin)->get("/admin?start_date={$start}&end_date={$end}");

        $response->assertStatus(200)
            ->assertSee('Executive Dashboard')
            ->assertSee($start)
            ->assertSee($end);
    }

    /**
     * Test Authenticated User can export custom date range report
     */
    public function test_authenticated_user_can_export_custom_date_range_report()
    {
        $start = now()->subDays(5)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = $this->actingAs($this->superadmin)->get("/admin/dashboard/export?start_date={$start}&end_date={$end}");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test Authenticated User can view Live Inbox page
     */

    /**
     * Test User can update their own password
     */
    public function test_user_can_update_own_password()
    {
        $response = $this->actingAs($this->superadmin)->put('/admin/profile/password', [
            'current_password' => 'password',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertSessionHas('success');
        $this->superadmin->refresh();
        $this->assertTrue(Hash::check('new-secret-123', $this->superadmin->password));
    }

    /**
     * Test User cannot update own password with incorrect current password
     */
    public function test_user_cannot_update_own_password_with_invalid_current_password()
    {
        $response = $this->actingAs($this->superadmin)->put('/admin/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->superadmin->refresh();
        $this->assertTrue(Hash::check('password', $this->superadmin->password));
    }
}
