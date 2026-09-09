<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTables extends Migration
{
    public function up()
    {
        // 1. Conversations
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('visitor_id')->constrained('visitors')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('status', ['open', 'pending', 'closed', 'resolved'])->default('open');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->enum('channel', ['widget', 'whatsapp', 'facebook', 'instagram'])->default('widget');
            
            $table->string('page_url', 500)->nullable();
            $table->string('page_title', 255)->nullable();
            
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message_preview')->nullable();
            $table->unsignedInteger('unread_agent_count')->default(0);
            $table->unsignedInteger('unread_visitor_count')->default(0);
            
            $table->timestamps();

            // Compound index for fast Admin Inbox listing & filtering
            $table->index(['project_id', 'status', 'last_message_at'], 'idx_conv_inbox');
            $table->index(['tenant_id', 'status'], 'idx_conv_tenant_status');
        });

        // 2. Conversation Participants (Agents involved in chat)
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->unsignedBigInteger('last_read_message_id')->nullable();

            $table->unique(['conversation_id', 'user_id'], 'uk_conv_participant');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
}
