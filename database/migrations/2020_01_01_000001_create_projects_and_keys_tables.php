<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsAndKeysTables extends Migration
{
    public function up()
    {
        // 1. Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('slug', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active'], 'idx_project_tenant');
        });

        // 2. Project Domains (Whitelist Domain)
        Schema::create('project_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('domain', 255);
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->index(['project_id', 'domain'], 'idx_domain_lookup');
        });

        // 3. API Keys (Public pk_live_xxx & Secret Hash)
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('public_key', 64)->unique();
            $table->string('secret_hash', 255)->nullable();
            $table->string('name', 100)->default('Default Key');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['public_key', 'is_active'], 'idx_api_key_public');
        });

        // 4. Widget Settings (Warna Core UI & Teks)
        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->onDelete('cascade');
            $table->string('primary_color', 10)->default('#1E1E1E');
            $table->string('accent_color', 10)->default('#FFFFFF');
            $table->string('position', 20)->default('bottom-right');
            $table->string('greeting_title', 100)->default('Hallo!');
            $table->string('greeting_subtitle', 255)->default('Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!');
            $table->boolean('is_online')->default(true);
            $table->string('auto_reply_offline', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('widget_settings');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('project_domains');
        Schema::dropIfExists('projects');
    }
}
