<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 100)->nullable();
            $table->enum('user_role', ['superadmin', 'agent', 'visitor', 'system'])->default('system');
            $table->string('action', 50);
            $table->text('description');
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at'], 'idx_logs_tenant_created');
            $table->index(['tenant_id', 'user_id'], 'idx_logs_tenant_user');
            $table->index(['tenant_id', 'user_role'], 'idx_logs_tenant_role');
            $table->index(['tenant_id', 'action'], 'idx_logs_tenant_action');
            $table->index(['subject_type', 'subject_id'], 'idx_logs_subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
