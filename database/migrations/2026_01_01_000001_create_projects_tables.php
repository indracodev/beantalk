<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active'], 'idx_project_tenant');
        });

        Schema::create('project_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        // Whitelist domain per project (dicek dari header Origin/Referer).
        Schema::create('project_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->index(['project_id', 'domain'], 'idx_domain_lookup');
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('public_key', 64)->unique();
            $table->string('secret_hash')->nullable();
            $table->string('name', 100)->default('Default Key');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['project_id', 'is_active'], 'idx_api_key_project');
        });

        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();

            // Tampilan & teks
            $table->string('primary_color', 10)->default('#1E1E1E');
            $table->string('accent_color', 10)->default('#FFFFFF');
            $table->string('position', 20)->default('bottom-right');
            $table->string('language', 10)->default('id');
            $table->string('greeting_title', 100)->default('Hallo!');
            $table->string('greeting_subtitle')->default('Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!');
            $table->string('support_title', 100)->default('Customer Support');
            $table->boolean('is_online')->default(true);
            $table->string('auto_reply_offline', 500)->nullable();
            $table->string('find_us_title', 100)->nullable()->default('Reach Us Anywhere Else');
            $table->json('social_channels')->nullable();

            // Smart bot
            $table->boolean('bot_enabled')->default(false);
            $table->boolean('bot_mode_query')->default(true);
            $table->boolean('bot_mode_options')->default(true);
            $table->string('bot_name', 100)->default('BeanBot');
            $table->text('bot_welcome_message')->nullable();
            $table->json('bot_welcome_options')->nullable();
            $table->text('bot_offline_message')->nullable();
            $table->json('bot_rules')->nullable();
            $table->json('bot_tree')->nullable();
            $table->boolean('bot_ai_enabled')->default(false);
            $table->text('bot_ai_prompt')->nullable();

            // Telegram: token disimpan terenkripsi (cast model), jadi kolom text.
            $table->text('telegram_bot_token')->nullable();
            $table->string('telegram_chat_id')->nullable();
            $table->boolean('telegram_notifications_enabled')->default(false);
            $table->boolean('telegram_topic_mode_enabled')->default(true);

            // Jam kerja
            $table->boolean('business_hours_enabled')->default(false);
            $table->json('business_hours')->nullable();
            $table->string('business_hours_timezone', 50)->default('Asia/Jakarta');
            $table->string('business_hours_off_message', 500)->nullable();
            $table->json('holidays')->nullable();

            // Suara notifikasi (admin & widget)
            $table->boolean('sound_enabled')->default(true);
            $table->string('sound_type', 50)->default('pedestrian');
            $table->unsignedSmallInteger('sound_duration')->default(15);
            $table->string('sound_custom_url', 500)->nullable();
            $table->boolean('widget_sound_enabled')->default(true);
            $table->string('widget_sound_type', 50)->default('chime');
            $table->string('widget_sound_custom_url', 500)->nullable();

            // Launcher
            $table->string('launcher_type', 30)->default('default');
            $table->string('mascot_id', 50)->default('fox');
            $table->unsignedSmallInteger('mascot_size')->default(72);
            $table->boolean('mascot_tracking')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_settings');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('project_domains');
        Schema::dropIfExists('project_users');
        Schema::dropIfExists('projects');
    }
};
