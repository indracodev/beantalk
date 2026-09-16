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

        // 2. KEYWORD & FAQ MATCHING (Rule-Based Engine with Longest-Match Specificity Priority)
        $rules = is_array($widgetSetting->bot_rules) ? $widgetSetting->bot_rules : [];
        $bestMatch = null;
        $longestMatchLen = 0;

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
                if ($kwLower === '') {
                    continue;
                }

                $isMatch = false;
                if (mb_strlen($kwLower) <= 4 || is_numeric($kwLower)) {
                    if ($text === $kwLower || preg_match('/(^|\s|[.,!?])' . preg_quote($kwLower, '/') . '($|\s|[.,!?])/iu', $text)) {
                        $isMatch = true;
                    }
                } else {
                    if (Str::contains($text, $kwLower)) {
                        $isMatch = true;
                    }
                }

                if ($isMatch) {
                    $matchWeight = mb_strlen($kwLower);
                    // Prioritize exact full text match highest
                    if ($text === $kwLower) {
                        $matchWeight += 1000;
                    }

                    if ($matchWeight > $longestMatchLen) {
                        $longestMatchLen = $matchWeight;
                        $bestMatch = [
                            'response' => $responseTemplate,
                            'keyword'  => $kwLower,
                            'options'  => $rule['options'] ?? null,
                        ];
                    }
                }
            }
        }

        if ($bestMatch) {
            $res = $this->conversationService->appendMessage($conversation, [
                'sender_type'       => 'bot',
                'sender_name'       => $botName,
                'content'           => $bestMatch['response'],
                'client_message_id' => 'bot_rule_' . $inboundMessage->id . '_' . time(),
                'metadata'          => [
                    'is_bot'          => true,
                    'matched_keyword' => $bestMatch['keyword'],
                    'inbound_msg_id'  => $inboundMessage->id,
                    'options'         => $bestMatch['options'] ?? null,
                ],
            ]);

            return $res['message'];
        }

        // 3. FIRST INBOUND WELCOME MESSAGE (Jika tiket obrolan ini belum pernah mendapat sambutan bot)
        $hasBotReplied = Message::where('conversation_id', $conversation->id)
            ->where('sender_type', 'bot')
            ->exists();

        if (!$hasBotReplied && !empty($widgetSetting->bot_welcome_message)) {
            // Find root menu options if available
            $welcomeOptions = null;
            if (!empty($widgetSetting->bot_rules) && is_array($widgetSetting->bot_rules)) {
                foreach ($widgetSetting->bot_rules as $r) {
                    if (($r['name'] ?? '') === 'Menu Utama' && !empty($r['options'])) {
                        $welcomeOptions = $r['options'];
                        break;
                    }
                }
            }

            $res = $this->conversationService->appendMessage($conversation, [
                'sender_type'       => 'bot',
                'sender_name'       => $botName,
                'content'           => $widgetSetting->bot_welcome_message,
                'client_message_id' => 'bot_welcome_' . $inboundMessage->id . '_' . time(),
                'metadata'          => [
                    'is_bot'         => true,
                    'is_welcome'     => true,
                    'inbound_msg_id' => $inboundMessage->id,
                    'options'        => $welcomeOptions,
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
            'ya',
            'iya',
            'yes',
        ];

        foreach ($handoffKeywords as $kw) {
            $kwLower = mb_strtolower(trim($kw));
            if ($kwLower === '') {
                continue;
            }

            // Word boundary check for short words (<= 3 chars, e.g. 'ya', 'cs')
            if (mb_strlen($kwLower) <= 3) {
                if ($text === $kwLower || preg_match('/(^|\s|[.,!?])' . preg_quote($kwLower, '/') . '($|\s|[.,!?])/iu', $text)) {
                    return true;
                }
            } else {
                if (Str::contains($text, $kwLower) || preg_match('/(^|\s|[.,!?])' . preg_quote($kwLower, '/') . '($|\s|[.,!?])/iu', $text)) {
                    return true;
                }
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
