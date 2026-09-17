<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';

    protected $fillable = [
        'project_id',
        'contact_id',
        'name',
        'email',
        'customer_code',
        'visitor_uuid',
        'ip_address',
        'user_agent',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    protected $appends = [
        'display_name',
        'customer_code_formatted',
    ];

    public function getCustomerCodeFormattedAttribute(): string
    {
        return $this->customer_code ?: ('CUS-' . strtoupper(substr(md5(($this->visitor_uuid ?? 'visitor') . $this->id), 0, 4)));
    }

    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        return 'Tamu · ' . $this->customer_code_formatted;
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
