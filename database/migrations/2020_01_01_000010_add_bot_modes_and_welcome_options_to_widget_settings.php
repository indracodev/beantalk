<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBotModesAndWelcomeOptionsToWidgetSettings extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->boolean('bot_mode_query')->default(true)->after('bot_enabled');
            $table->boolean('bot_mode_options')->default(true)->after('bot_mode_query');
            $table->json('bot_welcome_options')->nullable()->after('bot_welcome_message');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn([
                'bot_mode_query',
                'bot_mode_options',
                'bot_welcome_options',
            ]);
        });
    }
}
