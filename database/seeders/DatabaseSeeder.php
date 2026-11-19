<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Tenant root + superadmin utama + chatbot bawaan. */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(['slug' => 'indraco'], ['name' => 'PT Indraco Jaya Perkasa', 'plan' => 'enterprise', 'is_active' => true]);
        app()->instance('current_tenant_id', $tenant->id);

        User::firstOrCreate(['tenant_id' => $tenant->id, 'email' => 'superadmin@indraco.com'], [
            'name' => 'Superadmin',
            'username' => 'superadmin',
            'password' => env('SEED_SUPERADMIN_PASSWORD', 'password'), // ganti segera setelah login pertama
            'role' => 'superadmin',
            'status' => 'online',
        ]);

        $this->call([IndracoStoreChatbotSeeder::class, SupressoCoffeeChatbotSeeder::class]);
    }
}
