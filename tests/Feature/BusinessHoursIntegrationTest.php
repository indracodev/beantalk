<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WidgetSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BusinessHoursIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;
    protected User $admin;
    protected Project $project;
    protected WidgetSetting $widgetSetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'   => 'Test Tenant',
            'slug'   => 'test-tenant-' . uniqid(),
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Super Admin',
            'email'     => 'superadmin@example.com',
            'password'  => bcrypt('password123'),
            'role'      => 'superadmin',
        ]);

        $this->project = Project::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Store Testing',
            'slug'      => 'store-testing-' . uniqid(),
            'status'    => 'active',
        ]);

        $this->widgetSetting = WidgetSetting::create([
            'project_id'             => $this->project->id,
            'primary_color'          => '#0071E3',
            'business_hours_enabled' => false,
            'business_hours_timezone'=> 'Asia/Jakarta',
        ]);
    }

    public function test_integration_detail_view_contains_business_hours_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.integrations.detail', $this->project->id));

        $response->assertStatus(200);
        $response->assertViewHas('isWithinBusinessHours');
        $response->assertViewHas('currentSchedule');
        $response->assertSee('Jam Kerja (Business Hours)');
        $response->assertSee('tab-pane-hours');
        $response->assertSee('business_hours_timezone');
    }

    public function test_admin_can_save_business_hours_configuration(): void
    {
        $payload = [
            'primary_color'              => '#0071E3',
            'has_business_hours_form'    => '1',
            'business_hours_enabled'     => '1',
            'business_hours_timezone'    => 'Asia/Makassar',
            'business_hours_off_message' => 'Toko kami sedang tutup (WITA). Silakan tinggalkan email.',
            'business_hours'             => [
                'mon' => ['enabled' => '1', 'start' => '09:00', 'end' => '18:00'],
                'tue' => ['enabled' => '1', 'start' => '09:00', 'end' => '18:00'],
                'wed' => ['enabled' => '1', 'start' => '09:00', 'end' => '18:00'],
                'thu' => ['enabled' => '1', 'start' => '09:00', 'end' => '18:00'],
                'fri' => ['enabled' => '1', 'start' => '09:00', 'end' => '18:00'],
                'sat' => ['enabled' => '0', 'start' => '09:00', 'end' => '14:00'],
                'sun' => ['enabled' => '0', 'start' => '09:00', 'end' => '14:00'],
            ]
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.integrations.settings', $this->project->id), $payload);

        $response->assertRedirect(route('admin.integrations.detail', $this->project->id));

        $this->widgetSetting->refresh();

        $this->assertTrue((bool)$this->widgetSetting->business_hours_enabled);
        $this->assertEquals('Asia/Makassar', $this->widgetSetting->business_hours_timezone);
        $this->assertEquals('Toko kami sedang tutup (WITA). Silakan tinggalkan email.', $this->widgetSetting->business_hours_off_message);
        
        $schedule = $this->widgetSetting->business_hours;
        $this->assertIsArray($schedule);
        $this->assertTrue($schedule['mon']['enabled']);
        $this->assertEquals('09:00', $schedule['mon']['start']);
        $this->assertEquals('18:00', $schedule['mon']['end']);
        $this->assertFalse($schedule['sat']['enabled']);
    }
}
