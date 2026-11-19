<?php

namespace App\Http\Controllers\Api\Client\Concerns;

use App\Models\Conversation;
use App\Models\Project;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Helper bersama controller widget: project aktif, visitor_uuid, kepemilikan percakapan. */
trait ResolvesVisitorConversation
{
    protected function project(Request $request): Project
    {
        return $request->attributes->get('project');
    }

    /** visitor_uuid dari header (widget baru) atau body/query (klien lama). */
    protected function visitorUuid(Request $request): ?string
    {
        $uuid = $request->header('X-Visitor-Uuid') ?: $request->input('visitor_uuid');

        return is_string($uuid) && $uuid !== '' ? $uuid : null;
    }

    /** Percakapan milik pengunjung pemanggil, atau null (→ 404). */
    protected function ownedConversation(Request $request, mixed $id): ?Conversation
    {
        return app(ConversationService::class)->findForVisitor($this->project($request), $id, $this->visitorUuid($request));
    }

    protected function conversationNotFound(): JsonResponse
    {
        return $this->fail('CONVERSATION_NOT_FOUND', 'Percakapan tidak ditemukan.', 404);
    }
}
