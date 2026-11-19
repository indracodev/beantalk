<!-- MODAL PENGATURAN WIDGET PROYEK INI -->
<div class="modal-backdrop fixed inset-0 bg-apple-textPrimary/25 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden" id="modalSettings_{{ $p->id }}" onclick="if(event.target===this) closeModal('modalSettings_{{ $p->id }}')">
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
                    $th = $channelsMap->get('threads');
                    $x = $channelsMap->get('x') ?? $channelsMap->get('twitter');
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

                <!-- Threads -->
                <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-neutral-900">Threads</span>
                        <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                            <input type="checkbox" name="channels[threads][enabled]" value="1" {{ !empty($th['enabled']) ? 'checked' : '' }}>
                            <span>Aktif</span>
                        </label>
                    </div>
                    <input type="text" name="channels[threads][url]" value="{{ $th['url'] ?? '' }}" placeholder="https://threads.net/@akunanda atau @akunanda" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
                </div>

                <!-- X (Twitter) -->
                <div class="p-2.5 border border-apple-border rounded-lg bg-apple-canvas/30 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-neutral-900">X (Twitter)</span>
                        <label class="flex items-center gap-1.5 text-[11px] cursor-pointer">
                            <input type="checkbox" name="channels[x][enabled]" value="1" {{ !empty($x['enabled']) ? 'checked' : '' }}>
                            <span>Aktif</span>
                        </label>
                    </div>
                    <input type="text" name="channels[x][url]" value="{{ $x['url'] ?? '' }}" placeholder="https://x.com/akunanda atau @akunanda" class="w-full px-2.5 py-1 text-[11.5px] border border-apple-border rounded-md bg-white">
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
