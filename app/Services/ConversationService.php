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
     * Generates a unique, short, human-friendly customer code (e.g. CUS-8F21)
     */
    public function generateCustomerCode(?string $seed = null): string
    {
        $hash = strtoupper(substr(md5($seed ?? \Illuminate\Support\Str::uuid()), 0, 4));
        return 'CUS-' . $hash;
    }

    /**
     * Resolves an existing visitor or creates a new one based on client UUID
     */
    public function getOrCreateVisitor(Project $project, string $visitorUuid, ?string $ip = null, ?string $userAgent = null, ?string $name = null): Visitor
    {
        $visitor = Visitor::where('project_id', $project->id)
            ->where('visitor_uuid', $visitorUuid)
            ->first();

        if ($visitor) {
            $updates = [
                'last_seen_at' => now(),
            ];
            if ($name && (empty($visitor->name) || $visitor->name !== $name)) {
                $updates['name'] = strip_tags($name);
            }
            if (empty($visitor->customer_code)) {
                $updates['customer_code'] = $this->generateCustomerCode($visitorUuid . $project->id);
            }
            $visitor->update($updates);
            return $visitor;
        }

        return Visitor::create([
            'project_id'    => $project->id,
            'visitor_uuid'  => $visitorUuid,
            'customer_code' => $this->generateCustomerCode($visitorUuid . $project->id),
            'name'          => $name ? strip_tags($name) : null,
            'ip_address'    => $ip,
            'user_agent'    => $userAgent,
            'last_seen_at'  => now(),
        ]);
    }

    /**
     * Gets the active conversation for a visitor ONLY if it has messages (does not auto-create empty conversation)
     */
    public function getActiveConversation(Project $project, Visitor $visitor): ?Conversation
    {
        $conversation = Conversation::where('project_id', $project->id)
            ->where('visitor_id', $visitor->id)
            ->whereIn('status', ['open', 'pending'])
            ->latest('id')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        return Conversation::where('project_id', $project->id)
            ->where('visitor_id', $visitor->id)
            ->whereHas('messages')
            ->latest('id')
            ->first();
    }

    /**
     * Gets the active conversation for a visitor or creates a new thread on first customer message
     * Guarantees 1 single consolidated conversation per customer
     */
    public function getOrCreateConversation(Project $project, Visitor $visitor, array $context = []): Conversation
    {
        return DB::transaction(function () use ($project, $visitor, $context) {
            // 1. Ambil semua percakapan terbuka/aktif milik visitor ini
            $conversations = Conversation::where('project_id', $project->id)
                ->where('visitor_id', $visitor->id)
                ->whereIn('status', ['open', 'pending'])
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            if ($conversations->isNotEmpty()) {
                $primaryConv = $conversations->first();

                // Konsolidasi pesan jika sebelumnya sempat terbuat lebih dari 1 thread untuk customer yang sama
                if ($conversations->count() > 1) {
                    $otherIds = $conversations->slice(1)->pluck('id')->toArray();
                    Message::whereIn('conversation_id', $otherIds)->update(['conversation_id' => $primaryConv->id]);
                    Conversation::whereIn('id', $otherIds)->delete();
                }

                // Update page context jika berganti URL
                if (!empty($context['page_url']) && $primaryConv->page_url !== $context['page_url']) {
                    $primaryConv->update([
                        'page_url' => $context['page_url'],
                        'page_title' => $context['page_title'] ?? $primaryConv->page_title,
                    ]);
                }
                return $primaryConv;
            }

            // 2. Buat percakapan baru saat pesan pertama dikirim
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
        });
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
            $currentAgentUnread = (int) ($conversation->unread_agent_count ?? 0);
            $currentVisitorUnread = (int) ($conversation->unread_visitor_count ?? 0);

            $conversation->update([
                'last_message_at' => now(),
                'last_message_preview' => $snippet,
                'unread_agent_count' => $isVisitor ? ($currentAgentUnread + 1) : 0,
                'unread_visitor_count' => !$isVisitor ? ($currentVisitorUnread + 1) : $currentVisitorUnread,
            ]);

            return [
                'message' => $message,
                'is_duplicate' => false,
            ];
        });
    }
}
