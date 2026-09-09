<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use BelongsToTenant;

    protected $table = 'contacts';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'email',
        'phone',
        'name',
        'avatar_url',
        'custom_attributes',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
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
