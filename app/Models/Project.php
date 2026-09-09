<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use BelongsToTenant;

    protected $table = 'projects';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domains()
    {
        return $this->hasMany(ProjectDomain::class);
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    public function widgetSetting()
    {
        return $this->hasOne(WidgetSetting::class);
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
