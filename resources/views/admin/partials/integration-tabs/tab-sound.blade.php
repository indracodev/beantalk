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
