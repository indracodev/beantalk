<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTables extends Migration
{
    public function up()
    {
        // 1. Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            $table->enum('sender_type', ['visitor', 'agent', 'bot', 'system'])->default('visitor');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_name', 100);
            
            $table->string('client_message_id', 64)->nullable();
            $table->text('content');
            $table->enum('content_type', ['text', 'image', 'file', 'card'])->default('text');
            $table->json('metadata')->nullable();
            
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->boolean('is_internal_note')->default(false);
            $table->timestamps();

            // Idempotency: Prevent double submission from client retry
            $table->unique(['conversation_id', 'client_message_id'], 'uk_msg_idempotent');
            
            // Core Adaptive Polling Index: sub-5ms range scan via WHERE conversation_id = ? AND id > ?
            $table->index(['conversation_id', 'id'], 'idx_msg_poll');
            $table->index(['tenant_id', 'created_at'], 'idx_msg_tenant_created');
        });

        // 2. Message Attachments (Image/File Uploads)
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('original_size')->nullable();
            $table->unsignedBigInteger('compressed_size')->nullable();
            $table->timestamps();
        });

        // 3. Realtime Events Buffer (Transient events queue)
        Schema::create('realtime_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained('conversations')->onDelete('cascade');
            $table->string('event_name', 50);
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['conversation_id', 'id'], 'idx_event_poll');
            $table->index(['project_id', 'id'], 'idx_project_event_poll');
        });
    }

    public function down()
    {
        Schema::dropIfExists('realtime_events');
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('messages');
    }
}
