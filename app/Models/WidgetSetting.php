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
        'bot_enabled',
        'bot_name',
        'bot_welcome_message',
        'bot_offline_message',
        'bot_rules',
        'bot_ai_enabled',
        'bot_ai_prompt',
        'telegram_bot_token',
        'telegram_chat_id',
        'telegram_notifications_enabled',
        'telegram_topic_mode_enabled',
    ];

    protected $casts = [
        'is_online'                      => 'boolean',
        'social_channels'                => 'array',
        'bot_enabled'                    => 'boolean',
        'bot_rules'                      => 'array',
        'bot_ai_enabled'                 => 'boolean',
        'telegram_notifications_enabled' => 'boolean',
        'telegram_topic_mode_enabled'    => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
