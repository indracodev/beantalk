<?php

namespace App\Models;

use App\Traits\FlushesProjectKeyCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetSetting extends Model
{
    use FlushesProjectKeyCache;

    protected $fillable = [
        'project_id',
        'primary_color',
        'accent_color',
        'position',
        'language',
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
        'business_hours_enabled',
        'business_hours',
        'business_hours_timezone',
        'business_hours_off_message',
        'holidays',
        'sound_enabled',
        'sound_type',
        'sound_duration',
        'sound_custom_url',
        'widget_sound_enabled',
        'widget_sound_type',
        'widget_sound_custom_url',
        'launcher_type',
        'mascot_id',
        'mascot_size',
        'mascot_tracking',
    ];

    /** Token bot tidak pernah ikut terserialisasi ke JSON/array. */
    protected $hidden = ['telegram_bot_token'];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'social_channels' => 'array',
            'bot_enabled' => 'boolean',
            'bot_mode_query' => 'boolean',
            'bot_mode_options' => 'boolean',
            'bot_welcome_options' => 'array',
            'bot_rules' => 'array',
            'bot_tree' => 'array',
            'bot_ai_enabled' => 'boolean',
            'telegram_bot_token' => 'encrypted',
            'telegram_notifications_enabled' => 'boolean',
            'telegram_topic_mode_enabled' => 'boolean',
            'business_hours_enabled' => 'boolean',
            'business_hours' => 'array',
            'holidays' => 'array',
            'sound_enabled' => 'boolean',
            'sound_duration' => 'integer',
            'widget_sound_enabled' => 'boolean',
            'mascot_size' => 'integer',
            'mascot_tracking' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Konfigurasi publik untuk widget (tanpa rahasia). $ws null = default. */
    public static function widgetConfig(?self $ws): array
    {
        return [
            'language' => $ws?->language ?: 'id',
            'primary_color' => $ws?->primary_color ?? '#1E1E1E',
            'accent_color' => $ws?->accent_color ?? '#FFFFFF',
            'position' => $ws?->position ?? 'bottom-right',
            'greeting_title' => $ws?->greeting_title ?? 'Hallo!',
            'greeting_subtitle' => $ws?->greeting_subtitle ?? 'Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
            'support_title' => $ws?->support_title ?: 'Customer Support',
            'is_online' => $ws?->is_online ?? true,
            'find_us_title' => $ws?->find_us_title ?: 'Reach Us Anywhere Else',
            'social_channels' => is_array($ws?->social_channels) ? $ws->social_channels : [],
            'bot_enabled' => (bool) $ws?->bot_enabled,
            'bot_name' => $ws?->bot_name ?? 'BeanBot',
            'bot_welcome_message' => $ws?->bot_welcome_message,
            'bot_mode_query' => (bool) ($ws?->bot_mode_query ?? true),
            'bot_mode_options' => (bool) ($ws?->bot_mode_options ?? true),
            'bot_welcome_options' => $ws?->bot_welcome_options,
            'sound_enabled' => (bool) ($ws?->widget_sound_enabled ?? true),
            'sound_type' => $ws?->widget_sound_type ?: 'chime',
            'sound_custom_url' => $ws?->widget_sound_custom_url,
            'launcher_type' => $ws?->launcher_type ?: 'default',
            'mascot_id' => $ws?->mascot_id ?: 'fox',
            'mascot_size' => $ws?->mascot_size ?: 72,
            'mascot_tracking' => (bool) ($ws?->mascot_tracking ?? true),
            'api_url' => url('/'),
        ];
    }

    /** Bunyi notifikasi admin untuk pesan masuk. */
    public function adminSoundConfig(): array
    {
        return [
            'sound_enabled' => (bool) $this->sound_enabled,
            'sound_type' => $this->sound_type ?: 'pedestrian',
            'sound_duration' => (int) ($this->sound_duration ?: 15),
            'sound_custom_url' => $this->sound_custom_url,
        ];
    }
}
