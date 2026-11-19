<!-- 4. VISITOR FOOTPRINT DETAIL MODAL -->
<div id="modalVisitorDetail" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-apple-textPrimary/25 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-2xl border border-apple-border shadow-apple-popover w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="px-4 py-3 border-b border-apple-border bg-apple-canvas/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-apple-blue/10 text-apple-blue flex items-center justify-center font-bold text-[12px]">
                    👤
                </div>
                <div>
                    <h3 class="font-semibold text-apple-textPrimary text-[13px]" id="modalVisitorTitle">Jejak Digital Pengunjung</h3>
                    <div class="text-[10.5px] font-mono text-apple-textTertiary" id="modalVisitorCode">-</div>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalVisitorDetail')" class="w-7 h-7 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-apple-textSecondary transition text-[13px] font-bold">
                &times;
            </button>
        </div>

        <!-- Modal Body (Dynamic AJAX Content) -->
        <div class="p-4 overflow-y-auto flex-1 flex flex-col gap-4 text-[12px]" id="modalVisitorBody">
            <div class="py-8 text-center text-apple-textTertiary" id="modalVisitorLoading">
                <div class="w-6 h-6 border-2 border-apple-blue border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                <span>Memuat data jejak pengunjung...</span>
            </div>

            <div id="modalVisitorContent" class="hidden flex flex-col gap-4">
                <!-- Section 1: Profil & Identitas -->
                <div class="bg-apple-canvas/40 p-3 rounded-xl border border-apple-border/70 flex flex-col gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-apple-textTertiary">Identitas &amp; Kontak</span>
                    <div class="grid grid-cols-2 gap-2 text-[11.5px]">
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">Nama:</span>
                            <strong class="text-apple-textPrimary font-medium" id="detVisitorName">-</strong>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">Email:</span>
                            <span class="text-apple-textPrimary font-mono text-[11px]" id="detVisitorEmail">-</span>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">Status Kehadiran:</span>
                            <span id="detVisitorPresence">-</span>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">Website/Project:</span>
                            <span class="text-apple-textPrimary font-medium" id="detVisitorProject">-</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Metadata Perangkat & Jaringan -->
                <div class="bg-apple-canvas/40 p-3 rounded-xl border border-apple-border/70 flex flex-col gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-apple-textTertiary">Perangkat &amp; Jaringan</span>
                    <div class="grid grid-cols-2 gap-2 text-[11.5px]">
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">Perangkat &amp; OS:</span>
                            <span class="text-apple-textPrimary font-medium" id="detVisitorDevice">-</span>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">Browser:</span>
                            <span class="text-apple-textPrimary font-medium" id="detVisitorBrowser">-</span>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">IP Address:</span>
                            <span class="font-mono text-apple-textSecondary text-[11px]" id="detVisitorIp">-</span>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[10px]">Terakhir Aktif:</span>
                            <span class="font-mono text-apple-textSecondary text-[10.5px]" id="detVisitorLastSeen">-</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-apple-subtleBorder">
                            <span class="text-apple-textTertiary block text-[9.5px]">User Agent String:</span>
                            <span class="font-mono text-apple-textTertiary text-[9.5px] break-all block" id="detVisitorUserAgent">-</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Riwayat Percakapan / Tiket Chat -->
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-apple-textTertiary">Riwayat Tiket Percakapan</span>
                        <span class="text-[10px] font-mono text-apple-textSecondary" id="detConvCountBadge">0 tiket</span>
                    </div>

                    <div id="detConversationsList" class="flex flex-col gap-2">
                        <!-- Populated via JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-4 py-2.5 border-t border-apple-border bg-apple-canvas/30 flex items-center justify-end">
            <button type="button" onclick="closeModal('modalVisitorDetail')"
                class="px-3.5 py-1.5 rounded-lg border border-apple-border bg-white hover:bg-black/5 text-apple-textPrimary transition text-[11.5px] font-medium shadow-2xs">
                Tutup
            </button>
        </div>
    </div>
</div>
