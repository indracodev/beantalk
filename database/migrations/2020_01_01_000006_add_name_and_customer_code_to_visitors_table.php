<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNameAndCustomerCodeToVisitorsTable extends Migration
{
    public function up()
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('name')->nullable()->after('contact_id');
            $table->string('customer_code', 16)->nullable()->after('name');

            $table->index(['project_id', 'customer_code'], 'idx_visitor_cust_code');
        });

        // Generate customer_code for existing visitors
        $visitors = DB::table('visitors')->get();
        foreach ($visitors as $v) {
            $code = 'CUS-' . strtoupper(substr(md5(($v->visitor_uuid ?? 'visitor') . $v->id), 0, 4));
            DB::table('visitors')->where('id', $v->id)->update(['customer_code' => $code]);
        }
    }

    public function down()
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex('idx_visitor_cust_code');
            $table->dropColumn(['name', 'customer_code']);
        });
    }
}
