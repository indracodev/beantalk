@extends('layouts.admin')

@section('title', 'Pengaturan Integrasi: ' . $project->name)
@section('header_title', 'Pengaturan Integrasi: ' . $project->name)

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 w-full">

    <!-- MAIN FORM WRAPPER -->
    <form id="formIntegrationSettings" action="{{ route('admin.integrations.settings', $project->id) }}" method="POST" enctype="multipart/form-data" onsubmit="serializeAllSettings()" class="flex flex-col gap-4 sm:gap-5 w-full">
        @csrf
        @method('PUT')

        <!-- Hidden Inputs to serialize rules & social channels into JSON -->
        <input type="hidden" name="bot_rules" id="hiddenBotRules" value="{{ json_encode($botRules) }}">
        <input type="hidden" name="social_channels" id="hiddenSocialChannels" value="{{ json_encode($socialChannelsList) }}">
        <input type="hidden" name="bot_welcome_options" id="hiddenBotWelcomeOptions" value="{{ json_encode($widgetSetting->bot_welcome_options ?? []) }}">
        <input type="hidden" name="bot_tree" id="hiddenBotTree" value="{{ json_encode($widgetSetting->bot_tree ?? []) }}">
        <input type="hidden" name="has_sound_settings_form" value="1">
        <input type="hidden" name="has_widget_sound_settings_form" value="1">
        <input type="hidden" name="active_tab" id="hiddenActiveTab" value="{{ request('tab', 'bot') }}">

        <!-- Header Action Bar -->
        <div class="bg-white border border-apple-border rounded-xl p-3.5 sm:p-4 shadow-apple-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.integrations') }}" class="w-8 h-8 rounded-lg bg-apple-canvas border border-apple-border text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-border/40 flex items-center justify-center transition shadow-2xs shrink-0" title="Kembali ke Daftar Integrasi">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-[16px] font-bold text-apple-textPrimary tracking-tight">{{ $project->name }}</h2>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Terkoneksi
                        </span>
                        <span id="badgeBotStatus" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold {{ $widgetSetting->bot_enabled ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-neutral-100 text-neutral-600 border border-neutral-200' }}">
                            {{ $widgetSetting->bot_enabled ? '🤖 Bot: Aktif' : '🤖 Bot: Nonaktif' }}
                        </span>
                        <span id="badgeHeaderHoursStatus" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200') : 'bg-neutral-100 text-neutral-600 border border-neutral-200' }}">
                            {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? '⏰ Jam Kerja: Buka' : '🌙 Jam Kerja: Tutup') : '⏰ Jam Kerja: 24/7' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-apple-textSecondary mt-0.5">
                        <span class="font-mono text-apple-textPrimary">{{ $project->domains->first()->domain ?? 'website.com' }}</span>
                        <span class="text-apple-textTertiary">&bull;</span>
                        <span class="font-mono text-apple-textTertiary">Key: {{ $project->activeApiKey->public_key ?? 'pk_live_' . $project->slug }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                <a href="/demo-store.html?key={{ $project->activeApiKey->public_key ?? 'pk_live_' . $project->slug }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white border border-apple-border hover:bg-apple-canvas text-apple-textPrimary px-3 py-1.5 rounded-lg text-[11.5px] font-medium transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span>Demo Store</span>
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 bg-apple-blue hover:bg-apple-blueHover text-white px-3.5 py-1.5 rounded-lg text-[12px] font-semibold transition shadow-apple-sm cursor-pointer">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>

        <!-- Telemetry Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white border border-apple-border rounded-xl p-3 sm:p-3.5 shadow-apple-sm flex flex-col">
                <span class="text-[10px] font-semibold text-apple-textTertiary uppercase tracking-wider">Total Percakapan</span>
                <span class="text-[17px] font-bold text-apple-textPrimary font-mono mt-0.5">{{ number_format($project->conversations_count) }}</span>
                <span class="text-[10.5px] text-apple-textSecondary mt-0.5">Seluruh tiket masuk</span>
            </div>
            <div class="bg-white border border-apple-border rounded-xl p-3 sm:p-3.5 shadow-apple-sm flex flex-col">
                <span class="text-[10px] font-semibold text-apple-textTertiary uppercase tracking-wider">Pengunjung Unik</span>
                <span class="text-[17px] font-bold text-apple-textPrimary font-mono mt-0.5">{{ number_format($project->visitors_count) }}</span>
                <span class="text-[10.5px] text-apple-textSecondary mt-0.5">Customer website</span>
            </div>
            <div class="bg-white border border-apple-border rounded-xl p-3 sm:p-3.5 shadow-apple-sm flex flex-col">
                <span class="text-[10px] font-semibold text-apple-textTertiary uppercase tracking-wider">Smart Bot Status</span>
                <span id="cardBotText" class="text-[13px] font-bold {{ $widgetSetting->bot_enabled ? 'text-purple-600' : 'text-neutral-500' }} mt-0.5 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $widgetSetting->bot_enabled ? 'bg-purple-500' : 'bg-neutral-400' }}"></span>
                    {{ $widgetSetting->bot_enabled ? 'Aktif' : 'Nonaktif' }}
                </span>
                <span class="text-[10.5px] text-apple-textSecondary mt-0.5">{{ $widgetSetting->bot_name ?: 'BeanBot' }}</span>
            </div>
            <div class="bg-white border border-apple-border rounded-xl p-3 sm:p-3.5 shadow-apple-sm flex flex-col">
                <span class="text-[10px] font-semibold text-apple-textTertiary uppercase tracking-wider">Telegram Forum</span>
                <span id="cardTelegramStatus" class="text-[13px] font-bold {{ !empty($widgetSetting->telegram_topic_mode_enabled) ? 'text-sky-600' : 'text-neutral-500' }} mt-0.5 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ !empty($widgetSetting->telegram_topic_mode_enabled) ? 'bg-sky-500' : 'bg-neutral-400' }}"></span>
                    {{ !empty($widgetSetting->telegram_topic_mode_enabled) ? 'Mode Topik ON' : 'Mode Topik OFF' }}
                </span>
                <span class="text-[10.5px] text-apple-textSecondary mt-0.5">{{ !empty($widgetSetting->telegram_notifications_enabled) ? 'Notif 1x Aktif' : 'Notif OFF' }}</span>
            </div>
            <div class="bg-white border border-apple-border rounded-xl p-3 sm:p-3.5 shadow-apple-sm flex flex-col">
                <span class="text-[10px] font-semibold text-apple-textTertiary uppercase tracking-wider">Warna Brand</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span id="previewHeaderSwatch" class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-2xs shrink-0" style="background-color: {{ $widgetSetting->primary_color }}"></span>
                    <span id="previewHeaderHex" class="text-[13px] font-bold text-apple-textPrimary font-mono">{{ $widgetSetting->primary_color }}</span>
                </div>
                <span class="text-[10.5px] text-apple-textSecondary mt-0.5">Aksen UI Widget</span>
            </div>
        </div>

        <!-- Clean Tab Bar Navigation -->
        <div class="bg-white p-1 rounded-xl border border-apple-border shadow-apple-sm flex items-center gap-1 overflow-x-auto no-scrollbar shrink-0">
            <button type="button" onclick="switchDetailTab('bot')" id="tab-btn-bot" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-purple-600 text-white shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                    <circle cx="12" cy="5" r="2"></circle>
                    <path d="M12 7v4"></path>
                    <line x1="8" y1="16" x2="8" y2="16"></line>
                    <line x1="16" y1="16" x2="16" y2="16"></line>
                </svg>
                <span>Smart Bot &amp; FAQ Rules</span>
                <span id="badgeTabRuleCount" class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 text-white font-bold">{{ count($botRules) }}</span>
            </button>

            <button type="button" onclick="switchDetailTab('appearance')" id="tab-btn-appearance" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                    <path d="M2 12h20"></path>
                </svg>
                <span>Tampilan &amp; Branding</span>
            </button>

            <button type="button" onclick="switchDetailTab('mascot')" id="tab-btn-mascot" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <span class="text-[14px]">🦊</span>
                <span>Maskot Interaktif</span>
                <span id="badgeTabMascotStatus" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ ($widgetSetting->launcher_type ?? 'default') === 'mascot' ? 'bg-amber-100 text-amber-800' : 'bg-neutral-100 text-neutral-600' }}">
                    {{ ($widgetSetting->launcher_type ?? 'default') === 'mascot' ? 'Aktif' : 'Off' }}
                </span>
            </button>

            <button type="button" onclick="switchDetailTab('social')" id="tab-btn-social" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
                <span>Saluran Sosial &amp; Marketplace</span>
                <span id="badgeTabSocialCount" class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold">{{ collect($socialChannelsList)->where('enabled', true)->count() }}</span>
            </button>

            <button type="button" onclick="switchDetailTab('embed')" id="tab-btn-embed" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
                <span>Domain, Agent &amp; Embed Script</span>
            </button>

            <button type="button" onclick="switchDetailTab('telegram')" id="tab-btn-telegram" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-sky-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.62 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.89 8.01-3.46 3.82-1.6 4.61-1.88 5.14-1.89.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.19-.04.35z"/>
                </svg>
                <span>Notifikasi &amp; Forum Telegram</span>
                @if(!empty($widgetSetting->telegram_notifications_enabled) && !empty($widgetSetting->telegram_bot_token))
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                @endif
            </button>

            <button type="button" onclick="switchDetailTab('hours')" id="tab-btn-hours" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Jam Kerja (Business Hours)</span>
                <span id="badgeTabHoursStatus" class="px-1.5 py-0.2 rounded-full text-[10px] {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') : 'bg-neutral-100 text-neutral-600' }} font-bold">
                    {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? 'Buka' : 'Tutup') : '24/7' }}
                </span>
            </button>

            <button type="button" onclick="switchDetailTab('sound')" id="tab-btn-sound" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                </svg>
                <span>Suara Notifikasi</span>
                <span id="badgeTabSoundStatus" class="px-1.5 py-0.2 rounded-full text-[10px] {{ $widgetSetting->sound_enabled ? 'bg-rose-100 text-rose-800' : 'bg-neutral-100 text-neutral-600' }} font-bold">
                    Agent: {{ $widgetSetting->sound_enabled ? ($widgetSetting->sound_duration . 's') : 'Mute' }} &bull; Cust: {{ ($widgetSetting->widget_sound_enabled ?? true) ? 'ON' : 'OFF' }}
                </span>
            </button>
        </div>

        <!-- ============================================================ -->
        <!-- TAB PANES CONTAINER (MODULAR BLADE PARTIALS)                 -->
        <!-- ============================================================ -->
        <div id="tabPanesContainer" class="w-full flex flex-col gap-4">
            @include('admin.partials.integration-tabs.tab-bot')
            @include('admin.partials.integration-tabs.tab-appearance')
            @include('admin.partials.integration-tabs.tab-mascot')
            @include('admin.partials.integration-tabs.tab-social')
            @include('admin.partials.integration-tabs.tab-embed')
            @include('admin.partials.integration-tabs.tab-telegram')
            @include('admin.partials.integration-tabs.tab-hours')
            @include('admin.partials.integration-tabs.tab-sound')
        </div>

        <!-- Form Bottom Action Bar (In-flow, non-sticky) -->
        <div class="bg-white border border-apple-border rounded-xl p-3.5 sm:p-4 shadow-apple-sm flex items-center justify-between gap-3 mt-2">
            <div class="flex items-center gap-2 text-[12px] text-apple-textSecondary">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Perubahan tersimpan saat Anda menekan tombol simpan.</span>
            </div>
            <button type="submit" class="inline-flex items-center gap-1.5 bg-apple-blue hover:bg-apple-blueHover text-white px-4 py-2 rounded-lg text-[12.5px] font-semibold transition shadow-apple-sm cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Simpan Seluruh Pengaturan</span>
            </button>
        </div>

    </form>
</div>

<!-- Vanilla JavaScript for Tab Switching, FAQ Rule Builder, Multi-Contact Channels, & Color Sync -->
<script>
    window.BT = {{ \Illuminate\Support\Js::from([
        'faqRules' => $botRules,
        'socialChannels' => $socialChannelsList,
        'welcomeOptions' => $widgetSetting->bot_welcome_options ?? [],
        'botTree' => $widgetSetting->bot_tree ?? [],
        'soundCustomUrl' => $widgetSetting->sound_custom_url,
        'widgetSoundCustomUrl' => $widgetSetting->widget_sound_custom_url,
        'routes' => [
            'testTelegram' => route('admin.integrations.test-telegram', $project->id),
            'setWebhook' => route('admin.integrations.set-telegram-webhook', $project->id),
        ],
        'csrf' => csrf_token(),
        'webhookUrl' => url('/api/v1/telegram/webhook'),
        'mascot' => ['id' => $widgetSetting->mascot_id ?? 'fox', 'size' => (int) ($widgetSetting->mascot_size ?? 72), 'tracking' => (bool) ($widgetSetting->mascot_tracking ?? true)],
        'initialTab' => request('tab', 'bot'),
    ]) }};
</script>
<script src="@asset('js/integration/core.js')"></script>
<script src="@asset('js/integration/sound.js')"></script>
<script src="@asset('js/integration/hours-telegram.js')"></script>
<script src="@asset('js/integration/bot-tree.js')"></script>
<script src="@asset('js/integration/bot-tree-edit.js')"></script>
<script src="@asset('js/integration/bot-rules.js')"></script>
<script src="@asset('js/integration/social.js')"></script>
<script src="@asset('js/integration/ui.js')"></script>
<script src="@asset('js/integration/mascot.js')"></script>
<script src="@asset('js/integration/boot.js')"></script>
@endsection
