<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailToVisitorsTable extends Migration
{
    public function up()
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');

            $table->index(['project_id', 'email'], 'idx_visitor_project_email');
        });
    }

    public function down()
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex('idx_visitor_project_email');
            $table->dropColumn('email');
        });
    }
}
