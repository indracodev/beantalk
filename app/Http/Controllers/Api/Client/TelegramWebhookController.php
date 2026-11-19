<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Balasan staf dari topik forum Telegram → pesan agen di widget.
 * Selalu membalas 200 agar Telegram tidak mengulang kirim; penolakan diberi 'note'.
 */
class TelegramWebhookController extends Controller
{
    /** POST /api/v1/telegram/webhook/{token_hash?} */
    public function handle(Request $request, ConversationService $conversations): JsonResponse
    {
        $msg = $request->input('message');
        if (! is_array($msg) || ! empty($msg['from']['is_bot'])) {
            return $this->ack();
        }

        $text = trim($msg['text'] ?? ($msg['caption'] ?? ''));
        $topicId = $msg['message_thread_id'] ?? ($msg['reply_to_message']['message_thread_id'] ?? null);
        if ($text === '' || ! $topicId) {
            return $this->ack($text === '' ? null : 'no_matching_conversation');
        }

        // ID topik forum hanya unik per grup Telegram → wajib dicocokkan dengan chat.id grup milik project.
        // (telegram_chat_id bukan kolom terenkripsi, jadi aman difilter di PHP setelah index telegram_topic_id.)
        $chatId = (string) ($msg['chat']['id'] ?? '');
        $conversation = Conversation::with('project.widgetSetting')
            ->where('telegram_topic_id', $topicId)
            ->get()
            ->first(fn (Conversation $c) => $chatId !== '' && (string) $c->project?->widgetSetting?->telegram_chat_id === $chatId);
        if (! $conversation) {
            return $this->ack('no_matching_conversation');
        }

        $ws = $conversation->project?->widgetSetting;
        if (! $ws || (! $ws->telegram_topic_mode_enabled && ! $ws->telegram_notifications_enabled)) {
            return $this->ack('telegram_mode_disabled');
        }

        // Verifikasi secret token: tanpa ini siapa pun bisa memalsukan balasan atas nama staf.
        $expected = $ws->telegram_bot_token ? TelegramService::webhookSecret($ws->telegram_bot_token) : '';
        if ($expected === '' || ! hash_equals($expected, (string) $request->header('X-Telegram-Bot-Api-Secret-Token', ''))) {
            Log::warning('[TelegramWebhook] Secret token tidak cocok; daftarkan ulang webhook.', ['conversation_id' => $conversation->id]);

            return $this->ack('invalid_secret_token');
        }

        $agent = $this->matchAgent($conversation->tenant_id, $msg['from'] ?? []);
        if (! $agent) {
            Log::info('[TelegramWebhook] Pengirim bukan staf terdaftar.', ['conversation_id' => $conversation->id]);

            return $this->ack('unauthorized_telegram_sender', ['message' => 'Pengirim Telegram bukan akun staf CS yang terdaftar di BeanTalk.']);
        }

        // Auto-yield bot & buka ulang tiket bila sudah ditutup.
        $conversation->fill(['is_bot_active' => false, 'bot_handoff_at' => $conversation->bot_handoff_at ?? now()]);
        if ($conversation->status === 'closed') {
            $conversation->status = 'open';
        }
        $conversation->save();

        $conversations->appendMessage($conversation, [
            'sender_type' => 'agent',
            'sender_name' => $agent->name,
            'content' => $text,
            'client_message_id' => 'tg_'.($msg['message_id'] ?? time()),
            'metadata' => [
                'from_telegram' => true,
                'telegram_message_id' => $msg['message_id'] ?? null,
                'telegram_user_id' => $msg['from']['id'] ?? null,
                'agent_id' => $agent->id,
                'telegram_username' => $msg['from']['username'] ?? null,
            ],
        ]);

        return response()->json(['ok' => true, 'conversation_id' => $conversation->id, 'agent_name' => $agent->name]);
    }

    /** Cocokkan akun Telegram pengirim dengan staf tenant (id atau username). */
    private function matchAgent(int $tenantId, array $from): ?User
    {
        $id = ! empty($from['id']) ? (string) $from['id'] : null;
        $username = ! empty($from['username']) ? ltrim(trim($from['username']), '@') : null;
        if (! $id && ! $username) {
            return null;
        }

        return User::where('tenant_id', $tenantId)
            ->where(fn ($q) => $q->when($id, fn ($q) => $q->where('telegram_user_id', $id))
                ->when($username, fn ($q) => $q->orWhere('telegram_username', $username)))
            ->first();
    }

    private function ack(?string $note = null, array $extra = []): JsonResponse
    {
        return response()->json(['ok' => true] + ($note ? ['note' => $note] : []) + $extra);
    }
}
