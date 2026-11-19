<!-- ========================================== -->
<div id="resolveTicketModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition-opacity">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-apple-border/80 overflow-hidden transform transition-all flex flex-col animate-scaleIn">
        <div class="px-5 py-4 border-b border-apple-border flex items-center justify-between bg-apple-canvas/40">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-[13.5px] font-bold text-apple-textPrimary">Selesaikan & Tutup Tiket</h3>
                    <p class="text-[11px] text-apple-textSecondary">Tandai percakapan ini sebagai tiket selesai</p>
                </div>
            </div>
            <button type="button" onclick="closeResolveModal()" class="w-7 h-7 rounded-lg text-apple-textTertiary hover:text-apple-textPrimary hover:bg-black/5 flex items-center justify-center text-lg transition">&times;</button>
        </div>

        <div class="p-5 flex flex-col gap-3.5 text-[12px]">
            <div>
                <label class="block font-semibold text-apple-textPrimary text-[11.5px] mb-1.5">
                    Pilih Template Sapaan Penutup:
                </label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="applyClosingTemplate('ramah')" class="px-2.5 py-1 text-[10.5px] rounded-lg border border-apple-border bg-white hover:bg-apple-canvas text-apple-textPrimary font-medium transition cursor-pointer active:scale-95 shadow-2xs">
                        🌟 Ramah & Sopan
                    </button>
                    <button type="button" onclick="applyClosingTemplate('solutif')" class="px-2.5 py-1 text-[10.5px] rounded-lg border border-apple-border bg-white hover:bg-apple-canvas text-apple-textPrimary font-medium transition cursor-pointer active:scale-95 shadow-2xs">
                        ✅ Solutif & Tuntas
                    </button>
                    <button type="button" onclick="applyClosingTemplate('singkat')" class="px-2.5 py-1 text-[10.5px] rounded-lg border border-apple-border bg-white hover:bg-apple-canvas text-apple-textPrimary font-medium transition cursor-pointer active:scale-95 shadow-2xs">
                        ⚡ Singkat
                    </button>
                </div>
            </div>

            <div>
                <label for="resolveClosingMessage" class="block font-semibold text-apple-textPrimary text-[11.5px] mb-1">
                    Pesan Penutup untuk Pelanggan:
                </label>
                <textarea id="resolveClosingMessage" rows="3"
                    class="w-full px-3 py-2 border border-apple-border rounded-xl text-[12px] placeholder:text-apple-textTertiary focus:outline-none focus:ring-2 focus:ring-apple-blue/30 focus:border-apple-blue resize-none transition"
                    placeholder="Tulis pesan sapaan penutup untuk pelanggan..."></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="resolveSendClosingCheck" checked class="w-4 h-4 rounded border-apple-border text-apple-blue focus:ring-apple-blue/20 cursor-pointer">
                <label for="resolveSendClosingCheck" class="text-[11.5px] text-apple-textSecondary cursor-pointer select-none">
                    Kirim pesan penutup ini ke pelanggan sebelum tiket ditutup
                </label>
            </div>
        </div>

        <div class="px-5 py-3 bg-apple-canvas/40 border-t border-apple-border flex items-center justify-end gap-2">
            <button type="button" onclick="closeResolveModal()" class="px-3.5 py-1.5 rounded-xl border border-apple-border bg-white hover:bg-black/5 text-apple-textSecondary font-semibold text-[11.5px] transition cursor-pointer">
                Batal
            </button>
            <button type="button" id="btnConfirmResolve" onclick="confirmResolveTicket()" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11.5px] shadow-sm transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Selesaikan Tiket</span>
            </button>
        </div>
    </div>
</div>
