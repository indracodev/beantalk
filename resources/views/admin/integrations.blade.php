@extends('layouts.admin')

@section('title', 'Connected Channels & Integrations')
@section('header_title', 'Connected Channels & Integrations')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 bg-apple-canvas/30 w-full">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-apple-border gap-2.5">
        <div>
            <h2 class="text-[16px] sm:text-[17px] font-semibold text-apple-textPrimary tracking-tight">Connected Channels &amp; Integrasi Web</h2>
            <p class="text-[11.5px] sm:text-[12px] text-apple-textSecondary">Deploy unified chat across Shopify, WooCommerce, or custom websites with a 1-line script tag.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openModal('modalNewIntegration')" class="inline-flex items-center gap-1.5 bg-apple-blue hover:bg-apple-blueHover text-white px-3 py-1.5 rounded-lg text-[11.5px] sm:text-[12px] font-medium transition shadow-apple-sm cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span>Connect Website</span>
            </button>
            <a href="/demo-store.html" target="_blank" class="inline-flex items-center gap-1.5 bg-white border border-apple-border hover:bg-apple-canvas text-apple-textPrimary px-2.5 py-1.5 rounded-lg text-[11.5px] sm:text-[12px] font-medium transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Demo Store</span>
            </a>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="integrations-card-container">
        @forelse($projects as $p)
            @php
                $primaryColor = $p->widgetSetting->primary_color ?? '#0071E3';
                $publicKey = $p->activeApiKey->public_key ?? 'pk_live_' . $p->slug;
                $domain = $p->domains->first()->domain ?? 'store.com';
                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $p->name), 0, 2));
            @endphp
            <div class="bg-white border border-apple-border rounded-xl p-4 shadow-apple-sm flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg text-white flex items-center justify-center font-bold text-[13px] shadow-2xs" style="background-color: {{ $primaryColor }}">
                            {{ $initials }}
                        </div>
                        <div>
                            <h4 class="font-semibold text-[13px] text-apple-textPrimary">{{ $p->name }}</h4>
                            <div class="text-[11px] text-apple-textTertiary font-mono">{{ $domain }}</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 bg-apple-canvas/50 p-2 rounded-lg text-[11px]">
                    <div>
                        <span class="text-apple-textTertiary text-[10px] block">Total Percakapan</span>
                        <span class="font-semibold text-apple-textPrimary font-mono">{{ $p->conversations_count }}</span>
                    </div>
                    <div>
                        <span class="text-apple-textTertiary text-[10px] block">Pengunjung Terdaftar</span>
                        <span class="font-semibold text-apple-textPrimary font-mono">{{ $p->visitors_count }}</span>
                    </div>
                </div>

                <!-- Assigned CS Agent Info -->
                <div class="flex items-center justify-between text-[11px] px-2.5 py-1.5 rounded-lg bg-apple-canvas/40 border border-apple-subtleBorder">
                    <span class="text-apple-textTertiary text-[10.5px]">Agent CS:</span>
                    @if($p->assignedUsers->isEmpty())
                        <span class="inline-flex items-center gap-1 font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[10.5px]">
                            <span>👥 Semua Agent (All)</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 font-medium text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200 text-[10.5px] truncate max-w-[200px]" title="{{ $p->assignedUsers->pluck('name')->implode(', ') }}">
                            <span>👤 {{ $p->assignedUsers->pluck('name')->implode(', ') }}</span>
                        </span>
                    @endif
                </div>

                <!-- Code Snippet Box -->
                <div>
                    <div class="flex items-center justify-between text-[10px] text-apple-textTertiary mb-1">
                        <span>Universal Embed Script</span>
                        <span class="font-mono text-[9.5px]">Public Key: {{ $publicKey }}</span>
                    </div>
                    <div class="bg-[#1C1C1E] text-neutral-200 font-mono text-[10.5px] p-2.5 rounded-lg break-all select-all leading-relaxed" id="snippet_{{ $p->id }}">&lt;script src="{{ url('/chat-widget.js') }}" data-project-key="{{ $publicKey }}" data-api-url="{{ url('/') }}" async&gt;&lt;/script&gt;</div>
                </div>

                <!-- Card Actions -->
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-apple-subtleBorder">
                    <div class="flex gap-2">
                        <button type="button" onclick="copySnippet('snippet_{{ $p->id }}', this)" class="px-2.5 py-1 text-[11px] font-medium rounded-md border border-apple-border text-apple-textPrimary hover:bg-apple-canvas transition shadow-2xs">
                            Copy Code
                        </button>
                        <a href="{{ route('admin.integrations.detail', $p->id) }}" class="px-2.5 py-1 text-[11px] font-medium rounded-md bg-apple-blue/10 text-apple-blue hover:bg-apple-blue hover:text-white transition inline-flex items-center gap-1">
                            <span>Settings &amp; Bot</span>
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </div>

                    <form action="{{ route('admin.integrations.regenerate', $p->id) }}" method="POST" class="m-0" onsubmit="return confirm('Regenerate API Key untuk {{ $p->name }}?\n\nKey lama akan dinonaktifkan. Anda harus memperbarui kode embed di website klien.')">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-[10.5px] text-apple-orange hover:text-red-600 transition">
                            Regenerate Key
                        </button>
                    </form>
                </div>
            </div>

            <!-- MODAL PENGATURAN WIDGET PROYEK INI -->
            <div class="modal-backdrop fixed inset-0 bg-black/30 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden" id="modalSettings_{{ $p->id }}" onclick="if(event.target===this) closeModal('modalSettings_{{ $p->id }}')">
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-apple-modal border border-apple-border overflow-hidden">
                    <div class="px-4 py-3.5 border-b border-apple-border flex items-center justify-between bg-apple-canvas/50">
                        <div>
                            <h4 class="font-semibold text-[14px] text-apple-textPrimary">Widget Settings: {{ $p->name }}</h4>
                            <span class="text-[11px] text-apple-textTertiary font-mono">Domain: {{ $domain }}</span>
                        </div>
                        <button type="button" class="w-6 h-6 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-apple-textSecondary transition text-[12px]" onclick="closeModal('modalSettings_{{ $p->id }}')">✕</button>
                    </div>

                    <form action="{{ route('admin.integrations.settings', $p->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-4 flex flex-col gap-3.5 text-[12px] max-h-[70vh] overflow-y-auto">
                            <!-- 0. Widget Language -->
                            <div>
                                <label class="block font-medium text-apple-textPrimary mb-1">Widget Language</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 p-2 border border-apple-border rounded-lg bg-apple-canvas/30 cursor-pointer text-[11px] font-medium">
                                        <input type="radio" name="language" value="id" {{ ($p->widgetSetting->language ?? 'id') === 'id' ? 'checked' : '' }} class="text-apple-blue">
                                        <span>🇮🇩 Indonesia</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 border border-apple-border rounded-lg bg-apple-canvas/30 cursor-pointer text-[11px] font-medium">
                                        <input type="radio" name="language" value="en" {{ ($p->widgetSetting->language ?? 'id') === 'en' ? 'checked' : '' }} class="text-apple-blue">
                                        <span>🇬🇧 English</span>
                                    </label>
                                </div>
                            </div>

                            <!-- 1. Primary Color -->
                            <div>
                                <label class="block font-medium text-apple-textPrimary mb-1">Primary Accent Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="colorPicker_{{ $p->id }}" value="{{ $primaryColor }}" class="w-9 h-8 border border-apple-border rounded-lg cursor-pointer bg-transparent" onchange="document.getElementById('colorHex_{{ $p->id }}').value = this.value">
                                    <input type="text" id="colorHex_{{ $p->id }}" name="primary_color" value="{{ $primaryColor }}" class="w-28 px-2.5 py-1.5 border border-apple-border rounded-lg font-mono text-[11px]" oninput="document.getElementById('colorPicker_{{ $p->id }}').value = this.value">
                                </div>
                            </div>

                            <!-- 2. Greeting Title & Subtitle -->
                            <div>
                                <label class="block font-medium text-apple-textPrimary mb-1">Greeting Title</label>
                                <input type="text" name="greeting_title" class="w-full px-3 py-1.5 border border-apple-border rounded-lg" value="{{ $p->widgetSetting->greeting_title ?? 'Hallo!' }}" required>
                            </div>

                            <div>
                                <label class="block font-medium text-apple-textPrimary mb-1">Greeting Subtitle</label>
                                <input type="text" name="greeting_subtitle" class="w-full px-3 py-1.5 border border-apple-border rounded-lg" value="{{ $p->widgetSetting->greeting_subtitle ?? 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!' }}" required>
                            </div>

                            <div>
                                <label class="block font-medium text-apple-textPrimary mb-1">Support Title</label>
                                <input type="text" name="support_title" class="w-full px-3 py-1.5 border border-apple-border rounded-lg" value="{{ $p->widgetSetting->support_title ?? 'Customer Support' }}" required>
                            </div>

                            <hr class="border-apple-border my-1">

                            <!-- 3. Social Channels -->
                            @php
                                $channelsMap = collect($p->widgetSetting->social_channels ?? [])->keyBy('id');
                                $wa = $channelsMap->get('whatsapp');
                                $ig = $channelsMap->get('instagram');
                                $fb = $channelsMap->get('facebook') ?? $channelsMap->get('messenger');
                                $tt = $channelsMap->get('tiktok');
                                $yt = $channelsMap->get('youtube');
                                $tg = $channelsMap->get('telegram');
                                $shp = $channelsMap->get('shopee');
                                $tkp = $channelsMap->get('tokopedia');
                            @endphp

                            <div class="text-[12px] font-semibold text-apple-textPrimary">
                                Social Shortcuts ("Find Us Somewhere Else")
                            </div>

                            <!-- WhatsApp -->
                            <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#25D366]">WhatsApp</span>
                                    <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                                        <input type="checkbox" name="channels[whatsapp][enabled]" value="1" {{ !empty($wa['enabled']) ? 'checked' : '' }}>
                                        <span>Aktif</span>
                                    </label>
                                </div>
                                <input type="text" name="channels[whatsapp][url]" value="{{ $wa['url'] ?? '' }}" placeholder="https://wa.me/628123456789 atau 08123456789" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                            </div>

                            <!-- Instagram -->
                            <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#E1306C]">Instagram</span>
                                    <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                                        <input type="checkbox" name="channels[instagram][enabled]" value="1" {{ !empty($ig['enabled']) ? 'checked' : '' }}>
                                        <span>Aktif</span>
                                    </label>
                                </div>
                                <input type="text" name="channels[instagram][url]" value="{{ $ig['url'] ?? '' }}" placeholder="https://instagram.com/akunanda atau @akunanda" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                            </div>

                            <!-- Facebook -->
                            <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#1877F2]">Facebook</span>
                                    <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                                        <input type="checkbox" name="channels[facebook][enabled]" value="1" {{ !empty($fb['enabled']) ? 'checked' : '' }}>
                                        <span>Aktif</span>
                                    </label>
                                </div>
                                <input type="text" name="channels[facebook][url]" value="{{ $fb['url'] ?? '' }}" placeholder="https://facebook.com/akunanda atau username" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                            </div>

                            <!-- TikTok -->
                            <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-neutral-900">TikTok</span>
                                    <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                                        <input type="checkbox" name="channels[tiktok][enabled]" value="1" {{ !empty($tt['enabled']) ? 'checked' : '' }}>
                                        <span>Aktif</span>
                                    </label>
                                </div>
                                <input type="text" name="channels[tiktok][url]" value="{{ $tt['url'] ?? '' }}" placeholder="https://tiktok.com/@akunanda atau @akunanda" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                            </div>

                            <!-- YouTube -->
                            <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#FF0000]">YouTube</span>
                                    <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                                        <input type="checkbox" name="channels[youtube][enabled]" value="1" {{ !empty($yt['enabled']) ? 'checked' : '' }}>
                                        <span>Aktif</span>
                                    </label>
                                </div>
                                <input type="text" name="channels[youtube][url]" value="{{ $yt['url'] ?? '' }}" placeholder="https://youtube.com/@channelanda atau @channelanda" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                            </div>

                            <!-- Shopee -->
                            <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#EE4D2D]">Shopee</span>
                                    <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                                        <input type="checkbox" name="channels[shopee][enabled]" value="1" {{ !empty($shp['enabled']) ? 'checked' : '' }}>
                                        <span>Aktif</span>
                                    </label>
                                </div>
                                <input type="text" name="channels[shopee][url]" value="{{ $shp['url'] ?? '' }}" placeholder="https://shopee.co.id/storeanda" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                            </div>

                            <!-- Tokopedia -->
                            <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#03AC0E]">Tokopedia</span>
                                    <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                                        <input type="checkbox" name="channels[tokopedia][enabled]" value="1" {{ !empty($tkp['enabled']) ? 'checked' : '' }}>
                                        <span>Aktif</span>
                                    </label>
                                </div>
                                <input type="text" name="channels[tokopedia][url]" value="{{ $tkp['url'] ?? '' }}" placeholder="https://tokopedia.com/storeanda" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                            </div>

                            <hr class="border-apple-border my-1">

                            <!-- 4. Smart Bot & Auto-Responder Settings -->
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-3 flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-indigo-600 text-white flex items-center justify-center font-bold text-[11px] shadow-2xs">
                                            🤖
                                        </div>
                                        <div>
                                            <span class="font-semibold text-apple-textPrimary text-[12.5px]">Smart Bot &amp; Auto-Responder</span>
                                            <p class="text-[10px] text-apple-textTertiary">Balas chat pengunjung otomatis 24/7 berbasis kata kunci &amp; FAQ.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="bot_enabled" value="1" class="sr-only peer" {{ !empty($p->widgetSetting->bot_enabled) ? 'checked' : '' }} onchange="toggleBotSection('{{ $p->id }}', this.checked)">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>

                                <div id="botFields_{{ $p->id }}" class="flex flex-col gap-2.5 pt-2 border-t border-indigo-100 {{ empty($p->widgetSetting->bot_enabled) ? 'hidden' : '' }}">
                                    <!-- Bot Name -->
                                    <div>
                                        <label class="block font-medium text-apple-textPrimary text-[11px] mb-1">Nama Asisten Bot</label>
                                        <input type="text" name="bot_name" class="w-full px-2.5 py-1.5 text-[11.5px] border border-apple-border rounded-lg bg-white" value="{{ $p->widgetSetting->bot_name ?? 'BeanBot' }}" placeholder="e.g. BeanBot / Asisten CS">
                                    </div>

                                    <!-- Welcome Message -->
                                    <div>
                                        <label class="block font-medium text-apple-textPrimary text-[11px] mb-1">Pesan Sambutan Otomatis (First Inbound Greeting)</label>
                                        <textarea name="bot_welcome_message" rows="2" class="w-full px-2.5 py-1.5 text-[11.5px] border border-apple-border rounded-lg bg-white resize-none" placeholder="Halo! Ada yang bisa kami bantu seputar produk atau pesanan Anda?">{{ $p->widgetSetting->bot_welcome_message ?? '' }}</textarea>
                                        <span class="text-[9.5px] text-apple-textTertiary">Dikirimkan otomatis saat pengunjung pertama kali mengirim pesan.</span>
                                    </div>

                                    <!-- Offline Message -->
                                    <div>
                                        <label class="block font-medium text-apple-textPrimary text-[11px] mb-1">Pesan Luar Jam Operasional (Offline Auto-Reply)</label>
                                        <textarea name="bot_offline_message" rows="2" class="w-full px-2.5 py-1.5 text-[11.5px] border border-apple-border rounded-lg bg-white resize-none" placeholder="Halo! Toko kami saat ini sedang tutup. Tinggalkan pesan Anda, staf kami akan segera merespons saat kembali online.">{{ $p->widgetSetting->bot_offline_message ?? '' }}</textarea>
                                    </div>

                                    <!-- FAQ & Keyword Rules Builder -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="font-medium text-apple-textPrimary text-[11px]">Aturan FAQ &amp; Kata Kunci</label>
                                            <button type="button" onclick="addBotRule('{{ $p->id }}')" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">+ Tambah Aturan</button>
                                        </div>
                                        
                                        <div id="rulesContainer_{{ $p->id }}" class="flex flex-col gap-2">
                                            @php
                                                $botRules = is_array($p->widgetSetting->bot_rules ?? null) ? $p->widgetSetting->bot_rules : [];
                                            @endphp
                                            @forelse($botRules as $idx => $r)
                                                @php
                                                    $kwStr = is_array($r['keywords'] ?? null) ? implode(', ', $r['keywords']) : ($r['keywords'] ?? '');
                                                    $respStr = $r['response'] ?? ($r['response_text'] ?? '');
                                                @endphp
                                                <div class="p-2 bg-white rounded-lg border border-indigo-100 flex flex-col gap-1.5 rule-item shadow-2xs relative">
                                                    <button type="button" onclick="this.closest('.rule-item').remove(); syncBotRules('{{ $p->id }}');" class="absolute top-1.5 right-1.5 text-gray-400 hover:text-red-500 text-[11px] font-bold cursor-pointer">✕</button>
                                                    <input type="text" placeholder="Kata kunci (pisah koma: ongkir, tarif, kirim)" value="{{ $kwStr }}" class="w-[90%] px-2 py-1 text-[11px] border border-apple-border rounded font-mono rule-keywords" oninput="syncBotRules('{{ $p->id }}')">
                                                    <textarea rows="1" placeholder="Jawaban otomatis bot..." class="w-full px-2 py-1 text-[11px] border border-apple-border rounded resize-none rule-response" oninput="syncBotRules('{{ $p->id }}')">{{ $respStr }}</textarea>
                                                </div>
                                            @empty
                                                <!-- Default example rule if none -->
                                                <div class="p-2 bg-white rounded-lg border border-indigo-100 flex flex-col gap-1.5 rule-item shadow-2xs relative">
                                                    <button type="button" onclick="this.closest('.rule-item').remove(); syncBotRules('{{ $p->id }}');" class="absolute top-1.5 right-1.5 text-gray-400 hover:text-red-500 text-[11px] font-bold cursor-pointer">✕</button>
                                                    <input type="text" placeholder="Kata kunci (pisah koma: ongkir, tarif, kirim)" value="ongkir, pengiriman, tarif" class="w-[90%] px-2 py-1 text-[11px] border border-apple-border rounded font-mono rule-keywords" oninput="syncBotRules('{{ $p->id }}')">
                                                    <textarea rows="1" placeholder="Jawaban otomatis bot..." class="w-full px-2 py-1 text-[11px] border border-apple-border rounded resize-none rule-response" oninput="syncBotRules('{{ $p->id }}')">Pesanan sebelum jam 15:00 WIB dikirim pada hari yang sama! Ongkir otomatis dihitung saat checkout.</textarea>
                                                </div>
                                            @endforelse
                                        </div>
                                        <input type="hidden" name="bot_rules" id="botRulesJson_{{ $p->id }}" value="{{ json_encode($botRules) }}">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="px-4 py-3 border-t border-apple-border flex justify-end gap-2 bg-apple-canvas/40">
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-apple-border text-apple-textSecondary hover:bg-white text-[11.5px] font-medium cursor-pointer" onclick="closeModal('modalSettings_{{ $p->id }}')">Batal</button>
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[11.5px] font-medium shadow-apple-sm cursor-pointer">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-2 p-12 text-center text-apple-textTertiary bg-white rounded-xl border border-apple-border">
                <p class="text-[13px] font-medium text-apple-textSecondary">Belum ada website atau saluran yang terhubung.</p>
                <button type="button" onclick="openModal('modalNewIntegration')" class="mt-3 px-3.5 py-1.5 rounded-lg bg-apple-blue text-white text-[12px] font-medium shadow-apple-sm">
                    Hubungkan Website Pertama
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- MODAL TAMBAH INTEGRASI BARU -->
<div class="modal-backdrop fixed inset-0 bg-black/30 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden" id="modalNewIntegration" onclick="if(event.target===this) closeModal('modalNewIntegration')">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-apple-modal border border-apple-border overflow-hidden">
        <div class="px-4 py-3.5 border-b border-apple-border flex items-center justify-between bg-apple-canvas/50">
            <h4 class="font-semibold text-[14px] text-apple-textPrimary">Connect Website Channel</h4>
            <button type="button" class="w-6 h-6 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-apple-textSecondary transition text-[12px]" onclick="closeModal('modalNewIntegration')">✕</button>
        </div>
        <form action="{{ route('admin.integrations.store') }}" method="POST">
            @csrf
            <div class="p-4 flex flex-col gap-3.5 text-[12px]">
                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Site / Store Name</label>
                    <input type="text" id="integName" name="name" placeholder="e.g. Supresso Singapore" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" required>
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Nama saluran untuk pengenal tiket inbox CS.</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Domain Whitelist</label>
                    <input type="text" id="integDomain" name="domain" placeholder="e.g. supresso.sg atau tokoanda.com" class="w-full px-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20" required>
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Domain tempat widget diinstal (tanpa http://).</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Widget Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="integColorPicker" value="#0071E3" class="w-9 h-8 border border-apple-border rounded-lg cursor-pointer bg-transparent" onchange="document.getElementById('integColorHex').value = this.value">
                        <input type="text" id="integColorHex" name="primary_color" value="#0071E3" class="w-28 px-2.5 py-1.5 border border-apple-border rounded-lg font-mono text-[11px]" oninput="document.getElementById('integColorPicker').value = this.value">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Greeting Title</label>
                    <input type="text" name="greeting_title" value="Hallo!" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none">
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Greeting Subtitle</label>
                    <input type="text" name="greeting_subtitle" value="Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none">
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Agent CS Penanggung Jawab</label>
                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-2 cursor-pointer text-[11.5px] text-apple-textPrimary">
                            <input type="radio" name="agent_scope" value="all" checked onchange="document.getElementById('newIntegAgentList').classList.add('hidden')" class="text-apple-blue focus:ring-apple-blue/20">
                            <span>Semua Agent (Default &mdash; All CS)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-[11.5px] text-apple-textPrimary">
                            <input type="radio" name="agent_scope" value="selected" onchange="document.getElementById('newIntegAgentList').classList.remove('hidden')" class="text-apple-blue focus:ring-apple-blue/20">
                            <span>Pilih Agent Tertentu (Multi-Select)</span>
                        </label>
                        <div id="newIntegAgentList" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-1.5 p-2 rounded-lg border border-apple-border bg-apple-canvas/40 max-h-36 overflow-y-auto">
                            @if(isset($agents))
                                @foreach($agents as $ag)
                                    <label class="flex items-center gap-2 p-1 text-[11px] text-apple-textPrimary hover:bg-white rounded cursor-pointer">
                                        <input type="checkbox" name="agent_ids[]" value="{{ $ag->id }}" class="rounded border-apple-border text-apple-blue focus:ring-apple-blue/20">
                                        <span class="truncate">{{ $ag->name }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-apple-border flex justify-end gap-2 bg-apple-canvas/40">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-apple-border text-apple-textSecondary hover:bg-white text-[11.5px] font-medium" onclick="closeModal('modalNewIntegration')">Batal</button>
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[11.5px] font-medium shadow-apple-sm">Create Channel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copySnippet(elementId, btn) {
        const text = document.getElementById(elementId).innerText;
        copyToClipboard(text, btn);
        if (typeof showToast === 'function') {
            showToast('Copied!', 'Widget embed code copied to clipboard.');
        }
    }

    function toggleBotSection(projectId, isChecked) {
        const el = document.getElementById('botFields_' + projectId);
        if (el) {
            el.classList.toggle('hidden', !isChecked);
        }
    }

    function addBotRule(projectId) {
        const container = document.getElementById('rulesContainer_' + projectId);
        if (!container) return;

        const div = document.createElement('div');
        div.className = 'p-2 bg-white rounded-lg border border-indigo-100 flex flex-col gap-1.5 rule-item shadow-2xs relative';
        div.innerHTML = `
            <button type="button" onclick="this.closest('.rule-item').remove(); syncBotRules('${projectId}');" class="absolute top-1.5 right-1.5 text-gray-400 hover:text-red-500 text-[11px] font-bold cursor-pointer">✕</button>
            <input type="text" placeholder="Kata kunci (pisah koma: ongkir, tarif, kirim)" class="w-[90%] px-2 py-1 text-[11px] border border-apple-border rounded font-mono rule-keywords" oninput="syncBotRules('${projectId}')">
            <textarea rows="1" placeholder="Jawaban otomatis bot..." class="w-full px-2 py-1 text-[11px] border border-apple-border rounded resize-none rule-response" oninput="syncBotRules('${projectId}')"></textarea>
        `;
        container.appendChild(div);
        div.querySelector('input').focus();
    }

    function syncBotRules(projectId) {
        const container = document.getElementById('rulesContainer_' + projectId);
        const hiddenInput = document.getElementById('botRulesJson_' + projectId);
        if (!container || !hiddenInput) return;

        const rules = [];
        container.querySelectorAll('.rule-item').forEach(item => {
            const kwInput = item.querySelector('.rule-keywords');
            const respInput = item.querySelector('.rule-response');
            const kwVal = kwInput ? kwInput.value.trim() : '';
            const respVal = respInput ? respInput.value.trim() : '';

            if (kwVal && respVal) {
                const kwArray = kwVal.split(',').map(s => s.trim()).filter(Boolean);
                rules.push({
                    keywords: kwArray,
                    response: respVal
                });
            }
        });

        hiddenInput.value = JSON.stringify(rules);
    }
</script>
@endpush

