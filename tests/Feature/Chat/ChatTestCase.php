<?php

namespace Tests\Feature\Chat;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WidgetSetting;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Fixture widget: tenant, project Supresso, API key, visitor & percakapan #1. */
abstract class ChatTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::firstOrCreate(['slug' => 'indraco'], ['name' => 'PT Indraco Jaya Perkasa', 'is_active' => true]);
        $project = Project::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'supresso'],
            ['name' => 'Supresso Coffee', 'is_active' => true]
        );
        ApiKey::firstOrCreate(
            ['public_key' => 'pk_live_supresso_8819'],
            ['tenant_id' => $tenant->id, 'project_id' => $project->id, 'name' => 'Supresso Test Key', 'is_active' => true]
        );
        $widgetSetting = WidgetSetting::firstOrCreate(
            ['project_id' => $project->id],
            ['primary_color' => '#1E1E1E', 'accent_color' => '#FFFFFF', 'position' => 'bottom-right', 'greeting_title' => 'Hallo!', 'greeting_subtitle' => 'Ada yang bisa dibantu?', 'is_online' => true]
        );
        $widgetSetting->update(['bot_enabled' => false]);

        // Superadmin tenant (dulu "kebetulan" ada dari sisa test lain; kini tiap test terisolasi).
        User::firstOrCreate(
            ['email' => 'chatapi-admin@indraco.com'],
            ['tenant_id' => $tenant->id, 'name' => 'Chat API Admin', 'username' => 'chatapi_admin', 'password' => 'secret123', 'role' => 'superadmin']
        );

        $visitor = Visitor::firstOrCreate(
            ['project_id' => $project->id, 'visitor_uuid' => '550e8400-e29b-41d4-a716-446655440000'],
            ['name' => 'Jane Doe', 'tenant_id' => $project->tenant_id]
        );

        DB::table('conversations')->updateOrInsert(
            ['id' => 1],
            [
                'project_id' => $project->id,
                'tenant_id' => $project->tenant_id,
                'visitor_id' => $visitor->id,
                'status' => 'open',
                'channel' => 'widget',
                'unread_agent_count' => 0,
                'unread_visitor_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
