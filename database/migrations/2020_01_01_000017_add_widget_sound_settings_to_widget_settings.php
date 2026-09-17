<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWidgetSoundSettingsToWidgetSettings extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->boolean('widget_sound_enabled')->default(true)->after('sound_custom_url');
            $table->string('widget_sound_type', 50)->default('chime')->after('widget_sound_enabled');
            $table->string('widget_sound_custom_url', 500)->nullable()->after('widget_sound_type');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn([
                'widget_sound_enabled',
                'widget_sound_type',
                'widget_sound_custom_url',
            ]);
        });
    }
}
