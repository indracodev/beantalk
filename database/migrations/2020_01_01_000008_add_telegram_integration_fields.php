<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTelegramIntegrationFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->string('telegram_bot_token')->nullable()->after('social_channels');
            $table->string('telegram_chat_id')->nullable()->after('telegram_bot_token');
            $table->boolean('telegram_notifications_enabled')->default(false)->after('telegram_chat_id');
            $table->boolean('telegram_topic_mode_enabled')->default(true)->after('telegram_notifications_enabled');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('telegram_topic_id')->nullable()->after('channel');
            $table->boolean('telegram_notif_sent')->default(false)->after('telegram_topic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_bot_token',
                'telegram_chat_id',
                'telegram_notifications_enabled',
                'telegram_topic_mode_enabled',
            ]);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_topic_id',
                'telegram_notif_sent',
            ]);
        });
    }
}
