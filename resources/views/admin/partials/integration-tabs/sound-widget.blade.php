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
