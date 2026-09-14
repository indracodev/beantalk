<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use BelongsToTenant;

    protected $table = 'conversations';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'visitor_id',
        'contact_id',
        'assigned_user_id',
        'status',
        'priority',
        'channel',
        'page_url',
        'page_title',
        'last_message_at',
        'last_message_preview',
        'unread_agent_count',
        'unread_visitor_count',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_agent_count' => 'integer',
        'unread_visitor_count' => 'integer',
    ];

    protected $appends = [
        'channel_label',
    ];

    public function getChannelLabelAttribute(): string
    {
        switch ($this->channel) {
            case 'whatsapp': return 'WhatsApp';
            case 'instagram': return 'Instagram';
            case 'facebook': return 'Messenger';
            default: return 'Web Chat';
        }
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot('joined_at', 'last_read_message_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('id', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
