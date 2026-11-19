{{-- Toolbar daftar: judul, jumlah, switch tabel/kartu, hint scroll mobile. --}}
@props(['paginator', 'title', 'noun', 'modeFn', 'scrollHint'])

<!-- Subheader Toolbar with View Switcher (Table vs Card) -->
<div class="px-3.5 sm:px-4 py-2.5 border-b border-apple-border bg-apple-canvas/50 flex items-center justify-between gap-2">
    <div class="flex items-center gap-2">
        <span class="font-semibold text-apple-textPrimary text-[12px] sm:text-[12.5px]">{{ $title }}</span>
        <span class="text-[10.5px] font-medium px-2 py-0.2 rounded-full bg-white border border-apple-border/70 text-apple-textSecondary shadow-2xs">
            {{ $paginator->total() }} {{ $noun }}
        </span>
    </div>

    <!-- View Mode Switcher -->
    <div class="flex items-center bg-black/5 p-0.5 rounded-lg text-[11px] font-medium text-apple-textSecondary shadow-inner shrink-0">
        <button type="button" onclick="{{ $modeFn }}('table')" id="btnViewTable"
            class="px-2.5 py-1 rounded-md transition inline-flex items-center gap-1.5" title="Tampilan Tabel">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18"></path>
            </svg>
            <span class="text-[10.5px] sm:text-[11px]">Tabel</span>
        </button>
        <button type="button" onclick="{{ $modeFn }}('cards')" id="btnViewCards"
            class="px-2.5 py-1 rounded-md transition inline-flex items-center gap-1.5" title="Tampilan Kartu">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1"></rect>
            </svg>
            <span class="text-[10.5px] sm:text-[11px]">Kartu</span>
        </button>
    </div>
</div>

<!-- Scroll Helper Notice on Tablet / Mobile when Table View is Active -->
<div id="tableScrollHint" class="lg:hidden flex items-center justify-between px-3.5 py-1.5 bg-apple-canvas/70 text-[10.5px] text-apple-textTertiary border-b border-apple-subtleBorder">
    <div class="flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-apple-blue shrink-0 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
        </svg>
        <span>{{ $scrollHint }}</span>
    </div>
    <button type="button" onclick="{{ $modeFn }}('cards')" class="text-apple-blue hover:text-apple-blueHover font-medium inline-flex items-center gap-0.5 shrink-0 ml-2">
        <span>Mode Kartu</span> &rarr;
    </button>
</div>
