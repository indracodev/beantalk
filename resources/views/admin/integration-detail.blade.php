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
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
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
                <span id="badgeTabSocialCount" class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold">{{ count(array_filter($socialChannelsList, fn($c) => !empty($c['enabled']))) }}</span>
            </button>

            <button type="button" onclick="switchDetailTab('embed')" id="tab-btn-embed" class="tab-btn shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-[12px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition flex items-center gap-2 cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
                <span>Embed Code &amp; API Keys</span>
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

            <!-- Dynamic Interactive FAQ Rules Builder Card -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[14px] font-bold text-apple-textPrimary">Aturan Kata Kunci &amp; Template Jawaban FAQ</h3>
                            <span id="labelRuleCount" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700">{{ count($botRules) }} Aturan</span>
                        </div>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Jika pesan pelanggan mengandung salah satu kata kunci, bot akan membalas dengan template jawaban otomatis.</p>
                    </div>

                    <button type="button" onclick="addNewFaqRuleRow()" class="inline-flex items-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-[11.5px] font-semibold transition shadow-apple-sm cursor-pointer self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Aturan FAQ Baru</span>
                    </button>
                </div>

                <!-- Container Baris Aturan FAQ (Vanilla JS Managed) -->
                <div id="faqRulesListContainer" class="flex flex-col gap-3">
                    <!-- Dynamic Rows Rendered Here by JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="faqEmptyState" class="{{ count($botRules) > 0 ? 'hidden' : '' }} p-6 text-center border-2 border-dashed border-apple-border rounded-xl text-[12px] text-apple-textTertiary bg-apple-canvas/20">
                    <svg class="w-7 h-7 text-neutral-300 mx-auto mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <p class="font-medium text-apple-textSecondary">Belum ada aturan kata kunci FAQ.</p>
                    <p class="mt-0.5 text-apple-textTertiary text-[11px]">Klik tombol <strong>"Tambah Aturan FAQ Baru"</strong> di atas untuk membuat auto-responder kata kunci pertama Anda.</p>
                </div>

                <!-- Info Box Smart CS Handoff -->
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
                            <span id="labelSocialCount" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">{{ count(array_filter($socialChannelsList, fn($c) => !empty($c['enabled']))) }} Aktif</span>
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
        <!-- TAB 4: EMBED CODE & API KEYS                                 -->
        <!-- ============================================================ -->
        <div id="tab-pane-embed" class="tab-pane flex flex-col gap-4" style="display: none;">
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

                <!-- API Keys & Domains -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3 border-t border-apple-border">
                    <div class="p-3 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-[11px] font-semibold text-apple-textPrimary block mb-1">Public API Key</span>
                        <div class="font-mono text-[11px] text-neutral-700 bg-white p-2 rounded border border-apple-border select-all">{{ $publicKey }}</div>
                    </div>

                    <div class="p-3 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-[11px] font-semibold text-apple-textPrimary block mb-1">Domain Terdaftar</span>
                        <div class="font-mono text-[11px] text-neutral-700 bg-white p-2 rounded border border-apple-border">{{ $project->domains->first()->domain ?? 'website.com' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 5: TELEGRAM NOTIFICATIONS & FORUM TOPICS                 -->
        <!-- ============================================================ -->
        <div id="tab-pane-telegram" class="tab-pane flex flex-col gap-4" style="display: none;">
            
            <!-- Master Toggle & Configuration Card -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[14px] font-bold text-apple-textPrimary">Integrasi Notifikasi &amp; Forum Telegram</h3>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ !empty($widgetSetting->telegram_notifications_enabled) ? 'bg-sky-100 text-sky-800' : 'bg-neutral-100 text-neutral-600' }}">
                                {{ !empty($widgetSetting->telegram_notifications_enabled) ? '⚡ Terhubung' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Dapatkan notifikasi instan 1x di grup Telegram tim CS Anda, dan balas chat customer langsung dari aplikasi Telegram via mode Forum Topics.</p>
                    </div>

                    <button type="button" onclick="testTelegramConnection()" id="btnTestTelegram" class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white px-3.5 py-1.5 rounded-lg text-[12px] font-semibold transition shadow-apple-sm cursor-pointer self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.62 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.89 8.01-3.46 3.82-1.6 4.61-1.88 5.14-1.89.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.19-.04.35z"/></svg>
                        <span>⚡ Test Kirim Pesan ke Telegram</span>
                    </button>
                </div>

                <!-- Live Test Alert Box -->
                <div id="telegramTestResult" class="hidden p-3 rounded-xl text-[12px]"></div>

                <!-- Toggles & Inputs Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Toggle 1: Master Notifikasi -->
                    <div class="p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30 flex items-start justify-between gap-3">
                        <div>
                            <span class="text-[12px] font-bold text-apple-textPrimary block">Aktifkan Notifikasi Telegram</span>
                            <p class="text-[11px] text-apple-textSecondary mt-0.5">Kirim notifikasi 1x saat ada customer baru memulai obrolan (anti-spam).</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                            <input type="checkbox" name="telegram_notifications_enabled" value="1" {{ !empty($widgetSetting->telegram_notifications_enabled) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-10 h-5.5 bg-neutral-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-sky-500"></div>
                        </label>
                    </div>

                    <!-- Toggle 2: Mode Topic Forum -->
                    <div class="p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30 flex items-start justify-between gap-3">
                        <div>
                            <span class="text-[12px] font-bold text-apple-textPrimary block">Mode Forum Topics (1 Topik per Customer)</span>
                            <p class="text-[11px] text-apple-textSecondary mt-0.5">Buat room topik terpisah per customer di grup Telegram dengan nama <code>[{{ $project->name }}] Nama (Kode)</code>.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                            <input type="checkbox" name="telegram_topic_mode_enabled" value="1" {{ !empty($widgetSetting->telegram_topic_mode_enabled) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-10 h-5.5 bg-neutral-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-sky-500"></div>
                        </label>
                    </div>

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
            row.className = 'p-3.5 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-purple-300 transition flex flex-col md:flex-row items-start md:items-center gap-3';
            row.innerHTML = `
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

// Initial Boot
document.addEventListener('DOMContentLoaded', function() {
    renderFaqRulesList();
    renderSocialChannelsList();
});
</script>
@endsection
