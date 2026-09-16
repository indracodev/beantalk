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
        'bot_mode_query',
        'bot_mode_options',
        'bot_name',
        'bot_welcome_message',
        'bot_welcome_options',
        'bot_offline_message',
        'bot_rules',
        'bot_tree',
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
        'bot_mode_query'                 => 'boolean',
        'bot_mode_options'               => 'boolean',
        'bot_welcome_options'            => 'array',
        'bot_rules'                      => 'array',
        'bot_tree'                       => 'array',
        'bot_ai_enabled'                 => 'boolean',
        'telegram_notifications_enabled' => 'boolean',
        'telegram_topic_mode_enabled'    => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
