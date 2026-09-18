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
        <!-- TAB 1: SMART BOT & FAQ RULES BUILDER                         -->
        <!-- ============================================================ -->
        <div id="tab-pane-bot" class="tab-pane flex flex-col gap-4" style="display: flex;">
            
            <!-- Master Toggle Card -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-apple-border">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                <circle cx="12" cy="5" r="2"></circle>
                                <path d="M12 7v4"></path>
                                <line x1="8" y1="16" x2="8" y2="16"></line>
                                <line x1="16" y1="16" x2="16" y2="16"></line>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-apple-textPrimary">Aktivasi Smart Bot &amp; Auto-Responder</h3>
                            <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Bot membalas sapaan awal, menjawab pertanyaan FAQ kata kunci, dan merespons saat staf offline.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-apple-canvas/70 px-3.5 py-1.5 rounded-lg border border-apple-border self-start sm:self-auto">
                        <span id="botToggleLabel" class="text-[12px] font-bold {{ $widgetSetting->bot_enabled ? 'text-purple-700' : 'text-neutral-500' }}">
                            {{ $widgetSetting->bot_enabled ? 'Bot Aktif (ON)' : 'Bot Nonaktif (OFF)' }}
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="checkboxBotEnabled" name="bot_enabled" value="1" {{ $widgetSetting->bot_enabled ? 'checked' : '' }} onchange="toggleBotSwitch(this)" class="sr-only peer">
                            <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Bot Settings Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Nama Asisten Bot</label>
                        <input type="text" name="bot_name" value="{{ old('bot_name', $widgetSetting->bot_name ?: 'BeanBot') }}" placeholder="Contoh: BeanBot, CS Virtual" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Muncul di atas pesan balasan bot.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Pesan Sambutan Awal (Welcome Greeting)</label>
                        <input type="text" name="bot_welcome_message" value="{{ old('bot_welcome_message', $widgetSetting->bot_welcome_message ?: 'Halo! Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!') }}" placeholder="Pesan otomatis saat customer pertama kali menyapa" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Dikirimkan otomatis saat customer pertama kali mengirim pesan.</p>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Pesan Di Luar Jam Kerja (Offline Auto-Reply)</label>
                        <input type="text" name="bot_offline_message" value="{{ old('bot_offline_message', $widgetSetting->bot_offline_message ?: 'Terima kasih telah menghubungi kami. Staf kami sedang offline saat ini dan akan segera membalas begitu kembali.') }}" placeholder="Pesan otomatis saat customer chat di luar jam operasional" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>
                </div>
            </div>

            <!-- Sub-Tab Navigation Bar inside Smart Bot -->
            <div class="bg-white p-1.5 rounded-xl border border-apple-border shadow-apple-sm flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                    <button type="button" onclick="switchBotSubTab('query')" id="btn-botsubtab-query" class="px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer bg-purple-600 text-white shadow-2xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span>1. Mode Query (Kata Kunci FAQ)</span>
                        <span id="dotQueryStatus" class="w-2 h-2 rounded-full {{ ($widgetSetting->bot_mode_query ?? true) ? 'bg-emerald-300 animate-pulse' : 'bg-neutral-300' }}"></span>
                    </button>

                    <button type="button" onclick="switchBotSubTab('options')" id="btn-botsubtab-options" class="px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>2. Mode Opsi (Decision Tree Navigasi)</span>
                        <span id="dotOptionsStatus" class="w-2 h-2 rounded-full {{ ($widgetSetting->bot_mode_options ?? true) ? 'bg-emerald-300 animate-pulse' : 'bg-neutral-300' }}"></span>
                    </button>
                </div>

                <div class="flex items-center gap-2 text-[11px] text-apple-textSecondary px-2">
                    <span>Status:</span>
                    <span id="badgeBotModeStatus" class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-100 text-purple-700">
                        {{ ($widgetSetting->bot_mode_query ?? true) && ($widgetSetting->bot_mode_options ?? true) ? '⚡ Hybrid (Query + Opsi)' : (($widgetSetting->bot_mode_options ?? true) ? '🔘 Hanya Mode Opsi' : '💬 Hanya Mode Query') }}
                    </span>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SUB-TAB 1: MODE QUERY (KATA KUNCI FAQ)                       -->
            <!-- ============================================================ -->
            <div id="pane-botsubtab-query" class="flex flex-col gap-4" style="display: flex;">
                <!-- Independent Active Toggle Card for Query Mode -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Aktivasi Mode Query (Pencarian Kata Kunci FAQ)</h4>
                                <span id="badgeQueryActiveLabel" class="px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ ($widgetSetting->bot_mode_query ?? true) ? 'bg-blue-100 text-blue-700' : 'bg-neutral-100 text-neutral-500' }}">
                                    {{ ($widgetSetting->bot_mode_query ?? true) ? 'Aktif (ON)' : 'Nonaktif (OFF)' }}
                                </span>
                            </div>
                            <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Customer mengetik pesan bebas di kolom chat, bot otomatis mendeteksi kata kunci FAQ yang cocok dan membalas seketika.</p>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="bot_mode_query" value="1" id="toggleBotModeQuery" {{ ($widgetSetting->bot_mode_query ?? true) ? 'checked' : '' }} onchange="updateSubTabToggleStates()" class="sr-only peer">
                        <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- FAQ Rules Builder Card -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[14px] font-bold text-apple-textPrimary">Daftar Kata Kunci &amp; Jawaban Otomatis FAQ</h3>
                                <span id="labelRuleCount" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">0 Aturan</span>
                            </div>
                            <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tentukan kata kunci pemicu (pisahkan koma) dan template jawaban otomatis bot.</p>
                        </div>

                        <button type="button" onclick="addNewFaqRuleRow()" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-[11.5px] font-semibold transition shadow-apple-sm cursor-pointer self-start sm:self-auto">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Tambah Aturan FAQ Baru</span>
                        </button>
                    </div>

                    <!-- FAQ Rules Container -->
                    <div id="faqRulesListContainer" class="flex flex-col gap-3"></div>

                    <!-- Empty State -->
                    <div id="faqEmptyState" class="hidden p-6 text-center border-2 border-dashed border-apple-border rounded-xl text-[12px] text-apple-textTertiary bg-apple-canvas/20">
                        <p class="font-medium text-apple-textSecondary">Belum ada aturan kata kunci FAQ.</p>
                        <p class="mt-0.5 text-apple-textTertiary text-[11px]">Klik <strong>"Tambah Aturan FAQ Baru"</strong> di atas untuk membuat kata kunci pertama Anda.</p>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SUB-TAB 2: MODE OPSI (DECISION TREE NAVIGASI)                -->
            <!-- ============================================================ -->
            <div id="pane-botsubtab-options" class="flex flex-col gap-4" style="display: none;">
                <!-- Independent Active Toggle Card for Options Mode -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Aktivasi Mode Opsi (Pohon Keputusan / Decision Tree)</h4>
                                <span id="badgeOptionsActiveLabel" class="px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ ($widgetSetting->bot_mode_options ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-neutral-500' }}">
                                    {{ ($widgetSetting->bot_mode_options ?? true) ? 'Aktif (ON)' : 'Nonaktif (OFF)' }}
                                </span>
                            </div>
                            <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Bot menyajikan menu tombol pilihan terstruktur (menu bertingkat). Customer cukup mengklik tombol cabang materi yang diinginkan.</p>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="bot_mode_options" value="1" id="toggleBotModeOptions" {{ ($widgetSetting->bot_mode_options ?? true) ? 'checked' : '' }} onchange="updateSubTabToggleStates()" class="sr-only peer">
                        <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <!-- Decision Tree Manager Card -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[14px] font-bold text-apple-textPrimary">Pohon Navigasi Materi Bot (Decision Tree)</h3>
                                <span id="badgeTreeStats" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">0 Cabang</span>
                            </div>
                            <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Kelola struktur hirarki menu bot secara visual. Tambah sub-cabang, atur urutan (drag &amp; drop atau tombol naik/turun), dan ubah jawaban bot.</p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
                            <button type="button" onclick="expandAllTreeNodes()" class="px-2.5 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/40 hover:bg-white text-[11px] font-medium text-apple-textSecondary transition cursor-pointer">
                                📂 Buka Semua
                            </button>
                            <button type="button" onclick="collapseAllTreeNodes()" class="px-2.5 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/40 hover:bg-white text-[11px] font-medium text-apple-textSecondary transition cursor-pointer">
                                📁 Tutup Semua
                            </button>
                            <button type="button" onclick="addRootTreeNode()" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-[11.5px] font-semibold transition shadow-apple-sm cursor-pointer">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Tambah Menu Utama Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- Live Quick Search in Tree -->
                    <div class="relative">
                        <input type="text" id="inputSearchTree" oninput="filterDecisionTree(this.value)" placeholder="Cari cabang materi dalam pohon (cth: Kopi, Supresso, Distributor, Retur)..." class="w-full text-[12px] pl-9 pr-4 py-2 bg-apple-canvas/50 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                        <svg class="w-4 h-4 text-apple-textTertiary absolute left-3 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>

                    <!-- Visual Tree Container -->
                    <div id="treeContainer" class="flex flex-col gap-2.5 bg-apple-canvas/20 p-3 rounded-xl border border-apple-border/70 min-h-[220px]">
                        <!-- Rendered recursively by JS -->
                    </div>

                    <!-- Empty State Tree -->
                    <div id="treeEmptyState" class="hidden p-8 text-center border-2 border-dashed border-apple-border rounded-xl text-[12px] text-apple-textTertiary bg-apple-canvas/20">
                        <p class="font-medium text-apple-textSecondary">Belum ada cabang menu pada Decision Tree.</p>
                        <p class="mt-0.5 text-apple-textTertiary text-[11px]">Klik <strong>"Tambah Menu Utama Baru"</strong> di atas untuk membuat menu cabang pertama.</p>
                    </div>
                </div>
            </div>

            <!-- Global Tree Node Edit Modal -->
            <div id="modalEditTreeNode" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                <div class="bg-white rounded-2xl border border-apple-border shadow-2xl max-w-lg w-full p-5 flex flex-col gap-4 animate-in fade-in zoom-in duration-150">
                    <div class="flex items-center justify-between pb-3 border-b border-apple-border">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[13px]">🌲</span>
                            <div>
                                <h4 class="text-[14px] font-bold text-apple-textPrimary">Edit Cabang Materi Bot</h4>
                                <p class="text-[11px] text-apple-textSecondary">Konfigurasi teks tombol dan jawaban balasan bot.</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeEditTreeNodeModal()" class="text-neutral-400 hover:text-neutral-600 p-1 rounded-lg">✕</button>
                    </div>

                    <input type="hidden" id="modalEditNodeId">

                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Label Tombol Menu</label>
                            <input type="text" id="modalEditNodeLabel" placeholder="Contoh: ☕ 1.1 Kopi" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Nilai / Trigger Pemicu (Key)</label>
                            <input type="text" id="modalEditNodeValue" placeholder="Contoh: 1.1 atau kopi" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <p class="text-[10.5px] text-apple-textTertiary mt-0.5">Teks yang dikirimkan saat customer mengklik tombol ini.</p>
                        </div>

                        <div>
                            <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Jawaban Balasan Bot</label>
                            <textarea id="modalEditNodeResponse" rows="4" placeholder="Tulis jawaban lengkap bot saat cabang ini dipilih..." class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 leading-relaxed"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-apple-border">
                        <button type="button" onclick="closeEditTreeNodeModal()" class="px-3 py-1.5 text-[11.5px] font-semibold text-apple-textSecondary hover:bg-apple-canvas rounded-lg transition">Batal</button>
                        <button type="button" onclick="saveEditedTreeNode()" class="px-4 py-1.5 text-[12px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-apple-sm transition">Simpan Perubahan</button>
                    </div>
                </div>
            </div>

            <!-- Smart CS Auto-Handoff Info Box -->
            <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl text-[11.5px] text-blue-900 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <div>
                    <span class="font-bold">Smart CS Auto-Handoff &amp; Auto-Yielding</span>
                    <p class="mt-0.5 text-blue-800 leading-relaxed text-[11px]">
                        Jika pelanggan mengetik kata kunci eskalasi manusia seperti <em>"cs"</em>, <em>"manusia"</em>, <em>"bicara dengan staf"</em>, atau saat staf CS membalas manual dari Admin Live Inbox, sistem secara otomatis menonaktifkan bot untuk tiket tersebut agar tidak menginterupsi percakapan manusia.
                    </p>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 2: APPEARANCE & BRANDING                                 -->
        <!-- ============================================================ -->
        <div id="tab-pane-appearance" class="tab-pane flex flex-col gap-4" style="display: none;">
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border">
                    <h3 class="text-[14px] font-bold text-apple-textPrimary">Warna &amp; Teks Header Widget</h3>
                    <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Kustomisasi identitas visual chat widget agar selaras dengan brand website Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Widget Language -->
                    <div class="md:col-span-2 pb-3 border-b border-apple-border/60">
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1.5">Bahasa Tampilan Widget (Widget UI Language)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue">
                                <input type="radio" name="language" value="id" {{ ($widgetSetting->language ?? 'id') === 'id' ? 'checked' : '' }} class="w-4 h-4 text-apple-blue focus:ring-apple-blue">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[15px]">🇮🇩</span>
                                        <span class="text-[12.5px] font-bold text-apple-textPrimary">Bahasa Indonesia</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-600 font-medium">Default</span>
                                    </div>
                                    <p class="text-[10.5px] text-apple-textSecondary mt-0.5">Teks balon chat, salam sapa, formulir nama, aksi selesaikan tiket, dan riwayat obrolan dalam Bahasa Indonesia.</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue">
                                <input type="radio" name="language" value="en" {{ ($widgetSetting->language ?? 'id') === 'en' ? 'checked' : '' }} class="w-4 h-4 text-apple-blue focus:ring-apple-blue">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[15px]">🇬🇧</span>
                                        <span class="text-[12.5px] font-bold text-apple-textPrimary">English</span>
                                    </div>
                                    <p class="text-[10.5px] text-apple-textSecondary mt-0.5">Chat bubble, greeting prompts, visitor name intro, resolve ticket button, and tickets history in English.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Brand Color -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Warna Aksen Utama (Primary Brand Color)</label>
                        <div class="flex items-center gap-2.5">
                            <input type="color" id="inputColorPicker" name="primary_color" value="{{ $widgetSetting->primary_color }}" onchange="updateBrandColor(this.value)" class="w-9 h-9 rounded-lg border border-apple-border cursor-pointer p-0.5 bg-white shrink-0">
                            <input type="text" id="inputColorHex" value="{{ $widgetSetting->primary_color }}" oninput="updateBrandColor(this.value)" class="w-28 text-[12px] font-mono uppercase px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg">
                            
                            <!-- Presets -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" onclick="updateBrandColor('#0071E3')" class="w-5 h-5 rounded-full bg-[#0071E3] border border-black/10 shadow-2xs cursor-pointer" title="Apple Blue"></button>
                                <button type="button" onclick="updateBrandColor('#C59B27')" class="w-5 h-5 rounded-full bg-[#C59B27] border border-black/10 shadow-2xs cursor-pointer" title="Gold Supresso"></button>
                                <button type="button" onclick="updateBrandColor('#10B981')" class="w-5 h-5 rounded-full bg-[#10B981] border border-black/10 shadow-2xs cursor-pointer" title="Emerald"></button>
                                <button type="button" onclick="updateBrandColor('#6366F1')" class="w-5 h-5 rounded-full bg-[#6366F1] border border-black/10 shadow-2xs cursor-pointer" title="Indigo"></button>
                                <button type="button" onclick="updateBrandColor('#1C1C1E')" class="w-5 h-5 rounded-full bg-[#1C1C1E] border border-black/10 shadow-2xs cursor-pointer" title="Dark Slate"></button>
                            </div>
                        </div>
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Warna tombol widget terapung, header popup obrolan, dan bubble pesan.</p>
                    </div>

                    <!-- Custom Support Button Title -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Teks Tombol Layanan (Support Button Title)</label>
                        <input type="text" name="support_title" value="{{ old('support_title', $widgetSetting->support_title ?: 'Customer Support') }}" placeholder="Contoh: Customer Support, Live Help" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Muncul pada tombol mulai chat di Welcome Hub widget.</p>
                    </div>

                    <!-- Greeting Title -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Judul Sapaan Welcome Hub (Greeting Title)</label>
                        <input type="text" name="greeting_title" value="{{ old('greeting_title', $widgetSetting->greeting_title ?: 'Hallo!') }}" placeholder="Contoh: Hallo!, Selamat Datang!" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>

                    <!-- Greeting Subtitle -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Sub-judul Sapaan (Greeting Subtitle)</label>
                        <input type="text" name="greeting_subtitle" value="{{ old('greeting_subtitle', $widgetSetting->greeting_subtitle ?: 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!') }}" placeholder="Contoh: Ada yang bisa kami bantu?" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>

                    <!-- Find Us Title -->
                    <div class="md:col-span-2">
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Judul Saluran Eksternal (Find Us Section Title)</label>
                        <input type="text" name="find_us_title" value="{{ old('find_us_title', $widgetSetting->find_us_title ?: 'Find Us Somewhere Else') }}" placeholder="Contoh: Reach Us Anywhere Else, Saluran Resmi Lainnya" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 2.5: MASKOT INTERAKTIF (PAGE MASCOT)                     -->
        <!-- ============================================================ -->
        <div id="tab-pane-mascot" class="tab-pane flex flex-col gap-4" style="display: none;">
            <!-- Gaya Tombol Peluncur Chat (Chat Launcher Style) -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-[14px] font-bold text-apple-textPrimary flex items-center gap-2">
                            <span>Gaya Tombol Peluncur Chat (Chat Launcher Style)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Koboyo Mascot</span>
                        </h3>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Pilih gaya tombol terapung di sudut website Anda: balon pesan klasik atau maskot interaktif yang matanya mengikuti kursor pengunjung.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue" onclick="toggleLauncherType('default')">
                        <input type="radio" name="launcher_type" value="default" {{ ($widgetSetting->launcher_type ?? 'default') === 'default' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-apple-blue focus:ring-apple-blue">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-blue-100 text-apple-blue flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                </span>
                                <span class="text-[13px] font-bold text-apple-textPrimary">Balon Chat Standar (Default)</span>
                            </div>
                            <p class="text-[11px] text-apple-textSecondary mt-1.5 leading-relaxed">Tombol melayang bundar dengan ikon balon pesan klasik. Menggunakan warna aksen brand yang Anda pilih.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue" onclick="toggleLauncherType('mascot')">
                        <input type="radio" name="launcher_type" value="mascot" {{ ($widgetSetting->launcher_type ?? 'default') === 'mascot' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-apple-blue focus:ring-apple-blue">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-[15px] shrink-0">🦊</span>
                                <span class="text-[13px] font-bold text-apple-textPrimary">Maskot Interaktif (Page Mascot)</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-semibold">Interactive</span>
                            </div>
                            <p class="text-[11px] text-apple-textSecondary mt-1.5 leading-relaxed">Karakter maskot animasi lucu yang menoleh mengikuti kursor mouse pengunjung web, berkedip, dan berekspresi riang saat diklik.</p>
                        </div>
                    </label>
                </div>

                <!-- Sub Panel Mascot Settings -->
                <div id="sectionMascotOptions" class="{{ ($widgetSetting->launcher_type ?? 'default') === 'mascot' ? '' : 'hidden' }} flex flex-col gap-4 pt-4 border-t border-apple-border">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                        <!-- Left: Character Selection & Controls (7 Cols) -->
                        <div class="lg:col-span-7 flex flex-col gap-4">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-apple-textPrimary">Pilih Karakter Maskot</label>
                                        <span class="text-[10.5px] text-apple-textSecondary">Tersedia 58 karakter lengkap: Orang &amp; Profesi, Mesin &amp; Robot, Hewan, dan Art Style.</span>
                                    </div>
                                    <div class="relative w-full sm:w-48">
                                        <input type="text" id="inputSearchMascot" placeholder="Cari maskot..." oninput="searchMascots(this.value)" class="w-full text-[11px] px-2.5 py-1.5 pl-7 bg-apple-canvas/50 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-apple-blue">
                                        <svg class="w-3.5 h-3.5 text-neutral-400 absolute left-2 top-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                </div>

                                <!-- Category Filter Tabs -->
                                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1.5 mb-2.5">
                                    <button type="button" onclick="filterMascotsCategory('all', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-bold bg-apple-blue text-white shadow-2xs transition shrink-0">
                                        ✨ Semua (58)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('people', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🧑 Orang &amp; Profesi (19)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('machines', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🤖 Mesin &amp; Robot (13)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('animals', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🐾 Hewan (21)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('art', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🎨 Art Style (5)
                                    </button>
                                </div>

                                @php
                                    $allMascotsList = [
                                        // 🧑 Orang & Profesi
                                        ['id' => 'astronaut', 'name' => 'Astronaut', 'desc' => 'Penjelajah Antariksa', 'cat' => 'people'],
                                        ['id' => 'nurse', 'name' => 'Nurse', 'desc' => 'Tenaga Medis', 'cat' => 'people'],
                                        ['id' => 'chef', 'name' => 'Chef', 'desc' => 'Koki & Kuliner', 'cat' => 'people'],
                                        ['id' => 'scientist', 'name' => 'Scientist', 'desc' => 'Ilmuwan & Sains', 'cat' => 'people'],
                                        ['id' => 'hijabi', 'name' => 'Hijabi', 'desc' => 'Muslimah Ramah', 'cat' => 'people'],
                                        ['id' => 'builder', 'name' => 'Builder', 'desc' => 'Teknisi & Konstruksi', 'cat' => 'people'],
                                        ['id' => 'glasses', 'name' => 'Glasses', 'desc' => 'Kacamata Cerdas', 'cat' => 'people'],
                                        ['id' => 'beard', 'name' => 'Beard', 'desc' => 'Pria Brewok', 'cat' => 'people'],
                                        ['id' => 'cap', 'name' => 'Cap Boy', 'desc' => 'Kasual Topi', 'cat' => 'people'],
                                        ['id' => 'afro', 'name' => 'Afro', 'desc' => 'Gaya Afro', 'cat' => 'people'],
                                        ['id' => 'bald', 'name' => 'Bald', 'desc' => 'Plontos Ramah', 'cat' => 'people'],
                                        ['id' => 'ballerina', 'name' => 'Ballerina', 'desc' => 'Penari Balet', 'cat' => 'people'],
                                        ['id' => 'grandpa', 'name' => 'Grandpa', 'desc' => 'Kakek Bijak', 'cat' => 'people'],
                                        ['id' => 'granny', 'name' => 'Granny', 'desc' => 'Nenek Penyayang', 'cat' => 'people'],
                                        ['id' => 'kamran', 'name' => 'Kamran', 'desc' => 'Karakter Ceria', 'cat' => 'people'],
                                        ['id' => 'pirate', 'name' => 'Pirate', 'desc' => 'Bajak Laut Petualang', 'cat' => 'people'],
                                        ['id' => 'sikh', 'name' => 'Sikh', 'desc' => 'Bersorban Khas', 'cat' => 'people'],
                                        ['id' => 'skater', 'name' => 'Skater', 'desc' => 'Pemain Skateboard', 'cat' => 'people'],
                                        ['id' => 'wizard', 'name' => 'Wizard', 'desc' => 'Penyihir Ajaib', 'cat' => 'people'],

                                        // 🤖 Mesin & Robot
                                        ['id' => 'gearbot', 'name' => 'Gearbot', 'desc' => 'Robot Roda Gigi', 'cat' => 'machines'],
                                        ['id' => 'drone', 'name' => 'Drone', 'desc' => 'Drone Terbang Mini', 'cat' => 'machines'],
                                        ['id' => 'postbot', 'name' => 'Postbot', 'desc' => 'Robot Kurir Pesan', 'cat' => 'machines'],
                                        ['id' => 'scout', 'name' => 'Scout Bot', 'desc' => 'Robot Penjelajah', 'cat' => 'machines'],
                                        ['id' => 'clockwork', 'name' => 'Clockwork', 'desc' => 'Mesin Jam Antik', 'cat' => 'machines'],
                                        ['id' => 'crt', 'name' => 'CRT Monitor', 'desc' => 'Layar Komputer Retro', 'cat' => 'machines'],
                                        ['id' => 'tv', 'name' => 'Retro TV', 'desc' => 'Televisi Antena Klasik', 'cat' => 'machines'],
                                        ['id' => 'radio', 'name' => 'Retro Radio', 'desc' => 'Radio Gelombang Suara', 'cat' => 'machines'],
                                        ['id' => 'rocket', 'name' => 'Rocket', 'desc' => 'Roket Antariksa', 'cat' => 'machines'],
                                        ['id' => 'toaster', 'name' => 'Toaster', 'desc' => 'Pemanggang Roti Ceria', 'cat' => 'machines'],
                                        ['id' => 'cube', 'name' => 'Cyber Cube', 'desc' => 'Kubus Digital', 'cat' => 'machines'],
                                        ['id' => 'knight', 'name' => 'Knight Bot', 'desc' => 'Robot Ksatria Zirah', 'cat' => 'machines'],
                                        ['id' => 'lantern', 'name' => 'Lantern', 'desc' => 'Lentera Cahaya', 'cat' => 'machines'],

                                        // 🐾 Hewan
                                        ['id' => 'fox', 'name' => 'Fox (Rubah)', 'desc' => 'Lincah & Ramah', 'cat' => 'animals'],
                                        ['id' => 'cat', 'name' => 'Cat (Kucing)', 'desc' => 'Lucu & Santai', 'cat' => 'animals'],
                                        ['id' => 'panda', 'name' => 'Panda', 'desc' => 'Menggemaskan', 'cat' => 'animals'],
                                        ['id' => 'bunny', 'name' => 'Bunny (Kelinci)', 'desc' => 'Ceria & Aktif', 'cat' => 'animals'],
                                        ['id' => 'bear', 'name' => 'Bear (Beruang)', 'desc' => 'Hangat & Ramah', 'cat' => 'animals'],
                                        ['id' => 'otter', 'name' => 'Otter (Berang)', 'desc' => 'Playful & Cerdas', 'cat' => 'animals'],
                                        ['id' => 'owl', 'name' => 'Owl (Hantu)', 'desc' => 'Bijak & Waspada', 'cat' => 'animals'],
                                        ['id' => 'dino', 'name' => 'Dino', 'desc' => 'Dinosaurus Lucu', 'cat' => 'animals'],
                                        ['id' => 'frog', 'name' => 'Frog (Katak)', 'desc' => 'Katak Riang', 'cat' => 'animals'],
                                        ['id' => 'hamster', 'name' => 'Hamster', 'desc' => 'Pipi Tembem', 'cat' => 'animals'],
                                        ['id' => 'hedgehog', 'name' => 'Hedgehog', 'desc' => 'Landak Mini', 'cat' => 'animals'],
                                        ['id' => 'koala', 'name' => 'Koala', 'desc' => 'Tenang & Manis', 'cat' => 'animals'],
                                        ['id' => 'mouse', 'name' => 'Mouse (Tikus)', 'desc' => 'Mungil & Gesit', 'cat' => 'animals'],
                                        ['id' => 'penguin', 'name' => 'Penguin', 'desc' => 'Pinguin Kutub', 'cat' => 'animals'],
                                        ['id' => 'pug', 'name' => 'Pug Dog', 'desc' => 'Anjing Pug Setia', 'cat' => 'animals'],
                                        ['id' => 'raccoon', 'name' => 'Raccoon', 'desc' => 'Cerdik Bertopeng', 'cat' => 'animals'],
                                        ['id' => 'redpanda', 'name' => 'Red Panda', 'desc' => 'Panda Merah', 'cat' => 'animals'],
                                        ['id' => 'sheep', 'name' => 'Sheep (Domba)', 'desc' => 'Bulu Lembut', 'cat' => 'animals'],
                                        ['id' => 'sloth', 'name' => 'Sloth (Kukang)', 'desc' => 'Santai & Rileks', 'cat' => 'animals'],
                                        ['id' => 'tiger', 'name' => 'Tiger (Harimau)', 'desc' => 'Gagah & Berani', 'cat' => 'animals'],
                                        ['id' => 'deer', 'name' => 'Deer (Rusa)', 'desc' => 'Tanduk Anggun', 'cat' => 'animals'],

                                        // 🎨 Art Styles
                                        ['id' => 'fox-pixel', 'name' => 'Fox Pixel', 'desc' => 'Game 8-Bit Retro', 'cat' => 'art'],
                                        ['id' => 'fox-ink', 'name' => 'Fox Ink', 'desc' => 'Tinta Hitam Putih', 'cat' => 'art'],
                                        ['id' => 'fox-sketch', 'name' => 'Fox Sketch', 'desc' => 'Sketsa Pensil', 'cat' => 'art'],
                                        ['id' => 'fox-riso', 'name' => 'Fox Riso', 'desc' => 'Cetak Risograf 2-Warna', 'cat' => 'art'],
                                        ['id' => 'fox-paper', 'name' => 'Fox Paper', 'desc' => 'Guntingan Kertas', 'cat' => 'art'],
                                    ];
                                    $currentMascot = $widgetSetting->mascot_id ?? 'fox';
                                @endphp

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 max-h-72 overflow-y-auto pr-1 border border-apple-border/50 rounded-xl p-2 bg-apple-canvas/20" id="mascotCardsContainer">
                                    @foreach($allMascotsList as $m)
                                        <label class="mascot-card flex flex-col items-center justify-center p-2.5 rounded-xl border {{ $currentMascot === $m['id'] ? 'border-apple-blue bg-blue-50/50 ring-1 ring-apple-blue' : 'border-apple-border bg-white hover:bg-apple-canvas/40' }} cursor-pointer transition text-center group" data-mascot="{{ $m['id'] }}" data-cat="{{ $m['cat'] }}" data-name="{{ strtolower($m['name']) }} {{ strtolower($m['desc']) }}" onclick="selectMascot('{{ $m['id'] }}')">
                                            <input type="radio" name="mascot_id" value="{{ $m['id'] }}" {{ $currentMascot === $m['id'] ? 'checked' : '' }} class="sr-only">
                                            <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-50 shadow-2xs border border-apple-border/50 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                                                <div class="w-12 h-12" style="background-image: url('/mascots/{{ $m['id'] }}-directions.webp'); background-size: 300% 300%; background-position: 50% 50%;"></div>
                                            </div>
                                            <span class="text-[11.5px] font-bold text-apple-textPrimary leading-tight">{{ $m['name'] }}</span>
                                            <span class="text-[9.5px] text-apple-textTertiary mt-0.5 line-clamp-1">{{ $m['desc'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Controls: Size & Cursor Tracking -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-apple-border/60">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[11.5px] font-semibold text-apple-textPrimary">Ukuran Maskot</label>
                                        <span id="labelMascotSize" class="text-[11px] font-mono font-bold text-apple-blue">{{ $widgetSetting->mascot_size ?? 72 }}px</span>
                                    </div>
                                    <input type="range" name="mascot_size" id="inputMascotSize" min="54" max="96" step="2" value="{{ $widgetSetting->mascot_size ?? 72 }}" oninput="updateMascotSize(this.value)" class="w-full accent-apple-blue cursor-pointer">
                                    <div class="flex justify-between text-[10px] text-apple-textTertiary mt-0.5">
                                        <span>Kecil (54px)</span>
                                        <span>Normal (72px)</span>
                                        <span>Besar (96px)</span>
                                    </div>
                                </div>

                                <div class="flex flex-col justify-center">
                                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition">
                                        <input type="checkbox" name="mascot_tracking" value="1" id="inputMascotTracking" {{ ($widgetSetting->mascot_tracking ?? true) ? 'checked' : '' }} onchange="toggleMascotTracking(this.checked)" class="w-4 h-4 text-apple-blue rounded focus:ring-apple-blue">
                                        <div>
                                            <span class="text-[11.5px] font-bold text-apple-textPrimary block">Gerakan Ikuti Kursor</span>
                                            <span class="text-[10px] text-apple-textSecondary">Mata &amp; kepala menoleh mengikuti kursor mouse</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Live Interactive Sandbox Preview (5 Cols) -->
                        <div class="lg:col-span-5 flex flex-col">
                            <div class="bg-gradient-to-b from-slate-50 to-slate-100/70 border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center relative overflow-hidden h-full min-h-[220px]" id="adminMascotSandbox">
                                <div class="absolute top-2.5 left-3 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-[10.5px] font-semibold text-slate-600 uppercase tracking-wider">Live Preview</span>
                                </div>
                                <span class="absolute top-2.5 right-3 text-[10px] text-slate-400 font-medium">Arahkan kursor &amp; klik</span>

                                <!-- Mascot Character in Sandbox -->
                                <div class="flex flex-col items-center justify-center my-auto cursor-pointer select-none group" id="adminMascotPreviewWrapper" onclick="pokeAdminMascot()" title="Klik untuk menggelitik maskot!">
                                    <div id="adminMascotPreviewSprite" class="transition-transform group-hover:scale-105" style="width: {{ $widgetSetting->mascot_size ?? 72 }}px; height: {{ $widgetSetting->mascot_size ?? 72 }}px; background-image: url('/mascots/{{ $currentMascot }}-directions.webp'); background-size: 300% 300%; background-position: 50% 50%;"></div>
                                    <div class="w-10 h-2 rounded-full bg-black/10 blur-[2px] mt-1"></div>
                                </div>

                                <div class="mt-auto pt-2 text-center">
                                    <p class="text-[11px] font-medium text-slate-600" id="adminMascotStatusText">Arahkan mouse di sekitar kotak ini</p>
                                    <p class="text-[10px] text-slate-400">Maskot menoleh 9 arah sesuai posisi kursor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 3: SOCIAL CHANNELS & MULTI-CONTACT BUILDER               -->
        <!-- ============================================================ -->
        <div id="tab-pane-social" class="tab-pane flex flex-col gap-4" style="display: none;">
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[14px] font-bold text-apple-textPrimary">Saluran Sosial, Kontak &amp; Marketplace</h3>
                            <span id="labelSocialCount" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">{{ collect($socialChannelsList)->where('enabled', true)->count() }} Aktif</span>
                        </div>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tambahkan saluran komunikasi resmi atau media sosial Anda (WhatsApp, Instagram, Threads, X / Twitter, Facebook, TikTok, YouTube, Telegram, Shopee, Tokopedia, Custom Link, dsb). Mendukung icon resmi platform maupun icon kustom.</p>
                    </div>

                    <button type="button" onclick="addNewSocialChannel()" class="inline-flex items-center gap-1.5 bg-apple-blue hover:bg-apple-blueHover text-white px-3.5 py-1.5 rounded-lg text-[12px] font-semibold transition shadow-apple-sm cursor-pointer self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Saluran / Kontak Baru</span>
                    </button>
                </div>

                <!-- Container Baris Saluran Sosial (Vanilla JS Managed) -->
                <div id="socialChannelsListContainer" class="flex flex-col gap-3">
                    <!-- Dynamic Rows Rendered Here by JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="socialChannelsEmptyState" class="{{ count($socialChannelsList) > 0 ? 'hidden' : '' }} p-6 text-center border-2 border-dashed border-apple-border rounded-xl text-[12px] text-apple-textTertiary bg-apple-canvas/20">
                    <svg class="w-7 h-7 text-neutral-300 mx-auto mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    <p class="font-medium text-apple-textSecondary">Belum ada saluran kontak atau marketplace.</p>
                    <p class="mt-0.5 text-apple-textTertiary text-[11px]">Klik tombol <strong>"Tambah Saluran / Kontak Baru"</strong> di atas untuk menambahkan saluran kontak atau tautan toko pertama Anda.</p>
                </div>

                <!-- Informative Callout for Multi-Contact Feature -->
                <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl text-[11.5px] text-blue-900 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <div>
                        <span class="font-bold">Dukungan Multi-Akun &amp; Multi-Kontak</span>
                        <p class="mt-0.5 text-blue-800 leading-relaxed text-[11px]">
                            Jika Anda menambahkan <strong>lebih dari 1 kontak aktif pada platform yang sama</strong> (misal: beberapa nomor WhatsApp, akun Instagram cabang, dsb), pengunjung website akan disuguhkan <strong>Menu Pilihan Kontak</strong> di widget agar dapat memilih layanan yang dituju.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- ============================================================ -->
        <!-- TAB 4: DOMAIN, AGENT & EMBED CODE                            -->
        <!-- ============================================================ -->
        <div id="tab-pane-embed" class="tab-pane flex flex-col gap-4" style="display: none;">
            
            <!-- 1. PENGATURAN WEBSITE & DOMAIN WHITELIST -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border flex items-center justify-between">
                    <div>
                        <h3 class="text-[14px] font-bold text-apple-textPrimary">Pengaturan Website &amp; Domain Whitelist</h3>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Ubah nama identitas website serta kelola daftar domain yang diizinkan memuat widget chat ini.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold bg-blue-50 text-apple-blue border border-blue-200">Domain Whitelist</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[12px]">
                    <div>
                        <label class="block font-medium text-apple-textPrimary mb-1" for="inputProjectName">Nama Website / Brand</label>
                        <input type="text" id="inputProjectName" name="name" value="{{ $project->name }}" class="w-full px-3 py-2 border border-apple-border rounded-lg text-[12px] font-semibold text-apple-textPrimary focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Contoh: Supresso Coffee Store" required>
                        <span class="text-[10.5px] text-apple-textTertiary mt-1 block">Nama website untuk pengenal tiket dan filter percakapan di inbox.</span>
                    </div>

                    <div>
                        <label class="block font-medium text-apple-textPrimary mb-1" for="inputProjectDomains">Domain Whitelist (Pisah koma jika lebih dari satu)</label>
                        <textarea id="inputProjectDomains" name="domains" rows="2" class="w-full px-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] text-apple-textPrimary focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="contoh.com, www.contoh.com, dev.contoh.com" required>{{ $project->domains->pluck('domain')->implode(', ') }}</textarea>
                        <span class="text-[10.5px] text-apple-textTertiary mt-1 block">Hanya domain yang tertera di sini yang diizinkan berinteraksi dengan API chat.</span>
                    </div>
                </div>
            </div>

            <!-- 2. PENUGASAN AGENT CS (RESPONSIBLE AGENTS) -->
            @php
                $assignedIds = $project->assignedUsers->pluck('id')->toArray();
                $isAllAgents = empty($assignedIds);
            @endphp
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-[14px] font-bold text-apple-textPrimary">Staf Agent CS Penanggung Jawab</h3>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tentukan apakah semua staf CS menangani tiket website ini, atau hanya staf tertentu.</p>
                    </div>
                    <span id="badgeAgentScope" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold {{ $isAllAgents ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }} self-start sm:self-auto">
                        {{ $isAllAgents ? '👥 Semua Agent (All CS)' : '👤 ' . count($assignedIds) . ' Agent Terpilih' }}
                    </span>
                </div>

                <div class="flex flex-col gap-3 text-[12px]">
                    <!-- Radio Pilihan: Semua atau Tertentu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-3 rounded-xl border {{ $isAllAgents ? 'border-apple-blue bg-blue-50/30' : 'border-apple-border bg-white' }} flex items-start gap-3 cursor-pointer hover:border-apple-blue/60 transition" id="labelAgentAll">
                            <input type="radio" name="agent_scope" value="all" {{ $isAllAgents ? 'checked' : '' }} onchange="toggleAgentScope(this.value)" class="mt-0.5 text-apple-blue focus:ring-apple-blue/20">
                            <div>
                                <span class="font-bold text-apple-textPrimary block">Semua Agent CS (Default &mdash; All)</span>
                                <span class="text-[11px] text-apple-textSecondary mt-0.5 block leading-relaxed">Seluruh tim CS operasional memiliki akses dan dapat merespons tiket yang masuk dari website ini.</span>
                            </div>
                        </label>

                        <label class="p-3 rounded-xl border {{ !$isAllAgents ? 'border-apple-blue bg-blue-50/30' : 'border-apple-border bg-white' }} flex items-start gap-3 cursor-pointer hover:border-apple-blue/60 transition" id="labelAgentSelected">
                            <input type="radio" name="agent_scope" value="selected" {{ !$isAllAgents ? 'checked' : '' }} onchange="toggleAgentScope(this.value)" class="mt-0.5 text-apple-blue focus:ring-apple-blue/20">
                            <div>
                                <span class="font-bold text-apple-textPrimary block">Pilih Agent Tertentu (Multi-Select)</span>
                                <span class="text-[11px] text-apple-textSecondary mt-0.5 block leading-relaxed">Hanya staf yang dicentang di bawah yang secara spesifik ditugaskan menangani tiket website ini.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Multi-select Checklist Container -->
                    <div id="containerAgentChecklist" class="{{ $isAllAgents ? 'hidden' : 'grid' }} grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 pt-2 border-t border-apple-subtleBorder">
                        @forelse($agents as $ag)
                            @php
                                $isAssigned = in_array($ag->id, $assignedIds);
                                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $ag->name ?: 'User'), 0, 2));
                            @endphp
                            <label class="flex items-center gap-2.5 p-2.5 rounded-lg border {{ $isAssigned ? 'border-apple-blue/60 bg-blue-50/20' : 'border-apple-border bg-white' }} hover:bg-apple-canvas/60 cursor-pointer transition">
                                <input type="checkbox" name="agent_ids[]" value="{{ $ag->id }}" {{ $isAssigned ? 'checked' : '' }} onchange="updateAgentScopeBadge()" class="rounded border-apple-border text-apple-blue focus:ring-apple-blue/20 agent-checkbox">
                                <div class="w-7 h-7 rounded-full bg-apple-canvas text-apple-textPrimary font-semibold text-[10px] flex items-center justify-center border border-black/5 shrink-0">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-[11.5px] text-apple-textPrimary truncate">{{ $ag->name }}</div>
                                    <div class="text-[10px] text-apple-textTertiary truncate">{{ ucfirst($ag->role) }} &bull; {{ $ag->email }}</div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full p-4 text-center text-apple-textTertiary text-[11.5px]">
                                Belum ada anggota staf CS yang terdaftar. Tambahkan staf di menu Team.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 3. UNIVERSAL EMBED SCRIPT & PUBLIC KEY -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border">
                    <h3 class="text-[14px] font-bold text-apple-textPrimary">Universal Embed Script (1-Baris Kode)</h3>
                    <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tempelkan script tag ini sebelum penutup tag <code>&lt;/body&gt;</code> pada website Shopify, WordPress, WooCommerce, atau HTML kustom Anda.</p>
                </div>

                @php
                    $publicKey = $project->activeApiKey->public_key ?? 'pk_live_' . $project->slug;
                @endphp

                <!-- Snippet Box -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-[11.5px] font-semibold text-apple-textPrimary">
                        <span>Kode Script Embed</span>
                        <button type="button" onclick="copySnippetText('embedCodeDetail', this)" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-apple-border rounded-lg text-[11px] font-semibold hover:bg-apple-canvas transition shadow-2xs cursor-pointer">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span>Salin Kode</span>
                        </button>
                    </div>

                    <div id="embedCodeDetail" class="bg-[#1C1C1E] text-neutral-200 font-mono text-[11px] p-3 rounded-xl select-all break-all leading-relaxed shadow-inner">&lt;script src="{{ url('/chat-widget.js') }}" data-project-key="{{ $publicKey }}" data-api-url="{{ url('/') }}" async&gt;&lt;/script&gt;</div>
                </div>

                <!-- API Keys & Domains Quick Glance -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3 border-t border-apple-border">
                    <div class="p-3 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-[11px] font-semibold text-apple-textPrimary block mb-1">Public API Key</span>
                        <div class="font-mono text-[11px] text-neutral-700 bg-white p-2 rounded border border-apple-border select-all">{{ $publicKey }}</div>
                    </div>

                    <div class="p-3 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-[11px] font-semibold text-apple-textPrimary block mb-1">Domain Terdaftar Saat Ini</span>
                        <div class="font-mono text-[11px] text-neutral-700 bg-white p-2 rounded border border-apple-border">{{ $project->domains->pluck('domain')->implode(', ') ?: 'Belum ada domain' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 5: TELEGRAM NOTIFICATIONS & FORUM TOPICS                 -->
        <!-- ============================================================ -->
        <div id="tab-pane-telegram" class="tab-pane flex flex-col gap-4" style="display: none;">
            
            <!-- Master Toggle Card (Mirip Smart Bot) -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                
                <!-- Master Toggle Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-apple-border">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.62 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.89 8.01-3.46 3.82-1.6 4.61-1.88 5.14-1.89.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.19-.04.35z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-apple-textPrimary">Aktivasi Integrasi &amp; Notifikasi Telegram</h3>
                            <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Kirim notifikasi instan 1x ke grup CS dan balas chat customer via mode Telegram Forum Topics.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-apple-canvas/70 px-3.5 py-1.5 rounded-lg border border-apple-border self-start sm:self-auto">
                        <span id="telegramToggleLabel" class="text-[12px] font-bold {{ !empty($widgetSetting->telegram_notifications_enabled) ? 'text-sky-700' : 'text-neutral-500' }}">
                            {{ !empty($widgetSetting->telegram_notifications_enabled) ? 'Telegram Aktif (ON)' : 'Telegram Nonaktif (OFF)' }}
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="checkboxTelegramEnabled" name="telegram_notifications_enabled" value="1" {{ !empty($widgetSetting->telegram_notifications_enabled) ? 'checked' : '' }} onchange="toggleTelegramSwitch(this)" class="sr-only peer">
                            <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-sky-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Test Connection Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-sky-50/50 p-3 rounded-xl border border-sky-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ !empty($widgetSetting->telegram_notifications_enabled) ? 'bg-sky-500' : 'bg-neutral-400' }}"></span>
                        <span class="text-[12px] text-sky-950 font-medium">Uji token dan konektivitas bot ke grup Telegram:</span>
                    </div>
                    <button type="button" onclick="testTelegramConnection()" id="btnTestTelegram" class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white px-3.5 py-1.5 rounded-lg text-[11.5px] font-semibold transition shadow-apple-sm cursor-pointer self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.62 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.89 8.01-3.46 3.82-1.6 4.61-1.88 5.14-1.89.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.19-.04.35z"/></svg>
                        <span>⚡ Test Kirim Pesan ke Telegram</span>
                    </button>
                </div>

                <!-- Live Test Alert Box -->
                <div id="telegramTestResult" class="hidden p-3 rounded-xl text-[12px]"></div>

                <!-- Telegram Credentials & Topic Mode Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Input 1: Bot Token -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Telegram Bot Token (dari @BotFather)</label>
                        <input type="text" name="telegram_bot_token" id="inputTelegramBotToken" value="{{ old('telegram_bot_token', $widgetSetting->telegram_bot_token ?? '') }}" placeholder="Contoh: 7123456789:AAHq_ABCDEF123456789..." class="w-full text-[12px] font-mono px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Dibuat melalui bot resmi Telegram <strong>@BotFather</strong>.</p>
                    </div>

                    <!-- Input 2: Chat ID / Group ID -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Telegram Group / Supergroup Chat ID</label>
                        <input type="text" name="telegram_chat_id" id="inputTelegramChatId" value="{{ old('telegram_chat_id', $widgetSetting->telegram_chat_id ?? '') }}" placeholder="Contoh: -1001234567890 atau ID Akun Anda" class="w-full text-[12px] font-mono px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">ID Grup tempat bot diundang. Awali dengan tanda minus <code>-100...</code> untuk supergroup.</p>
                    </div>

                    <!-- Mode Forum Topics Sub-Toggle Card -->
                    <div class="md:col-span-2 p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30 flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[12px] font-bold text-apple-textPrimary block">Mode Forum Topics (Thread Balas 2-Arah)</span>
                                <span id="badgeTopicToggle" class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ !empty($widgetSetting->telegram_topic_mode_enabled) ? 'bg-sky-100 text-sky-800' : 'bg-neutral-100 text-neutral-600' }}">
                                    {{ !empty($widgetSetting->telegram_topic_mode_enabled) ? 'ON (Aktif)' : 'OFF' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-apple-textSecondary mt-0.5">Buat room topik terpisah per customer di grup: <code>[{{ $project->name }}] Nama (Kode)</code> dan teruskan balasan CS di topik Telegram kembali ke webchat pelanggan.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                            <input type="checkbox" name="telegram_topic_mode_enabled" id="toggleTelegramTopic" value="1" {{ !empty($widgetSetting->telegram_topic_mode_enabled) ? 'checked' : '' }} onchange="toggleTelegramTopicSwitch(this)" class="sr-only peer">
                            <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-sky-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Webhook Sync Box -->
                <div class="pt-3 border-t border-apple-border flex flex-col gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[12px] font-bold text-apple-textPrimary block">Telegram Webhook URL (Balas 2-Arah via Telegram)</span>
                            <p class="text-[11px] text-apple-textSecondary mt-0.5">Daftarkan URL ini ke server Telegram agar setiap pesan balasan dari staf CS di aplikasi Telegram otomatis tersinkron ke BeanTalk.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="setTelegramWebhookDirectly()" id="btnSetWebhook" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold transition shadow-apple-sm cursor-pointer">
                                <span>⚡ Daftarkan Webhook Otomatis</span>
                            </button>
                            <button type="button" onclick="copySnippetText('telegramWebhookUrl', this)" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white border border-apple-border rounded-lg text-[11px] font-semibold hover:bg-apple-canvas transition shadow-2xs cursor-pointer">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                <span>Salin URL</span>
                            </button>
                        </div>
                    </div>

                    <div id="telegramWebhookUrl" class="bg-[#1C1C1E] text-neutral-200 font-mono text-[11px] p-2.5 rounded-xl select-all break-all shadow-inner">{{ url('/api/v1/telegram/webhook') }}</div>

                    <!-- Live Webhook Set Result Box -->
                    <div id="telegramWebhookResult" class="hidden p-3 rounded-xl text-[12px]"></div>

                    <!-- Browser Manual Link Helper -->
                    <div class="p-3 bg-neutral-50 border border-apple-border rounded-xl text-[11px] text-apple-textSecondary flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span>💡 Atau buka link registrasi manual di browser:</span>
                        </div>
                        <a id="linkManualWebhook" href="https://api.telegram.org/bot{{ $widgetSetting->telegram_bot_token ?? '' }}/setWebhook?url={{ urlencode(url('/api/v1/telegram/webhook')) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-sky-600 hover:text-sky-700 font-semibold underline break-all">
                            <span>🔗 Buka Link Registrasi Webhook Telegram &rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Step-by-Step Setup Guide Card -->
                <div class="p-3.5 bg-sky-50/70 border border-sky-200 rounded-xl text-[11.5px] text-sky-950 flex flex-col gap-2">
                    <div class="font-bold text-sky-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-sky-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>Panduan 3 Langkah Cepat Integrasi Telegram</span>
                    </div>
                    <ol class="list-decimal list-inside space-y-1 text-[11px] text-sky-900/90 leading-relaxed pl-1">
                        <li>Buka <strong>@BotFather</strong> di Telegram, ketik <code>/newbot</code> untuk mendapatkan <strong>Token API</strong>. Kemudian matikan privasi dengan mengetik <code>/setprivacy</code> &rarr; pilih bot &rarr; <strong>Disable</strong>.</li>
                        <li>Buat Grup Telegram baru bersama tim CS, aktifkan <strong>Topics (Forum)</strong> di pengaturan grup, lalu undang bot Anda dan jadikan <strong>Administrator</strong> (centang <em>Manage Topics</em>).</li>
                        <li>Masukkan Token &amp; Chat ID di form ini, klik <strong>"⚡ Test Kirim Pesan"</strong>, lalu klik <strong>"⚡ Daftarkan Webhook Otomatis"</strong>.</li>
                    </ol>
                </div>

            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 6: JAM OPERASIONAL & OUT OF OFFICE (BUSINESS HOURS)      -->
        <!-- ============================================================ -->
        <div id="tab-pane-hours" class="tab-pane flex flex-col gap-4" style="display: none;">
            <input type="hidden" name="has_business_hours_form" value="1">

            <!-- Master Toggle Card -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-apple-border">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-apple-textPrimary">Aktivasi Jam Kerja &amp; Jadwal Operasional</h3>
                            <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Atur jam operasional tim CS Anda. Saat di luar jam kerja, widget otomatis menampilkan banner informasi dan mewajibkan email customer.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-apple-canvas/70 px-3.5 py-1.5 rounded-lg border border-apple-border self-start sm:self-auto">
                        <span id="hoursToggleLabel" class="text-[12px] font-bold {{ $widgetSetting->business_hours_enabled ? 'text-amber-700' : 'text-neutral-500' }}">
                            {{ $widgetSetting->business_hours_enabled ? 'Jam Kerja Aktif (ON)' : 'Jam Kerja Nonaktif (24/7 Selalu Buka)' }}
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="checkboxHoursEnabled" name="business_hours_enabled" value="1" {{ $widgetSetting->business_hours_enabled ? 'checked' : '' }} onchange="toggleHoursSwitch(this)" class="sr-only peer">
                            <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Current Status Telemetry Banner -->
                <div class="p-3.5 rounded-xl border {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? 'bg-emerald-50/70 border-emerald-200' : 'bg-amber-50/70 border-amber-200') : 'bg-neutral-50 border-neutral-200' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500') : 'bg-neutral-400' }}"></span>
                        <div>
                            <span class="text-[12px] font-bold {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? 'text-emerald-900' : 'text-amber-900') : 'text-neutral-700' }}">
                                @if(!$widgetSetting->business_hours_enabled)
                                    Status Saat Ini: Layanan Terbuka 24 Jam Nonstop (Fitur Jam Kerja Dimatikan)
                                @elseif($isWithinBusinessHours)
                                    Status Saat Ini: 🟢 Sedang Dalam Jam Operasional (CS Online)
                                @else
                                    Status Saat Ini: 🌙 Sedang Di Luar Jam Kerja (Auto-Responder Email Aktif)
                                @endif
                            </span>
                            <div class="text-[11px] {{ $widgetSetting->business_hours_enabled ? ($isWithinBusinessHours ? 'text-emerald-700' : 'text-amber-700') : 'text-neutral-500' }}">
                                Zona Waktu: <strong>{{ $widgetSetting->business_hours_timezone ?: 'Asia/Jakarta' }}</strong> &bull; Waktu Lokal CS Saat Ini: {{ \Carbon\Carbon::now($widgetSetting->business_hours_timezone ?: 'Asia/Jakarta')->format('l, d M Y — H:i') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timezone & Off-Hours Message Configuration -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Zona Waktu Operasional CS</label>
                        <select name="business_hours_timezone" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 cursor-pointer">
                            @php
                                $currentTimezone = $widgetSetting->business_hours_timezone ?: 'Asia/Jakarta';
                                $tzOptions = [
                                    'Asia/Jakarta'   => 'WIB — Waktu Indonesia Barat (Jakarta, Surabaya, Medan) [UTC+7]',
                                    'Asia/Makassar'  => 'WITA — Waktu Indonesia Tengah (Bali, Makassar, Manado) [UTC+8]',
                                    'Asia/Jayapura'  => 'WIT — Waktu Indonesia Timur (Papua, Ambon) [UTC+9]',
                                    'Asia/Singapore' => 'SGT — Singapore / Malaysia [UTC+8]',
                                    'UTC'            => 'UTC — Universal Time Coordinated [UTC+0]',
                                ];
                            @endphp
                            @foreach($tzOptions as $tzVal => $tzLabel)
                                <option value="{{ $tzVal }}" {{ $currentTimezone === $tzVal ? 'selected' : '' }}>{{ $tzLabel }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Perhitungan jam buka/tutup merujuk tepat pada waktu zona ini.</p>
                    </div>

                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Pesan Di Luar Jam Kerja (Off-Hours Banner)</label>
                        <textarea name="business_hours_off_message" rows="2" placeholder="Saat ini di luar jam kerja. Pesan Anda tetap kami terima dan akan dibalas via email." class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">{{ old('business_hours_off_message', $widgetSetting->business_hours_off_message) }}</textarea>
                        <p class="text-[10.5px] text-apple-textTertiary mt-0.5">Teks ini tampil otomatis di header banner widget saat customer chat di luar jam kerja.</p>
                    </div>
                </div>
            </div>

            <!-- Weekly Schedule Card -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                    <div>
                        <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Jadwal Jam Buka Harian (7 Hari)</h4>
                        <p class="text-[11px] text-apple-textSecondary mt-0.5">Tentukan hari aktif serta rentang jam operasional tim CS Anda.</p>
                    </div>

                    <!-- Preset Quick Action Buttons -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] text-apple-textTertiary mr-1">Template Cepat:</span>
                        <button type="button" onclick="applyHoursPreset('office')" class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-apple-canvas hover:bg-apple-border/50 text-apple-textPrimary border border-apple-border transition cursor-pointer shadow-2xs">
                            🏢 Senin-Jumat (08:00 - 17:00)
                        </button>
                        <button type="button" onclick="applyHoursPreset('retail')" class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-apple-canvas hover:bg-apple-border/50 text-apple-textPrimary border border-apple-border transition cursor-pointer shadow-2xs">
                            🛍️ Senin-Sabtu (09:00 - 21:00)
                        </button>
                        <button type="button" onclick="applyHoursPreset('always')" class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-apple-canvas hover:bg-apple-border/50 text-apple-textPrimary border border-apple-border transition cursor-pointer shadow-2xs">
                            ⚡ 24 Jam Nonstop
                        </button>
                    </div>
                </div>

                <!-- Days Schedule Grid -->
                @php
                    $daysMeta = [
                        'mon' => ['name' => 'Senin', 'default' => true],
                        'tue' => ['name' => 'Selasa', 'default' => true],
                        'wed' => ['name' => 'Rabu', 'default' => true],
                        'thu' => ['name' => 'Kamis', 'default' => true],
                        'fri' => ['name' => 'Jumat', 'default' => true],
                        'sat' => ['name' => 'Sabtu', 'default' => false],
                        'sun' => ['name' => 'Minggu', 'default' => false],
                    ];
                @endphp

                <div class="flex flex-col gap-2.5">
                    @foreach($daysMeta as $key => $meta)
                        @php
                            $dayConfig = $currentSchedule[$key] ?? ['enabled' => $meta['default'], 'start' => '08:00', 'end' => '17:00'];
                            $isDayEnabled = !empty($dayConfig['enabled']);
                            $startTime = $dayConfig['start'] ?? '08:00';
                            $endTime = $dayConfig['end'] ?? '17:00';
                        @endphp
                        <div id="row-day-{{ $key }}" class="p-3 rounded-xl border transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $isDayEnabled ? 'bg-white border-apple-border' : 'bg-neutral-50/70 border-neutral-200' }}">
                            <div class="flex items-center gap-3 sm:w-44">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="business_hours[{{ $key }}][enabled]" value="1" {{ $isDayEnabled ? 'checked' : '' }} onchange="toggleDayRow('{{ $key }}', this.checked)" class="sr-only peer day-checkbox" data-day="{{ $key }}">
                                    <div class="w-8 h-4 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                                <div>
                                    <span class="text-[12.5px] font-bold text-apple-textPrimary">{{ $meta['name'] }}</span>
                                    <span id="badge-day-{{ $key }}" class="block text-[10.5px] font-semibold {{ $isDayEnabled ? 'text-emerald-700' : 'text-neutral-400' }}">
                                        {{ $isDayEnabled ? 'Buka Operasional' : 'Libur / Tutup' }}
                                    </span>
                                </div>
                            </div>

                            <div id="times-day-{{ $key }}" class="flex items-center gap-2 flex-1 sm:justify-end {{ $isDayEnabled ? '' : 'opacity-40 pointer-events-none' }}">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] text-apple-textSecondary font-medium">Mulai:</span>
                                    <input type="time" name="business_hours[{{ $key }}][start]" value="{{ $startTime }}" class="text-[12px] font-mono px-2.5 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 day-start-input" data-day="{{ $key }}">
                                </div>
                                <span class="text-apple-textTertiary text-[12px] font-bold">—</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] text-apple-textSecondary font-medium">Selesai:</span>
                                    <input type="time" name="business_hours[{{ $key }}][end]" value="{{ $endTime }}" class="text-[12px] font-mono px-2.5 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 day-end-input" data-day="{{ $key }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 7: SOUND NOTIFICATION SETTINGS (AGENT & CUSTOMER)        -->
        <!-- ============================================================ -->
        <div id="tab-pane-sound" class="tab-pane flex flex-col gap-5" style="display: none;">
            <input type="hidden" name="has_sound_settings_form" value="1">
            <input type="hidden" name="has_widget_sound_settings_form" value="1">

            <!-- Global Audio Settings Header Banner -->
            <div class="bg-gradient-to-r from-rose-50 via-pink-50 to-purple-50 border border-rose-200/80 rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 to-pink-600 text-white flex items-center justify-center shadow-rose-500/20 shadow-md shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[14.5px] font-bold text-apple-textPrimary">Pusat Pengaturan Suara Notifikasi (Audio Alert Hub)</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">2 Jalur Mandiri</span>
                        </div>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">
                            Atur suara peringatan untuk <strong>Staf CS (Notify Agent)</strong> saat ada tiket masuk, dan suara interaktif untuk <strong>Pengunjung Website (Notify Customer)</strong> saat menerima balasan di balon chat.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                    <a href="#section-notify-agent" class="px-3 py-1.5 rounded-lg text-[11px] font-bold bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 transition shadow-2xs">
                        🎧 1. Notify Agent
                    </a>
                    <a href="#section-notify-customer" class="px-3 py-1.5 rounded-lg text-[11px] font-bold bg-white border border-purple-200 text-purple-700 hover:bg-purple-50 transition shadow-2xs">
                        💬 2. Notify Customer
                    </a>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- SECTION 1: NOTIFY AGENT (ADMIN & CS INBOX ALARM)               -->
            <!-- ============================================================== -->
            <div id="section-notify-agent" class="flex flex-col gap-4">
                
                <!-- Section 1 Header Badge -->
                <div class="flex items-center justify-between pb-1 border-b border-rose-200/80">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-rose-600 text-white font-bold text-[11px] flex items-center justify-center">1</span>
                        <h4 class="text-[14px] font-bold text-rose-950">NOTIFY AGENT: Alarm Notifikasi Staf CS (Admin Inbox)</h4>
                    </div>
                    <span class="text-[11px] font-semibold text-rose-600">Berdering saat customer mengirim pesan baru</span>
                </div>

                <!-- Master Toggle Card Agent -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-apple-border">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Aktivasi Alarm Pesan Masuk Staf CS</h4>
                                <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Bunyikan suara peringatan saat pengunjung mengirim chat baru ke inbox CS website ini.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 bg-apple-canvas/70 px-3.5 py-1.5 rounded-lg border border-apple-border self-start sm:self-auto">
                            <span id="soundToggleLabel" class="text-[12px] font-bold {{ $widgetSetting->sound_enabled ? 'text-rose-700' : 'text-neutral-500' }}">
                                {{ $widgetSetting->sound_enabled ? 'Alarm CS Aktif (ON)' : 'Alarm CS Hening (MUTE)' }}
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="checkboxSoundEnabled" name="sound_enabled" value="1" {{ $widgetSetting->sound_enabled ? 'checked' : '' }} onchange="toggleSoundSwitch(this)" class="sr-only peer">
                                <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-rose-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Info Banner Smart Auto-Mute -->
                    <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-200/80 text-[12px] text-rose-900 flex items-start gap-2.5 leading-relaxed">
                        <span class="text-base shrink-0">💡</span>
                        <div>
                            <strong class="font-bold text-rose-950">Aturan Cerdas Pemutaran Suara CS:</strong>
                            <ul class="list-disc list-inside mt-1 space-y-0.5 text-[11.5px] text-rose-800">
                                <li>Jika pesan masuk dan <strong>tiket obrolan sedang dibuka aktif</strong> oleh CS di layar, suara akan <strong>otomatis mati/hening</strong> agar tidak mengganggu.</li>
                                <li>Jika tiket pesan <strong>belum dibuka</strong> (atau CS sedang berada di tab/aplikasi lain), alarm suara akan <strong>berbunyi berulang-ulang</strong> selama batas durasi yang Anda tentukan di bawah, atau sampai CS membuka pesan tersebut.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Duration & Presets Card Agent -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                        <div>
                            <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Lama Waktu Suara Menyala (Durasi Alarm CS)</h4>
                            <p class="text-[11px] text-apple-textSecondary mt-0.5">Berapa detik suara terus berdering jika pesan masuk belum dibuka oleh CS.</p>
                        </div>

                        <!-- Preset Duration Quick Buttons -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] text-apple-textTertiary mr-1">Preset Cepat:</span>
                            <button type="button" onclick="setSoundDuration(5)" class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-apple-canvas hover:bg-apple-border/50 text-apple-textPrimary border border-apple-border transition cursor-pointer shadow-2xs">
                                5 Detik
                            </button>
                            <button type="button" onclick="setSoundDuration(10)" class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-apple-canvas hover:bg-apple-border/50 text-apple-textPrimary border border-apple-border transition cursor-pointer shadow-2xs">
                                10 Detik
                            </button>
                            <button type="button" onclick="setSoundDuration(15)" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition cursor-pointer shadow-2xs">
                                ⭐ 15 Detik (Ideal)
                            </button>
                            <button type="button" onclick="setSoundDuration(30)" class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-apple-canvas hover:bg-apple-border/50 text-apple-textPrimary border border-apple-border transition cursor-pointer shadow-2xs">
                                30 Detik
                            </button>
                            <button type="button" onclick="setSoundDuration(60)" class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-apple-canvas hover:bg-apple-border/50 text-apple-textPrimary border border-apple-border transition cursor-pointer shadow-2xs">
                                60 Detik
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div>
                            <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Durasi Menyala (Detik):</label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="inputSoundDuration" name="sound_duration" value="{{ old('sound_duration', $widgetSetting->sound_duration ?: 15) }}" min="3" max="120" class="w-32 text-[13px] font-mono font-bold px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                                <span class="text-[12px] font-semibold text-apple-textSecondary">Detik (Rentang: 3 - 120 detik)</span>
                            </div>
                            <p class="text-[10.5px] text-apple-textTertiary mt-1">Alarm otomatis berhenti setelah durasi ini berakhir jika pesan belum direspons.</p>
                        </div>

                        <div class="bg-apple-canvas/40 border border-apple-border rounded-xl p-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 font-mono font-bold text-[12px]">
                                ⏱️
                            </div>
                            <div class="text-[11.5px] text-apple-textSecondary leading-snug">
                                Status aktif saat ini: <strong id="previewCurrentDurationLabel" class="text-rose-700 font-bold font-mono">{{ $widgetSetting->sound_duration ?: 15 }} Detik</strong> per notifikasi pesan masuk.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sound Preset Selection Card Agent -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                    <div class="pb-3 border-b border-apple-border">
                        <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Pilih Karakter &amp; Tipe Suara Alarm CS</h4>
                        <p class="text-[11px] text-apple-textSecondary mt-0.5">Pilih preset efek suara peringatan CS atau unggah file audio khusus milik Anda.</p>
                    </div>

                    @php
                        $currentSoundType = old('sound_type', $widgetSetting->sound_type ?: 'pedestrian');
                    @endphp

                    <!-- Sound Option Radio Cards Grid Agent -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Option 1: Pedestrian (Tot Tot) -->
                        <label class="sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentSoundType === 'pedestrian' ? 'border-rose-500 bg-rose-50/30 ring-2 ring-rose-500/20' : 'border-apple-border hover:border-rose-300 bg-white' }}" onclick="previewAgentSound('pedestrian', event, false)">
                            <input type="radio" name="sound_type" value="pedestrian" {{ $currentSoundType === 'pedestrian' ? 'checked' : '' }} onchange="onSoundTypeChanged(this.value)" class="mt-1 text-rose-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🚦</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Lampu Merah Penyeberangan</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-amber-100 text-amber-800">"Tot-tot-tot"</span>
                                    </div>
                                    <button type="button" onclick="previewAgentSound('pedestrian', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Suara akustik penyeberangan zebra cross dengan ketukan ritmis teratur dan frekuensi tinggi yang sangat mudah disadari.
                                </p>
                            </div>
                        </label>

                        <!-- Option 2: Ambulance (Ninu Ninu) -->
                        <label class="sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentSoundType === 'ambulance' ? 'border-rose-500 bg-rose-50/30 ring-2 ring-rose-500/20' : 'border-apple-border hover:border-rose-300 bg-white' }}" onclick="previewAgentSound('ambulance', event, false)">
                            <input type="radio" name="sound_type" value="ambulance" {{ $currentSoundType === 'ambulance' ? 'checked' : '' }} onchange="onSoundTypeChanged(this.value)" class="mt-1 text-rose-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🚑</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Suara Ambulans</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-rose-100 text-rose-800">"Ninu-ninu"</span>
                                    </div>
                                    <button type="button" onclick="previewAgentSound('ambulance', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Sirine dual-tone ambulans darurat (frekuensi ganda 960Hz / 770Hz) bergantian, sangat mencolok untuk situasi prioritas tinggi.
                                </p>
                            </div>
                        </label>

                        <!-- Option 3: Police / Mobil Dinas (Wut Wut) -->
                        <label class="sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentSoundType === 'police' ? 'border-rose-500 bg-rose-50/30 ring-2 ring-rose-500/20' : 'border-apple-border hover:border-rose-300 bg-white' }}" onclick="previewAgentSound('police', event, false)">
                            <input type="radio" name="sound_type" value="police" {{ $currentSoundType === 'police' ? 'checked' : '' }} onchange="onSoundTypeChanged(this.value)" class="mt-1 text-rose-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🚓</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Mobil Dinas / Patwal</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-sky-100 text-sky-800">"Wut-wut"</span>
                                    </div>
                                    <button type="button" onclick="previewAgentSound('police', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Suara sirine yelp mobil dinas pengawalan dengan frekuensi melengking cepat (sweep band-pass), terdengar energik dan tegas.
                                </p>
                            </div>
                        </label>

                        <!-- Option 4: Apple Harmonic Chime (Elegan) -->
                        <label class="sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentSoundType === 'chime' ? 'border-rose-500 bg-rose-50/30 ring-2 ring-rose-500/20' : 'border-apple-border hover:border-rose-300 bg-white' }}" onclick="previewAgentSound('chime', event, false)">
                            <input type="radio" name="sound_type" value="chime" {{ $currentSoundType === 'chime' ? 'checked' : '' }} onchange="onSoundTypeChanged(this.value)" class="mt-1 text-rose-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🔔</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Apple Chime Harmonis</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-emerald-100 text-emerald-800">Lembut &amp; Elegan</span>
                                    </div>
                                    <button type="button" onclick="previewAgentSound('chime', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Nada chime akustik bernuansa Apple iOS (E5 -> A5 glide + C#6 harmonic), nyaman di telinga dan cocok untuk kantor tenang.
                                </p>
                            </div>
                        </label>

                        <!-- Option 5: Custom Audio Upload Agent -->
                        <label class="sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 md:col-span-2 {{ $currentSoundType === 'custom' ? 'border-rose-500 bg-rose-50/30 ring-2 ring-rose-500/20' : 'border-apple-border hover:border-rose-300 bg-white' }}" onclick="previewAgentSound('custom', event, false)">
                            <input type="radio" name="sound_type" value="custom" {{ $currentSoundType === 'custom' ? 'checked' : '' }} onchange="onSoundTypeChanged(this.value)" class="mt-1 text-rose-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">📁</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Unggah File Audio Kustom CS (Upload Sound)</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-purple-100 text-purple-800">MP3 / WAV / OGG</span>
                                    </div>
                                    <button type="button" onclick="previewAgentSound('custom', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Gunakan rekaman bel pintu khusus, ringtone WAV/MP3, atau efek suara perusahaan Anda sendiri untuk alarm CS (Maksimal 3MB).
                                </p>

                                <div id="containerCustomSoundUpload" class="mt-3 pt-3 border-t border-apple-border/70 flex flex-col sm:flex-row sm:items-center gap-3 {{ $currentSoundType === 'custom' ? '' : 'hidden' }}">
                                    <div class="flex-1">
                                        <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Pilih File Audio (MP3 / WAV / OGG):</label>
                                        <input type="file" id="inputCustomSoundFile" name="sound_custom_file" accept=".mp3,.wav,.ogg,audio/*" onchange="onCustomFileSelected(this)" class="w-full text-[11.5px] text-apple-textSecondary file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11.5px] file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer">
                                    </div>

                                    @if(!empty($widgetSetting->sound_custom_url))
                                        <div class="sm:w-64 bg-white p-2 rounded-lg border border-apple-border flex flex-col gap-1">
                                            <span class="text-[10px] font-bold text-emerald-700 flex items-center gap-1">
                                                ✓ Audio Kustom CS Tersimpan
                                            </span>
                                            <audio id="existingCustomAudioEl" controls class="w-full h-7">
                                                <source src="{{ $widgetSetting->sound_custom_url }}" type="audio/mpeg">
                                            </audio>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Live Test Controller Card Agent -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white flex items-center justify-center shadow-rose-500/20 shadow-lg shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Uji Coba Alarm CS (Live Test Agent)</h4>
                            <p class="text-[11px] text-apple-textSecondary mt-0.5">Dengarkan suara alarm CS sesuai durasi waktu yang sudah ditentukan di atas.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        <button type="button" id="btnTestPlaySound" onclick="testPlayCurrentSound()" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-[12px] font-semibold transition shadow-apple-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                            <span>▶ Putar Alarm CS</span>
                        </button>

                        <button type="button" id="btnTestStopSound" onclick="testStopCurrentSound()" disabled class="inline-flex items-center gap-2 bg-neutral-200 text-neutral-400 px-3.5 py-2 rounded-lg text-[12px] font-semibold transition cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="6" y="6" width="12" height="12" rx="2"></rect>
                            </svg>
                            <span>⏹ Hentikan (Stop)</span>
                        </button>
                    </div>
                </div>

                <!-- Testing Status Active Banner Agent -->
                <div id="bannerSoundTestingStatus" class="hidden p-3.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow-md flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-white animate-ping"></span>
                        <span class="text-[12.5px] font-bold">
                            🔊 Sedang Memutar Uji Coba Alarm CS: <span id="labelTestingCountdown" class="font-mono text-amber-200">15</span> detik tersisa...
                        </span>
                    </div>
                    <button type="button" onclick="testStopCurrentSound()" class="px-2.5 py-1 rounded bg-white/20 hover:bg-white/30 text-white text-[11px] font-bold cursor-pointer transition">
                        Hentikan Sekarang ✕
                    </button>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- SECTION 2: NOTIFY CUSTOMER (WEBSITE BUBBLE CHAT SOUND)         -->
            <!-- ============================================================== -->
            <div id="section-notify-customer" class="flex flex-col gap-4 pt-4 border-t-2 border-dashed border-apple-border">
                
                <!-- Section 2 Header Badge -->
                <div class="flex items-center justify-between pb-1 border-b border-purple-200/80">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-purple-600 text-white font-bold text-[11px] flex items-center justify-center">2</span>
                        <h4 class="text-[14px] font-bold text-purple-950">NOTIFY CUSTOMER: Suara Balasan Bubble Chat (Pengunjung Website)</h4>
                    </div>
                    <span class="text-[11px] font-semibold text-purple-600">Berbunyi 1x saat pengunjung menerima balasan chat</span>
                </div>

                <!-- Master Toggle Card Customer -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-apple-border">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Aktivasi Suara Notifikasi Bubble Chat Pengunjung</h4>
                                <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Bunyikan efek suara halus 1x ketika pengunjung menerima respon balasan dari Staf CS atau Bot di widget website.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 bg-apple-canvas/70 px-3.5 py-1.5 rounded-lg border border-apple-border self-start sm:self-auto">
                            <span id="widgetSoundToggleLabel" class="text-[12px] font-bold {{ ($widgetSetting->widget_sound_enabled ?? true) ? 'text-purple-700' : 'text-neutral-500' }}">
                                {{ ($widgetSetting->widget_sound_enabled ?? true) ? 'Suara Bubble ON' : 'Suara Bubble MUTE' }}
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="checkboxWidgetSoundEnabled" name="widget_sound_enabled" value="1" {{ ($widgetSetting->widget_sound_enabled ?? true) ? 'checked' : '' }} onchange="toggleWidgetSoundSwitch(this)" class="sr-only peer">
                                <div class="w-10 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-purple-50/70 border border-purple-200/80 text-[12px] text-purple-900 flex items-start gap-2.5 leading-relaxed">
                        <span class="text-base shrink-0">✨</span>
                        <div>
                            <strong class="font-bold text-purple-950">Kenyamanan Pengunjung:</strong>
                            <p class="mt-0.5 text-[11.5px] text-purple-800">
                                Suara notifikasi customer diputar sekilas (0.1 - 0.4 detik) hanya saat ada pesan baru dari pihak CS/Bot. Ini memastikan pengunjung yang sedang melihat tab lain di browser segera mengetahui bahwa chat mereka telah dibalas.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sound Preset Selection Card Customer -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                    <div class="pb-3 border-b border-apple-border">
                        <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Pilih Karakter Suara Bubble Chat Pengunjung</h4>
                        <p class="text-[11px] text-apple-textSecondary mt-0.5">Pilih salah satu dari 4 efek suara sintetis web audio berkelas, atau unggah nada sapaan khusus.</p>
                    </div>

                    @php
                        $currentWidgetSoundType = old('widget_sound_type', $widgetSetting->widget_sound_type ?: 'chime');
                    @endphp

                    <!-- Sound Option Radio Cards Grid Customer -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Option 1: Apple Harmonic Chime (Default) -->
                        <label class="widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentWidgetSoundType === 'chime' ? 'border-purple-500 bg-purple-50/30 ring-2 ring-purple-500/20' : 'border-apple-border hover:border-purple-300 bg-white' }}" onclick="previewWidgetSound('chime', event, false)">
                            <input type="radio" name="widget_sound_type" value="chime" {{ $currentWidgetSoundType === 'chime' ? 'checked' : '' }} onchange="onWidgetSoundTypeChanged(this.value)" class="mt-1 text-purple-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🔔</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Apple Chime Harmonis</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-purple-100 text-purple-800">Default &bull; Elegan</span>
                                    </div>
                                    <button type="button" onclick="previewWidgetSound('chime', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Nada glide E5 &rarr; A5 khas perangkat modern iOS, terasa sangat lembut, profesional, dan menyejukkan.
                                </p>
                            </div>
                        </label>

                        <!-- Option 2: Aquatic Bubble Pop -->
                        <label class="widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentWidgetSoundType === 'pop' ? 'border-purple-500 bg-purple-50/30 ring-2 ring-purple-500/20' : 'border-apple-border hover:border-purple-300 bg-white' }}" onclick="previewWidgetSound('pop', event, false)">
                            <input type="radio" name="widget_sound_type" value="pop" {{ $currentWidgetSoundType === 'pop' ? 'checked' : '' }} onchange="onWidgetSoundTypeChanged(this.value)" class="mt-1 text-purple-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🫧</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Aquatic Bubble Pop</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-sky-100 text-sky-800">"Pop!" Renyah</span>
                                    </div>
                                    <button type="button" onclick="previewWidgetSound('pop', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Letupan gelembung air instan berdurasi 0.08 detik. Segar, ramah, dan sangat pas untuk antarmuka chat modern.
                                </p>
                            </div>
                        </label>

                        <!-- Option 3: Crystal Ding Bell -->
                        <label class="widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentWidgetSoundType === 'ding' ? 'border-purple-500 bg-purple-50/30 ring-2 ring-purple-500/20' : 'border-apple-border hover:border-purple-300 bg-white' }}" onclick="previewWidgetSound('ding', event, false)">
                            <input type="radio" name="widget_sound_type" value="ding" {{ $currentWidgetSoundType === 'ding' ? 'checked' : '' }} onchange="onWidgetSoundTypeChanged(this.value)" class="mt-1 text-purple-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🛎️</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Crystal Reception Ding</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-amber-100 text-amber-800">"Ting!" Jernih</span>
                                    </div>
                                    <button type="button" onclick="previewWidgetSound('ding', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Lonceng meja resepsionis kristal dengan resonansi harmonik murni (1318Hz), memberi kesan pelayanan premium.
                                </p>
                            </div>
                        </label>

                        <!-- Option 4: Melodic Marimba -->
                        <label class="widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 {{ $currentWidgetSoundType === 'marimba' ? 'border-purple-500 bg-purple-50/30 ring-2 ring-purple-500/20' : 'border-apple-border hover:border-purple-300 bg-white' }}" onclick="previewWidgetSound('marimba', event, false)">
                            <input type="radio" name="widget_sound_type" value="marimba" {{ $currentWidgetSoundType === 'marimba' ? 'checked' : '' }} onchange="onWidgetSoundTypeChanged(this.value)" class="mt-1 text-purple-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🎶</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Melodic Marimba Chord</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-emerald-100 text-emerald-800">3-Nada Ceria</span>
                                    </div>
                                    <button type="button" onclick="previewWidgetSound('marimba', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Ketukan marimba kayu naik 3 nada cepat (C6 - E6 - G6), memberi nuansa hangat, ceria, dan bersahabat bagi customer.
                                </p>
                            </div>
                        </label>

                        <!-- Option 5: Custom Audio Upload Customer -->
                        <label class="widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 md:col-span-2 {{ $currentWidgetSoundType === 'custom' ? 'border-purple-500 bg-purple-50/30 ring-2 ring-purple-500/20' : 'border-apple-border hover:border-purple-300 bg-white' }}" onclick="previewWidgetSound('custom', event, false)">
                            <input type="radio" name="widget_sound_type" value="custom" {{ $currentWidgetSoundType === 'custom' ? 'checked' : '' }} onchange="onWidgetSoundTypeChanged(this.value)" class="mt-1 text-purple-600 focus:ring-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">📁</span>
                                        <strong class="text-[13px] font-bold text-apple-textPrimary">Unggah Audio Kustom Pengunjung (Upload Customer Sound)</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-violet-100 text-violet-800">MP3 / WAV / OGG</span>
                                    </div>
                                    <button type="button" onclick="previewWidgetSound('custom', event, true)" class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-white border border-apple-border text-apple-textPrimary hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition shadow-2xs flex items-center gap-1 shrink-0" title="Dengarkan cuplikan suara">
                                        <span>🔊 Dengar</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-apple-textSecondary mt-1 leading-relaxed">
                                    Gunakan sound effect khas brand Anda sendiri untuk diputar saat customer menerima balasan (Maksimal 3MB).
                                </p>

                                <div id="containerWidgetCustomSoundUpload" class="mt-3 pt-3 border-t border-apple-border/70 flex flex-col sm:flex-row sm:items-center gap-3 {{ $currentWidgetSoundType === 'custom' ? '' : 'hidden' }}">
                                    <div class="flex-1">
                                        <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Pilih File Audio Pengunjung (MP3 / WAV / OGG):</label>
                                        <input type="file" id="inputWidgetCustomSoundFile" name="widget_sound_custom_file" accept=".mp3,.wav,.ogg,audio/*" onchange="onWidgetCustomFileSelected(this)" class="w-full text-[11.5px] text-apple-textSecondary file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11.5px] file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                                    </div>

                                    @if(!empty($widgetSetting->widget_sound_custom_url))
                                        <div class="sm:w-64 bg-white p-2 rounded-lg border border-apple-border flex flex-col gap-1">
                                            <span class="text-[10px] font-bold text-emerald-700 flex items-center gap-1">
                                                ✓ Audio Kustom Pengunjung Tersimpan
                                            </span>
                                            <audio id="existingWidgetCustomAudioEl" controls class="w-full h-7">
                                                <source src="{{ $widgetSetting->widget_sound_custom_url }}" type="audio/mpeg">
                                            </audio>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Live Test Controller Card Customer -->
                <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center shadow-purple-500/20 shadow-lg shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-[13.5px] font-bold text-apple-textPrimary">Uji Coba Suara Balasan Customer (Live Test Customer)</h4>
                            <p class="text-[11px] text-apple-textSecondary mt-0.5">Dengarkan efek suara yang akan didengar oleh pengunjung website saat menerima balasan chat.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        <button type="button" id="btnTestPlayWidgetSound" onclick="testPlayCurrentWidgetSound()" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-[12px] font-semibold transition shadow-apple-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                            <span>▶ Putar Suara Customer</span>
                        </button>
                    </div>
                </div>

            </div>

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
