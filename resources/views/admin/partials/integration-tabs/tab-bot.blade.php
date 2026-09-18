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
