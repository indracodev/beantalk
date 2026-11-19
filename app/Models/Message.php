<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'conversation_id',
        'tenant_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'client_message_id',
        'content',
        'content_type',
        'metadata',
        'status',
        'is_internal_note',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_internal_note' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** Key metadata yang boleh sampai ke widget publik (data staf/Telegram/internal tidak pernah ikut). */
    private const WIDGET_METADATA = [
        'options', 'is_bot', 'is_welcome', 'is_handoff', 'is_offline',
        'file_name', 'file_url', 'original_size', 'compressed_size',
    ];

    /** Bentuk pesan untuk widget pengunjung (satu sumber untuk init, polling & kirim). */
    public function toWidgetArray(): array
    {
        return [
            'id' => $this->id,
            'client_message_id' => $this->client_message_id,
            'sender_type' => $this->sender_type,
            'sender_name' => $this->sender_name,
            'content' => $this->content,
            'content_type' => $this->content_type,
            'metadata' => array_intersect_key($this->metadata ?? [], array_flip(self::WIDGET_METADATA)) ?: null,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
