<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoundSettingsToWidgetSettings extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->boolean('sound_enabled')->default(true)->after('holidays');
            $table->string('sound_type', 50)->default('pedestrian')->after('sound_enabled');
            $table->unsignedSmallInteger('sound_duration')->default(15)->after('sound_type');
            $table->string('sound_custom_url', 500)->nullable()->after('sound_duration');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sound_enabled',
                'sound_type',
                'sound_duration',
                'sound_custom_url',
            ]);
        });
    }
}
