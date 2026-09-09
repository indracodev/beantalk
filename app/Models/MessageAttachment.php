<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    protected $table = 'message_attachments';

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'original_size',
        'compressed_size',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
