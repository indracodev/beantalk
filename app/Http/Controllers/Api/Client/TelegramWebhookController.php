<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * @var ConversationService
     */
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Handles incoming Webhook updates from Telegram Bot
     * POST /api/v1/telegram/webhook/{token_hash?}
     */
    public function handle(Request $request, ?string $tokenHash = null): JsonResponse
    {
        $payload = $request->all();

        // 1. Validasi pesan masuk dari Telegram
        $msg = $payload['message'] ?? null;
        if (!$msg || !empty($msg['from']['is_bot'])) {
            return response()->json(['ok' => true]);
        }

        $text = trim($msg['text'] ?? ($msg['caption'] ?? ''));
        if ($text === '') {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) ($msg['chat']['id'] ?? '');
        $topicId = $msg['message_thread_id'] ?? null;
        $replyTo = $msg['reply_to_message'] ?? null;

        // 2. Cari percakapan berdasarkan Topic ID (Mode Forum) atau Chat ID
        $conversation = null;

        if ($topicId) {
            $conversation = Conversation::where('telegram_topic_id', $topicId)->with('project.widgetSetting')->first();
        }

        // Fallback jika bukan mode forum tapi reply ke bubble customer
        if (!$conversation && $replyTo) {
            $replyTopicId = $replyTo['message_thread_id'] ?? null;
            if ($replyTopicId) {
                $conversation = Conversation::where('telegram_topic_id', $replyTopicId)->with('project.widgetSetting')->first();
            }
        }

        if (!$conversation) {
            return response()->json(['ok' => true, 'note' => 'no_matching_conversation']);
        }

        // 3. Validasi Toggle ON/OFF Telegram di Project
        $widgetSetting = $conversation->project->widgetSetting ?? null;
        if (!$widgetSetting || (!$widgetSetting->telegram_topic_mode_enabled && !$widgetSetting->telegram_notifications_enabled)) {
            return response()->json(['ok' => true, 'note' => 'telegram_mode_disabled']);
        }

        // 4. Validasi Keamanan: Cocokkan Akun Pengirim Telegram dengan Akun Staf CS di Web
        $from = $msg['from'] ?? [];
        $fromUserId = !empty($from['id']) ? (string) $from['id'] : null;
        $fromUsername = !empty($from['username']) ? ltrim(trim($from['username']), '@') : null;

        $matchedAgent = null;
        if ($fromUserId || $fromUsername) {
            $matchedAgent = User::where('tenant_id', $conversation->tenant_id)
                ->where(function ($q) use ($fromUserId, $fromUsername) {
                    if ($fromUserId) {
                        $q->where('telegram_user_id', $fromUserId);
                    }
                    if ($fromUsername) {
                        $q->orWhere('telegram_username', $fromUsername);
                    }
                })
                ->first();
        }

        // Jika pengirim bukan staf CS resmi terdaftar, abaikan pesan (tidak dikirim ke customer)
        if (!$matchedAgent) {
            Log::info('[TelegramWebhook] Pesan diabaikan: Pengirim Telegram tidak terdaftar sebagai staf CS resmi.', [
                'from'            => $from,
                'conversation_id' => $conversation->id,
            ]);

            return response()->json([
                'ok'      => true,
                'note'    => 'unauthorized_telegram_sender',
                'message' => 'Pengirim Telegram bukan akun staf CS yang terdaftar di BeanTalk.'
            ]);
        }

        // 5. Update Conversation: Auto-yield bot dan pastikan status open
        $updates = [];
        if ($conversation->is_bot_active) {
            $updates['is_bot_active'] = false;
            $updates['bot_handoff_at'] = now();
        }
        if ($conversation->status === 'closed') {
            $updates['status'] = 'open';
        }
        if (!empty($updates)) {
            $conversation->update($updates);
        }

        // 6. Append message: Tampil dengan nama resmi agen tanpa label "(via Telegram)"
        $this->conversationService->appendMessage($conversation, [
            'sender_type'       => 'agent',
            'sender_name'       => $matchedAgent->name,
            'content'           => $text,
            'client_message_id' => 'tg_' . ($msg['message_id'] ?? time()),
            'metadata'          => [
                'from_telegram'       => true,
                'telegram_message_id' => $msg['message_id'] ?? null,
                'telegram_user_id'    => $from['id'] ?? null,
                'agent_id'            => $matchedAgent->id,
                'telegram_username'   => $from['username'] ?? null,
            ],
        ]);

        return response()->json(['ok' => true, 'conversation_id' => $conversation->id, 'agent_name' => $matchedAgent->name]);
    }
}
