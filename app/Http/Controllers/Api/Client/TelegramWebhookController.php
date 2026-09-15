<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Project;
use App\Models\WidgetSetting;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected ConversationService $conversationService;

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
            $conversation = Conversation::where('telegram_topic_id', $topicId)->with('project')->first();
        }

        // Fallback jika bukan mode forum tapi reply ke bubble customer
        if (!$conversation && $replyTo) {
            $replyTopicId = $replyTo['message_thread_id'] ?? null;
            if ($replyTopicId) {
                $conversation = Conversation::where('telegram_topic_id', $replyTopicId)->with('project')->first();
            }
        }

        if (!$conversation) {
            return response()->json(['ok' => true, 'note' => 'no_matching_conversation']);
        }

        // 3. Ekstrak nama staf CS yang membalas dari Telegram
        $from = $msg['from'] ?? [];
        $firstName = $from['first_name'] ?? 'Staf';
        $lastName = $from['last_name'] ?? '';
        $senderName = trim("{$firstName} {$lastName}") ?: 'Staf CS';

        // 4. Update Conversation: Auto-yield bot dan pastikan status open
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

        // 5. Append message as agent
        $this->conversationService->appendMessage($conversation, [
            'sender_type'       => 'agent',
            'sender_name'       => $senderName,
            'content'           => $text,
            'client_message_id' => 'tg_' . ($msg['message_id'] ?? time()),
            'metadata'          => [
                'from_telegram'       => true,
                'telegram_message_id' => $msg['message_id'] ?? null,
                'telegram_user_id'    => $from['id'] ?? null,
            ],
        ]);

        return response()->json(['ok' => true, 'conversation_id' => $conversation->id]);
    }
}
