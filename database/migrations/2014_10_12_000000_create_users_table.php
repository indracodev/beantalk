<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('username', 50)->nullable()->unique();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['superadmin', 'owner', 'admin', 'agent'])->default('agent');
            $table->enum('status', ['online', 'busy', 'offline'])->default('offline');
            $table->string('avatar_url', 500)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['tenant_id', 'email'], 'uk_tenant_email');
            $table->index(['tenant_id', 'role'], 'idx_user_tenant_role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
