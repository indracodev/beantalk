<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\WidgetSetting;
use Illuminate\Support\Str;

class BotService
{
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Checks if the bot should process incoming messages for this conversation
     */
    public function shouldHandle(Conversation $conversation): bool
    {
        // 1. Cek status aktif di level percakapan (per-ticket toggle)
        if ($conversation->is_bot_active === false) {
            return false;
        }

        // 2. Cek status aktif di level website/project (global toggle)
        $project = $conversation->project;
        if (!$project) {
            return false;
        }

        $widgetSetting = $project->widgetSetting;
        if (!$widgetSetting || !$widgetSetting->bot_enabled) {
            return false;
        }

        return true;
    }

    /**
     * Processes an inbound visitor message and triggers automated bot response if rules match
     */
    public function processInboundMessage(Conversation $conversation, Message $inboundMessage): ?Message
    {
        if (!$this->shouldHandle($conversation)) {
            return null;
        }

        $project = $conversation->project;
        $widgetSetting = $project ? $project->widgetSetting : null;
        if (!$widgetSetting) {
            return null;
        }

        $botName = $widgetSetting->bot_name ?: 'BeanBot';
        $text = mb_strtolower(trim($inboundMessage->content));

        // 1. SMART HANDOFF: Cek apakah customer meminta berbicara dengan staf manusia / CS
        if ($this->isHandoffRequested($text)) {
            $conversation->update([
                'is_bot_active'  => false,
                'bot_handoff_at' => now(),
            ]);

            $handoffText = "Baik, saya telah mengalihkan percakapan ini ke staf Customer Service kami. Mohon tunggu sebentar, staf kami akan segera membalas pesan Anda.";

            $res = $this->conversationService->appendMessage($conversation, [
                'sender_type'       => 'bot',
                'sender_name'       => $botName,
                'content'           => $handoffText,
                'client_message_id' => 'bot_handoff_' . $inboundMessage->id . '_' . time(),
                'metadata'          => [
                    'is_bot'         => true,
                    'is_handoff'     => true,
                    'inbound_msg_id' => $inboundMessage->id,
                ],
            ]);

            return $res['message'];
        }

        // 2. KEYWORD & FAQ MATCHING (Rule-Based Engine)
        $rules = is_array($widgetSetting->bot_rules) ? $widgetSetting->bot_rules : [];
        foreach ($rules as $rule) {
            $keywords = [];
            if (!empty($rule['keywords'])) {
                if (is_array($rule['keywords'])) {
                    $keywords = $rule['keywords'];
                } elseif (is_string($rule['keywords'])) {
                    $keywords = array_map('trim', explode(',', $rule['keywords']));
                }
            }

            $responseTemplate = $rule['response'] ?? ($rule['response_text'] ?? null);
            if (empty($responseTemplate)) {
                continue;
            }

            foreach ($keywords as $kw) {
                $kwLower = mb_strtolower(trim($kw));
                if ($kwLower !== '' && Str::contains($text, $kwLower)) {
                    $res = $this->conversationService->appendMessage($conversation, [
                        'sender_type'       => 'bot',
                        'sender_name'       => $botName,
                        'content'           => $responseTemplate,
                        'client_message_id' => 'bot_rule_' . $inboundMessage->id . '_' . time(),
                        'metadata'          => [
                            'is_bot'            => true,
                            'matched_keyword'   => $kwLower,
                            'inbound_msg_id'    => $inboundMessage->id,
                        ],
                    ]);

                    return $res['message'];
                }
            }
        }

        // 3. FIRST INBOUND WELCOME MESSAGE (Jika pesan pertama kali masuk)
        $visitorMessageCount = Message::where('conversation_id', $conversation->id)
            ->where('sender_type', 'visitor')
            ->count();

        if ($visitorMessageCount <= 1 && !empty($widgetSetting->bot_welcome_message)) {
            $res = $this->conversationService->appendMessage($conversation, [
                'sender_type'       => 'bot',
                'sender_name'       => $botName,
                'content'           => $widgetSetting->bot_welcome_message,
                'client_message_id' => 'bot_welcome_' . $inboundMessage->id . '_' . time(),
                'metadata'          => [
                    'is_bot'         => true,
                    'is_welcome'     => true,
                    'inbound_msg_id' => $inboundMessage->id,
                ],
            ]);

            return $res['message'];
        }

        // 4. OFFLINE / FALLBACK AUTO-REPLY (Jika di luar jam online dan diset pesan offline)
        if (!$widgetSetting->is_online && !empty($widgetSetting->bot_offline_message)) {
            $res = $this->conversationService->appendMessage($conversation, [
                'sender_type'       => 'bot',
                'sender_name'       => $botName,
                'content'           => $widgetSetting->bot_offline_message,
                'client_message_id' => 'bot_offline_' . $inboundMessage->id . '_' . time(),
                'metadata'          => [
                    'is_bot'         => true,
                    'is_offline'     => true,
                    'inbound_msg_id' => $inboundMessage->id,
                ],
            ]);

            return $res['message'];
        }

        return null;
    }

    /**
     * Determines whether the visitor text requests handoff to human support
     */
    protected function isHandoffRequested(string $text): bool
    {
        $handoffKeywords = [
            'cs',
            'agent',
            'manusia',
            'operator',
            'admin',
            'bantuan cs',
            'staf',
            'staff',
            'bicara dengan orang',
            'bicara dengan cs',
            'hubungi admin',
            'human',
            'orang asli',
            'panggil cs',
            'bantuan agen',
        ];

        foreach ($handoffKeywords as $kw) {
            // Check exact whole word or exact substring
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $text) || Str::contains($text, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Toggles bot state for a specific conversation (manual override from CS Inbox)
     */
    public function toggleBot(Conversation $conversation, bool $active): Conversation
    {
        $conversation->update([
            'is_bot_active'  => $active,
            'bot_handoff_at' => $active ? null : now(),
        ]);

        return $conversation;
    }
}
