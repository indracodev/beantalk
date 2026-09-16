@extends('layouts.admin')

@section('title', 'Pengaturan Integrasi: ' . $project->name)
@section('header_title', 'Pengaturan Integrasi: ' . $project->name)

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 bg-apple-canvas/30 w-full">

    <!-- MAIN FORM WRAPPER -->
    <form id="formIntegrationSettings" action="{{ route('admin.integrations.settings', $project->id) }}" method="POST" onsubmit="serializeAllSettings()" class="flex flex-col gap-4 sm:gap-5 w-full">
        @csrf
        @method('PUT')

        <!-- Hidden Inputs to serialize rules & social channels into JSON -->
        <input type="hidden" name="bot_rules" id="hiddenBotRules" value="{{ json_encode($botRules) }}">
        <input type="hidden" name="social_channels" id="hiddenSocialChannels" value="{{ json_encode($socialChannelsList) }}">
        <input type="hidden" name="bot_welcome_options" id="hiddenBotWelcomeOptions" value="{{ json_encode($widgetSetting->bot_welcome_options ?? []) }}">
        <input type="hidden" name="bot_tree" id="hiddenBotTree" value="{{ json_encode($widgetSetting->bot_tree ?? []) }}">

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
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tambahkan saluran komunikasi resmi atau toko Anda (WhatsApp, Instagram, Telegram, Shopee, Tokopedia, Custom Link, dsb). Mendukung banyak kontak untuk platform yang sama.</p>
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
    { value: 'telegram', name: 'Telegram', placeholder: '@username atau https://t.me/...', badgeClass: 'bg-sky-100 text-sky-800' },
    { value: 'messenger', name: 'Facebook Messenger', placeholder: 'username atau https://m.me/...', badgeClass: 'bg-blue-100 text-blue-800' },
    { value: 'shopee', name: 'Shopee Store', placeholder: 'https://shopee.co.id/...', badgeClass: 'bg-orange-100 text-orange-800' },
    { value: 'tokopedia', name: 'Tokopedia Store', placeholder: 'https://tokopedia.com/...', badgeClass: 'bg-green-100 text-green-800' },
    { value: 'custom', name: 'Custom Link / Website', placeholder: 'https://...', badgeClass: 'bg-slate-100 text-slate-800' }
];

// 1. Tab Switching Function (Zero dependencies, Bulletproof inline display toggle)
function switchDetailTab(tabName) {
    const tabs = ['bot', 'appearance', 'social', 'embed', 'telegram'];
    
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
                } else if (t === 'telegram') {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-sky-600 text-white shadow-2xs cursor-pointer';
                } else {
                    btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2 bg-apple-blue text-white shadow-2xs cursor-pointer';
                }
            } else {
                btn.className = 'tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer';
            }
        }
    });
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
            const currentPlatform = chan.platform || chan.icon || 'whatsapp';
            const matchedOpt = PLATFORM_OPTIONS.find(o => o.value === currentPlatform) || PLATFORM_OPTIONS[0];

            row.className = 'p-3.5 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-emerald-300 transition flex flex-col md:flex-row items-start md:items-center gap-3';
            
            let platformOptionsHtml = '';
            PLATFORM_OPTIONS.forEach(opt => {
                const selected = (opt.value === currentPlatform) ? 'selected' : '';
                platformOptionsHtml += `<option value="${opt.value}" ${selected}>${opt.name}</option>`;
            });

            row.innerHTML = `
                <div class="flex items-center gap-2 shrink-0 self-start md:self-center">
                    <span class="w-6 h-6 rounded-full bg-apple-canvas border border-apple-border text-[11px] font-bold text-apple-textSecondary flex items-center justify-center">
                        ${idx + 1}
                    </span>
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

                <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                    <label class="inline-flex items-center gap-1.5 text-[11.5px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer ${isEnabled ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-neutral-100 border-neutral-200 text-neutral-500'}">
                        <input type="checkbox" ${isEnabled ? 'checked' : ''} onchange="updateSocialChannelField(${idx}, 'enabled', this.checked)" class="rounded text-emerald-600 focus:ring-0 cursor-pointer">
                        <span>${isEnabled ? 'Aktif' : 'Nonaktif'}</span>
                    </label>

                    <button type="button" onclick="removeSocialChannel(${idx})" class="text-neutral-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition cursor-pointer" title="Hapus Kontak Ini">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
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
        if (!currentSocialChannels[index].name || currentSocialChannels[index].name.startsWith('CS') || currentSocialChannels[index].name.includes('WhatsApp')) {
            const opt = PLATFORM_OPTIONS.find(o => o.value === newPlatform);
            if (opt) {
                currentSocialChannels[index].name = opt.name;
            }
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
        if (field === 'enabled') {
            renderSocialChannelsList();
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
        icon: platform
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

// Initial Boot
document.addEventListener('DOMContentLoaded', function() {
    renderDecisionTree();
    renderFaqRulesList();
    renderSocialChannelsList();
    updateSubTabToggleStates();
});
</script>
@endsection
