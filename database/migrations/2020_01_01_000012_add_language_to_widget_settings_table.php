<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageToWidgetSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->string('language', 10)->default('id')->after('position');
        });
    }

    public function down()
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
}
