<?php

namespace App\Models;

use App\Models\Concerns\FiltersVisitors;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visitor extends Model
{
    use FiltersVisitors;

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

    protected $appends = [
        'display_name',
        'customer_code_formatted',
        'device_type',
        'browser_name',
        'os_name',
        'is_online',
    ];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime'];
    }

    protected function customerCodeFormatted(): Attribute
    {
        return Attribute::get(fn () => $this->customer_code
            ?: 'CUS-'.strtoupper(substr(md5(($this->visitor_uuid ?? 'visitor').$this->id), 0, 4)));
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->name ?: 'Tamu · '.$this->customer_code_formatted);
    }

    /** 2 huruf avatar (dipakai feed polling inbox). */
    protected function initials(): Attribute
    {
        return Attribute::get(fn () => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->name ?: 'Tamu'), 0, 2)) ?: 'TM');
    }

    protected function deviceType(): Attribute
    {
        return Attribute::get(function () {
            $ua = strtolower($this->user_agent ?? '');

            return match (true) {
                str_contains($ua, 'ipad') || str_contains($ua, 'tablet') => 'tablet',
                str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone') => 'mobile',
                default => 'desktop',
            };
        });
    }

    protected function browserName(): Attribute
    {
        return Attribute::get(function () {
            $ua = $this->user_agent ?? '';

            return match (true) {
                $ua === '' => 'Lainnya',
                stripos($ua, 'Edg') !== false => 'Edge',
                stripos($ua, 'Chrome') !== false => 'Chrome',
                stripos($ua, 'Safari') !== false => 'Safari',
                stripos($ua, 'Firefox') !== false => 'Firefox',
                stripos($ua, 'Opera') !== false || stripos($ua, 'OPR') !== false => 'Opera',
                default => 'Lainnya',
            };
        });
    }

    protected function osName(): Attribute
    {
        return Attribute::get(function () {
            $ua = $this->user_agent ?? '';

            return match (true) {
                $ua === '' => 'Lainnya',
                stripos($ua, 'Windows') !== false => 'Windows',
                stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false || stripos($ua, 'iOS') !== false => 'iOS',
                stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS') !== false => 'macOS',
                stripos($ua, 'Android') !== false => 'Android',
                stripos($ua, 'Linux') !== false => 'Linux',
                default => 'Lainnya',
            };
        });
    }

    protected function isOnline(): Attribute
    {
        return Attribute::get(fn () => (bool) $this->last_seen_at?->greaterThanOrEqualTo(now()->subMinutes(5)));
    }

    /** Profil pengunjung untuk widget. */
    public function toWidgetArray(): array
    {
        return [
            'uuid' => $this->visitor_uuid,
            'name' => $this->name,
            'email' => $this->email,
            'customer_code' => $this->customer_code_formatted,
            'display_name' => $this->display_name,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function latestConversation(): HasOne
    {
        return $this->hasOne(Conversation::class)->latestOfMany();
    }
}
