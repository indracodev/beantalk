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
            @include('admin.partials.integration-tabs.sound-admin')

            <!-- ============================================================== -->
            <!-- SECTION 2: NOTIFY CUSTOMER (WEBSITE BUBBLE CHAT SOUND)         -->
            @include('admin.partials.integration-tabs.sound-widget')

        </div>
