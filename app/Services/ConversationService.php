<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    /**
     * Resolves an existing visitor or creates a new one based on client UUID
     */
    public function getOrCreateVisitor(Project $project, string $visitorUuid, ?string $ip = null, ?string $userAgent = null): Visitor
    {
        return Visitor::firstOrCreate(
            [
                'project_id' => $project->id,
                'visitor_uuid' => $visitorUuid,
            ],
            [
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'last_seen_at' => now(),
            ]
        );
    }

    /**
     * Gets the active conversation for a visitor or creates a new thread
     */
    public function getOrCreateConversation(Project $project, Visitor $visitor, array $context = []): Conversation
    {
        // Cari percakapan aktif yang belum di-close/resolved
        $conversation = Conversation::where('project_id', $project->id)
            ->where('visitor_id', $visitor->id)
            ->whereIn('status', ['open', 'pending'])
            ->latest('id')
            ->first();

        if ($conversation) {
            // Update page context jika berganti URL
            if (!empty($context['page_url']) && $conversation->page_url !== $context['page_url']) {
                $conversation->update([
                    'page_url' => $context['page_url'],
                    'page_title' => $context['page_title'] ?? $conversation->page_title,
                ]);
            }
            return $conversation;
        }

        // Buat percakapan baru jika belum ada
        return Conversation::create([
            'tenant_id' => $project->tenant_id,
            'project_id' => $project->id,
            'visitor_id' => $visitor->id,
            'status' => 'open',
            'channel' => 'widget',
            'page_url' => $context['page_url'] ?? null,
            'page_title' => $context['page_title'] ?? null,
            'last_message_at' => now(),
        ]);
    }

    /**
     * Appends a message to the conversation with strict idempotency check
     * 
     * @return array ['message' => Message, 'is_duplicate' => bool]
     */
    public function appendMessage(Conversation $conversation, array $payload): array
    {
        $clientMessageId = $payload['client_message_id'] ?? null;

        // 1. Idempotency Check: Cegah duplikasi saat koneksi timeout atau client retry
        if ($clientMessageId) {
            $existing = Message::where('conversation_id', $conversation->id)
                ->where('client_message_id', $clientMessageId)
                ->first();

            if ($existing) {
                return [
                    'message' => $existing,
                    'is_duplicate' => true,
                ];
            }
        }

        // 2. Simpan pesan baru dalam database transaction
        return DB::transaction(function () use ($conversation, $payload, $clientMessageId) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'tenant_id' => $conversation->tenant_id,
                'sender_type' => $payload['sender_type'] ?? 'visitor',
                'sender_id' => $payload['sender_id'] ?? null,
                'sender_name' => $payload['sender_name'] ?? 'Pengunjung',
                'client_message_id' => $clientMessageId,
                'content' => $payload['content'],
                'content_type' => $payload['content_type'] ?? 'text',
                'metadata' => $payload['metadata'] ?? null,
                'status' => 'sent',
                'is_internal_note' => $payload['is_internal_note'] ?? false,
            ]);

            // 3. Update preview dan unread counter di parent conversation
            $snippet = mb_substr(strip_tags($payload['content']), 0, 120);
            $isVisitor = ($payload['sender_type'] ?? 'visitor') === 'visitor';

            $conversation->update([
                'last_message_at' => now(),
                'last_message_preview' => $snippet,
                'unread_agent_count' => $isVisitor ? ($conversation->unread_agent_count + 1) : 0,
                'unread_visitor_count' => !$isVisitor ? ($conversation->unread_visitor_count + 1) : $conversation->unread_visitor_count,
            ]);

            return [
                'message' => $message,
                'is_duplicate' => false,
            ];
        });
    }
}
