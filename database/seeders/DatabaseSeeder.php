<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\ProjectDomain;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WidgetSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
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

        // 2. Akun Internal BeanTalk (2 Role: Superadmin & Staff/Agent)
        $superadmin = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'superadmin@indraco.com'],
            [
                'name'     => 'Superadmin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'status'   => 'online',
            ]
        );

        $agentSarah = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'sarah@indraco.com'],
            [
                'name'     => 'Sarah (Staff CS)',
                'username' => 'sarah',
                'password' => Hash::make('password'),
                'role'     => 'agent',
                'status'   => 'online',
            ]
        );

        $agentBudi = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'budi@indraco.com'],
            [
                'name'     => 'Budi (Staff CS)',
                'username' => 'budi',
                'password' => Hash::make('password'),
                'role'     => 'agent',
                'status'   => 'online',
            ]
        );

        // 3. 4 Multi-Site Projects
        $sites = [
            [
                'name'          => 'Supresso Coffee',
                'slug'          => 'supresso',
                'domain'        => 'supresso.myshopify.com',
                'public_key'    => 'pk_live_supresso_8819',
                'color'         => '#1E1E1E',
                'customer_name' => 'Jane Doe',
                'customer_email'=> 'jane.doe@gmail.com',
                'page_title'    => 'Supresso Sumatra Mandheling Capsule',
                'page_url'      => 'https://supresso.myshopify.com/products/sumatra-capsule',
                'messages'      => [
                    ['sender' => 'agent',   'name' => 'Sarah (Support)', 'text' => 'Halo! Ada yang bisa kami bantu seputar kopi Supresso hari ini?'],
                    ['sender' => 'visitor', 'name' => 'Jane Doe',        'text' => 'Halo, saya sedang lihat kapsul kopi Sumatra Mandheling. Profil roasting-nya apa ya?'],
                    ['sender' => 'agent',   'name' => 'Sarah (Support)', 'text' => 'Halo Kak Jane! Profil roasting-nya Medium-Dark dengan tasting notes cokelat hitam dan rempah halus. Cocok untuk mesin Nespresso original line!'],
                ],
                'agent_id'      => $agentSarah->id,
            ],
            [
                'name'          => 'Indraco Store',
                'slug'          => 'indraco',
                'domain'        => 'indracostore.com',
                'public_key'    => 'pk_live_indraco_5521',
                'color'         => '#6F4E37',
                'customer_name' => 'Budi Santoso',
                'customer_email'=> 'budi.santoso@yahoo.com',
                'page_title'    => 'Kopi Jahe Kental 10 Sachet',
                'page_url'      => 'https://indracostore.com/promo/kopi-jahe',
                'messages'      => [
                    ['sender' => 'visitor', 'name' => 'Budi Santoso',    'text' => 'Apakah promo beli 2 gratis 1 kopi jahe masih berlaku hari ini?'],
                    ['sender' => 'agent',   'name' => 'Sarah (Support)', 'text' => 'Halo Pak Budi! Promo masih berlaku sampai pukul 23:59 malam ini ya kak.'],
                ],
                'agent_id'      => $agentSarah->id,
            ],
            [
                'name'          => 'Indraco Global B2B',
                'slug'          => 'global',
                'domain'        => 'indracoglobal.com',
                'public_key'    => 'pk_live_global_3309',
                'color'         => '#0F2C59',
                'customer_name' => 'Mr. Tan',
                'customer_email'=> 'tan@singapore-cafe.sg',
                'page_title'    => 'Sumatra Green Beans Grade 1 (20ft Container)',
                'page_url'      => 'https://indracoglobal.com/export/green-beans',
                'messages'      => [
                    ['sender' => 'visitor', 'name' => 'Mr. Tan',         'text' => 'We are requesting quotation for 1x 20ft container of Sumatra Grade 1 shipped to Singapore port (FOB Tanjung Perak).'],
                    ['sender' => 'agent',   'name' => 'Budi (B2B)',      'text' => 'Hello Mr. Tan, our export team has received your inquiry. We will send the official proforma invoice to your email.'],
                ],
                'agent_id'      => $agentBudi->id,
            ],
            [
                'name'          => 'SDA Store Surabaya',
                'slug'          => 'sda',
                'domain'        => 'sdastore.id',
                'public_key'    => 'pk_live_sda_1190',
                'color'         => '#D9230F',
                'customer_name' => 'Rina Wijaya',
                'customer_email'=> 'rina.w@gmail.com',
                'page_title'    => 'Paket Kopi Retail Cabang SDA',
                'page_url'      => 'https://sdastore.id/paket-retail',
                'messages'      => [
                    ['sender' => 'visitor', 'name' => 'Rina Wijaya',     'text' => 'Halo, apakah toko cabang Surabaya melayani pengiriman sameday via GoSend?'],
                ],
                'agent_id'      => $agentSarah->id,
            ],
        ];

        foreach ($sites as $site) {
            // A. Create Project
            $project = Project::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $site['slug']],
                ['name' => $site['name'], 'is_active' => true]
            );

            // B. Whitelist Domain
            ProjectDomain::firstOrCreate(
                ['project_id' => $project->id, 'domain' => $site['domain']],
                ['is_verified' => true]
            );

            // C. API Key (pk_live_xxx)
            ApiKey::firstOrCreate(
                ['public_key' => $site['public_key']],
                [
                    'tenant_id'  => $tenant->id,
                    'project_id' => $project->id,
                    'name'       => $site['name'] . ' Production Key',
                    'is_active'  => true,
                ]
            );

            // D. Widget Settings
            WidgetSetting::firstOrCreate(
                ['project_id' => $project->id],
                [
                    'primary_color'     => $site['color'],
                    'accent_color'      => '#FFFFFF',
                    'position'          => 'bottom-right',
                    'greeting_title'    => 'Hallo!',
                    'greeting_subtitle' => 'Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
                    'is_online'         => true,
                ]
            );

            // E. Contact & Visitor
            $contact = Contact::firstOrCreate(
                ['tenant_id' => $tenant->id, 'project_id' => $project->id, 'email' => $site['customer_email']],
                ['name' => $site['customer_name']]
            );

            $visitor = Visitor::firstOrCreate(
                ['project_id' => $project->id, 'visitor_uuid' => (string) Str::uuid()],
                [
                    'contact_id'   => $contact->id,
                    'ip_address'   => '182.253.120.44',
                    'user_agent'   => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
                    'last_seen_at' => now(),
                ]
            );

            // F. Active Conversation
            $conversation = Conversation::firstOrCreate(
                ['project_id' => $project->id, 'visitor_id' => $visitor->id],
                [
                    'tenant_id'            => $tenant->id,
                    'contact_id'           => $contact->id,
                    'assigned_user_id'     => $site['agent_id'],
                    'status'               => 'open',
                    'channel'              => 'widget',
                    'page_title'           => $site['page_title'],
                    'page_url'             => $site['page_url'],
                    'last_message_at'      => now(),
                    'last_message_preview' => end($site['messages'])['text'] ?? null,
                    'unread_agent_count'   => 1,
                    'unread_visitor_count' => 0,
                ]
            );

            // G. Messages
            foreach ($site['messages'] as $idx => $msg) {
                Message::firstOrCreate(
                    [
                        'conversation_id' => $conversation->id,
                        'client_message_id' => 'seed_' . $site['slug'] . '_' . $idx,
                    ],
                    [
                        'tenant_id'    => $tenant->id,
                        'sender_type'  => $msg['sender'],
                        'sender_name'  => $msg['name'],
                        'content'      => $msg['text'],
                        'content_type' => 'text',
                        'status'       => 'delivered',
                    ]
                );
            }
        }

        // 4. Audit Trail & Activity Logs Across All Roles
        $auditLogs = [
            [
                'user_id'     => $superadmin->id,
                'user_name'   => $superadmin->name,
                'user_role'   => 'superadmin',
                'action'      => 'billing.upgrade',
                'description' => 'Mengupgrade paket subscription tenant ke Enterprise Multi-Tenant (Unlimited Sites & Agents)',
                'properties'  => ['plan' => 'enterprise', 'sites_limit' => 'unlimited'],
                'ip_address'  => '180.252.16.89',
                'created_at'  => now()->subHours(6),
            ],
            [
                'user_id'     => $superadmin->id,
                'user_name'   => $superadmin->name,
                'user_role'   => 'superadmin',
                'action'      => 'integration.created',
                'description' => 'Membuat integrasi channel website baru: Supresso Coffee (supresso.myshopify.com)',
                'properties'  => ['domain' => 'supresso.myshopify.com', 'public_key' => 'pk_live_supresso_8819', 'color' => '#1E1E1E'],
                'ip_address'  => '180.252.16.89',
                'created_at'  => now()->subHours(4),
            ],
            [
                'user_id'     => $superadmin->id,
                'user_name'   => $superadmin->name,
                'user_role'   => 'superadmin',
                'action'      => 'team.invited',
                'description' => 'Menambahkan anggota tim baru: Sarah (sarah@indraco.com) sebagai CS Staff',
                'properties'  => ['email' => 'sarah@indraco.com', 'role' => 'agent'],
                'ip_address'  => '180.252.16.89',
                'created_at'  => now()->subHours(3),
            ],
            [
                'user_id'     => null,
                'user_name'   => 'Jane Doe (Visitor)',
                'user_role'   => 'visitor',
                'action'      => 'session.init',
                'description' => 'Pengunjung menginisialisasi sesi chat dari halaman produk: Supresso Sumatra Mandheling Capsule',
                'properties'  => ['device' => 'Apple iPhone Safari', 'page_url' => 'https://supresso.myshopify.com/products/sumatra-capsule'],
                'ip_address'  => '180.252.16.89',
                'created_at'  => now()->subMinutes(45),
            ],
            [
                'user_id'     => $agentSarah->id,
                'user_name'   => $agentSarah->name,
                'user_role'   => 'agent',
                'action'      => 'message.replied',
                'description' => 'Membalas pesan tiket #1 (Jane Doe) seputar profil roasting kapsul kopi',
                'properties'  => ['conversation_id' => 1, 'channel' => 'widget'],
                'ip_address'  => '182.253.120.44',
                'created_at'  => now()->subMinutes(30),
            ],
            [
                'user_id'     => $agentBudi->id,
                'user_name'   => $agentBudi->name,
                'user_role'   => 'agent',
                'action'      => 'message.replied',
                'description' => 'Membalas inquiry ekspor kontainer green beans 20ft FOB Tanjung Perak ke Mr. Tan (SG)',
                'properties'  => ['conversation_id' => 3, 'channel' => 'widget'],
                'ip_address'  => '182.253.120.44',
                'created_at'  => now()->subMinutes(15),
            ],
        ];

        foreach ($auditLogs as $log) {
            \App\Models\ActivityLog::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'action'    => $log['action'],
                    'created_at'=> $log['created_at'],
                ],
                array_merge($log, ['tenant_id' => $tenant->id])
            );
        }
    }
}
