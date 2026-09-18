        <!-- ============================================================ -->
        <!-- TAB 3: SOCIAL CHANNELS & MULTI-CONTACT BUILDER               -->
        <!-- ============================================================ -->
        <div id="tab-pane-social" class="tab-pane flex flex-col gap-4" style="display: none;">
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-apple-border">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[14px] font-bold text-apple-textPrimary">Saluran Sosial, Kontak &amp; Marketplace</h3>
                            <span id="labelSocialCount" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">{{ collect($socialChannelsList)->where('enabled', true)->count() }} Aktif</span>
                        </div>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tambahkan saluran komunikasi resmi atau media sosial Anda (WhatsApp, Instagram, Threads, X / Twitter, Facebook, TikTok, YouTube, Telegram, Shopee, Tokopedia, Custom Link, dsb). Mendukung icon resmi platform maupun icon kustom.</p>
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