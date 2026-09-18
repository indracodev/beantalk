<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLauncherMascotToWidgetSettings extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->string('launcher_type', 30)->default('default')->after('widget_sound_custom_url');
            $table->string('mascot_id', 50)->default('fox')->after('launcher_type');
            $table->unsignedSmallInteger('mascot_size')->default(72)->after('mascot_id');
            $table->boolean('mascot_tracking')->default(true)->after('mascot_size');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn([
                'launcher_type',
                'mascot_id',
                'mascot_size',
                'mascot_tracking',
            ]);
        });
    }
}
