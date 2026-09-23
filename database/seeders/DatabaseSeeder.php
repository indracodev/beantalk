<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Hanya menyemai Tenant dan Akun Superadmin utama.
     */
    public function run()
    {
        // 1. Tenant Root
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'indraco'],
            [
                'name'      => 'PT Indraco Jaya Perkasa',
                'plan'      => 'enterprise',
                'is_active' => true,
            ]
        );

        // Bind tenant ke app container selama seeding
        app()->instance('current_tenant_id', $tenant->id);

        // 2. Akun Superadmin Utama (Fresh & Tunggal)
        User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'superadmin@indraco.com'],
            [
                'name'     => 'Superadmin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'status'   => 'online',
            ]
        );

        // 3. Semai Chatbot & Widget Setting INDRACO Store
        $this->call(IndracoStoreChatbotSeeder::class);

        // 4. Semai Chatbot & Widget Setting Supresso Coffee SG
        $this->call(SupressoCoffeeChatbotSeeder::class);
    }
}
