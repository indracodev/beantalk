<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIntegrationRequest;
use App\Http\Requests\UpdateWidgetSettingsRequest;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Services\ActivityLogger;
use App\Services\BusinessHoursService;
use App\Services\IntegrationService;
use App\Services\TelegramService;
use App\Services\WidgetSettingsUpdater;
use App\Support\SocialChannels;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Integrations Hub: daftar website, detail & pengaturan widget per project. */
class IntegrationController extends Controller
{
    /** GET /admin/integrations */
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('admin.integrations', [
            'projects' => Project::where('tenant_id', $tenantId)
                ->with(['domains', 'activeApiKey', 'widgetSetting', 'assignedUsers'])
                ->withCount(['conversations', 'visitors'])
                ->orderBy('id')
                ->get(),
            'agents' => User::where('tenant_id', $tenantId)->orderBy('name')->get(),
        ]);
    }

    /** POST /admin/integrations (superadmin) */
    public function store(StoreIntegrationRequest $request, IntegrationService $integrations): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $tenantId = $request->user()->tenant_id;
        ['project' => $project, 'domain' => $domain, 'api_key' => $key, 'setting' => $setting] = $integrations->create($tenantId, $data, '#C59B27');

        if (($data['agent_scope'] ?? null) === 'selected' && ! empty($data['agent_ids'])) {
            $ids = User::where('tenant_id', $tenantId)->whereIn('id', $data['agent_ids'])->pluck('id')->all();
            if ($ids) {
                $project->assignedUsers()->sync($ids);
                $project->update(['assigned_user_id' => $ids[0]]);
            }
        }

        ActivityLogger::log('integration.created', "Membuat integrasi website baru: {$project->name} ({$domain})", $project, [
            'domain' => $domain, 'public_key' => $key->public_key,
        ]);

        if ($request->wantsJson()) {
            return $this->ok([
                'project_id' => $project->id,
                'name' => $project->name,
                'domain' => $domain,
                'public_key' => $key->public_key,
                'primary_color' => $setting->primary_color,
            ], 201);
        }

        return to_route('admin.integrations')->with('success', "Integrasi '{$project->name}' berhasil ditambahkan!");
    }

    /** POST /admin/integrations/{id}/regenerate (superadmin) */
    public function regenerateKey(Request $request, string $id, IntegrationService $integrations): RedirectResponse
    {
        $project = $this->find($request, $id);
        $key = $integrations->regenerateKey($project);

        ActivityLogger::log('integration.key_regenerated', "Regenerasi API key untuk project: {$project->name}", $project, [
            'old_keys_deactivated' => true, 'new_public_key' => $key->public_key,
        ]);

        return to_route('admin.integrations')->with('success', "API Key untuk '{$project->name}' berhasil di-regenerasi. Salin ulang kode embed baru.");
    }

    /** GET /admin/integrations/{id} — halaman detail & seluruh tab pengaturan. */
    public function show(Request $request, string $id, BusinessHoursService $businessHours): View
    {
        $project = $this->find($request, $id)
            ->load(['domains', 'widgetSetting', 'activeApiKey', 'assignedUsers'])
            ->loadCount(['conversations', 'visitors']);

        $ws = $project->widgetSetting ?? WidgetSetting::firstOrCreate(['project_id' => $project->id], [
            'primary_color' => '#0071E3',
            'greeting_subtitle' => 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
        ]);

        $defaultSchedule = array_fill_keys(['mon', 'tue', 'wed', 'thu', 'fri'], ['enabled' => true, 'start' => '08:00', 'end' => '17:00'])
            + array_fill_keys(['sat', 'sun'], ['enabled' => false, 'start' => '08:00', 'end' => '17:00']);

        return view('admin.integration-detail', [
            'project' => $project,
            'widgetSetting' => $ws,
            'agents' => User::where('tenant_id', $project->tenant_id)->orderBy('name')->get(),
            'availableChannels' => SocialChannels::available(),
            'botRules' => is_array($ws->bot_rules) ? $ws->bot_rules : [],
            'socialChannelsList' => SocialChannels::forEditor($ws->social_channels),
            'recentLogs' => $this->recentLogs($project),
            'isWithinBusinessHours' => $businessHours->isWithinBusinessHours($ws),
            'businessHoursSummary' => $businessHours->getScheduleSummary($ws),
            'currentSchedule' => array_merge($defaultSchedule, is_array($ws->business_hours) ? $ws->business_hours : []),
        ]);
    }

    /** PUT /admin/integrations/{id}/settings (superadmin) */
    public function updateSettings(UpdateWidgetSettingsRequest $request, string $id, WidgetSettingsUpdater $updater): RedirectResponse
    {
        $project = $this->find($request, $id);
        $ws = $updater->update($project, $request);

        ActivityLogger::log('widget.updated', "Memperbarui pengaturan tampilan widget, bot, dan notifikasi Telegram untuk: {$project->name}", $project, [
            'project_id' => $project->id,
            'bot_enabled' => (bool) $ws->bot_enabled,
            'telegram_enabled' => (bool) $ws->telegram_notifications_enabled,
        ]);

        $tab = $request->input('active_tab');
        $params = ['id' => $project->id] + ($tab && $tab !== 'bot' ? ['tab' => $tab] : []);

        return to_route('admin.integrations.detail', $params)->with('success', "Pengaturan integrasi untuk '{$project->name}' berhasil disimpan!");
    }

    /** POST /admin/integrations/{id}/test-telegram (superadmin) */
    public function testTelegram(Request $request, string $id, TelegramService $telegram): JsonResponse
    {
        $ws = $this->find($request, $id)->widgetSetting;

        return response()->json($telegram->testConnection(
            (string) ($request->input('telegram_bot_token') ?: $ws?->telegram_bot_token),
            (string) ($request->input('telegram_chat_id') ?: $ws?->telegram_chat_id),
        ));
    }

    /** POST /admin/integrations/{id}/set-telegram-webhook (superadmin) */
    public function setTelegramWebhook(Request $request, string $id, TelegramService $telegram): JsonResponse
    {
        $ws = $this->find($request, $id)->widgetSetting;
        $url = $request->input('webhook_url') ?: url('/api/v1/telegram/webhook');

        return response()->json($telegram->setWebhook((string) ($request->input('telegram_bot_token') ?: $ws?->telegram_bot_token), (string) $url));
    }

    private function find(Request $request, string $id): Project
    {
        return Project::where('tenant_id', $request->user()->tenant_id)->findOrFail($id);
    }

    /** 8 log terakhir yang menyangkut project ini (subject langsung atau properties.project_id). */
    private function recentLogs(Project $project)
    {
        return ActivityLog::where('tenant_id', $project->tenant_id)
            ->where(fn ($q) => $q->where(fn ($s) => $s->where('subject_type', Project::class)->where('subject_id', $project->id))
                ->orWhere('properties->project_id', $project->id))
            ->latest('id')
            ->take(8)
            ->get();
    }
}
