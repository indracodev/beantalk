<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDomain extends Model
{
    protected $table = 'project_domains';

    protected $fillable = [
        'project_id',
        'domain',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
