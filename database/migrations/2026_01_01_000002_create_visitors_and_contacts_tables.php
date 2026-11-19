<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('name')->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'email'], 'idx_contact_tenant_email');
            $table->index(['project_id', 'email'], 'idx_contact_project_email');
        });

        // Pengunjung anonim (UUID lokal browser)
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('customer_code', 16)->nullable();
            $table->char('visitor_uuid', 36);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'visitor_uuid'], 'uk_project_visitor_uuid');
            $table->index(['project_id', 'last_seen_at'], 'idx_visitor_last_seen');
            $table->index(['project_id', 'created_at'], 'idx_visitor_created');
            $table->index(['project_id', 'customer_code'], 'idx_visitor_cust_code');
            $table->index(['project_id', 'email'], 'idx_visitor_project_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('contacts');
    }
};
