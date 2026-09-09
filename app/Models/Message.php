<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use BelongsToTenant;

    protected $table = 'messages';

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

    protected $casts = [
        'metadata' => 'array',
        'is_internal_note' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
