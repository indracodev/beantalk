<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\FlushesProjectKeyCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use BelongsToTenant, FlushesProjectKeyCache;

    protected $fillable = ['tenant_id', 'assigned_user_id', 'name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')->withTimestamps();
    }

    public function domains(): HasMany
    {
        return $this->hasMany(ProjectDomain::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function activeApiKey(): HasOne
    {
        return $this->hasOne(ApiKey::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('is_active', true));
    }

    public function widgetSetting(): HasOne
    {
        return $this->hasOne(WidgetSetting::class);
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
