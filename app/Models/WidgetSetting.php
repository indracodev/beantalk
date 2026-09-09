<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetSetting extends Model
{
    protected $table = 'widget_settings';

    protected $fillable = [
        'project_id',
        'primary_color',
        'accent_color',
        'position',
        'greeting_title',
        'greeting_subtitle',
        'is_online',
        'auto_reply_offline',
    ];

    protected $casts = [
        'is_online' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
