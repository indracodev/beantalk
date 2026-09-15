<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBotSettingsAndConversationBotState extends Migration
{
    public function up()
    {
        // 1. Tambahkan kolom bot pada widget_settings
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->boolean('bot_enabled')->default(false)->after('is_online');
            $table->string('bot_name', 100)->default('BeanBot')->after('bot_enabled');
            $table->text('bot_welcome_message')->nullable()->after('bot_name');
            $table->text('bot_offline_message')->nullable()->after('bot_welcome_message');
            $table->json('bot_rules')->nullable()->after('bot_offline_message');
            $table->boolean('bot_ai_enabled')->default(false)->after('bot_rules');
            $table->text('bot_ai_prompt')->nullable()->after('bot_ai_enabled');
        });

        // 2. Tambahkan status bot pada conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_bot_active')->default(true)->after('status');
            $table->timestamp('bot_handoff_at')->nullable()->after('is_bot_active');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn([
                'bot_enabled',
                'bot_name',
                'bot_welcome_message',
                'bot_offline_message',
                'bot_rules',
                'bot_ai_enabled',
                'bot_ai_prompt',
            ]);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn([
                'is_bot_active',
                'bot_handoff_at',
            ]);
        });
    }
}
