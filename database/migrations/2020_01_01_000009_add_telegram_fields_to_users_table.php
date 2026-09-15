<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTelegramFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_username', 100)->nullable()->after('avatar_url');
            $table->string('telegram_user_id', 100)->nullable()->after('telegram_username');

            $table->index(['tenant_id', 'telegram_username'], 'idx_users_tenant_tg_user');
            $table->index(['tenant_id', 'telegram_user_id'], 'idx_users_tenant_tg_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_tenant_tg_user');
            $table->dropIndex('idx_users_tenant_tg_id');
            $table->dropColumn(['telegram_username', 'telegram_user_id']);
        });
    }
}
