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
