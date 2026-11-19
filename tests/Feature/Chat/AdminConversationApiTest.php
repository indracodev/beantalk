<?php

namespace Tests\Feature\Chat;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
use App\Models\Visitor;

class AdminConversationApiTest extends ChatTestCase
{
    /**
     * Test 5: Admin lists inbox conversations
     */
    public function test_admin_list_conversations()
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->getJson('/api/v1/admin/conversations');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'conversations',
                    'pagination',
                ],
            ]);
    }

    /**
     * Test 6: Admin replies to conversation
     */
    public function test_admin_reply_conversation()
    {
        $admin = User::first();
        $conv = Conversation::where('tenant_id', $admin->tenant_id)->first();
        if (! $conv) {
            $project = Project::where('tenant_id', $admin->tenant_id)->first() ?? Project::first();
            $visitor = Visitor::first();
            $conv = Conversation::create([
                'tenant_id' => $admin->tenant_id,
                'project_id' => $project->id,
                'visitor_id' => $visitor->id,
                'channel_type' => 'widget',
                'status' => 'open',
            ]);
        }

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/conversations/{$conv->id}/reply", [
            'content' => 'Tentu kak, seluruh kapsul Supresso 100% aluminium daur ulang.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sender_type', 'agent');
    }

    /**
     * Test 6b: Unauthenticated request to admin conversations is rejected
     */
    public function test_unauthenticated_admin_access_rejected()
    {
        $response = $this->getJson('/api/v1/admin/conversations');

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    /**
     * Test 13: Admin can resolve ticket with closing greeting template
     */
    public function test_admin_can_resolve_conversation_with_closing_message_template()
    {
        $uuid = 'visitor-admin-resolve-'.uniqid();

        // Buat percakapan
        $res = $this->withHeaders([
            'X-Project-Key' => 'pk_live_supresso_8819',
        ])->postJson('/api/v1/client/conversations/0/messages', [
            'visitor_uuid' => $uuid,
            'sender_name' => 'Ibu Siska',
            'content' => 'Tanya informasi garansi produk',
        ]);
        $convId = $res->json('data.conversation_id');

        $user = User::first();
        $conv = Conversation::find($convId);
        $conv->update(['tenant_id' => $user->tenant_id]);

        $closingText = 'Terima kasih telah menghubungi kami. Semoga harimu menyenangkan! Jika ada pertanyaan lain, jangan ragu untuk chat kembali.';

        $statusRes = $this->actingAs($user)
            ->putJson("/admin/inbox/{$convId}/status", [
                'status' => 'closed',
                'closing_message' => $closingText,
            ]);

        $statusRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'closed')
            ->assertJsonPath('data.closing_message.content', $closingText);

        $conv = Conversation::find($convId);
        $this->assertEquals('closed', $conv->status);

        // Pastikan pesan penutup tersimpan di database sebagai pesan dari agen
        $lastMsg = $conv->latestMessage;
        $this->assertEquals('agent', $lastMsg->sender_type);
        $this->assertEquals($closingText, $lastMsg->content);
    }
}
