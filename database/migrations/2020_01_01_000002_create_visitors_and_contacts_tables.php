<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitorsAndContactsTables extends Migration
{
    public function up()
    {
        // 1. Contacts (Known leads/customers with email/phone)
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('name')->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'email'], 'idx_contact_tenant_email');
            $table->index(['project_id', 'email'], 'idx_contact_project_email');
        });

        // 2. Visitors (Anonymous browser fingerprint / local UUID)
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->char('visitor_uuid', 36);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'visitor_uuid'], 'uk_project_visitor_uuid');
            $table->index(['project_id', 'last_seen_at'], 'idx_visitor_last_seen');
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('contacts');
    }
}
