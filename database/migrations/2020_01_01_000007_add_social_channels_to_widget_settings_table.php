<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocialChannelsToWidgetSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->string('find_us_title', 100)->nullable()->default('Reach Us Anywhere Else')->after('auto_reply_offline');
            $table->json('social_channels')->nullable()->after('find_us_title');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn(['find_us_title', 'social_channels']);
        });
    }
}
