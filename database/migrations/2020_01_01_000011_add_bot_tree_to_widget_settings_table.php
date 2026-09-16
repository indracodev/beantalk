<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBotTreeToWidgetSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->json('bot_tree')->nullable()->after('bot_rules');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn('bot_tree');
        });
    }
}
