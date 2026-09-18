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
