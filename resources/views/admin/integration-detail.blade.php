@extends('layouts.admin')

@section('title', 'Detail Integrasi: ' . $project->name)
@section('header_title', 'Detail Integrasi: ' . $project->name)

@section('content')
<div class="flex-1 overflow-y-auto p-4 md:p-6 pb-24 md:pb-8 flex flex-col gap-6 bg-apple-canvas/30 w-full" x-data="integrationDetail()">

    <!-- Top Navigation & Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-apple-border gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.integrations') }}" class="w-9 h-9 rounded-xl bg-white border border-apple-border text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas flex items-center justify-center transition shadow-2xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-bold text-apple-textPrimary tracking-tight">{{ $project->name }}</h2>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Terkoneksi &amp; Live
                    </span>
                    @if($widgetSetting->bot_enabled)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-200">
                            🤖 Bot Aktif ({{ $widgetSetting->bot_name ?: 'BeanBot' }})
                        </span>
                    @endif
                </div>
                <p class="text-xs text-apple-textTertiary font-mono mt-0.5">{{ $project->domains->first()->domain ?? 'website.com' }} &bull; Public Key: {{ $project->activeApiKey->public_key ?? 'pk_live_' . $project->slug }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="submit" form="formIntegrationSettings" class="inline-flex items-center gap-2 bg-apple-blue hover:bg-apple-blueHover text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-apple-sm cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Simpan Semua Pengaturan</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash Message -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>
    @endif

    <!-- Telemetry Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs flex flex-col">
            <span class="text-[11px] font-medium text-apple-textTertiary">Total Percakapan</span>
            <span class="text-xl font-bold text-apple-textPrimary font-mono mt-1">{{ number_format($project->conversations_count) }}</span>
            <span class="text-[10px] text-apple-textSecondary mt-0.5">Seluruh tiket website ini</span>
        </div>
        <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs flex flex-col">
            <span class="text-[11px] font-medium text-apple-textTertiary">Pengunjung Terdata</span>
            <span class="text-xl font-bold text-apple-textPrimary font-mono mt-1">{{ number_format($project->visitors_count) }}</span>
            <span class="text-[10px] text-apple-textSecondary mt-0.5">Unique visitor session</span>
        </div>
        <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs flex flex-col">
            <span class="text-[11px] font-medium text-apple-textTertiary">Status Smart Bot</span>
            <span class="text-sm font-bold {{ $widgetSetting->bot_enabled ? 'text-purple-600' : 'text-neutral-500' }} mt-1 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full {{ $widgetSetting->bot_enabled ? 'bg-purple-500 animate-pulse' : 'bg-neutral-400' }}"></span>
                {{ $widgetSetting->bot_enabled ? 'Aktif (' . count($botRules) . ' Aturan FAQ)' : 'Nonaktif' }}
            </span>
            <span class="text-[10px] text-apple-textSecondary mt-0.5">{{ $widgetSetting->bot_name ?: 'BeanBot' }}</span>
        </div>
        <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs flex flex-col">
            <span class="text-[11px] font-medium text-apple-textTertiary">Warna Tema Widget</span>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-4 h-4 rounded-full border border-black/10 shadow-2xs" style="background-color: {{ $widgetSetting->primary_color }}"></span>
                <span class="text-sm font-bold text-apple-textPrimary font-mono">{{ $widgetSetting->primary_color }}</span>
            </div>
            <span class="text-[10px] text-apple-textSecondary mt-0.5">Aksen Brand Utama</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1.5 border-b border-apple-border pb-px overflow-x-auto text-xs font-medium scrollbar-none">
        <button type="button" @click="activeTab = 'bot'" :class="activeTab === 'bot' ? 'border-apple-blue text-apple-blue font-semibold bg-apple-blue/5' : 'border-transparent text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas'" class="px-3.5 py-2.5 rounded-t-xl border-b-2 transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                <circle cx="12" cy="5" r="2"></circle>
                <path d="M12 7v4"></path>
                <line x1="8" y1="16" x2="8" y2="16"></line>
                <line x1="16" y1="16" x2="16" y2="16"></line>
            </svg>
            <span>Smart Bot &amp; FAQ Rules</span>
            @if($widgetSetting->bot_enabled)
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            @endif
        </button>

        <button type="button" @click="activeTab = 'appearance'" :class="activeTab === 'appearance' ? 'border-apple-blue text-apple-blue font-semibold bg-apple-blue/5' : 'border-transparent text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas'" class="px-3.5 py-2.5 rounded-t-xl border-b-2 transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                <path d="M2 12h20"></path>
            </svg>
            <span>Tampilan &amp; Branding Widget</span>
        </button>

        <button type="button" @click="activeTab = 'social'" :class="activeTab === 'social' ? 'border-apple-blue text-apple-blue font-semibold bg-apple-blue/5' : 'border-transparent text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas'" class="px-3.5 py-2.5 rounded-t-xl border-b-2 transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            </svg>
            <span>Saluran Sosial &amp; Marketplace</span>
        </button>

        <button type="button" @click="activeTab = 'embed'" :class="activeTab === 'embed' ? 'border-apple-blue text-apple-blue font-semibold bg-apple-blue/5' : 'border-transparent text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas'" class="px-3.5 py-2.5 rounded-t-xl border-b-2 transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="16 18 22 12 16 6"></polyline>
                <polyline points="8 6 2 12 8 18"></polyline>
            </svg>
            <span>Embed Code &amp; API Keys</span>
        </button>
    </div>

    <!-- MAIN FORM WRAPPER -->
    <form id="formIntegrationSettings" action="{{ route('admin.integrations.settings', $project->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- TAB 1: SMART BOT & FAQ RULES BUILDER -->
        <div x-show="activeTab === 'bot'" class="flex flex-col gap-6">
            <!-- Master Toggle Card -->
            <div class="bg-white border border-apple-border rounded-2xl p-5 md:p-6 shadow-apple-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-apple-border">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-purple-100 text-purple-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path></svg>
                            </span>
                            <h3 class="text-sm font-bold text-apple-textPrimary">Aktivasi Smart Bot &amp; Auto-Responder</h3>
                        </div>
                        <p class="text-xs text-apple-textSecondary mt-1">Bot akan otomatis menyapa pengunjung baru, membalas pertanyaan sesuai aturan FAQ, dan menangani pesan saat CS offline.</p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="bot_enabled" value="1" {{ $widgetSetting->bot_enabled ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        <span class="ml-2.5 text-xs font-semibold text-apple-textPrimary peer-checked:text-purple-700">
                            {{ $widgetSetting->bot_enabled ? 'Aktif (ON)' : 'Nonaktif (OFF)' }}
                        </span>
                    </label>
                </div>

                <!-- Bot Settings Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div>
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Nama Asisten Bot</label>
                        <input type="text" name="bot_name" value="{{ old('bot_name', $widgetSetting->bot_name ?: 'BeanBot') }}" placeholder="Contoh: BeanBot Assistant, CS Virtual" class="w-full text-xs px-3.5 py-2.5 bg-apple-canvas/40 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Nama ini akan muncul di atas bubble chat bot pelanggan.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Pesan Sambutan Awal (Welcome Greeting)</label>
                        <input type="text" name="bot_welcome_message" value="{{ old('bot_welcome_message', $widgetSetting->bot_welcome_message ?: 'Halo! Saya BeanBot, ada yang bisa kami bantu hari ini?') }}" placeholder="Pesan otomatis saat pengunjung baru pertama kali menyapa" class="w-full text-xs px-3.5 py-2.5 bg-apple-canvas/40 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Dikirim secara otomatis saat pelanggan mengirim pesan pertama ke toko.</p>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Pesan Di Luar Jam Kerja (Offline Auto-Reply)</label>
                        <input type="text" name="bot_offline_message" value="{{ old('bot_offline_message', $widgetSetting->bot_offline_message ?: 'Terima kasih telah menghubungi kami. Tim kami sedang offline saat ini dan akan segera membalas pesan Anda begitu kembali.') }}" placeholder="Pesan otomatis saat customer chat di luar jam operasional" class="w-full text-xs px-3.5 py-2.5 bg-apple-canvas/40 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>
                </div>
            </div>

            <!-- Dynamic Interactive FAQ Rules Builder Card -->
            <div class="bg-white border border-apple-border rounded-2xl p-5 md:p-6 shadow-apple-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-apple-border">
                    <div>
                        <h3 class="text-sm font-bold text-apple-textPrimary flex items-center gap-2">
                            <span>Aturan Kata Kunci &amp; Jawaban FAQ Otomatis</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700" x-text="rules.length + ' Aturan Terpasang'"></span>
                        </h3>
                        <p class="text-xs text-apple-textSecondary mt-0.5">Jika pesan pelanggan mengandung salah satu kata kunci, bot akan membalas otomatis dengan jawaban yang ditentukan.</p>
                    </div>

                    <button type="button" @click="addRule()" class="inline-flex items-center gap-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>+ Tambah Aturan FAQ</span>
                    </button>
                </div>

                <!-- Hidden Input to serialize rules into JSON -->
                <input type="hidden" name="bot_rules" :value="JSON.stringify(rules)">

                <!-- Rules Table / Rows -->
                <div class="mt-4 flex flex-col gap-3">
                    <template x-for="(rule, index) in rules" :key="index">
                        <div class="p-4 rounded-xl border border-apple-border bg-apple-canvas/30 hover:border-purple-200 transition flex flex-col md:flex-row items-start md:items-center gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center shrink-0" x-text="index + 1"></div>
                            
                            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-12 gap-3">
                                <!-- Keyword Input -->
                                <div class="md:col-span-5">
                                    <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Kata Kunci Pemicu (Koma terpisah)</label>
                                    <input type="text" x-model="rule.keywords" placeholder="Contoh: ongkir, pengiriman, kurir, jne" class="w-full text-xs px-3 py-2 bg-white border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                                </div>

                                <!-- Response Template Input -->
                                <div class="md:col-span-7">
                                    <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Template Jawaban Bot</label>
                                    <textarea x-model="rule.response" rows="2" placeholder="Tulis jawaban lengkap yang akan dikirimkan bot ke customer..." class="w-full text-xs px-3 py-2 bg-white border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 resize-none"></textarea>
                                </div>
                            </div>

                            <button type="button" @click="removeRule(index)" class="text-neutral-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition shrink-0 cursor-pointer" title="Hapus Aturan">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </template>

                    <div x-show="rules.length === 0" class="p-8 text-center border-2 border-dashed border-apple-border rounded-xl text-xs text-apple-textTertiary">
                        Belum ada aturan kata kunci. Klik tombol <strong>"+ Tambah Aturan FAQ"</strong> di atas untuk membuat auto-responder kata kunci pertama Anda.
                    </div>
                </div>

                <!-- Info Box Smart Handoff -->
                <div class="mt-5 p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl text-xs text-blue-900 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <div>
                        <span class="font-bold">Smart CS Auto-Handoff &amp; Auto-Yield:</span>
                        Jika customer mengetik kata kunci seperti <em>"cs"</em>, <em>"manusia"</em>, <em>"bantuan staf"</em>, atau jika agen CS mengirim balasan manual dari Admin Inbox, bot akan secara otomatis menonaktifkan dirinya agar tidak mengganggu percakapan staf.
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: APPEARANCE & BRANDING WIDGET -->
        <div x-show="activeTab === 'appearance'" class="flex flex-col gap-6">
            <div class="bg-white border border-apple-border rounded-2xl p-5 md:p-6 shadow-apple-sm">
                <h3 class="text-sm font-bold text-apple-textPrimary pb-4 border-b border-apple-border">Warna &amp; Tampilan Widget</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                    <!-- Brand Color -->
                    <div>
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Warna Aksen Utama (Primary Brand Color)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="primaryColor" name="primary_color" class="w-10 h-10 rounded-xl border border-apple-border cursor-pointer p-0.5 bg-white">
                            <input type="text" x-model="primaryColor" class="w-32 text-xs font-mono uppercase px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-xl">
                            
                            <!-- Presets -->
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="primaryColor = '#0071E3'" class="w-6 h-6 rounded-full bg-[#0071E3] border border-black/10 shadow-2xs"></button>
                                <button type="button" @click="primaryColor = '#C59B27'" class="w-6 h-6 rounded-full bg-[#C59B27] border border-black/10 shadow-2xs"></button>
                                <button type="button" @click="primaryColor = '#10B981'" class="w-6 h-6 rounded-full bg-[#10B981] border border-black/10 shadow-2xs"></button>
                                <button type="button" @click="primaryColor = '#6366F1'" class="w-6 h-6 rounded-full bg-[#6366F1] border border-black/10 shadow-2xs"></button>
                                <button type="button" @click="primaryColor = '#1C1C1E'" class="w-6 h-6 rounded-full bg-[#1C1C1E] border border-black/10 shadow-2xs"></button>
                            </div>
                        </div>
                        <p class="text-[10.5px] text-apple-textTertiary mt-1.5">Mempengaruhi warna tombol widget pemicu, header obrolan, dan bubble pesan pengunjung.</p>
                    </div>

                    <!-- Custom Support Button Title -->
                    <div>
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Teks Tombol Layanan (Support Button Title)</label>
                        <input type="text" name="support_title" value="{{ old('support_title', $widgetSetting->support_title ?: 'Customer Support') }}" placeholder="Contoh: Customer Support, Live Help, Layanan Pelanggan" class="w-full text-xs px-3.5 py-2.5 bg-apple-canvas/40 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>

                    <!-- Greeting Title -->
                    <div>
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Judul Sapaan Welcome Hub (Greeting Title)</label>
                        <input type="text" name="greeting_title" value="{{ old('greeting_title', $widgetSetting->greeting_title ?: 'Hallo!') }}" placeholder="Contoh: Hallo!, Selamat Datang!" class="w-full text-xs px-3.5 py-2.5 bg-apple-canvas/40 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>

                    <!-- Greeting Subtitle -->
                    <div>
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Sub-judul Sapaan (Greeting Subtitle)</label>
                        <input type="text" name="greeting_subtitle" value="{{ old('greeting_subtitle', $widgetSetting->greeting_subtitle ?: 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!') }}" placeholder="Contoh: Ada yang bisa kami bantu?" class="w-full text-xs px-3.5 py-2.5 bg-apple-canvas/40 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>

                    <!-- Find Us Title -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-apple-textPrimary mb-1.5">Judul Saluran Eksternal (Find Us Section Title)</label>
                        <input type="text" name="find_us_title" value="{{ old('find_us_title', $widgetSetting->find_us_title ?: 'Find Us Somewhere Else') }}" placeholder="Contoh: Reach Us Anywhere Else, Saluran Resmi Lainnya" class="w-full text-xs px-3.5 py-2.5 bg-apple-canvas/40 border border-apple-border rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: SOCIAL CHANNELS & MARKETPLACE -->
        <div x-show="activeTab === 'social'" class="flex flex-col gap-6">
            <div class="bg-white border border-apple-border rounded-2xl p-5 md:p-6 shadow-apple-sm">
                <div class="pb-4 border-b border-apple-border">
                    <h3 class="text-sm font-bold text-apple-textPrimary">Saluran Kontak Tambahan ("Find Us Somewhere Else")</h3>
                    <p class="text-xs text-apple-textSecondary mt-0.5">Tautan alternatif yang muncul pada Halaman Awal (Welcome Hub) widget agar pelanggan dapat menghubungi Anda di luar website.</p>
                </div>

                @php
                    $savedChannels = collect($widgetSetting->social_channels ?? [])->keyBy('id');
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                    @foreach($availableChannels as $channelKey => $cMeta)
                        @php
                            $chData = $savedChannels->get($channelKey);
                            $chEnabled = !empty($chData['enabled']);
                            $chUrl = $chData['url'] ?? '';
                        @endphp
                        <div class="p-4 rounded-xl border border-apple-border bg-apple-canvas/20 flex flex-col gap-2.5">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 font-semibold text-xs text-apple-textPrimary cursor-pointer">
                                    <input type="checkbox" name="channels[{{ $channelKey }}][enabled]" value="1" {{ $chEnabled ? 'checked' : '' }} class="rounded text-apple-blue focus:ring-0">
                                    <span>{{ $cMeta['name'] }}</span>
                                </label>
                                <span class="text-[10.5px] {{ $chEnabled ? 'text-emerald-600 font-semibold' : 'text-neutral-400' }}">
                                    {{ $chEnabled ? '● Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <input type="text" name="channels[{{ $channelKey }}][url]" value="{{ $chUrl }}" placeholder="{{ $cMeta['placeholder'] }}" class="w-full text-xs px-3 py-2 bg-white border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 4: EMBED CODE & API KEYS -->
        <div x-show="activeTab === 'embed'" class="flex flex-col gap-6">
            <div class="bg-white border border-apple-border rounded-2xl p-5 md:p-6 shadow-apple-sm flex flex-col gap-5">
                <div class="pb-4 border-b border-apple-border">
                    <h3 class="text-sm font-bold text-apple-textPrimary">Universal Embed Script (1-Line Code)</h3>
                    <p class="text-xs text-apple-textSecondary mt-0.5">Tempelkan script tag ini sebelum penutup tag <code>&lt;/body&gt;</code> pada website Shopify, WordPress, WooCommerce, atau HTML kustom Anda.</p>
                </div>

                @php
                    $publicKey = $project->activeApiKey->public_key ?? 'pk_live_' . $project->slug;
                @endphp

                <!-- Snippet Box -->
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between text-xs font-semibold text-apple-textPrimary">
                        <span>Kode Script Embed</span>
                        <button type="button" onclick="copySnippet('embedCodeDetail', this)" class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-apple-border rounded-lg text-xs font-semibold hover:bg-apple-canvas transition shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span>Salin Kode</span>
                        </button>
                    </div>

                    <div id="embedCodeDetail" class="bg-[#1C1C1E] text-neutral-200 font-mono text-xs p-4 rounded-xl select-all break-all leading-relaxed shadow-inner">
&lt;script src="{{ url('/chat-widget.js') }}" data-project-key="{{ $publicKey }}" data-api-url="{{ url('/') }}" async&gt;&lt;/script&gt;
                    </div>
                </div>

                <!-- API Keys & Domains -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-apple-border">
                    <div class="p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-xs font-semibold text-apple-textPrimary block mb-1">Public API Key</span>
                        <div class="font-mono text-xs text-neutral-700 bg-white p-2 rounded border border-apple-border select-all">{{ $publicKey }}</div>
                    </div>

                    <div class="p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-xs font-semibold text-apple-textPrimary block mb-1">Domain Terdaftar</span>
                        <div class="font-mono text-xs text-neutral-700 bg-white p-2 rounded border border-apple-border">{{ $project->domains->first()->domain ?? 'website.com' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function integrationDetail() {
    return {
        activeTab: 'bot',
        primaryColor: '{{ $widgetSetting->primary_color ?? "#0071E3" }}',
        rules: @json($botRules),
        addRule() {
            this.rules.push({
                keywords: '',
                response: ''
            });
        },
        removeRule(index) {
            this.rules.splice(index, 1);
        }
    }
}

function copySnippet(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text.trim()).then(() => {
        const origText = btn.innerHTML;
        btn.innerHTML = '<span>✓ Tersalin!</span>';
        setTimeout(() => { btn.innerHTML = origText; }, 2000);
    });
}
</script>
@endsection
