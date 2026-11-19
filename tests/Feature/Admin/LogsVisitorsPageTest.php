<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Visitor;

class LogsVisitorsPageTest extends AdminTestCase
{
    /**
     * Test Authenticated User can view Activity Logs page
     */
    public function test_authenticated_user_can_view_logs()
    {
        // Seed an activity log
        ActivityLog::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->superadmin->id,
            'user_name' => $this->superadmin->name,
            'user_role' => 'superadmin',
            'action' => 'auth.test_login',
            'description' => 'Admin test logged in successfully',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->superadmin)->get('/admin/logs');

        $response->assertStatus(200)
            ->assertSee('Activity Logs &amp; Audit Trail', false)
            ->assertSee('auth.test_login')
            ->assertSee('Admin test logged in successfully')
            ->assertSee('logsTableView')
            ->assertSee('logsCardsView')
            ->assertSee('tableScrollHint');
    }

    /**
     * Test Activity Logs Filter, Search, Sort and Responsive Views
     */
    public function test_logs_page_filter_sort_and_responsive_features()
    {
        ActivityLog::where('tenant_id', $this->tenant->id)->delete();

        ActivityLog::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->agent->id,
            'user_name' => 'Agent Special Ops',
            'user_role' => 'agent',
            'action' => 'integration.create_secret',
            'description' => 'Created secret key for webhook',
            'ip_address' => '192.168.1.100',
            'created_at' => now()->subMinutes(10),
        ]);

        ActivityLog::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->superadmin->id,
            'user_name' => 'Super Commander',
            'user_role' => 'superadmin',
            'action' => 'widget.color_updated',
            'description' => 'Updated primary accent color to amber',
            'ip_address' => '10.0.0.1',
            'created_at' => now()->subMinutes(5),
        ]);

        // 1. Search filter: only matching query appears
        $resSearch = $this->actingAs($this->superadmin)->get('/admin/logs?search=webhook');
        $resSearch->assertStatus(200)
            ->assertSee('Created secret key for webhook')
            ->assertDontSee('Updated primary accent color to amber');

        // 2. Role filter: only agent appears
        $resRole = $this->actingAs($this->superadmin)->get('/admin/logs?role=agent');
        $resRole->assertStatus(200)
            ->assertSee('Created secret key for webhook')
            ->assertDontSee('Updated primary accent color to amber');

        // 3. Action filter: wildcard 'widget.*'
        $resAction = $this->actingAs($this->superadmin)->get('/admin/logs?action=widget.*');
        $resAction->assertStatus(200)
            ->assertSee('widget.color_updated')
            ->assertDontSee('Created secret key for webhook');

        // 4. Sort and direction: check sort headers
        $resSort = $this->actingAs($this->superadmin)->get('/admin/logs?sort=user_name&dir=asc');
        $resSort->assertStatus(200)
            ->assertSee('Agent Special Ops')
            ->assertSee('Super Commander');
    }

    /**
     * Test Authenticated User can view Tracked Visitors page
     */
    public function test_authenticated_user_can_view_visitors_page()
    {
        Visitor::where('visitor_uuid', 'uuid-visitor-test-view')->delete();

        $visitor = Visitor::create([
            'project_id' => $this->project->id,
            'visitor_uuid' => 'uuid-visitor-test-view',
            'customer_code' => 'CUS-TEST99',
            'name' => 'Tracked Visitor Alpha',
            'email' => 'alpha@tracked.test',
            'ip_address' => '203.0.113.195',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
            'last_seen_at' => now(),
        ]);

        $response = $this->actingAs($this->superadmin)->get('/admin/visitors');

        $response->assertStatus(200)
            ->assertSee('Pengunjung Terlacak')
            ->assertSee('Tracked Visitor Alpha')
            ->assertSee('CUS-TEST99')
            ->assertSee('visitorsTableView')
            ->assertSee('visitorsCardsView');
    }

    /**
     * Test Visitors search, filter, and detail endpoint
     */
    public function test_visitors_search_filter_and_detail_endpoint()
    {
        Visitor::whereIn('visitor_uuid', ['uuid-vis-mobile-search', 'uuid-vis-desktop-other'])->delete();

        $v1 = Visitor::create([
            'project_id' => $this->project->id,
            'visitor_uuid' => 'uuid-vis-mobile-search',
            'customer_code' => 'CUS-SRCH01',
            'name' => 'Target Search Person',
            'email' => 'target@search.test',
            'ip_address' => '10.10.10.10',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
            'last_seen_at' => now(),
        ]);

        $v2 = Visitor::create([
            'project_id' => $this->project->id,
            'visitor_uuid' => 'uuid-vis-desktop-other',
            'customer_code' => 'CUS-DESK02',
            'name' => 'Other Desktop Guy',
            'email' => 'other@desktop.test',
            'ip_address' => '192.168.10.20',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'last_seen_at' => now()->subDays(2),
        ]);

        // 1. Search by customer code
        $resSearch = $this->actingAs($this->agent)->get('/admin/visitors?search=CUS-SRCH01');
        $resSearch->assertStatus(200)
            ->assertSee('Target Search Person')
            ->assertDontSee('Other Desktop Guy');

        // 2. Filter by status online
        $resOnline = $this->actingAs($this->agent)->get('/admin/visitors?status=online');
        $resOnline->assertStatus(200)
            ->assertSee('Target Search Person')
            ->assertDontSee('Other Desktop Guy');

        // 3. Filter by device mobile
        $resDevice = $this->actingAs($this->agent)->get('/admin/visitors?device=mobile');
        $resDevice->assertStatus(200)
            ->assertSee('Target Search Person')
            ->assertDontSee('Other Desktop Guy');

        // 4. AJAX Detail endpoint
        $resDetail = $this->actingAs($this->agent)->getJson("/admin/visitors/{$v1->id}");
        $resDetail->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $v1->id,
                    'customer_code' => 'CUS-SRCH01',
                    'name' => 'Target Search Person',
                    'email' => 'target@search.test',
                    'device_type' => 'mobile',
                    'is_online' => true,
                ],
            ]);
    }
}
