<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 100)->nullable();
            $table->enum('user_role', ['superadmin', 'agent', 'visitor', 'system'])->default('system');
            $table->string('action', 50); // e.g. integration.created, team.invited, role.updated, message.replied, chat.closed
            $table->text('description');
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes for high-speed audit queries
            $table->index(['tenant_id', 'created_at'], 'idx_logs_tenant_created');
            $table->index(['tenant_id', 'user_id'], 'idx_logs_tenant_user');
            $table->index(['tenant_id', 'user_role'], 'idx_logs_tenant_role');
            $table->index(['tenant_id', 'action'], 'idx_logs_tenant_action');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
}
