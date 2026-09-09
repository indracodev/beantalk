<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\MessageAttachment;
use App\Models\Project;
use App\Services\ConversationService;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Uploads a compressed photo/file from client
     * POST /api/v1/client/conversations/{id}/upload
     */
    public function upload(Request $request, $conversationId)
    {
        $request->validate([
            'file'              => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:10240', // Max 10MB
            'client_message_id' => 'nullable|string|max:64',
            'caption'           => 'nullable|string|max:1000',
            'original_size'     => 'nullable|integer',
            'compressed_size'   => 'nullable|integer',
        ]);

        /** @var Project $project */
        $project = $request->attributes->get('project');

        $conversation = Conversation::where('project_id', $project->id)
            ->where('id', $conversationId)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CONVERSATION_NOT_FOUND',
                    'message' => 'Percakapan tidak ditemukan untuk project ini.',
                ]
            ], 404);
        }

        $uploadedFile = $request->file('file');
        $fileName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();

        // Simpan file ke storage/app/public/attachments/{conversation_id}/
        $path = $uploadedFile->store("attachments/{$conversation->id}", 'public');

        // Buat record pesan
        $caption = $request->input('caption') ?: "Mengirim lampiran: {$fileName}";
        $result = $this->conversationService->appendMessage($conversation, [
            'sender_type'       => 'visitor',
            'sender_name'       => 'Pengunjung',
            'content'           => $caption,
            'client_message_id' => $request->input('client_message_id'),
            'content_type'      => 'image',
            'metadata'          => [
                'file_name'       => $fileName,
                'file_url'        => asset("storage/{$path}"),
                'original_size'   => $request->input('original_size', $fileSize),
                'compressed_size' => $request->input('compressed_size', $fileSize),
            ],
        ]);

        $message = $result['message'];

        // Simpan metadata attachment
        $attachment = MessageAttachment::create([
            'message_id'      => $message->id,
            'file_name'       => $fileName,
            'file_path'       => $path,
            'file_size'       => $fileSize,
            'mime_type'       => $mimeType,
            'original_size'   => $request->input('original_size'),
            'compressed_size' => $request->input('compressed_size', $fileSize),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message_id' => $message->id,
                'file_name'  => $fileName,
                'file_url'   => asset("storage/{$path}"),
                'file_size'  => $fileSize,
            ]
        ], 201);
    }
}
