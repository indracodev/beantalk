<?php

namespace Tests\Feature\Chat;

use App\Models\Conversation;

class WidgetMessagingTest extends ChatTestCase
{
    /**
     * Test 3: Polling messages via range scan
     */
    public function test_poll_messages_range_scan()
    {
        $response = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
            'X-Visitor-Uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ])->getJson('/api/v1/client/conversations/1/messages?after_id=0');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'messages',
                    'last_id',
                ],
            ]);
    }

    /**
     * Test 4: Send message with idempotency
     */
    public function test_send_message_with_idempotency()
    {
        $clientMsgId = 'test_msg_idempotent_'.uniqid();

        // Send 1st time
        $res1 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
            'X-Visitor-Uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ])->postJson('/api/v1/client/conversations/1/messages', [
            'client_message_id' => $clientMsgId,
            'content' => 'Apakah kemasan ini ramah lingkungan?',
            'content_type' => 'text',
        ]);

        $res1->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_duplicate', false);

        $msgId = $res1->json('data.id');

        // Send 2nd time (Retry with same client_message_id)
        $res2 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
            'X-Visitor-Uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ])->postJson('/api/v1/client/conversations/1/messages', [
            'client_message_id' => $clientMsgId,
            'content' => 'Apakah kemasan ini ramah lingkungan?',
            'content_type' => 'text',
        ]);

        $res2->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_duplicate', true)
            ->assertJsonPath('data.id', $msgId); // Same message returned, no duplicate created!
    }

    /**
     * Test 11: First customer message creates conversation lazily
     */
    public function test_first_message_creates_conversation_lazily()
    {
        $uniqueUuid = 'chatting-visitor-'.uniqid();

        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uniqueUuid,
            'sender_name' => 'Ibu Maya',
            'content' => 'Halo kak, apakah ada promo diskon hari ini?',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sender_name', 'Ibu Maya')
            ->assertJsonPath('data.content', 'Halo kak, apakah ada promo diskon hari ini?');

        $convId = $res->json('data.conversation_id');
        $this->assertNotNull($convId);
        $this->assertGreaterThan(0, $convId);

        $conv = Conversation::find($convId);
        $this->assertNotNull($conv);
        $this->assertEquals('Halo kak, apakah ada promo diskon hari ini?', $conv->last_message_preview);

        // Next page load / session init must return this active conversation and messages directly
        $initAfterChat = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/session/init', [
            'visitor_uuid' => $uniqueUuid,
        ]);
        $initAfterChat->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.conversation.id', $convId)
            ->assertJsonPath('data.conversation.status', 'open');
        $this->assertNotEmpty($initAfterChat->json('data.conversation.messages'));
    }

    /**
     * Test 12: Customer can resolve ticket, view ticket history, and start a fresh ticket
     */
    public function test_customer_can_resolve_conversation_and_start_new_ticket()
    {
        $uuid = 'visitor-multi-ticket-'.uniqid();

        // 1. Kirim pesan pertama untuk membuat tiket #1
        $res1 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uuid,
            'sender_name' => 'Pelanggan 1',
            'content' => 'Pertanyaan tiket pertama',
        ]);
        $res1->assertStatus(201);
        $conv1Id = $res1->json('data.conversation_id');

        // 2. Pelanggan menyelesaikan tiket #1
        $resolveRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson("/api/v1/client/conversations/{$conv1Id}/resolve", [
            'visitor_uuid' => $uuid,
        ]);
        $resolveRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'closed');

        $this->assertEquals('closed', Conversation::find($conv1Id)->status);

        // 3. Mengirim pesan ke tiket yang sudah closed harus ditolak
        $closedRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson("/api/v1/client/conversations/{$conv1Id}/messages", [
            'visitor_uuid' => $uuid,
            'content' => 'Pesan di tiket yang sudah tutup',
        ]);
        $closedRes->assertStatus(422)
            ->assertJsonPath('error.code', 'CONVERSATION_CLOSED');

        // 4. Pelanggan membuat tiket baru (id: 0)
        $res2 = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uuid,
            'sender_name' => 'Pelanggan 1',
            'content' => 'Pertanyaan tiket kedua yang fresh',
        ]);
        $res2->assertStatus(201);
        $conv2Id = $res2->json('data.conversation_id');

        $this->assertNotEquals($conv1Id, $conv2Id, 'Harus membuat tiket percakapan baru yang terpisah.');
        $this->assertEquals('open', Conversation::find($conv2Id)->status);

        // 5. Cek daftar riwayat tiket pelanggan
        $historyRes = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->getJson("/api/v1/client/conversations?visitor_uuid={$uuid}");

        $historyRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $tickets = $historyRes->json('data.conversations');
        $this->assertCount(2, $tickets);
        $statuses = array_column($tickets, 'status');
        $this->assertContains('closed', $statuses);
        $this->assertContains('open', $statuses);
    }
}
