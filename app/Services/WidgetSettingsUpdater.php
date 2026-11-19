<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Support\SocialChannels;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Simpan seluruh tab pengaturan integrasi (umum, sosial, bot, Telegram, jam kerja, suara, launcher).
 * Tiap tab punya penanda form tersembunyi agar tab lain tidak tertimpa default.
 */
class WidgetSettingsUpdater
{
    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function update(Project $project, Request $request): WidgetSetting
    {
        return DB::transaction(function () use ($project, $request) {
            if ($request->filled('name')) {
                $project->update(['name' => $request->input('name')]);
            }
            if ($request->filled('domains')) {
                $this->syncDomains($project, $request->input('domains'));
            }
            if ($request->has('agent_scope')) {
                $this->syncAgents($project, $request);
            }

            $ws = WidgetSetting::firstOrCreate(['project_id' => $project->id], [
                'primary_color' => '#0071E3',
                'greeting_subtitle' => 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
            ]);
            if ($ws->wasRecentlyCreated) {
                $ws->refresh(); // muat default kolom DB (greeting_title, dll.) sebelum dipakai sebagai fallback
            }

            $ws->update($this->general($request, $ws)
                + $this->businessHours($request, $ws)
                + $this->adminSound($request, $ws, $project)
                + $this->widgetSound($request, $ws, $project)
                + $this->launcher($request));

            return $ws;
        });
    }

    private function syncDomains(Project $project, string $input): void
    {
        $domains = collect(preg_split('/[,\r\n]+/', $input))
            ->map(fn ($d) => IntegrationService::normalizeDomain($d))
            ->filter()->unique()->values();

        if ($domains->isEmpty()) {
            return;
        }
        $project->domains()->whereNotIn('domain', $domains)->delete();
        $domains->each(fn ($d) => $project->domains()->firstOrCreate(['domain' => $d], ['is_verified' => true]));
    }

    /** all = semua CS; selected = hanya agen terpilih (divalidasi milik tenant yang sama). */
    private function syncAgents(Project $project, Request $request): void
    {
        $ids = $request->input('agent_scope') === 'selected' && $request->filled('agent_ids')
            ? User::where('tenant_id', $project->tenant_id)->whereIn('id', (array) $request->input('agent_ids'))->pluck('id')->all()
            : [];

        $project->assignedUsers()->sync($ids);
        $project->update(['assigned_user_id' => $ids[0] ?? null]);
    }

    private function general(Request $request, WidgetSetting $ws): array
    {
        return [
            'language' => $request->input('language', $ws->language ?: 'id'),
            'primary_color' => $request->input('primary_color', $ws->primary_color),
            'greeting_title' => $request->input('greeting_title', $ws->greeting_title),
            'greeting_subtitle' => $request->input('greeting_subtitle', $ws->greeting_subtitle),
            'support_title' => $request->input('support_title', $ws->support_title ?: 'Customer Support'),
            'find_us_title' => $request->input('find_us_title', 'Find Us Somewhere Else'),
            'social_channels' => SocialChannels::fromInput($request->input('social_channels'), (array) $request->input('channels', [])),
            'bot_enabled' => $request->boolean('bot_enabled'),
            'bot_mode_query' => $request->has('bot_mode_query'),
            'bot_mode_options' => $request->has('bot_mode_options'),
            'bot_name' => $request->input('bot_name', $ws->bot_name ?: 'BeanBot'),
            'bot_welcome_message' => $request->input('bot_welcome_message', $ws->bot_welcome_message),
            'bot_welcome_options' => $request->has('bot_welcome_options') ? $this->json($request->input('bot_welcome_options')) : $ws->bot_welcome_options,
            'bot_offline_message' => $request->input('bot_offline_message', $ws->bot_offline_message),
            'bot_rules' => $this->json($request->input('bot_rules')) ?? [],
            'bot_tree' => $request->has('bot_tree') ? $this->json($request->input('bot_tree')) : $ws->bot_tree,
            // Token tidak pernah dirender ulang ke form; kosong = pertahankan token lama.
            'telegram_bot_token' => $request->filled('telegram_bot_token') ? trim($request->input('telegram_bot_token')) : $ws->telegram_bot_token,
            'telegram_chat_id' => $request->input('telegram_chat_id', $ws->telegram_chat_id),
            'telegram_notifications_enabled' => $request->has('telegram_notifications_enabled'),
            'telegram_topic_mode_enabled' => $request->has('telegram_topic_mode_enabled'),
        ];
    }

    private function businessHours(Request $request, WidgetSetting $ws): array
    {
        if (! $request->hasAny(['has_business_hours_form', 'business_hours', 'business_hours_enabled'])) {
            return [];
        }

        $raw = (array) $request->input('business_hours', []);
        $schedule = [];
        foreach (self::DAYS as $day) {
            $schedule[$day] = [
                'enabled' => ! empty($raw[$day]['enabled']),
                'start' => trim($raw[$day]['start'] ?? '') ?: '08:00',
                'end' => trim($raw[$day]['end'] ?? '') ?: '17:00',
            ];
        }

        return [
            'business_hours_enabled' => $request->has('business_hours_enabled'),
            'business_hours_timezone' => $request->input('business_hours_timezone', $ws->business_hours_timezone ?: 'Asia/Jakarta'),
            'business_hours_off_message' => $request->input('business_hours_off_message', $ws->business_hours_off_message),
            'business_hours' => $schedule,
        ];
    }

    private function adminSound(Request $request, WidgetSetting $ws, Project $project): array
    {
        if (! $request->hasAny(['has_sound_settings_form', 'sound_type', 'sound_enabled'])) {
            return [];
        }

        return [
            'sound_enabled' => $request->has('sound_enabled'),
            'sound_type' => $request->input('sound_type', $ws->sound_type ?: 'pedestrian'),
            'sound_duration' => max(3, min(180, (int) $request->input('sound_duration', $ws->sound_duration ?: 15))),
        ] + $this->soundFile($request, 'sound_custom_file', 'remove_custom_sound', 'sound_custom_url', "sound_{$project->id}");
    }

    private function widgetSound(Request $request, WidgetSetting $ws, Project $project): array
    {
        if (! $request->hasAny(['has_widget_sound_settings_form', 'widget_sound_type', 'widget_sound_enabled'])) {
            return [];
        }

        return [
            'widget_sound_enabled' => $request->has('widget_sound_enabled'),
            'widget_sound_type' => $request->input('widget_sound_type', $ws->widget_sound_type ?: 'chime'),
        ] + $this->soundFile($request, 'widget_sound_custom_file', 'remove_widget_custom_sound', 'widget_sound_custom_url', "widget_sound_{$project->id}");
    }

    /** Unggah/hapus berkas suara kustom. Nama berkas dibuat server, bukan dari klien. */
    private function soundFile(Request $request, string $field, string $removeFlag, string $column, string $prefix): array
    {
        $file = $request->file($field);
        if ($file instanceof UploadedFile && $file->isValid()) {
            $path = $file->storeAs('sounds', $prefix.'_'.time().'.'.strtolower($file->getClientOriginalExtension()), 'public');

            return [$column => '/storage/'.$path];
        }

        return $request->input($removeFlag) === '1' ? [$column => null] : [];
    }

    private function launcher(Request $request): array
    {
        if (! $request->has('launcher_type')) {
            return [];
        }

        return [
            'launcher_type' => $request->input('launcher_type') === 'mascot' ? 'mascot' : 'default',
            'mascot_id' => $request->input('mascot_id', 'fox'),
            'mascot_size' => max(48, min(120, (int) $request->input('mascot_size', 72))),
            'mascot_tracking' => $request->has('mascot_tracking'),
        ];
    }

    /** Field JSON dari form: string JSON / array / kosong menjadi array atau null. */
    private function json(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : (json_decode($value, true) ?: null);
    }
}
