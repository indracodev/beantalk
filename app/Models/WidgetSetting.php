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
        'support_title',
        'is_online',
        'auto_reply_offline',
        'find_us_title',
        'social_channels',
    ];

    protected $casts = [
        'is_online'       => 'boolean',
        'social_channels' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
