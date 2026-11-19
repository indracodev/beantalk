<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\Client\Concerns\ResolvesVisitorConversation;
use App\Http\Controllers\Controller;
use App\Models\MessageAttachment;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    use ResolvesVisitorConversation;

    /** POST /api/v1/client/conversations/{id}/upload — lampiran foto terkompresi / PDF. */
    public function upload(Request $request, string $id, ConversationService $conversations): JsonResponse
    {
        // Kepemilikan dicek sebelum validasi berkas.
        $conversation = $this->ownedConversation($request, $id);
        if (! $conversation) {
            return $this->conversationNotFound();
        }

        $data = $request->validate([
            // mimes + mimetypes: ekstensi DAN isi berkas harus cocok (cegah polyglot).
            'file' => 'required|file|max:10240|mimes:jpeg,png,jpg,webp,pdf|mimetypes:image/jpeg,image/png,image/webp,application/pdf',
            'client_message_id' => 'nullable|string|max:64',
            'caption' => 'nullable|string|max:1000',
            'original_size' => 'nullable|integer|min:0',
            'compressed_size' => 'nullable|integer|min:0',
        ]);

        $file = $request->file('file');
        $fileName = mb_substr(strip_tags($file->getClientOriginalName()), 0, 200);
        $mime = $file->getMimeType();
        $size = $file->getSize();
        $path = $file->store("attachments/{$conversation->id}", 'public');
        $url = Storage::disk('public')->url($path);

        $message = DB::transaction(function () use ($conversations, $conversation, $data, $fileName, $mime, $size, $path, $url) {
            $message = $conversations->appendMessage($conversation, [
                'sender_type' => 'visitor',
                'sender_name' => $conversation->visitor->name ?: 'Pengunjung',
                'content' => $data['caption'] ?? "Mengirim lampiran: {$fileName}",
                'client_message_id' => $data['client_message_id'] ?? null,
                'content_type' => str_starts_with((string) $mime, 'image/') ? 'image' : 'file',
                'metadata' => [
                    'file_name' => $fileName,
                    'file_url' => $url,
                    'original_size' => $data['original_size'] ?? $size,
                    'compressed_size' => $data['compressed_size'] ?? $size,
                ],
            ])['message'];

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_name' => $fileName,
                'file_path' => $path,
                'file_size' => $size,
                'mime_type' => $mime,
                'original_size' => $data['original_size'] ?? null,
                'compressed_size' => $data['compressed_size'] ?? $size,
            ]);

            return $message;
        });

        return $this->ok([
            'message_id' => $message->id,
            'file_name' => $fileName,
            'file_url' => $url,
            'file_size' => $size,
        ], 201);
    }
}
