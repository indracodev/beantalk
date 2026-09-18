@extends('layouts.admin')

@section('title', 'Pengaturan Integrasi: ' . $project->name)
@section('header_title', 'Pengaturan Integrasi: ' . $project->name)

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 bg-apple-canvas/30 w-full">

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

        <!-- Sticky / Bottom Form Action Bar -->
        <div class="bg-white border border-apple-border rounded-xl p-3.5 sm:p-4 shadow-apple-sm flex items-center justify-between gap-3 sticky bottom-4 z-20">
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
// Initial Data from Backend
let currentFaqRules = @json($botRules);
if (!Array.isArray(currentFaqRules)) {
    currentFaqRules = [];
}

let currentSocialChannels = @json($socialChannelsList);
if (!Array.isArray(currentSocialChannels)) {
    currentSocialChannels = [];
}

let currentWelcomeOptions = @json($widgetSetting->bot_welcome_options ?? []);
if (!Array.isArray(currentWelcomeOptions)) {
    currentWelcomeOptions = [];
}

let currentBotTree = @json($widgetSetting->bot_tree ?? []);
if (!Array.isArray(currentBotTree)) {
    currentBotTree = [];
}

// Ensure every node in tree has a unique ID and children array
function ensureTreeIds(nodes) {
    if (!Array.isArray(nodes)) return [];
    nodes.forEach((node, idx) => {
        if (!node.id) {
            node.id = 'node_' + Math.random().toString(36).substr(2, 7) + '_' + (Date.now() + idx);
        }
        if (node.children && Array.isArray(node.children)) {
            ensureTreeIds(node.children);
        } else {
            node.children = [];
        }
    });
    return nodes;
}
ensureTreeIds(currentBotTree);

let collapsedNodes = new Set();
let draggedNodeId = null;

const PLATFORM_OPTIONS = [
    { value: 'whatsapp', name: 'WhatsApp', placeholder: '08123456789 atau https://wa.me/...', badgeClass: 'bg-emerald-100 text-emerald-800' },
    { value: 'instagram', name: 'Instagram', placeholder: '@username atau https://instagram.com/...', badgeClass: 'bg-pink-100 text-pink-800' },
    { value: 'threads', name: 'Threads', placeholder: '@username atau https://threads.net/@...', badgeClass: 'bg-neutral-900 text-white' },
    { value: 'x', name: 'X (Twitter)', placeholder: '@username atau https://x.com/...', badgeClass: 'bg-neutral-900 text-white' },
    { value: 'facebook', name: 'Facebook', placeholder: 'username atau https://facebook.com/...', badgeClass: 'bg-blue-100 text-blue-800' },
    { value: 'tiktok', name: 'TikTok', placeholder: '@username atau https://tiktok.com/@...', badgeClass: 'bg-neutral-900 text-white' },
    { value: 'youtube', name: 'YouTube', placeholder: '@channel atau https://youtube.com/@...', badgeClass: 'bg-red-100 text-red-800' },
    { value: 'telegram', name: 'Telegram', placeholder: '@username atau https://t.me/...', badgeClass: 'bg-sky-100 text-sky-800' },
    { value: 'shopee', name: 'Shopee Store', placeholder: 'https://shopee.co.id/...', badgeClass: 'bg-orange-100 text-orange-800' },
    { value: 'tokopedia', name: 'Tokopedia Store', placeholder: 'https://tokopedia.com/...', badgeClass: 'bg-green-100 text-green-800' },
    { value: 'custom', name: 'Custom Link / Website', placeholder: 'https://...', badgeClass: 'bg-slate-100 text-slate-800' }
];

const BRAND_ICONS_SVG = {
    whatsapp: `<svg class="w-4 h-4 text-[#25D366]" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.53c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.03-1.25-.75-.67-1.26-1.5-1.41-1.75-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.44.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.12-.23-.19-.48-.32z"/></svg>`,
    instagram: `<svg class="w-4 h-4 text-[#E1306C]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>`,
    threads: `<svg class="w-4 h-4 text-neutral-900" viewBox="0 0 24 24" fill="currentColor"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017C1.5 8.418 2.35 5.564 3.995 3.516 5.845 1.211 8.598.03 12.179.006h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.592 12c.024 3.088.715 5.5 2.054 7.164 1.43 1.778 3.63 2.691 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.34-.779-.963-1.4-1.785-1.816a9.93 9.93 0 0 1-.367 2.36c-.495 1.595-1.4 2.726-2.614 3.266-.966.43-2.11.5-3.201.198-1.266-.35-2.297-1.163-2.903-2.292-.507-.943-.726-2.12-.617-3.312.2-2.193 1.567-3.882 3.76-4.642.89-.309 1.832-.416 2.77-.37a9.04 9.04 0 0 1 1.588.191c-.07-.48-.172-.94-.32-1.37-.483-1.397-1.378-2.2-2.658-2.39-1.12-.166-2.24.092-3.138.725l-1.17-1.638c1.258-.886 2.77-1.27 4.278-1.07 1.944.258 3.382 1.452 4.086 3.39.258.71.42 1.5.487 2.37.654.265 1.238.595 1.74.997 1.176.94 1.926 2.277 2.17 3.868.335 2.18-.263 4.585-1.734 6.395C18.6 22.465 15.847 23.977 12.186 24zM10.57 14.545c-.076.835.06 1.576.383 2.177.382.71 1.003 1.175 1.747 1.381.672.186 1.378.14 1.978-.127.777-.345 1.383-1.117 1.748-2.29.265-.854.374-1.79.326-2.678-.94-.31-1.96-.416-2.96-.316-1.578.184-2.838 1.07-2.986 2.553l-.004.044-.002.024-.002.017.005-.037-.002.015-.002.015.002-.015z"/></svg>`,
    x: `<svg class="w-4 h-4 text-neutral-900" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`,
    twitter: `<svg class="w-4 h-4 text-neutral-900" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`,
    facebook: `<svg class="w-4 h-4 text-[#1877F2]" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>`,
    messenger: `<svg class="w-4 h-4 text-[#1877F2]" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>`,
    tiktok: `<svg class="w-4 h-4 text-neutral-900" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>`,
    youtube: `<svg class="w-4 h-4 text-[#FF0000]" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>`,
    telegram: `<svg class="w-4 h-4 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/></svg>`,
    shopee: `<svg class="w-4 h-4 text-[#EE4D2D]" viewBox="0 0 24 24" fill="currentColor"><path d="M19.78 7.37c-.36-.58-.87-1.04-1.53-1.37-.66-.33-1.42-.5-2.28-.5H8.03c-.86 0-1.62.17-2.28.5-.66.33-1.17.79-1.53 1.37-.36.58-.54 1.25-.54 2.01v9.23c0 .76.18 1.43.54 2.01.36.58.87 1.04 1.53 1.37.66.33 1.42.5 2.28.5h7.94c.86 0 1.62-.17 2.28-.5.66-.33 1.17-.79 1.53-1.37.36-.58.54-1.25.54-2.01V9.38c0-.76-.18-1.43-.54-2.01zm-7.78-4.87c1.38 0 2.5.89 2.78 2.1h-5.56c.28-1.21 1.4-2.1 2.78-2.1z"/></svg>`,
    tokopedia: `<svg class="w-4 h-4 text-[#03AC0E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>`,
    custom: `<svg class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>`
};

function getSocialPreviewIconHtml(chan, platform) {
    if (chan && chan.icon_type === 'custom' && chan.custom_icon) {
        const custom = (chan.custom_icon || '').trim();
        if (custom) {
            if (custom.startsWith('<svg') && custom.endsWith('</svg>')) {
                return custom;
            }
            return `<img src="${escapeHtml(custom)}" alt="icon" class="w-4 h-4 object-contain rounded" onerror="this.onerror=null; this.parentElement.innerHTML=BRAND_ICONS_SVG['${platform}'] || BRAND_ICONS_SVG.custom;" />`;
        }
    }
    const p = platform || 'whatsapp';
    return BRAND_ICONS_SVG[p] || BRAND_ICONS_SVG.custom;
}

// 1. Tab Switching Function (Zero dependencies, Bulletproof inline display toggle)
function switchDetailTab(tabName) {
    const tabs = ['bot', 'appearance', 'mascot', 'social', 'embed', 'telegram', 'hours', 'sound'];
    
    // Save to hidden input and update URL
    const hiddenTab = document.getElementById('hiddenActiveTab');
    if (hiddenTab) hiddenTab.value = tabName;
    try {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState(null, '', url.toString());
    } catch (e) {}

    tabs.forEach(t => {
        const pane = document.getElementById('tab-pane-' + t);
        const btn = document.getElementById('tab-btn-' + t);
        
        if (pane) {
            pane.style.display = (t === tabName) ? 'flex' : 'none';
        }
        
        if (btn) {
            if (t === tabName) {
                if (t === 'bot') {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-purple-600 text-white shadow-2xs cursor-pointer';
                } else if (t === 'mascot') {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-amber-500 text-white shadow-2xs cursor-pointer';
                } else if (t === 'telegram') {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-sky-600 text-white shadow-2xs cursor-pointer';
                } else if (t === 'hours') {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-amber-600 text-white shadow-2xs cursor-pointer';
                } else if (t === 'sound') {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-rose-600 text-white shadow-2xs cursor-pointer';
                } else {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-apple-blue text-white shadow-2xs cursor-pointer';
                }
            } else {
                btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer';
            }
        }
    });

    if (tabName === 'mascot') {
        setTimeout(initAdminMascotSandbox, 50);
    }
}

// 1.05 Sound Alert Configuration Helpers
let testSoundTimer = null;
let testSoundCountdownInterval = null;

function toggleSoundSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('soundToggleLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const duration = document.getElementById('inputSoundDuration')?.value || 15;

    if (label) {
        label.innerText = isChecked ? 'Suara Aktif (ON)' : 'Suara Hening (MUTE)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-rose-700' : 'text-neutral-500');
    }
    if (badgeTab) {
        badgeTab.innerText = isChecked ? (duration + 's') : 'Mute';
        badgeTab.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold ' + (isChecked ? 'bg-rose-100 text-rose-800' : 'bg-neutral-100 text-neutral-600');
    }
}

function setSoundDuration(sec) {
    const input = document.getElementById('inputSoundDuration');
    const preview = document.getElementById('previewCurrentDurationLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const isMasterOn = document.getElementById('checkboxSoundEnabled')?.checked;

    if (input) input.value = sec;
    if (preview) preview.innerText = sec + ' Detik';
    if (badgeTab && isMasterOn) badgeTab.innerText = sec + 's';
}

document.getElementById('inputSoundDuration')?.addEventListener('input', function() {
    let val = parseInt(this.value, 10);
    if (isNaN(val) || val < 1) val = 1;
    const preview = document.getElementById('previewCurrentDurationLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const isMasterOn = document.getElementById('checkboxSoundEnabled')?.checked;

    if (preview) preview.innerText = val + ' Detik';
    if (badgeTab && isMasterOn) badgeTab.innerText = val + 's';
});

function previewAgentSound(type, e, isButton = false) {
    if (e && isButton) {
        e.stopPropagation();
        e.preventDefault();
    }
    const radio = document.querySelector(`input[name="sound_type"][value="${type}"]`);
    if (radio) {
        radio.checked = true;
        updateSoundOptionCardsHighlight(type);
    }
    const containerUpload = document.getElementById('containerCustomSoundUpload');
    if (containerUpload) {
        if (type === 'custom') {
            containerUpload.classList.remove('hidden');
        } else {
            containerUpload.classList.add('hidden');
        }
    }
    let customUrl = null;
    if (type === 'custom') {
        customUrl = window._pendingCustomSoundBlobUrl || @json($widgetSetting->sound_custom_url ?? null);
    }
    if (window.BeanTalkAudio && typeof window.BeanTalkAudio.preview === 'function') {
        window.BeanTalkAudio.preview(type, customUrl);
    }
}

function onSoundTypeChanged(selectedType) {
    previewAgentSound(selectedType, null, false);
}

function updateSoundOptionCardsHighlight(activeType) {
    document.querySelectorAll('.sound-option-card').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.value === activeType) {
            card.className = 'sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-rose-500 bg-rose-50/30 ring-2 ring-rose-500/20' + (activeType === 'custom' ? ' md:col-span-2' : '');
        } else {
            card.className = 'sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-apple-border hover:border-rose-300 bg-white' + (card.querySelector('input[value="custom"]') ? ' md:col-span-2' : '');
        }
    });
}

function onCustomFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const blobUrl = URL.createObjectURL(file);
        window._pendingCustomSoundBlobUrl = blobUrl;
    }
}

function testPlayCurrentSound() {
    testStopCurrentSound();

    const selectedRadio = document.querySelector('input[name="sound_type"]:checked');
    const soundType = selectedRadio ? selectedRadio.value : 'pedestrian';
    let duration = parseInt(document.getElementById('inputSoundDuration')?.value, 10);
    if (isNaN(duration) || duration < 1) duration = 15;

    let customUrl = null;
    if (soundType === 'custom') {
        customUrl = window._pendingCustomSoundBlobUrl || @json($widgetSetting->sound_custom_url ?? null);
    }

    if (!window.BeanTalkAudio) {
        alert('Modul audio BeanTalk belum dimuat. Silakan refresh halaman.');
        return;
    }

    const btnPlay = document.getElementById('btnTestPlaySound');
    const btnStop = document.getElementById('btnTestStopSound');
    const banner = document.getElementById('bannerSoundTestingStatus');
    const labelCountdown = document.getElementById('labelTestingCountdown');

    if (btnPlay) {
        btnPlay.disabled = true;
        btnPlay.classList.add('opacity-50', 'pointer-events-none');
    }
    if (btnStop) {
        btnStop.disabled = false;
        btnStop.className = 'inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-lg text-[12px] font-semibold transition cursor-pointer shadow-apple-sm';
    }
    if (banner) {
        banner.classList.remove('hidden');
    }

    let remainingSeconds = duration;
    if (labelCountdown) labelCountdown.innerText = remainingSeconds;

    testSoundCountdownInterval = setInterval(() => {
        remainingSeconds -= 1;
        if (labelCountdown) labelCountdown.innerText = remainingSeconds;
        if (remainingSeconds <= 0) {
            testStopCurrentSound();
        }
    }, 1000);

    window.BeanTalkAudio.play(soundType, duration, customUrl);

    testSoundTimer = setTimeout(() => {
        testStopCurrentSound();
    }, duration * 1000);
}

function testStopCurrentSound() {
    if (testSoundTimer) {
        clearTimeout(testSoundTimer);
        testSoundTimer = null;
    }
    if (testSoundCountdownInterval) {
        clearInterval(testSoundCountdownInterval);
        testSoundCountdownInterval = null;
    }

    if (window.BeanTalkAudio) {
        window.BeanTalkAudio.stop();
    }

    const btnPlay = document.getElementById('btnTestPlaySound');
    const btnStop = document.getElementById('btnTestStopSound');
    const banner = document.getElementById('bannerSoundTestingStatus');

    if (btnPlay) {
        btnPlay.disabled = false;
        btnPlay.classList.remove('opacity-50', 'pointer-events-none');
    }
    if (btnStop) {
        btnStop.disabled = true;
        btnStop.className = 'inline-flex items-center gap-2 bg-neutral-200 text-neutral-400 px-3.5 py-2 rounded-lg text-[12px] font-semibold transition cursor-not-allowed';
    }
    if (banner) {
        banner.classList.add('hidden');
    }
}

// 1.06 Customer Bubble Chat Sound Helpers
function toggleWidgetSoundSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('widgetSoundToggleLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const agentDuration = document.getElementById('inputSoundDuration')?.value || 15;
    const isAgentOn = document.getElementById('checkboxSoundEnabled')?.checked;

    if (label) {
        label.innerText = isChecked ? 'Suara Bubble ON' : 'Suara Bubble MUTE';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-purple-700' : 'text-neutral-500');
    }
    if (badgeTab) {
        const agentTxt = isAgentOn ? (agentDuration + 's') : 'Mute';
        const custTxt = isChecked ? 'ON' : 'OFF';
        badgeTab.innerText = `Agent: ${agentTxt} • Cust: ${custTxt}`;
    }
}

function previewWidgetSound(type, e, isButton = false) {
    if (e && isButton) {
        e.stopPropagation();
        e.preventDefault();
    }
    const radio = document.querySelector(`input[name="widget_sound_type"][value="${type}"]`);
    if (radio) {
        radio.checked = true;
        updateWidgetSoundOptionCardsHighlight(type);
    }
    const containerUpload = document.getElementById('containerWidgetCustomSoundUpload');
    if (containerUpload) {
        if (type === 'custom') {
            containerUpload.classList.remove('hidden');
        } else {
            containerUpload.classList.add('hidden');
        }
    }
    let customUrl = null;
    if (type === 'custom') {
        customUrl = window._pendingWidgetCustomSoundBlobUrl || @json($widgetSetting->widget_sound_custom_url ?? null);
    }
    if (window.BeanTalkAudio && typeof window.BeanTalkAudio.preview === 'function') {
        window.BeanTalkAudio.preview(type, customUrl);
    }
}

function onWidgetSoundTypeChanged(selectedType) {
    previewWidgetSound(selectedType, null, false);
}

function updateWidgetSoundOptionCardsHighlight(activeType) {
    document.querySelectorAll('.widget-sound-option-card').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.value === activeType) {
            card.className = 'widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-purple-500 bg-purple-50/30 ring-2 ring-purple-500/20' + (activeType === 'custom' ? ' md:col-span-2' : '');
        } else {
            card.className = 'widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-apple-border hover:border-purple-300 bg-white' + (card.querySelector('input[value="custom"]') ? ' md:col-span-2' : '');
        }
    });
}

function onWidgetCustomFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const blobUrl = URL.createObjectURL(file);
        window._pendingWidgetCustomSoundBlobUrl = blobUrl;
    }
}

function testPlayCurrentWidgetSound() {
    const selectedRadio = document.querySelector('input[name="widget_sound_type"]:checked');
    const soundType = selectedRadio ? selectedRadio.value : 'chime';

    let customUrl = null;
    if (soundType === 'custom') {
        customUrl = window._pendingWidgetCustomSoundBlobUrl || @json($widgetSetting->widget_sound_custom_url ?? null);
    }

    if (!window.BeanTalkAudio || typeof window.BeanTalkAudio.playVisitorSound !== 'function') {
        alert('Modul audio BeanTalk belum siap. Silakan refresh halaman.');
        return;
    }

    const btnPlay = document.getElementById('btnTestPlayWidgetSound');
    if (btnPlay) {
        const origHtml = btnPlay.innerHTML;
        btnPlay.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
            <span>Memutar Suara Customer...</span>
        `;
        btnPlay.classList.add('opacity-90');
        setTimeout(() => {
            btnPlay.innerHTML = origHtml;
            btnPlay.classList.remove('opacity-90');
        }, 800);
    }

    window.BeanTalkAudio.playVisitorSound(soundType, customUrl);
}

// 1.1 Business Hours Toggle & Day Row Controls
function toggleHoursSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('hoursToggleLabel');
    const badgeTab = document.getElementById('badgeTabHoursStatus');
    const badgeHeader = document.getElementById('badgeHeaderHoursStatus');

    if (label) {
        label.innerText = isChecked ? 'Jam Kerja Aktif (ON)' : 'Jam Kerja Nonaktif (24/7 Selalu Buka)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-amber-700' : 'text-neutral-500');
    }
    if (badgeTab) {
        badgeTab.innerText = isChecked ? 'Aktif' : '24/7';
        badgeTab.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold ' + (isChecked ? 'bg-amber-100 text-amber-800' : 'bg-neutral-100 text-neutral-600');
    }
    if (badgeHeader) {
        badgeHeader.innerText = isChecked ? '⏰ Jam Kerja: Aktif' : '⏰ Jam Kerja: 24/7';
        badgeHeader.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold ' + (isChecked ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-neutral-100 text-neutral-600 border border-neutral-200');
    }
}

function toggleDayRow(dayKey, isChecked) {
    const badge = document.getElementById('badge-day-' + dayKey);
    const times = document.getElementById('times-day-' + dayKey);
    const row = document.getElementById('row-day-' + dayKey);

    if (badge) {
        badge.innerText = isChecked ? 'Buka Operasional' : 'Libur / Tutup';
        badge.className = 'block text-[10.5px] font-semibold ' + (isChecked ? 'text-emerald-700' : 'text-neutral-400');
    }
    if (times) {
        if (isChecked) {
            times.classList.remove('opacity-40', 'pointer-events-none');
        } else {
            times.classList.add('opacity-40', 'pointer-events-none');
        }
    }
    if (row) {
        if (isChecked) {
            row.className = 'p-3 rounded-xl border transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border-apple-border';
        } else {
            row.className = 'p-3 rounded-xl border transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-neutral-50/70 border-neutral-200';
        }
    }
}

function applyHoursPreset(preset) {
    const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    days.forEach(d => {
        const checkbox = document.querySelector(`.day-checkbox[data-day="${d}"]`);
        const startInput = document.querySelector(`.day-start-input[data-day="${d}"]`);
        const endInput = document.querySelector(`.day-end-input[data-day="${d}"]`);

        if (!checkbox || !startInput || !endInput) return;

        if (preset === 'office') {
            const isWorkday = (d !== 'sat' && d !== 'sun');
            checkbox.checked = isWorkday;
            startInput.value = '08:00';
            endInput.value = '17:00';
            toggleDayRow(d, isWorkday);
        } else if (preset === 'retail') {
            const isWorkday = (d !== 'sun');
            checkbox.checked = isWorkday;
            startInput.value = '09:00';
            endInput.value = '21:00';
            toggleDayRow(d, isWorkday);
        } else if (preset === 'always') {
            checkbox.checked = true;
            startInput.value = '00:00';
            endInput.value = '23:59';
            toggleDayRow(d, true);
        }
    });

    const masterCheckbox = document.getElementById('checkboxHoursEnabled');
    if (masterCheckbox && !masterCheckbox.checked) {
        masterCheckbox.checked = true;
        toggleHoursSwitch(masterCheckbox);
    }
}

// Telegram Test Connection Helper
function testTelegramConnection() {
    const btn = document.getElementById('btnTestTelegram');
    const resultBox = document.getElementById('telegramTestResult');
    const tokenInput = document.getElementById('inputTelegramBotToken');
    const chatInput = document.getElementById('inputTelegramChatId');

    const token = tokenInput ? tokenInput.value.trim() : '';
    const chatId = chatInput ? chatInput.value.trim() : '';

    if (!token || !chatId) {
        if (resultBox) {
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-amber-50 border border-amber-200 text-amber-800';
            resultBox.innerHTML = '⚠️ Mohon isi <strong>Telegram Bot Token</strong> dan <strong>Chat/Group ID</strong> terlebih dahulu.';
            resultBox.classList.remove('hidden');
        }
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">⏳</span> Menguji Koneksi...';
    }

    fetch('{{ route("admin.integrations.test-telegram", $project->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            telegram_bot_token: token,
            telegram_chat_id: chatId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.62 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.89 8.01-3.46 3.82-1.6 4.61-1.88 5.14-1.89.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.19-.04.35z"/></svg> <span>⚡ Test Kirim Pesan ke Telegram</span>';
        }

        if (resultBox) {
            resultBox.classList.remove('hidden');
            if (data.success) {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-emerald-50 border border-emerald-200 text-emerald-800';
                resultBox.innerHTML = '✅ <strong>' + data.message + '</strong>';
            } else {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
                resultBox.innerHTML = '❌ <strong>Gagal:</strong> ' + data.message;
            }
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>⚡ Test Kirim Pesan ke Telegram</span>';
        }
        if (resultBox) {
            resultBox.classList.remove('hidden');
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
            resultBox.innerHTML = '❌ Gagal mengirim request pengujian: ' + err.message;
        }
    });
}

// 1.2 Direct One-Click Telegram Webhook Registration
function setTelegramWebhookDirectly() {
    const token = document.getElementById('inputTelegramBotToken')?.value.trim();
    const btn = document.getElementById('btnSetWebhook');
    const resultBox = document.getElementById('telegramWebhookResult');

    if (!token) {
        if (resultBox) {
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-amber-50 border border-amber-200 text-amber-800';
            resultBox.innerHTML = '⚠️ Mohon isi <strong>Telegram Bot Token</strong> terlebih dahulu.';
            resultBox.classList.remove('hidden');
        }
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">⏳</span> Mendaftarkan Webhook...';
    }

    fetch('{{ route("admin.integrations.set-telegram-webhook", $project->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            telegram_bot_token: token,
            webhook_url: '{{ url("/api/v1/telegram/webhook") }}'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>⚡ Daftarkan Webhook Otomatis</span>';
        }

        if (resultBox) {
            resultBox.classList.remove('hidden');
            if (data.success) {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-emerald-50 border border-emerald-200 text-emerald-800';
                resultBox.innerHTML = '✅ <strong>' + data.message + '</strong>';
            } else {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
                resultBox.innerHTML = '❌ <strong>Gagal:</strong> ' + data.message;
            }
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>⚡ Daftarkan Webhook Otomatis</span>';
        }
        if (resultBox) {
            resultBox.classList.remove('hidden');
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
            resultBox.innerHTML = '❌ Gagal mendaftarkan webhook: ' + err.message;
        }
    });
}

// Update manual link dynamically when token changes
document.getElementById('inputTelegramBotToken')?.addEventListener('input', function() {
    const token = this.value.trim();
    const link = document.getElementById('linkManualWebhook');
    if (link) {
        link.href = 'https://api.telegram.org/bot' + encodeURIComponent(token) + '/setWebhook?url=' + encodeURIComponent('{{ url("/api/v1/telegram/webhook") }}');
    }
});

// Real-time Telegram toggle badge updater
function updateTelegramToggleBadges() {
    const notifChecked = document.getElementById('toggleTelegramNotif')?.checked;
    const topicChecked = document.getElementById('toggleTelegramTopic')?.checked;

    const notifBadge = document.getElementById('badgeNotifToggle');
    const topicBadge = document.getElementById('badgeTopicToggle');
    const cardStatus = document.getElementById('cardTelegramStatus');

    if (notifBadge) {
        notifBadge.className = notifChecked 
            ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800'
            : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600';
        notifBadge.textContent = notifChecked ? 'ON (Aktif)' : 'OFF';
    }

    if (topicBadge) {
        topicBadge.className = topicChecked 
            ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800'
            : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600';
        topicBadge.textContent = topicChecked ? 'ON (Aktif)' : 'OFF';
    }

    if (cardStatus) {
        cardStatus.className = topicChecked 
            ? 'text-[13px] font-bold text-sky-600 mt-0.5 flex items-center gap-1.5'
            : 'text-[13px] font-bold text-neutral-500 mt-0.5 flex items-center gap-1.5';
        cardStatus.innerHTML = '<span class="w-2 h-2 rounded-full ' + (topicChecked ? 'bg-sky-500' : 'bg-neutral-400') + '"></span>' + (topicChecked ? 'Mode Topik ON' : 'Mode Topik OFF');
    }
}

// ============================================================
// 1.5 SMART BOT SUB-TABS & DECISION TREE MANAGER
// ============================================================

// Switch between Sub-Tab 1 (Query) and Sub-Tab 2 (Options)
function switchBotSubTab(subTab) {
    const queryPane = document.getElementById('pane-botsubtab-query');
    const optionsPane = document.getElementById('pane-botsubtab-options');
    const queryBtn = document.getElementById('btn-botsubtab-query');
    const optionsBtn = document.getElementById('btn-botsubtab-options');

    if (subTab === 'query') {
        if (queryPane) queryPane.style.display = 'flex';
        if (optionsPane) optionsPane.style.display = 'none';
        if (queryBtn) {
            queryBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer bg-purple-600 text-white shadow-2xs';
        }
        if (optionsBtn) {
            optionsBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas';
        }
    } else {
        if (queryPane) queryPane.style.display = 'none';
        if (optionsPane) optionsPane.style.display = 'flex';
        if (optionsBtn) {
            optionsBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer bg-emerald-600 text-white shadow-2xs';
        }
        if (queryBtn) {
            queryBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas';
        }
    }
}

// Update badges and dots for independent sub-tab toggles
function updateSubTabToggleStates() {
    const isQuery = Boolean(document.getElementById('toggleBotModeQuery')?.checked);
    const isOptions = Boolean(document.getElementById('toggleBotModeOptions')?.checked);

    const dotQuery = document.getElementById('dotQueryStatus');
    const dotOptions = document.getElementById('dotOptionsStatus');
    const badgeQuery = document.getElementById('badgeQueryActiveLabel');
    const badgeOptions = document.getElementById('badgeOptionsActiveLabel');

    if (dotQuery) {
        dotQuery.className = 'w-2 h-2 rounded-full ' + (isQuery ? 'bg-emerald-300 animate-pulse' : 'bg-neutral-300');
    }
    if (dotOptions) {
        dotOptions.className = 'w-2 h-2 rounded-full ' + (isOptions ? 'bg-emerald-300 animate-pulse' : 'bg-neutral-300');
    }

    if (badgeQuery) {
        badgeQuery.className = 'px-2 py-0.5 rounded-full text-[10.5px] font-bold ' + (isQuery ? 'bg-blue-100 text-blue-700' : 'bg-neutral-100 text-neutral-500');
        badgeQuery.textContent = isQuery ? 'Aktif (ON)' : 'Nonaktif (OFF)';
    }

    if (badgeOptions) {
        badgeOptions.className = 'px-2 py-0.5 rounded-full text-[10.5px] font-bold ' + (isOptions ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-neutral-500');
        badgeOptions.textContent = isOptions ? 'Aktif (ON)' : 'Nonaktif (OFF)';
    }

    updateBotModeBadge();
}

function updateBotModeBadge() {
    const isQuery = Boolean(document.getElementById('toggleBotModeQuery')?.checked);
    const isOptions = Boolean(document.getElementById('toggleBotModeOptions')?.checked);
    const badge = document.getElementById('badgeBotModeStatus');
    if (!badge) return;

    if (isQuery && isOptions) {
        badge.innerText = '⚡ Mode Hybrid (Query + Opsi)';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-100 text-purple-700';
    } else if (isOptions) {
        badge.innerText = '🔘 Hanya Mode Opsi';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-800';
    } else if (isQuery) {
        badge.innerText = '💬 Hanya Mode Query';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-100 text-blue-800';
    } else {
        badge.innerText = '⚠️ Nonaktif (Hanya CS Handoff)';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-neutral-100 text-neutral-600';
    }
}

// Tree Traversal & Search Helper
function findNodeAndParent(nodes, targetId, parent = null) {
    if (!Array.isArray(nodes)) return null;
    for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].id === targetId) {
            return { node: nodes[i], parent: parent, index: i, siblings: nodes };
        }
        if (nodes[i].children && nodes[i].children.length > 0) {
            const res = findNodeAndParent(nodes[i].children, targetId, nodes[i]);
            if (res) return res;
        }
    }
    return null;
}

function countTotalNodes(nodes) {
    let count = 0;
    if (!Array.isArray(nodes)) return count;
    nodes.forEach(n => {
        count += 1;
        if (n.children && n.children.length > 0) {
            count += countTotalNodes(n.children);
        }
    });
    return count;
}

// Visual Decision Tree Renderer
function renderDecisionTree(filterText = '') {
    const container = document.getElementById('treeContainer');
    const emptyState = document.getElementById('treeEmptyState');
    const badgeStats = document.getElementById('badgeTreeStats');

    if (!container) return;
    container.innerHTML = '';

    const totalNodes = countTotalNodes(currentBotTree);
    if (badgeStats) badgeStats.innerText = totalNodes + ' Cabang';

    if (!Array.isArray(currentBotTree) || currentBotTree.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
        serializeBotTree();
        return;
    }

    if (emptyState) emptyState.classList.add('hidden');

    const cleanFilter = filterText ? filterText.trim().toLowerCase() : '';

    function renderBranch(nodes, depth = 0, parentNode = null) {
        nodes.forEach((node, idx) => {
            const hasChildren = node.children && node.children.length > 0;
            const isCollapsed = collapsedNodes.has(node.id);
            
            // Check match filter
            const matchesThis = !cleanFilter || 
                (node.label && node.label.toLowerCase().includes(cleanFilter)) ||
                (node.value && node.value.toLowerCase().includes(cleanFilter)) ||
                (node.response && node.response.toLowerCase().includes(cleanFilter));

            let hasMatchingChild = false;
            if (cleanFilter && hasChildren) {
                const checkChildrenMatch = (ch) => {
                    for (let c of ch) {
                        if ((c.label && c.label.toLowerCase().includes(cleanFilter)) ||
                            (c.value && c.value.toLowerCase().includes(cleanFilter)) ||
                            (c.response && c.response.toLowerCase().includes(cleanFilter))) {
                            return true;
                        }
                        if (c.children && checkChildrenMatch(c.children)) return true;
                    }
                    return false;
                };
                hasMatchingChild = checkChildrenMatch(node.children);
            }

            if (cleanFilter && !matchesThis && !hasMatchingChild) {
                return;
            }

            const card = document.createElement('div');
            card.className = 'tree-node-item flex flex-col gap-1 transition select-none';
            card.id = 'tree-node-' + node.id;
            card.setAttribute('data-node-id', node.id);

            // Layer badge
            let layerBadge = '';
            if (depth === 0) {
                layerBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">Menu Utama (Lvl 1)</span>';
            } else if (depth === 1) {
                layerBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Sub-Menu (Lvl 2)</span>';
            } else {
                layerBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Cabang (Lvl ${depth + 1})</span>`;
            }

            const indentMargin = depth * 28;
            const cleanResponse = (node.response || '').replace(/\s+/g, ' ').trim();
            const responsePreview = cleanResponse.length > 70 ? cleanResponse.substring(0, 70) + '...' : cleanResponse;

            const isFirst = idx === 0;
            const isLast = idx === nodes.length - 1;

            card.innerHTML = `
                <div class="tree-card relative flex flex-col md:flex-row items-start md:items-center justify-between gap-2.5 p-3 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-emerald-400 transition"
                     style="margin-left: ${indentMargin}px;"
                     draggable="true"
                     ondragstart="handleTreeDragStart(event, '${node.id}')"
                     ondragover="handleTreeDragOver(event, '${node.id}')"
                     ondragleave="handleTreeDragLeave(event, '${node.id}')"
                     ondrop="handleTreeDrop(event, '${node.id}')">
                    
                    ${depth > 0 ? `<div class="absolute -left-4 top-1/2 -translate-y-1/2 w-4 h-0.5 bg-neutral-300 pointer-events-none"></div>` : ''}

                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                        <span class="cursor-grab active:cursor-grabbing text-neutral-400 hover:text-neutral-700 font-mono text-[14px] px-1 py-0.5 rounded hover:bg-neutral-100" title="Seret untuk memindahkan posisi / urutan">⠿</span>
                        
                        ${hasChildren ? `
                            <button type="button" onclick="toggleTreeNodeCollapse('${node.id}')" class="w-5 h-5 flex items-center justify-center rounded hover:bg-neutral-100 text-neutral-500 font-mono text-[10px] cursor-pointer" title="${isCollapsed ? 'Buka Sub-Cabang' : 'Tutup Sub-Cabang'}">
                                ${isCollapsed ? '▶' : '▼'}
                            </button>
                        ` : `
                            <span class="w-5 h-5 flex items-center justify-center text-neutral-300 text-[10px]">&bull;</span>
                        `}

                        <div class="flex flex-col gap-0.5 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                ${layerBadge}
                                <span class="font-bold text-[12.5px] text-apple-textPrimary truncate">${escapeHtml(node.label || 'Tanpa Label')}</span>
                                <span class="px-1.5 py-0.2 rounded bg-apple-canvas text-neutral-600 font-mono text-[10.5px] border border-apple-border" title="Trigger Key: ${escapeHtml(node.value || '')}">${escapeHtml(node.value || '-')}</span>
                                ${hasChildren ? `<span class="px-1.5 py-0.2 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold">${node.children.length} Sub-Pilihan</span>` : ''}
                            </div>
                            ${responsePreview ? `
                                <div class="text-[11px] text-apple-textSecondary flex items-center gap-1 truncate max-w-xl">
                                    <span class="text-neutral-400">💬</span>
                                    <span class="truncate">${escapeHtml(responsePreview)}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 self-end md:self-center">
                        <button type="button" onclick="addChildTreeNode('${node.id}')" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-[11px] border border-emerald-200 transition cursor-pointer" title="Tambah Sub-Cabang ke menu ini">
                            <span>➕ Sub-Cabang</span>
                        </button>

                        <button type="button" onclick="openEditTreeNodeModal('${node.id}')" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-apple-canvas/70 hover:bg-apple-canvas text-apple-textPrimary font-semibold text-[11px] border border-apple-border transition cursor-pointer" title="Edit Teks & Jawaban">
                            <span>✏️ Edit</span>
                        </button>

                        <div class="flex items-center rounded-lg border border-apple-border bg-apple-canvas/40 overflow-hidden">
                            <button type="button" onclick="moveTreeNode('${node.id}', 'up')" ${isFirst ? 'disabled' : ''} class="px-1.5 py-1 text-[10px] text-neutral-600 hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent transition cursor-pointer" title="Geser ke Atas">▲</button>
                            <span class="w-[1px] h-3 bg-apple-border"></span>
                            <button type="button" onclick="moveTreeNode('${node.id}', 'down')" ${isLast ? 'disabled' : ''} class="px-1.5 py-1 text-[10px] text-neutral-600 hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent transition cursor-pointer" title="Geser ke Bawah">▼</button>
                        </div>

                        <button type="button" onclick="deleteTreeNode('${node.id}')" class="p-1 rounded-lg text-neutral-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer" title="Hapus Cabang Ini">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(card);

            if (hasChildren && (!isCollapsed || (cleanFilter && hasMatchingChild))) {
                renderBranch(node.children, depth + 1, node);
            }
        });
    }

    renderBranch(currentBotTree, 0, null);
    serializeBotTree();
}

// Drag and Drop Logic
function handleTreeDragStart(e, nodeId) {
    draggedNodeId = nodeId;
    e.dataTransfer.setData('text/plain', nodeId);
    e.dataTransfer.effectAllowed = 'move';
}

function handleTreeDragOver(e, targetNodeId) {
    e.preventDefault();
    if (draggedNodeId === targetNodeId) return;
    const targetCard = e.currentTarget;
    if (targetCard) {
        targetCard.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
    }
}

function handleTreeDragLeave(e, targetNodeId) {
    const targetCard = e.currentTarget;
    if (targetCard) {
        targetCard.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
    }
}

function handleTreeDrop(e, targetNodeId) {
    e.preventDefault();
    const targetCard = e.currentTarget;
    if (targetCard) {
        targetCard.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
    }

    if (!draggedNodeId || draggedNodeId === targetNodeId) return;

    const draggedInfo = findNodeAndParent(currentBotTree, draggedNodeId);
    if (!draggedInfo) return;

    const isDescendant = (parent, checkId) => {
        if (!parent.children || parent.children.length === 0) return false;
        for (let c of parent.children) {
            if (c.id === checkId) return true;
            if (isDescendant(c, checkId)) return true;
        }
        return false;
    };

    if (isDescendant(draggedInfo.node, targetNodeId)) {
        alert('Tidak dapat memindahkan cabang ke dalam sub-cabangnya sendiri!');
        return;
    }

    // Unlink dragged node from current position
    const removedNode = draggedInfo.siblings.splice(draggedInfo.index, 1)[0];

    // Re-insert adjacent to target node
    const targetInfo = findNodeAndParent(currentBotTree, targetNodeId);
    if (targetInfo) {
        targetInfo.siblings.splice(targetInfo.index + 1, 0, removedNode);
    } else {
        currentBotTree.push(removedNode);
    }

    draggedNodeId = null;
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Reorder Up / Down
function moveTreeNode(nodeId, direction) {
    const info = findNodeAndParent(currentBotTree, nodeId);
    if (!info) return;

    const siblings = info.siblings;
    const idx = info.index;

    if (direction === 'up' && idx > 0) {
        const temp = siblings[idx];
        siblings[idx] = siblings[idx - 1];
        siblings[idx - 1] = temp;
    } else if (direction === 'down' && idx < siblings.length - 1) {
        const temp = siblings[idx];
        siblings[idx] = siblings[idx + 1];
        siblings[idx + 1] = temp;
    }

    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Add New Root Node (Layer 1)
function addRootTreeNode() {
    const nextVal = (currentBotTree.length + 1).toString();
    const newNode = {
        id: 'node_' + Math.random().toString(36).substr(2, 7) + '_' + Date.now(),
        label: 'Menu Utama ' + nextVal,
        value: nextVal,
        response: 'Silakan pilih menu di bawah ini:',
        children: []
    };
    currentBotTree.push(newNode);
    renderDecisionTree();
    openEditTreeNodeModal(newNode.id);
}

// Add New Sub-Branch Node
function addChildTreeNode(parentId) {
    const info = findNodeAndParent(currentBotTree, parentId);
    if (!info) return;
    const parent = info.node;
    if (!Array.isArray(parent.children)) parent.children = [];

    const nextVal = (parent.value ? parent.value + '.' : '') + (parent.children.length + 1);
    const newChild = {
        id: 'node_' + Math.random().toString(36).substr(2, 7) + '_' + Date.now(),
        label: 'Sub-Pilihan ' + nextVal,
        value: nextVal,
        response: 'Berikut informasi untuk pilihan ini:',
        children: []
    };
    parent.children.push(newChild);
    collapsedNodes.delete(parentId);
    renderDecisionTree();
    openEditTreeNodeModal(newChild.id);
}

// Delete Node
function deleteTreeNode(nodeId) {
    if (!confirm('Apakah Anda yakin ingin menghapus cabang materi ini beserta seluruh sub-cabangnya?')) return;
    const info = findNodeAndParent(currentBotTree, nodeId);
    if (!info) return;
    info.siblings.splice(info.index, 1);
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Open Edit Modal
function openEditTreeNodeModal(nodeId) {
    const info = findNodeAndParent(currentBotTree, nodeId);
    if (!info) return;
    const node = info.node;

    document.getElementById('modalEditNodeId').value = node.id;
    document.getElementById('modalEditNodeLabel').value = node.label || '';
    document.getElementById('modalEditNodeValue').value = node.value || '';
    document.getElementById('modalEditNodeResponse').value = node.response || '';

    const modal = document.getElementById('modalEditTreeNode');
    if (modal) modal.classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('modalEditNodeLabel')?.focus();
    }, 50);
}

function closeEditTreeNodeModal() {
    const modal = document.getElementById('modalEditTreeNode');
    if (modal) modal.classList.add('hidden');
}

function saveEditedTreeNode() {
    const id = document.getElementById('modalEditNodeId')?.value;
    const label = document.getElementById('modalEditNodeLabel')?.value.trim();
    const val = document.getElementById('modalEditNodeValue')?.value.trim();
    const response = document.getElementById('modalEditNodeResponse')?.value.trim();

    if (!label) {
        alert('Label tombol menu tidak boleh kosong!');
        return;
    }

    const info = findNodeAndParent(currentBotTree, id);
    if (info) {
        info.node.label = label;
        info.node.value = val || label;
        info.node.response = response;
    }

    closeEditTreeNodeModal();
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Expand / Collapse
function toggleTreeNodeCollapse(nodeId) {
    if (collapsedNodes.has(nodeId)) {
        collapsedNodes.delete(nodeId);
    } else {
        collapsedNodes.add(nodeId);
    }
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

function expandAllTreeNodes() {
    collapsedNodes.clear();
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

function collapseAllTreeNodes() {
    collapsedNodes.clear();
    const markCollapsed = (nodes) => {
        nodes.forEach(n => {
            if (n.children && n.children.length > 0) {
                collapsedNodes.add(n.id);
                markCollapsed(n.children);
            }
        });
    };
    markCollapsed(currentBotTree);
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

function filterDecisionTree(query) {
    renderDecisionTree(query);
}

// Tree Synchronization & Compilation
function serializeBotTree() {
    // 1. Simpan struktur Tree utuh ke #hiddenBotTree
    const hiddenTree = document.getElementById('hiddenBotTree');
    if (hiddenTree) {
        hiddenTree.value = JSON.stringify(currentBotTree);
    }

    // 2. Ekstrak Root Level (Layer 1) menjadi Welcome Options
    const rootOptions = currentBotTree.map(n => ({
        label: n.label,
        value: n.value
    }));
    const hiddenWelcome = document.getElementById('hiddenBotWelcomeOptions');
    if (hiddenWelcome) {
        hiddenWelcome.value = JSON.stringify(rootOptions);
    }

    // 3. Compile Tree menjadi Flat Bot Rules (lengkap dengan navigasi opsi tombol)
    const treeRules = [];
    const compileNodeToRule = (node, parentNode = null) => {
        const options = [];
        if (node.children && node.children.length > 0) {
            node.children.forEach(c => {
                options.push({ label: c.label, value: c.value });
            });
            if (parentNode) {
                options.push({ label: '⬅️ Kembali', value: parentNode.value });
            }
            options.push({ label: '🏠 Menu Utama', value: 'MENU' });
        }

        const keywords = [node.value];
        if (node.label) {
            const cleanLabel = node.label.replace(/^[^\w\d]+/, '').trim().toLowerCase();
            if (cleanLabel && !keywords.includes(cleanLabel)) {
                keywords.push(cleanLabel);
            }
        }

        treeRules.push({
            name: node.label,
            keywords: keywords,
            response: node.response || '',
            options: options.length > 0 ? options : undefined
        });

        if (node.children && node.children.length > 0) {
            node.children.forEach(c => compileNodeToRule(c, node));
        }
    };

    currentBotTree.forEach(rootNode => compileNodeToRule(rootNode, null));

    // Tambahkan Menu Utama rule
    treeRules.unshift({
        name: 'Menu Utama',
        keywords: ['menu', 'menu utama', 'bantuan', 'help', 'mulai', 'start', 'halo', 'hai', 'pilihan'],
        response: document.querySelector('input[name="bot_welcome_message"]')?.value || 'Halo! Silakan pilih menu di bawah ini:',
        options: rootOptions
    });

    // Gabungkan dengan currentFaqRules (Mode Query FAQ)
    const combinedRules = [...treeRules];
    if (Array.isArray(currentFaqRules)) {
        currentFaqRules.forEach(faq => {
            if (faq.keywords && faq.response) {
                combinedRules.push(faq);
            }
        });
    }

    const hiddenRules = document.getElementById('hiddenBotRules');
    if (hiddenRules) {
        hiddenRules.value = JSON.stringify(combinedRules);
    }
}

// 2. Render FAQ Rules List
function renderFaqRulesList() {
    const container = document.getElementById('faqRulesListContainer');
    const emptyState = document.getElementById('faqEmptyState');
    const labelCount = document.getElementById('labelRuleCount');
    const badgeTabCount = document.getElementById('badgeTabRuleCount');

    if (!container) return;

    container.innerHTML = '';

    if (!Array.isArray(currentFaqRules) || currentFaqRules.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
    } else {
        if (emptyState) emptyState.classList.add('hidden');

        currentFaqRules.forEach((rule, idx) => {
            const row = document.createElement('div');
            const ruleOptions = Array.isArray(rule.options) ? rule.options : [];
            row.className = 'p-3.5 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-purple-300 transition flex flex-col gap-3';
            
            let optionsSectionHtml = '';
            if (ruleOptions.length > 0) {
                optionsSectionHtml = `
                    <div class="mt-1 pt-2.5 border-t border-apple-border/70 flex flex-col gap-2 bg-apple-canvas/30 p-2.5 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-apple-textSecondary flex items-center gap-1.5">
                                🔘 Tombol Opsi Balasan (${ruleOptions.length})
                            </span>
                            <button type="button" onclick="addOptionToRule(${idx})" class="text-purple-600 hover:text-purple-700 font-semibold text-[10.5px] cursor-pointer">
                                + Tambah Tombol
                            </button>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            ${ruleOptions.map((opt, optIdx) => `
                                <div class="flex items-center gap-2">
                                    <input type="text" value="${escapeHtml(opt.label || '')}" oninput="updateRuleOption(${idx}, ${optIdx}, 'label', this.value)" placeholder="Label Tombol (cth: 📦 Pilihan A)" class="text-[11px] px-2.5 py-1 bg-white border border-apple-border rounded-md flex-1">
                                    <input type="text" value="${escapeHtml(opt.value || '')}" oninput="updateRuleOption(${idx}, ${optIdx}, 'value', this.value)" placeholder="Nilai / Kata Kunci (cth: A)" class="text-[11px] px-2.5 py-1 bg-white border border-apple-border rounded-md w-36">
                                    <button type="button" onclick="removeOptionFromRule(${idx}, ${optIdx})" class="text-neutral-400 hover:text-red-600 p-1 text-[12px] cursor-pointer" title="Hapus Tombol">✕</button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                optionsSectionHtml = `
                    <div class="flex items-center justify-end">
                        <button type="button" onclick="addOptionToRule(${idx})" class="text-[10.5px] font-semibold text-purple-600 hover:text-purple-700 hover:underline flex items-center gap-1 cursor-pointer">
                            <span>+ Pasang Tombol Pilihan Interaktif untuk Jawaban Ini</span>
                        </button>
                    </div>
                `;
            }

            row.innerHTML = `
                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-[11px] font-bold flex items-center justify-center shrink-0">
                        ${idx + 1}
                    </div>
                    <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-12 gap-2.5">
                        <div class="md:col-span-5">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Kata Kunci Pemicu (Koma terpisah)</label>
                            <input type="text" value="${escapeHtml(rule.keywords || '')}" oninput="updateRuleField(${idx}, 'keywords', this.value)" placeholder="Contoh: ongkir, tarif, kurir, jne" class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        </div>
                        <div class="md:col-span-7">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Template Jawaban Bot</label>
                            <textarea oninput="updateRuleField(${idx}, 'response', this.value)" rows="2" placeholder="Tulis jawaban otomatis lengkap untuk customer..." class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 resize-none leading-relaxed">${escapeHtml(rule.response || '')}</textarea>
                        </div>
                    </div>
                    <button type="button" onclick="removeFaqRuleRow(${idx})" class="text-neutral-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition shrink-0 cursor-pointer self-end md:self-center" title="Hapus Aturan FAQ Ini">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
                ${optionsSectionHtml}
            `;
            container.appendChild(row);
        });
    }

    const count = Array.isArray(currentFaqRules) ? currentFaqRules.length : 0;
    if (labelCount) labelCount.innerText = count + ' Aturan';
    if (badgeTabCount) badgeTabCount.innerText = count;
    serializeBotRules();
}

function updateRuleField(index, field, value) {
    if (currentFaqRules[index]) {
        currentFaqRules[index][field] = value;
        serializeBotRules();
    }
}

function addOptionToRule(ruleIdx) {
    if (!currentFaqRules[ruleIdx].options || !Array.isArray(currentFaqRules[ruleIdx].options)) {
        currentFaqRules[ruleIdx].options = [];
    }
    currentFaqRules[ruleIdx].options.push({ label: '', value: '' });
    renderFaqRulesList();
}

function updateRuleOption(ruleIdx, optIdx, field, value) {
    if (currentFaqRules[ruleIdx] && currentFaqRules[ruleIdx].options && currentFaqRules[ruleIdx].options[optIdx]) {
        currentFaqRules[ruleIdx].options[optIdx][field] = value;
        serializeBotRules();
    }
}

function removeOptionFromRule(ruleIdx, optIdx) {
    if (currentFaqRules[ruleIdx] && currentFaqRules[ruleIdx].options && currentFaqRules[ruleIdx].options[optIdx]) {
        currentFaqRules[ruleIdx].options.splice(optIdx, 1);
        if (currentFaqRules[ruleIdx].options.length === 0) {
            delete currentFaqRules[ruleIdx].options;
        }
        renderFaqRulesList();
    }
}

function addNewFaqRuleRow() {
    if (!Array.isArray(currentFaqRules)) {
        currentFaqRules = [];
    }
    currentFaqRules.push({
        keywords: '',
        response: ''
    });
    renderFaqRulesList();

    setTimeout(() => {
        const inputs = document.querySelectorAll('#faqRulesListContainer input');
        if (inputs.length > 0) {
            inputs[inputs.length - 1].focus();
        }
    }, 50);
}

function removeFaqRuleRow(index) {
    if (Array.isArray(currentFaqRules) && currentFaqRules[index]) {
        currentFaqRules.splice(index, 1);
        renderFaqRulesList();
    }
}

function serializeBotRules() {
    const hidden = document.getElementById('hiddenBotRules');
    if (hidden) {
        hidden.value = JSON.stringify(currentFaqRules);
    }
}

// 3. Dynamic Multi-Contact Social Channels Builder
function renderSocialChannelsList() {
    const container = document.getElementById('socialChannelsListContainer');
    const emptyState = document.getElementById('socialChannelsEmptyState');
    const labelCount = document.getElementById('labelSocialCount');
    const badgeTabCount = document.getElementById('badgeTabSocialCount');

    if (!container) return;

    container.innerHTML = '';

    if (!Array.isArray(currentSocialChannels) || currentSocialChannels.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
    } else {
        if (emptyState) emptyState.classList.add('hidden');

        currentSocialChannels.forEach((chan, idx) => {
            const row = document.createElement('div');
            const isEnabled = Boolean(chan.enabled);
            const currentPlatform = (chan.platform || chan.icon || 'whatsapp').toLowerCase();
            const matchedOpt = PLATFORM_OPTIONS.find(o => o.value === currentPlatform) || PLATFORM_OPTIONS[0];
            const isCustomIcon = (chan.icon_type === 'custom') || (Boolean(chan.custom_icon) && chan.icon_type !== 'default');

            row.className = 'p-3.5 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-emerald-300 transition flex flex-col gap-3';
            
            let platformOptionsHtml = '';
            PLATFORM_OPTIONS.forEach(opt => {
                const selected = (opt.value === currentPlatform) ? 'selected' : '';
                platformOptionsHtml += `<option value="${opt.value}" ${selected}>${opt.name}</option>`;
            });

            row.innerHTML = `
                <!-- Baris Utama: Platform, Nama, URL, Status & Hapus -->
                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                    <div class="flex items-center gap-2 shrink-0 self-start md:self-center">
                        <span class="w-6 h-6 rounded-full bg-apple-canvas border border-apple-border text-[11px] font-bold text-apple-textSecondary flex items-center justify-center">
                            ${idx + 1}
                        </span>
                        <div id="socialIconPreview_${idx}" class="w-7 h-7 rounded-lg border border-apple-border bg-apple-canvas/60 flex items-center justify-center shrink-0 shadow-2xs">
                            ${getSocialPreviewIconHtml(chan, currentPlatform)}
                        </div>
                        <select onchange="updateSocialPlatform(${idx}, this.value)" class="text-[11.5px] font-semibold py-1.5 px-2.5 bg-apple-canvas/60 border border-apple-border rounded-lg focus:bg-white focus:outline-none cursor-pointer">
                            ${platformOptionsHtml}
                        </select>
                    </div>

                    <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-12 gap-2.5">
                        <div class="md:col-span-5">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Nama / Label Kontak</label>
                            <input type="text" value="${escapeHtml(chan.name || '')}" oninput="updateSocialChannelField(${idx}, 'name', this.value)" placeholder="Contoh: CS Sales, Akun Resmi, CS Retur..." class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                        <div class="md:col-span-7">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Nomor HP / URL Tautan</label>
                            <input type="text" value="${escapeHtml(chan.url || '')}" oninput="updateSocialChannelField(${idx}, 'url', this.value)" placeholder="${matchedOpt.placeholder}" class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0 self-end md:self-center">
                        <label class="inline-flex items-center gap-1.5 text-[11.5px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer ${isEnabled ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-neutral-100 border-neutral-200 text-neutral-500'}">
                            <input type="checkbox" ${isEnabled ? 'checked' : ''} onchange="updateSocialChannelField(${idx}, 'enabled', this.checked)" class="rounded text-emerald-600 focus:ring-0 cursor-pointer">
                            <span>${isEnabled ? 'Aktif' : 'Nonaktif'}</span>
                        </label>

                        <button type="button" onclick="removeSocialChannel(${idx})" class="text-neutral-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition cursor-pointer" title="Hapus Kontak Ini">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Baris Opsi Icon: Default Asli vs Custom Icon -->
                <div class="pt-2 border-t border-apple-border/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-apple-canvas/20 -mx-3.5 -mb-3.5 p-2.5 px-3.5 rounded-b-xl text-[11px]">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-apple-textSecondary flex items-center gap-1">
                            🎨 Pilihan Icon:
                        </span>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="icon_type_${idx}" value="default" ${!isCustomIcon ? 'checked' : ''} onchange="updateSocialChannelField(${idx}, 'icon_type', 'default')" class="text-apple-blue focus:ring-0">
                            <span class="${!isCustomIcon ? 'font-bold text-apple-textPrimary' : 'text-apple-textSecondary'}">Default Icon Asli (${matchedOpt.name})</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="icon_type_${idx}" value="custom" ${isCustomIcon ? 'checked' : ''} onchange="updateSocialChannelField(${idx}, 'icon_type', 'custom')" class="text-apple-blue focus:ring-0">
                            <span class="${isCustomIcon ? 'font-bold text-apple-textPrimary' : 'text-apple-textSecondary'}">Custom Icon</span>
                        </label>
                    </div>

                    ${isCustomIcon ? `
                        <div class="flex-1 sm:max-w-md flex items-center gap-2">
                            <input type="text" value="${escapeHtml(chan.custom_icon || '')}" oninput="updateSocialChannelField(${idx}, 'custom_icon', this.value)" placeholder="URL Gambar / SVG (cth: https://.../logo.png)" class="w-full text-[11px] px-2.5 py-1 bg-white border border-apple-border rounded-md focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue focus:outline-none">
                        </div>
                    ` : ''}
                </div>
            `;
            container.appendChild(row);
        });
    }

    const activeCount = Array.isArray(currentSocialChannels) ? currentSocialChannels.filter(c => c.enabled).length : 0;
    if (labelCount) labelCount.innerText = activeCount + ' Aktif';
    if (badgeTabCount) badgeTabCount.innerText = activeCount;
    serializeSocialChannels();
}

function updateSocialPlatform(index, newPlatform) {
    if (currentSocialChannels[index]) {
        currentSocialChannels[index].platform = newPlatform;
        currentSocialChannels[index].icon = newPlatform;
        const opt = PLATFORM_OPTIONS.find(o => o.value === newPlatform);
        if (opt && (!currentSocialChannels[index].name || PLATFORM_OPTIONS.some(p => p.name === currentSocialChannels[index].name))) {
            currentSocialChannels[index].name = opt.name;
        }
        renderSocialChannelsList();
    }
}

function updateSocialChannelField(index, field, value) {
    if (currentSocialChannels[index]) {
        currentSocialChannels[index][field] = value;
        // Auto-enable if user entered URL
        if (field === 'url' && value && value.trim().length > 0 && !currentSocialChannels[index].enabled) {
            currentSocialChannels[index].enabled = true;
            renderSocialChannelsList();
            return;
        }
        if (field === 'enabled' || field === 'icon_type') {
            renderSocialChannelsList();
        } else if (field === 'custom_icon') {
            const previewEl = document.getElementById('socialIconPreview_' + index);
            if (previewEl) {
                previewEl.innerHTML = getSocialPreviewIconHtml(currentSocialChannels[index], currentSocialChannels[index].platform);
            }
            serializeSocialChannels();
        } else {
            serializeSocialChannels();
        }
    }
}

function addNewSocialChannel(platform = 'whatsapp', name = '', url = '') {
    if (!Array.isArray(currentSocialChannels)) {
        currentSocialChannels = [];
    }

    const opt = PLATFORM_OPTIONS.find(o => o.value === platform) || PLATFORM_OPTIONS[0];
    const defaultName = name || opt.name;

    currentSocialChannels.push({
        id: platform + '_' + Date.now(),
        platform: platform,
        name: defaultName,
        url: url,
        enabled: true,
        icon: platform,
        icon_type: 'default',
        custom_icon: ''
    });

    renderSocialChannelsList();

    setTimeout(() => {
        const inputs = document.querySelectorAll('#socialChannelsListContainer input[type="text"]');
        if (inputs.length > 0) {
            inputs[inputs.length - 2].focus();
        }
    }, 50);
}

function removeSocialChannel(index) {
    if (Array.isArray(currentSocialChannels) && currentSocialChannels[index]) {
        currentSocialChannels.splice(index, 1);
        renderSocialChannelsList();
    }
}

function serializeSocialChannels() {
    const hidden = document.getElementById('hiddenSocialChannels');
    if (hidden) {
        hidden.value = JSON.stringify(currentSocialChannels);
    }
}

function serializeAllSettings() {
    serializeBotTree();
    serializeBotRules();
    serializeSocialChannels();
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

// 4. Bot Toggle Switch Visual Updates
function toggleBotSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('botToggleLabel');
    const badge = document.getElementById('badgeBotStatus');
    const cardText = document.getElementById('cardBotText');

    if (label) {
        label.innerText = isChecked ? 'Bot Aktif (ON)' : 'Bot Nonaktif (OFF)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-purple-700' : 'text-neutral-500');
    }

    if (badge) {
        badge.innerText = isChecked ? '🤖 Bot: Aktif' : '🤖 Bot: Nonaktif';
        badge.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold ' + (isChecked ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-neutral-100 text-neutral-600 border border-neutral-200');
    }

    if (cardText) {
        cardText.innerHTML = `<span class="w-2 h-2 rounded-full ${isChecked ? 'bg-purple-500' : 'bg-neutral-400'}"></span> ${isChecked ? 'Aktif' : 'Nonaktif'}`;
        cardText.className = 'text-[13px] font-bold ' + (isChecked ? 'text-purple-600' : 'text-neutral-500') + ' mt-0.5 flex items-center gap-1.5';
    }
}

// 4.1 Telegram Master Toggle Switch Visual Updates
function toggleTelegramSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('telegramToggleLabel');
    const cardText = document.getElementById('cardTelegramStatus');

    if (label) {
        label.innerText = isChecked ? 'Telegram Aktif (ON)' : 'Telegram Nonaktif (OFF)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-sky-700' : 'text-neutral-500');
    }

    if (cardText) {
        const topicCheckbox = document.getElementById('toggleTelegramTopic');
        const isTopicOn = topicCheckbox ? topicCheckbox.checked : false;
        cardText.innerHTML = `<span class="w-2 h-2 rounded-full ${isChecked ? 'bg-sky-500' : 'bg-neutral-400'}"></span> ${isChecked ? (isTopicOn ? 'Mode Topik ON' : 'Notif ON') : 'Nonaktif'}`;
        cardText.className = 'text-[13px] font-bold ' + (isChecked ? 'text-sky-600' : 'text-neutral-500') + ' mt-0.5 flex items-center gap-1.5';
    }
}

// 4.2 Telegram Topic Mode Toggle Visual Updates
function toggleTelegramTopicSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const badge = document.getElementById('badgeTopicToggle');
    const cardText = document.getElementById('cardTelegramStatus');

    if (badge) {
        badge.innerText = isChecked ? 'ON (Aktif)' : 'OFF';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold ' + (isChecked ? 'bg-sky-100 text-sky-800' : 'bg-neutral-100 text-neutral-600');
    }

    if (cardText) {
        const masterCheckbox = document.getElementById('checkboxTelegramEnabled');
        const isMasterOn = masterCheckbox ? masterCheckbox.checked : false;
        cardText.innerHTML = `<span class="w-2 h-2 rounded-full ${isChecked ? 'bg-sky-500' : (isMasterOn ? 'bg-sky-500' : 'bg-neutral-400')}"></span> ${isChecked ? 'Mode Topik ON' : (isMasterOn ? 'Notif 1x ON' : 'Nonaktif')}`;
        cardText.className = 'text-[13px] font-bold ' + (isChecked || isMasterOn ? 'text-sky-600' : 'text-neutral-500') + ' mt-0.5 flex items-center gap-1.5';
    }
}

// 5. Brand Color Realtime Sync
function updateBrandColor(hex) {
    if (!hex) return;
    if (!hex.startsWith('#')) hex = '#' + hex;

    const inputColor = document.getElementById('inputColorPicker');
    const inputHex = document.getElementById('inputColorHex');
    const swatch = document.getElementById('previewHeaderSwatch');
    const hexText = document.getElementById('previewHeaderHex');

    if (inputColor) inputColor.value = hex;
    if (inputHex) inputHex.value = hex;
    if (swatch) swatch.style.backgroundColor = hex;
    if (hexText) hexText.innerText = hex;
}

// 6. Copy Code Snippet Helper
function copySnippetText(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text.trim()).then(() => {
        const origHTML = btn.innerHTML;
        btn.innerHTML = '<span class="text-emerald-600 font-bold">✓ Tersalin!</span>';
        setTimeout(() => { btn.innerHTML = origHTML; }, 2000);
    });
}

// 7. Agent Scope & Multi-select Toggle
function toggleAgentScope(scope) {
    const container = document.getElementById('containerAgentChecklist');
    const labelAll = document.getElementById('labelAgentAll');
    const labelSelected = document.getElementById('labelAgentSelected');

    if (scope === 'all') {
        if (container) container.classList.add('hidden');
        if (labelAll) {
            labelAll.classList.add('border-apple-blue', 'bg-blue-50/30');
            labelAll.classList.remove('border-apple-border', 'bg-white');
        }
        if (labelSelected) {
            labelSelected.classList.remove('border-apple-blue', 'bg-blue-50/30');
            labelSelected.classList.add('border-apple-border', 'bg-white');
        }
    } else {
        if (container) container.classList.remove('hidden');
        if (labelSelected) {
            labelSelected.classList.add('border-apple-blue', 'bg-blue-50/30');
            labelSelected.classList.remove('border-apple-border', 'bg-white');
        }
        if (labelAll) {
            labelAll.classList.remove('border-apple-blue', 'bg-blue-50/30');
            labelAll.classList.add('border-apple-border', 'bg-white');
        }
    }
    updateAgentScopeBadge();
}

function updateAgentScopeBadge() {
    const scopeRadio = document.querySelector('input[name="agent_scope"]:checked');
    const badge = document.getElementById('badgeAgentScope');
    if (!badge || !scopeRadio) return;

    if (scopeRadio.value === 'all') {
        badge.innerText = '👥 Semua Agent (All CS)';
        badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto';
    } else {
        const checkedCount = document.querySelectorAll('.agent-checkbox:checked').length;
        badge.innerText = '👤 ' + checkedCount + ' Agent Terpilih';
        badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 self-start sm:self-auto';
    }
}

// 8. Launcher & Mascot Controls
let currentAdminMascotId = '{{ $widgetSetting->mascot_id ?? "fox" }}';
let adminMascotSize = {{ $widgetSetting->mascot_size ?? 72 }};
let adminMascotTrackingEnabled = {{ ($widgetSetting->mascot_tracking ?? true) ? 'true' : 'false' }};
let adminMascotBlinkTimer = null;
let adminMascotPokeTimeout = null;

function toggleLauncherType(type) {
    const section = document.getElementById('sectionMascotOptions');
    if (!section) return;
    if (type === 'mascot') {
        section.classList.remove('hidden');
        initAdminMascotSandbox();
    } else {
        section.classList.add('hidden');
    }
}

let currentMascotCategory = 'all';
let currentMascotSearch = '';

function filterMascotsCategory(cat, btn) {
    currentMascotCategory = cat;
    const buttons = document.querySelectorAll('.mascot-cat-btn');
    buttons.forEach(b => {
        b.className = 'mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0';
    });
    if (btn) {
        btn.className = 'mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-bold bg-apple-blue text-white shadow-2xs transition shrink-0';
    }
    applyMascotFilters();
}

function searchMascots(query) {
    currentMascotSearch = query.trim().toLowerCase();
    applyMascotFilters();
}

function applyMascotFilters() {
    const cards = document.querySelectorAll('#mascotCardsContainer .mascot-card');
    cards.forEach(card => {
        const cat = card.getAttribute('data-cat');
        const name = card.getAttribute('data-name') || '';
        const matchesCat = (currentMascotCategory === 'all' || cat === currentMascotCategory);
        const matchesSearch = (!currentMascotSearch || name.includes(currentMascotSearch));

        if (matchesCat && matchesSearch) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

function selectMascot(mascotId) {
    currentAdminMascotId = mascotId;
    
    // Clear any timers
    if (adminMascotBlinkTimer) {
        clearTimeout(adminMascotBlinkTimer);
        adminMascotBlinkTimer = null;
    }
    if (adminMascotPokeTimeout) {
        clearTimeout(adminMascotPokeTimeout);
        adminMascotPokeTimeout = null;
    }

    const cards = document.querySelectorAll('#mascotCardsContainer .mascot-card');
    cards.forEach(c => {
        const isSelected = (c.getAttribute('data-mascot') === mascotId);
        if (isSelected) {
            c.classList.add('border-apple-blue', 'bg-blue-50/50', 'ring-1', 'ring-apple-blue');
            c.classList.remove('border-apple-border', 'bg-white');
            const radio = c.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        } else {
            c.classList.remove('border-apple-blue', 'bg-blue-50/50', 'ring-1', 'ring-apple-blue');
            c.classList.add('border-apple-border', 'bg-white');
        }
    });

    const sprite = document.getElementById('adminMascotPreviewSprite');
    const statusText = document.getElementById('adminMascotStatusText');
    if (sprite) {
        sprite.style.transform = '';
        sprite.style.backgroundImage = `url('/mascots/${mascotId}-directions.webp')`;
        sprite.style.backgroundPosition = '50% 50%';
    }
    if (statusText) {
        statusText.innerText = 'Arahkan mouse di sekitar kotak ini';
    }

    scheduleAdminBlink();
}

function updateMascotSize(val) {
    adminMascotSize = parseInt(val, 10);
    const label = document.getElementById('labelMascotSize');
    if (label) label.innerText = `${adminMascotSize}px`;

    const sprite = document.getElementById('adminMascotPreviewSprite');
    if (sprite) {
        sprite.style.width = `${adminMascotSize}px`;
        sprite.style.height = `${adminMascotSize}px`;
    }
}

function toggleMascotTracking(enabled) {
    adminMascotTrackingEnabled = enabled;
    const sprite = document.getElementById('adminMascotPreviewSprite');
    if (!enabled && sprite) {
        sprite.style.backgroundPosition = '50% 50%';
    }
}

function pokeAdminMascot() {
    const sprite = document.getElementById('adminMascotPreviewSprite');
    const statusText = document.getElementById('adminMascotStatusText');
    if (!sprite) return;

    if (adminMascotPokeTimeout) clearTimeout(adminMascotPokeTimeout);

    // Switch to reactions sheet frame (col 2, row 2 = 100% 100% or 50% 50%)
    sprite.style.backgroundImage = `url('/mascots/${currentAdminMascotId}-reactions.webp')`;
    sprite.style.backgroundPosition = '50% 50%';
    sprite.style.transform = 'scale(1.15) rotate(5deg)';
    if (statusText) statusText.innerText = '✨ Yaaay! (Gembira)';

    adminMascotPokeTimeout = setTimeout(() => {
        sprite.style.backgroundImage = `url('/mascots/${currentAdminMascotId}-directions.webp')`;
        sprite.style.backgroundPosition = '50% 50%';
        sprite.style.transform = '';
        if (statusText) statusText.innerText = 'Arahkan mouse di sekitar kotak ini';
        adminMascotPokeTimeout = null;
    }, 1200);
}

function scheduleAdminBlink() {
    if (adminMascotBlinkTimer) clearTimeout(adminMascotBlinkTimer);
    const delay = Math.random() * 3000 + 2500;
    adminMascotBlinkTimer = setTimeout(() => {
        const sprite = document.getElementById('adminMascotPreviewSprite');
        if (!sprite || adminMascotPokeTimeout) {
            scheduleAdminBlink();
            return;
        }
        
        const directionsBg = `url('/mascots/${currentAdminMascotId}-directions.webp')`;
        const currentPos = sprite.style.backgroundPosition || '50% 50%';
        sprite.style.backgroundImage = `url('/mascots/${currentAdminMascotId}-reactions.webp')`;
        sprite.style.backgroundPosition = '0% 0%'; // Closed eyes
        
        setTimeout(() => {
            if (!adminMascotPokeTimeout && sprite) {
                sprite.style.backgroundImage = directionsBg;
                sprite.style.backgroundPosition = currentPos;
            }
        }, 140);
        scheduleAdminBlink();
    }, delay);
}

function initAdminMascotSandbox() {
    const sandbox = document.getElementById('adminMascotSandbox');
    const sprite = document.getElementById('adminMascotPreviewSprite');
    if (!sandbox || !sprite || sandbox.dataset.initialized) return;
    sandbox.dataset.initialized = 'true';

    const directionGrid = [
        [0, 0],   // 0: Top-Left
        [50, 0],  // 1: Top
        [100, 0], // 2: Top-Right
        [0, 50],  // 3: Center-Left
        [50, 50], // 4: Center
        [100, 50],// 5: Center-Right
        [0, 100], // 6: Bottom-Left
        [50, 100],// 7: Bottom
        [100, 100]// 8: Bottom-Right
    ];

    sandbox.addEventListener('mousemove', (e) => {
        if (!adminMascotTrackingEnabled || adminMascotPokeTimeout) return;
        const rect = sprite.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const dx = e.clientX - centerX;
        const dy = e.clientY - centerY;
        const distance = Math.hypot(dx, dy);

        // Center deadzone
        if (distance < 20) {
            sprite.style.backgroundPosition = '50% 50%';
            return;
        }

        const angle = Math.atan2(dy, dx) * (180 / Math.PI);
        let dirIndex = 4;

        if (angle >= -157.5 && angle < -112.5) dirIndex = 0;       // Up-Left
        else if (angle >= -112.5 && angle < -67.5) dirIndex = 1;   // Up
        else if (angle >= -67.5 && angle < -22.5) dirIndex = 2;    // Up-Right
        else if (angle >= -22.5 && angle < 22.5) dirIndex = 5;     // Right
        else if (angle >= 22.5 && angle < 67.5) dirIndex = 8;      // Down-Right
        else if (angle >= 67.5 && angle < 112.5) dirIndex = 7;     // Down
        else if (angle >= 112.5 && angle < 157.5) dirIndex = 6;    // Down-Left
        else dirIndex = 3;                                         // Left

        const [x, y] = directionGrid[dirIndex];
        sprite.style.backgroundPosition = `${x}% ${y}%`;
    });

    sandbox.addEventListener('mouseleave', () => {
        if (adminMascotPokeTimeout) return;
        sprite.style.backgroundPosition = '50% 50%';
    });

    scheduleAdminBlink();
}

// Initial Boot
document.addEventListener('DOMContentLoaded', function() {
    renderDecisionTree();
    renderFaqRulesList();
    renderSocialChannelsList();
    updateSubTabToggleStates();

    // Check active tab from URL query params or server session
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || '{{ request("tab", "bot") }}';
    if (initialTab && initialTab !== 'bot') {
        switchDetailTab(initialTab);
    } else {
        initAdminMascotSandbox();
    }
});
</script>
@endsection
