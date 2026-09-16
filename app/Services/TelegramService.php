<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\WidgetSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Base URL for Telegram Bot API
     *
     * @var string
     */
    protected $apiBase = 'https://api.telegram.org/bot';

    /**
     * HTTP client with safe SSL verification for local development (avoids Windows Laragon cURL error 60)
     */
    protected function http(int $timeout = 5)
    {
        return Http::timeout($timeout)->withOptions([
            'verify' => app()->environment('production'),
        ]);
    }

    /**
     * Send instant alert notification to Telegram (1x only per new conversation)
     */
    public function sendNewTicketAlert(Conversation $conversation, Message $message): ?array
    {
        try {
            $project = $conversation->project;
            if (!$project) return null;

            $setting = $project->widgetSetting;
            if (!$this->isConfigured($setting) || !$setting->telegram_notifications_enabled) {
                return null;
            }

            // Aturan 1-Kali: Jika sudah pernah dikirimkan notif tiket baru untuk percakapan ini, lewati
            if ($conversation->telegram_notif_sent) {
                return null;
            }

            $token = $setting->telegram_bot_token;
            $chatId = $setting->telegram_chat_id;
            $visitorName = $conversation->visitor ? $conversation->visitor->display_name : 'Pengunjung Tamu';
            $pageInfo = $conversation->page_title ?: ($conversation->page_url ?: 'Live Storefront');
            $snippet = mb_substr(strip_tags($message->content), 0, 200);
            $timeWib = now()->timezone('Asia/Jakarta')->format('H:i:s \W\I\B');

            $inboxUrl = url('/admin/inbox/' . $conversation->id);

            $text = "🔔 <b>[" . htmlspecialchars($project->name) . "] Pesan Masuk Baru!</b>\n\n"
                  . "👤 <b>Pengunjung:</b> " . htmlspecialchars($visitorName) . "\n"
                  . "📍 <b>Halaman:</b> " . htmlspecialchars($pageInfo) . "\n"
                  . "💬 <b>Pesan:</b> <i>\"" . htmlspecialchars($snippet) . "\"</i>\n"
                  . "⏰ <b>Waktu:</b> " . $timeWib . "\n";

            $inlineKeyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '💬 Buka di Admin Inbox', 'url' => $inboxUrl],
                    ]
                ]
            ];

            $response = $this->http(4)->post("{$this->apiBase}{$token}/sendMessage", [
                'chat_id'                  => $chatId,
                'text'                     => $text,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
                'reply_markup'             => json_encode($inlineKeyboard),
            ]);

            if ($response->successful()) {
                $conversation->update(['telegram_notif_sent' => true]);
                return $response->json();
            } else {
                Log::warning('[TelegramService] Send alert failed:', ['body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('[TelegramService] Exception on sendNewTicketAlert: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Gets or creates a Forum Topic thread for this conversation
     */
    public function getOrCreateForumTopic(Conversation $conversation): ?int
    {
        try {
            if ($conversation->telegram_topic_id) {
                return (int) $conversation->telegram_topic_id;
            }

            $project = $conversation->project;
            if (!$project) return null;

            $setting = $project->widgetSetting;
            if (!$this->isConfigured($setting) || !$setting->telegram_topic_mode_enabled) {
                return null;
            }

            $token = $setting->telegram_bot_token;
            $chatId = $setting->telegram_chat_id;

            $visitorName = $conversation->visitor ? ($conversation->visitor->name ?: 'Tamu') : 'Tamu';
            $custCode = $conversation->visitor ? ($conversation->visitor->customer_code ?: 'CUS') : 'CUS';
            
            // Format Nama Topik: [NamaWebsite] Nama Customer (Kode Tiket)
            $topicName = '[' . mb_substr($project->name, 0, 20) . '] ' . mb_substr($visitorName, 0, 30) . ' (' . $custCode . ')';
            $topicName = mb_substr($topicName, 0, 120);

            $response = $this->http(4)->post("{$this->apiBase}{$token}/createForumTopic", [
                'chat_id'          => $chatId,
                'name'             => $topicName,
                'icon_color'       => 7322096, // Emerald / Blue accent
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $topicId = $data['result']['message_thread_id'] ?? null;
                if ($topicId) {
                    $conversation->update(['telegram_topic_id' => $topicId]);

                    // Kirim Header Pesan Sambutan di dalam Topik baru
                    $introText = "👋 <b>Topik Obrolan Dimulai</b>\n"
                               . "👤 <b>Customer:</b> " . htmlspecialchars($visitorName) . " (<code>{$custCode}</code>)\n"
                               . "🌐 <b>Website:</b> " . htmlspecialchars($project->name) . "\n\n"
                               . "<i>Ketik balasan Anda di topik ini untuk langsung membalas ke widget website customer.</i>";

                    $this->http(3)->post("{$this->apiBase}{$token}/sendMessage", [
                        'chat_id'           => $chatId,
                        'message_thread_id' => $topicId,
                        'text'              => $introText,
                        'parse_mode'        => 'HTML',
                    ]);

                    return (int) $topicId;
                }
            } else {
                Log::warning('[TelegramService] Create forum topic failed (Supergroup Topics mode might need to be enabled in Telegram):', ['body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('[TelegramService] Exception on getOrCreateForumTopic: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Forwards an incoming visitor message to the corresponding Forum Topic thread
     */
    public function forwardVisitorMessage(Conversation $conversation, Message $message): ?array
    {
        try {
            $project = $conversation->project;
            if (!$project) return null;

            $setting = $project->widgetSetting;
            if (!$this->isConfigured($setting) || !$setting->telegram_topic_mode_enabled) {
                return null;
            }

            $topicId = $this->getOrCreateForumTopic($conversation);
            if (!$topicId) return null;

            $token = $setting->telegram_bot_token;
            $chatId = $setting->telegram_chat_id;
            $visitorName = $message->sender_name ?: ($conversation->visitor ? $conversation->visitor->display_name : 'Pengunjung');

            $text = "👤 <b>" . htmlspecialchars($visitorName) . ":</b>\n" . htmlspecialchars($message->content);

            $response = $this->http(4)->post("{$this->apiBase}{$token}/sendMessage", [
                'chat_id'           => $chatId,
                'message_thread_id' => $topicId,
                'text'              => $text,
                'parse_mode'        => 'HTML',
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('[TelegramService] Exception on forwardVisitorMessage: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Forwards an agent reply sent from Web Inbox to the Telegram Topic thread (to keep history synced)
     */
    public function forwardAgentReply(Conversation $conversation, Message $message): ?array
    {
        try {
            if (!$conversation->telegram_topic_id) return null;

            $project = $conversation->project;
            if (!$project) return null;

            $setting = $project->widgetSetting;
            if (!$this->isConfigured($setting) || !$setting->telegram_topic_mode_enabled) {
                return null;
            }

            $token = $setting->telegram_bot_token;
            $chatId = $setting->telegram_chat_id;
            $agentName = $message->sender_name ?: 'Staf CS';

            $text = "👨‍💼 <b>" . htmlspecialchars($agentName) . " (Web Admin):</b>\n" . htmlspecialchars($message->content);

            $response = $this->http(4)->post("{$this->apiBase}{$token}/sendMessage", [
                'chat_id'           => $chatId,
                'message_thread_id' => $conversation->telegram_topic_id,
                'text'              => $text,
                'parse_mode'        => 'HTML',
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('[TelegramService] Exception on forwardAgentReply: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Closes the Telegram Forum Topic thread when ticket is resolved
     */
    public function closeForumTopic(Conversation $conversation): bool
    {
        try {
            if (!$conversation->telegram_topic_id) return false;

            $project = $conversation->project;
            if (!$project) return false;

            $setting = $project->widgetSetting;
            if (!$this->isConfigured($setting)) return false;

            $token = $setting->telegram_bot_token;
            $chatId = $setting->telegram_chat_id;

            $response = $this->http(3)->post("{$this->apiBase}{$token}/closeForumTopic", [
                'chat_id'           => $chatId,
                'message_thread_id' => $conversation->telegram_topic_id,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Reopens a closed Telegram Forum Topic thread if customer chats again
     */
    public function reopenForumTopic(Conversation $conversation): bool
    {
        try {
            if (!$conversation->telegram_topic_id) return false;

            $project = $conversation->project;
            if (!$project) return false;

            $setting = $project->widgetSetting;
            if (!$this->isConfigured($setting)) return false;

            $token = $setting->telegram_bot_token;
            $chatId = $setting->telegram_chat_id;

            $response = $this->http(3)->post("{$this->apiBase}{$token}/reopenForumTopic", [
                'chat_id'           => $chatId,
                'message_thread_id' => $conversation->telegram_topic_id,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Tests connection to Telegram Bot API and sends a test ping message
     */
    public function testConnection(string $botToken, string $chatId): array
    {
        $botToken = trim($botToken);
        $chatId = trim($chatId);

        if (empty($botToken) || empty($chatId)) {
            return [
                'success' => false,
                'message' => 'Bot Token dan Chat ID wajib diisi untuk melakukan pengujian.',
            ];
        }

        try {
            // 1. Get Me (Cek Validitas Token)
            $meRes = $this->http(5)->get("{$this->apiBase}{$botToken}/getMe");
            if (!$meRes->successful()) {
                return [
                    'success' => false,
                    'message' => 'Token Bot tidak valid atau tidak ditemukan di server Telegram (@BotFather).',
                ];
            }

            $botData = $meRes->json()['result'] ?? [];
            $botUsername = $botData['username'] ?? 'Bot';

            // 2. Kirim Pesan Uji Coba ke Chat / Group ID
            $testMsg = "⚡ <b>Tes Koneksi BeanTalk Berhasil!</b>\n\n"
                     . "🤖 <b>Bot:</b> @" . htmlspecialchars($botUsername) . "\n"
                     . "📅 <b>Waktu:</b> " . now()->timezone('Asia/Jakarta')->format('d M Y H:i:s \W\I\B') . "\n"
                     . "✅ Bot berhasil terhubung ke server BeanTalk.";

            $msgRes = $this->http(5)->post("{$this->apiBase}{$botToken}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $testMsg,
                'parse_mode' => 'HTML',
            ]);

            if (!$msgRes->successful()) {
                $err = $msgRes->json()['description'] ?? 'Gagal mengirim pesan ke Chat/Group ID.';
                return [
                    'success' => false,
                    'message' => "Bot valid (@{$botUsername}), namun gagal kirim ke Chat ID: {$err}. Pastikan Bot sudah diundang/masuk ke dalam grup tersebut.",
                ];
            }

            return [
                'success'      => true,
                'message'      => "Koneksi sukses! Pesan uji coba berhasil dikirim via @{$botUsername}.",
                'bot_username' => $botUsername,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke Telegram: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Registers the Webhook URL directly to Telegram Bot API
     */
    public function setWebhook(string $botToken, string $webhookUrl): array
    {
        $botToken = trim($botToken);
        $webhookUrl = trim($webhookUrl);

        if (empty($botToken)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token wajib diisi terlebih dahulu.',
            ];
        }

        if (empty($webhookUrl)) {
            return [
                'success' => false,
                'message' => 'Webhook URL wajib diisi.',
            ];
        }

        if (!str_starts_with(strtolower($webhookUrl), 'https://')) {
            return [
                'success' => false,
                'message' => 'Telegram mewajibkan Webhook URL menggunakan protokol HTTPS aman (SSL aktif). Jika di lokal, gunakan tunnel seperti Ngrok.',
            ];
        }

        try {
            $response = $this->http(8)->post("{$this->apiBase}{$botToken}/setWebhook", [
                'url'                  => $webhookUrl,
                'drop_pending_updates' => false,
            ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['ok'])) {
                return [
                    'success'     => true,
                    'message'     => 'Webhook berhasil didaftarkan ke server Telegram! Pesan balasan dari Telegram sekarang otomatis tersinkron ke BeanTalk.',
                    'description' => $data['description'] ?? 'Webhook was set',
                ];
            } else {
                $err = $data['description'] ?? 'Gagal mendaftarkan webhook ke Telegram.';
                return [
                    'success' => false,
                    'message' => "Telegram menolak webhook: {$err}",
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke Telegram: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper to verify if settings have required Telegram credentials
     */
    protected function isConfigured(?WidgetSetting $setting): bool
    {
        return $setting && !empty($setting->telegram_bot_token) && !empty($setting->telegram_chat_id);
    }
}
