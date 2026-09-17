<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessHoursToWidgetSettings extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->boolean('business_hours_enabled')->default(false)->after('auto_reply_offline');
            $table->json('business_hours')->nullable()->after('business_hours_enabled');
            $table->string('business_hours_timezone', 50)->default('Asia/Jakarta')->after('business_hours');
            $table->string('business_hours_off_message', 500)->nullable()->after('business_hours_timezone');
            $table->json('holidays')->nullable()->after('business_hours_off_message');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn([
                'business_hours_enabled',
                'business_hours',
                'business_hours_timezone',
                'business_hours_off_message',
                'holidays',
            ]);
        });
    }
}
