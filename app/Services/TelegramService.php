<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\WidgetSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Integrasi Telegram: notifikasi tiket baru, topik forum per percakapan (sinkron 2 arah), uji koneksi & webhook.
 * Semua kegagalan jaringan ditelan + dicatat agar alur chat utama tidak pernah terganggu.
 */
class TelegramService
{
    private const API = 'https://api.telegram.org/bot';

    /** Secret token webhook per bot: HMAC token bot dengan APP_KEY (dikirim balik di header X-Telegram-Bot-Api-Secret-Token). */
    public static function webhookSecret(string $botToken): string
    {
        return substr(hash_hmac('sha256', 'telegram-webhook|'.trim($botToken), (string) config('app.key')), 0, 64);
    }

    /** Notifikasi "pesan masuk baru" — sekali per percakapan. */
    public function sendNewTicketAlert(Conversation $conversation, Message $message): ?array
    {
        $ws = $this->settings($conversation);
        if (! $ws?->telegram_notifications_enabled || $conversation->telegram_notif_sent) {
            return null;
        }

        $project = $conversation->project;
        $text = '🔔 <b>['.e($project->name).'] Pesan Masuk Baru!</b>'."\n\n"
            .'👤 <b>Pengunjung:</b> '.e($conversation->visitor?->display_name ?? 'Pengunjung Tamu')."\n"
            .'📍 <b>Halaman:</b> '.e($conversation->page_title ?: ($conversation->page_url ?: 'Live Storefront'))."\n"
            .'💬 <b>Pesan:</b> <i>"'.e(mb_substr(strip_tags($message->content), 0, 200)).'"</i>'."\n"
            .'⏰ <b>Waktu:</b> '.now('Asia/Jakarta')->format('H:i:s \W\I\B')."\n";

        $res = $this->call($ws, 'sendMessage', [
            'chat_id' => $ws->telegram_chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '💬 Buka di Admin Inbox', 'url' => url("/admin/inbox/{$conversation->id}")]]]]),
        ], 4);

        if ($res?->successful()) {
            $conversation->update(['telegram_notif_sent' => true]);

            return $res->json();
        }

        return null;
    }

    /** ID topik forum percakapan (dibuat bila belum ada, beserta pesan pembuka). */
    public function getOrCreateForumTopic(Conversation $conversation): ?int
    {
        if ($conversation->telegram_topic_id) {
            return (int) $conversation->telegram_topic_id;
        }

        $ws = $this->settings($conversation);
        if (! $ws?->telegram_topic_mode_enabled) {
            return null;
        }

        $project = $conversation->project;
        $name = $conversation->visitor?->name ?: 'Tamu';
        $code = $conversation->visitor?->customer_code ?: 'CUS';
        $topicName = mb_substr('['.mb_substr($project->name, 0, 20).'] '.mb_substr($name, 0, 30)." ({$code})", 0, 120);

        $topicId = $this->call($ws, 'createForumTopic', ['chat_id' => $ws->telegram_chat_id, 'name' => $topicName, 'icon_color' => 7322096], 4)
            ?->json('result.message_thread_id');

        if (! $topicId) {
            return null;
        }

        $conversation->update(['telegram_topic_id' => $topicId]);
        $this->call($ws, 'sendMessage', [
            'chat_id' => $ws->telegram_chat_id,
            'message_thread_id' => $topicId,
            'parse_mode' => 'HTML',
            'text' => "👋 <b>Topik Obrolan Dimulai</b>\n👤 <b>Customer:</b> ".e($name)." (<code>{$code}</code>)\n🌐 <b>Website:</b> ".e($project->name)
                ."\n\n<i>Ketik balasan Anda di topik ini untuk langsung membalas ke widget website customer.</i>",
        ], 3);

        return (int) $topicId;
    }

    /** Teruskan pesan pengunjung ke topik forum. */
    public function forwardVisitorMessage(Conversation $conversation, Message $message): ?array
    {
        $ws = $this->settings($conversation);
        if (! $ws?->telegram_topic_mode_enabled || ! ($topicId = $this->getOrCreateForumTopic($conversation))) {
            return null;
        }

        $sender = $message->sender_name ?: ($conversation->visitor?->display_name ?? 'Pengunjung');

        return $this->postToTopic($ws, $topicId, '👤 <b>'.e($sender).":</b>\n".e($message->content));
    }

    /** Teruskan balasan agen dari web inbox ke topik (riwayat tetap sinkron). */
    public function forwardAgentReply(Conversation $conversation, Message $message): ?array
    {
        $ws = $conversation->telegram_topic_id ? $this->settings($conversation) : null;
        if (! $ws?->telegram_topic_mode_enabled) {
            return null;
        }

        return $this->postToTopic($ws, (int) $conversation->telegram_topic_id, '👨‍💼 <b>'.e($message->sender_name ?: 'Staf CS')." (Web Admin):</b>\n".e($message->content));
    }

    public function closeForumTopic(Conversation $conversation): bool
    {
        return $this->topicAction($conversation, 'closeForumTopic');
    }

    public function reopenForumTopic(Conversation $conversation): bool
    {
        return $this->topicAction($conversation, 'reopenForumTopic');
    }

    /** Uji token (getMe) lalu kirim pesan uji ke chat/grup. */
    public function testConnection(string $botToken, string $chatId): array
    {
        [$botToken, $chatId] = [trim($botToken), trim($chatId)];
        if ($botToken === '' || $chatId === '') {
            return $this->result(false, 'Bot Token dan Chat ID wajib diisi untuk melakukan pengujian.');
        }

        try {
            $me = $this->http(5)->get(self::API."{$botToken}/getMe");
            if (! $me->successful()) {
                return $this->result(false, 'Token Bot tidak valid atau tidak ditemukan di server Telegram (@BotFather).');
            }
            $bot = $me->json('result.username', 'Bot');

            $sent = $this->http(5)->post(self::API."{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => "⚡ <b>Tes Koneksi BeanTalk Berhasil!</b>\n\n🤖 <b>Bot:</b> @".e($bot)
                    ."\n📅 <b>Waktu:</b> ".now('Asia/Jakarta')->format('d M Y H:i:s \W\I\B')."\n✅ Bot berhasil terhubung ke server BeanTalk.",
            ]);

            if (! $sent->successful()) {
                $err = $sent->json('description', 'Gagal mengirim pesan ke Chat/Group ID.');

                return $this->result(false, "Bot valid (@{$bot}), namun gagal kirim ke Chat ID: {$err}. Pastikan Bot sudah diundang/masuk ke dalam grup tersebut.");
            }

            return $this->result(true, "Koneksi sukses! Pesan uji coba berhasil dikirim via @{$bot}.", ['bot_username' => $bot]);
        } catch (Throwable $e) {
            return $this->result(false, 'Terjadi kesalahan koneksi ke Telegram: '.$e->getMessage());
        }
    }

    /** Daftarkan webhook (wajib HTTPS) beserta secret token. */
    public function setWebhook(string $botToken, string $webhookUrl): array
    {
        [$botToken, $webhookUrl] = [trim($botToken), trim($webhookUrl)];
        if ($botToken === '') {
            return $this->result(false, 'Telegram Bot Token wajib diisi terlebih dahulu.');
        }
        if (! str_starts_with(strtolower($webhookUrl), 'https://')) {
            return $this->result(false, 'Telegram mewajibkan Webhook URL menggunakan protokol HTTPS aman (SSL aktif). Jika di lokal, gunakan tunnel seperti Ngrok.');
        }

        try {
            $res = $this->http(8)->post(self::API."{$botToken}/setWebhook", [
                'url' => $webhookUrl,
                'drop_pending_updates' => false,
                'secret_token' => self::webhookSecret($botToken),
            ]);

            return $res->successful() && $res->json('ok')
                ? $this->result(true, 'Webhook berhasil didaftarkan ke server Telegram! Pesan balasan dari Telegram sekarang otomatis tersinkron ke BeanTalk.', ['description' => $res->json('description', 'Webhook was set')])
                : $this->result(false, 'Telegram menolak webhook: '.$res->json('description', 'Gagal mendaftarkan webhook ke Telegram.'));
        } catch (Throwable $e) {
            return $this->result(false, 'Terjadi kesalahan saat menghubungkan ke Telegram: '.$e->getMessage());
        }
    }

    /** Setting widget project bila kredensial Telegram lengkap, selain itu null. */
    private function settings(Conversation $conversation): ?WidgetSetting
    {
        $ws = $conversation->project?->widgetSetting;

        return $ws && $ws->telegram_bot_token && $ws->telegram_chat_id ? $ws : null;
    }

    private function postToTopic(WidgetSetting $ws, int $topicId, string $html): ?array
    {
        $res = $this->call($ws, 'sendMessage', ['chat_id' => $ws->telegram_chat_id, 'message_thread_id' => $topicId, 'text' => $html, 'parse_mode' => 'HTML'], 4);

        return $res?->successful() ? $res->json() : null;
    }

    private function topicAction(Conversation $conversation, string $method): bool
    {
        $ws = $conversation->telegram_topic_id ? $this->settings($conversation) : null;

        return (bool) $ws && (bool) $this->call($ws, $method, ['chat_id' => $ws->telegram_chat_id, 'message_thread_id' => $conversation->telegram_topic_id], 3)?->successful();
    }

    /** Panggil Bot API; error jaringan dicatat dan dikembalikan sebagai null. */
    private function call(WidgetSetting $ws, string $method, array $payload, int $timeout): ?Response
    {
        try {
            $res = $this->http($timeout)->post(self::API."{$ws->telegram_bot_token}/{$method}", $payload);
            if (! $res->successful()) {
                Log::warning("[TelegramService] {$method} gagal", ['body' => $res->body()]);
            }

            return $res;
        } catch (Throwable $e) {
            Log::error("[TelegramService] {$method}: ".$e->getMessage());

            return null;
        }
    }

    /** Verifikasi SSL hanya dilonggarkan di luar production (cURL error 60 di Laragon Windows). */
    private function http(int $timeout): PendingRequest
    {
        return Http::timeout($timeout)->withOptions(['verify' => app()->isProduction()]);
    }

    private function result(bool $success, string $message, array $extra = []): array
    {
        return ['success' => $success, 'message' => $message] + $extra;
    }
}
