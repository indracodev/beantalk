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
        'device_type',
        'browser_name',
        'os_name',
        'is_online',
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

    public function getDeviceTypeAttribute(): string
    {
        $ua = strtolower($this->user_agent ?? '');
        if (strpos($ua, 'ipad') !== false || strpos($ua, 'tablet') !== false) {
            return 'tablet';
        }
        if (strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false) {
            return 'mobile';
        }
        return 'desktop';
    }

    public function getBrowserNameAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (empty($ua)) return 'Lainnya';
        if (stripos($ua, 'Edg') !== false) return 'Edge';
        if (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edg') === false) return 'Chrome';
        if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) return 'Safari';
        if (stripos($ua, 'Firefox') !== false) return 'Firefox';
        if (stripos($ua, 'Opera') !== false || stripos($ua, 'OPR') !== false) return 'Opera';
        return 'Lainnya';
    }

    public function getOsNameAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (empty($ua)) return 'Lainnya';
        if (stripos($ua, 'Windows') !== false) return 'Windows';
        if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false || stripos($ua, 'iOS') !== false) return 'iOS';
        if (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS') !== false) return 'macOS';
        if (stripos($ua, 'Android') !== false) return 'Android';
        if (stripos($ua, 'Linux') !== false) return 'Linux';
        return 'Lainnya';
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
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

    public function latestConversation()
    {
        return $this->hasOne(Conversation::class)->latest('id');
    }
}
