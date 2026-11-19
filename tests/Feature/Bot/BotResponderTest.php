<?php

namespace Tests\Feature\Bot;

use App\Models\Conversation;
use App\Models\Message;

class BotResponderTest extends BotTestCase
{
    /**
     * Test 1: Bot does NOT respond when globally disabled
     */
    public function test_bot_does_not_respond_when_globally_disabled()
    {
        $this->widgetSetting->update(['bot_enabled' => false]);

        $visitorUuid = 'bot-test-visitor-disabled-'.uniqid();

        // 1. Send Message with visitor_uuid on conv 0
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Berapa ongkir ke Surabaya?',
            'client_message_id' => 'msg-bot-01-'.uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        // Ensure NO bot messages exist
        $botMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->count();

        $this->assertEquals(0, $botMessages);
    }

    /**
     * Test 2: Bot sends welcome message on first generic message
     */
    public function test_bot_sends_welcome_message_on_first_generic_message()
    {
        $this->widgetSetting->update(['bot_enabled' => true]);

        $visitorUuid = 'bot-test-visitor-welcome-'.uniqid();

        // Send First Message without keyword match
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Selamat pagi!',
            'client_message_id' => 'msg-bot-02-'.uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $botMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->get();

        $this->assertCount(1, $botMessages);
        $this->assertStringContainsString('Halo! Saya BeanBot', $botMessages[0]->content);
    }

    /**
     * Test 3: Bot answers with keyword FAQ rule
     */
    public function test_bot_answers_with_keyword_faq_rule()
    {
        $this->widgetSetting->update(['bot_enabled' => true]);

        $visitorUuid = 'bot-test-visitor-faq-'.uniqid();

        // Send Message matching keyword 'ongkir'
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Berapa ongkir pengiriman ke Bandung?',
            'client_message_id' => 'msg-bot-03-'.uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $botMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->get();

        $this->assertCount(1, $botMessages);
        $this->assertStringContainsString('Pengiriman kami menggunakan JNE', $botMessages[0]->content);
    }

    /**
     * Test 4: Bot smart handoff when visitor requests human CS
     */
    public function test_bot_smart_handoff_to_human_agent()
    {
        $this->widgetSetting->update(['bot_enabled' => true]);

        $visitorUuid = 'bot-test-visitor-handoff-'.uniqid();

        // Customer requests CS
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Saya mau bicara dengan CS manusia tolong',
            'client_message_id' => 'msg-bot-04-'.uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $conversation = Conversation::find($conversationId);

        $this->assertFalse((bool) $conversation->is_bot_active);
        $this->assertNotNull($conversation->bot_handoff_at);

        $lastBotMsg = Message::where('conversation_id', $conversation->id)
            ->where('sender_type', 'bot')
            ->latest('id')
            ->first();

        $this->assertNotNull($lastBotMsg);
        $this->assertStringContainsString('staf Customer Service', $lastBotMsg->content);
    }

    /**
     * Test 7: Bot respects bot_mode_query toggle
     */
    public function test_bot_respects_mode_query_toggle()
    {
        $this->widgetSetting->update([
            'bot_enabled' => true,
            'bot_mode_query' => false, // Query matching disabled
            'bot_mode_options' => true,
        ]);

        $visitorUuid = 'bot-test-visitor-noquery-'.uniqid();

        // Send loose query containing keyword 'ongkir'
        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Berapa tarif ongkir ke Jakarta?',
            'client_message_id' => 'msg-bot-noquery-'.uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        // Since mode_query is false, it should NOT match the 'ongkir' rule via loose substring,
        // it falls back to welcome message on first message
        $botMsg = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->first();

        $this->assertNotNull($botMsg);
        $this->assertEquals($this->widgetSetting->bot_welcome_message, $botMsg->content);
    }

    /**
     * Test 8: Bot strips options when bot_mode_options is disabled
     */
    public function test_bot_strips_options_when_mode_options_disabled()
    {
        $this->widgetSetting->update([
            'bot_enabled' => true,
            'bot_mode_query' => true,
            'bot_mode_options' => false, // Options disabled
            'bot_welcome_options' => [
                ['label' => 'Tombol Test', 'value' => 'test'],
            ],
        ]);

        $visitorUuid = 'bot-test-visitor-nooptions-'.uniqid();

        $msgRes = $this->withHeaders([
            'X-Project-Key' => $this->apiKey->public_key,
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $visitorUuid,
            'content' => 'Halo kak',
            'client_message_id' => 'msg-bot-noopt-'.uniqid(),
        ]);

        $msgRes->assertStatus(201);
        $conversationId = $msgRes->json('data.conversation_id');

        $botMsg = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'bot')
            ->first();

        $this->assertNotNull($botMsg);
        $this->assertNull($botMsg->metadata['options'] ?? null);
    }
}
