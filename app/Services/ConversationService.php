<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    /** Kode pelanggan pendek & ramah, mis. CUS-8F21. */
    public function generateCustomerCode(?string $seed = null): string
    {
        return 'CUS-'.strtoupper(substr(md5($seed ?? (string) Str::uuid()), 0, 4));
    }

    public function getOrCreateVisitor(Project $project, string $visitorUuid, ?string $ip = null, ?string $userAgent = null, ?string $name = null, ?string $email = null): Visitor
    {
        $name = $name ? strip_tags($name) : null;
        $email = $email ? strtolower(trim($email)) : null;

        $visitor = Visitor::where('project_id', $project->id)->where('visitor_uuid', $visitorUuid)->first();

        if (! $visitor) {
            return Visitor::create([
                'project_id' => $project->id,
                'visitor_uuid' => $visitorUuid,
                'customer_code' => $this->generateCustomerCode($visitorUuid.$project->id),
                'name' => $name,
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'last_seen_at' => now(),
            ]);
        }

        $visitor->fill(array_filter([
            'name' => $name,
            'email' => $email,
            'customer_code' => $visitor->customer_code ?: $this->generateCustomerCode($visitorUuid.$project->id),
        ]));
        $visitor->last_seen_at = now();
        $visitor->save();

        return $visitor;
    }

    /**
     * Percakapan HANYA bila milik pemegang visitor_uuid. Tanpa uuid / bukan pemilik = null (controller → 404).
     */
    public function findForVisitor(Project $project, mixed $conversationId, ?string $visitorUuid): ?Conversation
    {
        if (empty($visitorUuid) || ! is_numeric($conversationId) || (int) $conversationId <= 0) {
            return null;
        }

        return Conversation::where('project_id', $project->id)
            ->whereKey((int) $conversationId)
            ->whereHas('visitor', fn ($q) => $q->where('project_id', $project->id)->where('visitor_uuid', $visitorUuid))
            ->first();
    }

    /** Percakapan aktif (open/pending) pengunjung. */
    public function getActiveConversation(Project $project, Visitor $visitor): ?Conversation
    {
        return Conversation::where('project_id', $project->id)
            ->where('visitor_id', $visitor->id)
            ->whereIn('status', ['open', 'pending'])
            ->latest('id')
            ->first();
    }

    /** Riwayat tiket pengunjung (cuplikan tanpa catatan internal). */
    public function getVisitorConversations(Project $project, Visitor $visitor, int $limit = 20): Collection
    {
        return Conversation::where('project_id', $project->id)
            ->where('visitor_id', $visitor->id)
            ->whereHas('messages')
            ->with('latestPublicMessage')
            ->latest('id')
            ->take($limit)
            ->get();
    }

    /** Bentuk ringkas tiket untuk widget. */
    public function toWidgetSummary(Conversation $c): array
    {
        $fallbackPreview = $c->latestPublicMessage ? mb_substr(strip_tags($c->latestPublicMessage->content), 0, 75) : null;

        return [
            'id' => $c->id,
            'status' => $c->status,
            'channel' => $c->channel,
            'channel_label' => $c->channel_label,
            'last_message_preview' => $c->last_message_preview ?? $fallbackPreview,
            'last_message_at' => ($c->last_message_at ?? $c->created_at)?->toIso8601String(),
            'unread_visitor_count' => (int) $c->unread_visitor_count,
            'created_at' => $c->created_at?->toIso8601String(),
        ];
    }

    /**
     * Ambil percakapan aktif pengunjung atau buat baru saat pesan pertama.
     * Menjamin 1 thread terbuka per pengunjung (thread ganda dikonsolidasi).
     */
    public function getOrCreateConversation(Project $project, Visitor $visitor, array $context = []): Conversation
    {
        return DB::transaction(function () use ($project, $visitor, $context) {
            $open = Conversation::where('project_id', $project->id)
                ->where('visitor_id', $visitor->id)
                ->whereIn('status', ['open', 'pending'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($primary = $open->first()) {
                if ($open->count() > 1) {
                    $otherIds = $open->slice(1)->modelKeys();
                    Message::whereIn('conversation_id', $otherIds)->update(['conversation_id' => $primary->id]);
                    Conversation::whereIn('id', $otherIds)->delete();
                }

                if (! empty($context['page_url']) && $primary->page_url !== $context['page_url']) {
                    $primary->update([
                        'page_url' => $context['page_url'],
                        'page_title' => $context['page_title'] ?? $primary->page_title,
                    ]);
                }

                return $primary;
            }

            return Conversation::create([
                'tenant_id' => $project->tenant_id,
                'project_id' => $project->id,
                'visitor_id' => $visitor->id,
                'assigned_user_id' => $project->assigned_user_id,
                'status' => 'open',
                'channel' => 'widget',
                'page_url' => $context['page_url'] ?? null,
                'page_title' => $context['page_title'] ?? null,
                'last_message_at' => now(),
            ]);
        });
    }

    /** Ubah status tiket + sinkron topik forum Telegram (tutup/buka ulang). */
    public function setStatus(Conversation $conversation, string $status): void
    {
        $conversation->update(['status' => $status]);

        $telegram = app(TelegramService::class);
        $status === 'closed' ? $telegram->closeForumTopic($conversation) : $telegram->reopenForumTopic($conversation);
    }

    /**
     * Tambah pesan dengan idempotensi client_message_id.
     *
     * @return array{message: Message, is_duplicate: bool}
     */
    public function appendMessage(Conversation $conversation, array $payload): array
    {
        $senderType = $payload['sender_type'] ?? 'visitor';
        $clientMessageId = $payload['client_message_id'] ?? null;

        if ($clientMessageId) {
            $existing = Message::where('conversation_id', $conversation->id)
                ->where('client_message_id', $clientMessageId)
                ->first();

            if ($existing) {
                // client_message_id milik pengirim lain / catatan internal tidak boleh dikembalikan.
                if ($existing->sender_type !== $senderType || $existing->is_internal_note) {
                    throw ValidationException::withMessages(['client_message_id' => 'client_message_id sudah dipakai.']);
                }

                return ['message' => $existing, 'is_duplicate' => true];
            }
        }

        return DB::transaction(function () use ($conversation, $payload, $senderType, $clientMessageId) {
            $isInternal = (bool) ($payload['is_internal_note'] ?? false);

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'tenant_id' => $conversation->tenant_id,
                'sender_type' => $senderType,
                'sender_id' => $payload['sender_id'] ?? null,
                'sender_name' => $payload['sender_name'] ?? 'Pengunjung',
                'client_message_id' => $clientMessageId,
                'content' => $payload['content'],
                'content_type' => $payload['content_type'] ?? 'text',
                'metadata' => $payload['metadata'] ?? null,
                'status' => 'sent',
                'is_internal_note' => $isInternal,
            ]);

            // Catatan internal tak terlihat pelanggan: cuplikan & counter tidak berubah.
            if ($isInternal) {
                return ['message' => $message, 'is_duplicate' => false];
            }

            $extra = [
                'last_message_at' => now(),
                'last_message_preview' => mb_substr(strip_tags($payload['content']), 0, 120),
            ];

            // Increment atomik di SQL (aman dari race dua pesan bersamaan).
            if ($senderType === 'visitor') {
                $conversation->increment('unread_agent_count', 1, $extra);
            } else {
                $conversation->increment('unread_visitor_count', 1, $extra + ['unread_agent_count' => 0]);
            }

            return ['message' => $message, 'is_duplicate' => false];
        });
    }
}
