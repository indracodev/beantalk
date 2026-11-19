<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi halaman detail integrasi (semua tab dikirim lewat satu form). */
class UpdateWidgetSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        $audio = 'nullable|file|max:2048|extensions:mp3,wav,ogg,m4a,aac';

        return [
            'name' => 'nullable|string|max:100',
            'domains' => 'nullable|string|max:2000',
            'agent_scope' => 'nullable|in:all,selected',
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'integer',
            'language' => 'nullable|in:id,en',
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'greeting_title' => 'nullable|string|max:100',
            'greeting_subtitle' => 'nullable|string|max:255',
            'support_title' => 'nullable|string|max:100',
            'find_us_title' => 'nullable|string|max:100',
            'bot_name' => 'nullable|string|max:100',
            'bot_welcome_message' => 'nullable|string|max:5000',
            'bot_offline_message' => 'nullable|string|max:5000',
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:100',
            'business_hours_timezone' => 'nullable|timezone',
            'business_hours_off_message' => 'nullable|string|max:500',
            'business_hours' => 'nullable|array',
            'sound_type' => 'nullable|alpha_dash|max:50',
            'sound_duration' => 'nullable|integer',
            'sound_custom_file' => $audio,
            'widget_sound_type' => 'nullable|alpha_dash|max:50',
            'widget_sound_custom_file' => $audio,
            'launcher_type' => 'nullable|in:default,mascot',
            'mascot_id' => 'nullable|alpha_dash|max:50',
            'mascot_size' => 'nullable|integer',
            'active_tab' => 'nullable|alpha_dash|max:30',
        ];
    }
}
