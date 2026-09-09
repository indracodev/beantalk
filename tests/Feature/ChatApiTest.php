<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatApiTest extends TestCase
{
    /**
     * Test 1: Client Session Init without key fails (401)
     */
    public function testSessionInitRequiresProjectKey()
    {
        $response = $this->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response->assertStatus(401)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('error.code', 'MISSING_PROJECT_KEY');
    }

    /**
     * Test 2: Client Session Init with valid public key succeeds (200)
     */
    public function testSessionInitWithValidKey()
    {
        $response = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'page_url'     => 'https://supresso.myshopify.com/products/sumatra-capsule',
            'page_title'   => 'Supresso Sumatra Mandheling Capsule',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'visitor' => ['uuid'],
                         'conversation' => ['id', 'status'],
                         'project' => ['id', 'name'],
                         'widget' => ['primary_color', 'greeting_title', 'greeting_subtitle'],
                     ]
                 ]);
    }

    /**
     * Test 3: Polling messages via range scan
     */
    public function testPollMessagesRangeScan()
    {
        $response = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->getJson('/api/v1/client/conversations/1/messages?after_id=0');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'messages',
                         'last_id',
                     ]
                 ]);
    }

    /**
     * Test 4: Send message with idempotency
     */
    public function testSendMessageWithIdempotency()
    {
        $clientMsgId = 'test_msg_idempotent_' . uniqid();

        // Send 1st time
        $res1 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/1/messages', [
            'client_message_id' => $clientMsgId,
            'content'           => 'Apakah kemasan ini ramah lingkungan?',
            'content_type'      => 'text',
        ]);

        $res1->assertStatus(201)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.is_duplicate', false);

        $msgId = $res1->json('data.id');

        // Send 2nd time (Retry with same client_message_id)
        $res2 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/1/messages', [
            'client_message_id' => $clientMsgId,
            'content'           => 'Apakah kemasan ini ramah lingkungan?',
            'content_type'      => 'text',
        ]);

        $res2->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.is_duplicate', true)
             ->assertJsonPath('data.id', $msgId); // Same message returned, no duplicate created!
    }

    /**
     * Test 5: Admin lists inbox conversations
     */
    public function testAdminListConversations()
    {
        $response = $this->getJson('/api/v1/admin/conversations');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'conversations',
                         'pagination',
                     ]
                 ]);
    }

    /**
     * Test 6: Admin replies to conversation
     */
    public function testAdminReplyConversation()
    {
        $response = $this->postJson('/api/v1/admin/conversations/1/reply', [
            'content' => 'Tentu kak, seluruh kapsul Supresso 100% aluminium daur ulang.',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.sender_type', 'agent');
    }

    /**
     * Test 7: Public CDN JS endpoints are directly accessible
     */
    public function testPublicWidgetScriptsAreAccessible()
    {
        $this->get('/chat-widget.js')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');

        $this->get('/chat.js')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');

        $this->get('/widget.js')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    }
}
