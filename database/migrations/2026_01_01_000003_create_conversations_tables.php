<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['open', 'pending', 'closed', 'resolved'])->default('open');
            $table->boolean('is_bot_active')->default(true);
            $table->timestamp('bot_handoff_at')->nullable();
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->enum('channel', ['widget', 'whatsapp', 'facebook', 'instagram'])->default('widget');
            $table->unsignedBigInteger('telegram_topic_id')->nullable()->index();
            $table->boolean('telegram_notif_sent')->default(false);

            $table->string('page_url', 500)->nullable();
            $table->string('page_title')->nullable();

            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message_preview')->nullable();
            $table->unsignedInteger('unread_agent_count')->default(0);
            $table->unsignedInteger('unread_visitor_count')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'status', 'last_message_at'], 'idx_conv_inbox');
            $table->index(['tenant_id', 'status', 'last_message_at'], 'idx_conv_tenant_status');
            $table->index(['tenant_id', 'assigned_user_id', 'status'], 'idx_conv_tenant_assignee');
            $table->index(['tenant_id', 'created_at'], 'idx_conv_tenant_created');
            $table->index(['visitor_id', 'status'], 'idx_conv_visitor_status');
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->unsignedBigInteger('last_read_message_id')->nullable();

            $table->unique(['conversation_id', 'user_id'], 'uk_conv_participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
