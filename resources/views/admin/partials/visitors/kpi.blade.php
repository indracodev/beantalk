<!-- Space-Saving Compact KPI Ribbon (Horizontal Mini-Bar) -->
<div class="bg-white border border-apple-border/80 rounded-xl px-3 py-1.5 shadow-2xs flex flex-wrap items-center justify-between gap-1.5 text-[11.5px] shrink-0">
    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 text-apple-textSecondary">
        <a href="{{ route('admin.visitors') }}" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md hover:bg-black/5 transition text-apple-textPrimary font-medium" title="Semua Pengunjung">
            <span class="text-apple-textTertiary text-[10px]">Total:</span>
            <span class="font-bold text-apple-textPrimary font-mono text-[12px]">{{ number_format($visitorStats->total_visitors ?? 0) }}</span>
        </a>
        <span class="text-apple-border">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'online']) }}" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md hover:bg-emerald-50 transition {{ request('status') === 'online' ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}" title="Online 5 Menit Terakhir">
            <span class="w-2 h-2 rounded-full bg-emerald-500 {{ ($visitorStats->online_visitors ?? 0) > 0 ? 'animate-ping' : '' }}"></span>
            <span class="text-apple-textTertiary text-[10px]">Online:</span>
            <span class="font-bold text-emerald-600 font-mono text-[12px]">{{ number_format($visitorStats->online_visitors ?? 0) }}</span>
        </a>
        <span class="text-apple-border">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'identified']) }}" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md hover:bg-apple-blue/10 transition {{ request('status') === 'identified' ? 'bg-apple-blue/10 text-apple-blue font-semibold' : '' }}" title="Pengunjung dengan Nama atau Email">
            <span class="text-apple-textTertiary text-[10px]">Teridentifikasi:</span>
            <span class="font-bold text-apple-blue font-mono text-[12px]">{{ number_format($visitorStats->identified_visitors ?? 0) }}</span>
        </a>
        <span class="text-apple-border">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'chatting']) }}" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md hover:bg-purple-50 transition {{ request('status') === 'chatting' ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}" title="Pengunjung yang Memulai Chat">
            <span class="text-apple-textTertiary text-[10px]">Chat:</span>
            <span class="font-bold text-purple-700 font-mono text-[12px]">{{ number_format($chattingVisitorsCount ?? 0) }}</span>
            <span class="text-[9.5px] text-purple-600 font-normal">({{ $trafficStats['chatConversionRate'] }}%)</span>
        </a>
        <span class="text-apple-border">|</span>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'today']) }}" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md hover:bg-amber-50 transition {{ request('status') === 'today' ? 'bg-amber-50 text-amber-800 font-semibold' : '' }}" title="Aktif Hari Ini">
            <span class="text-apple-textTertiary text-[10px]">Hari Ini:</span>
            <span class="font-bold text-amber-700 font-mono text-[12px]">{{ number_format($visitorStats->today_visitors ?? 0) }}</span>
        </a>
    </div>
    <div class="text-[10px] text-apple-textTertiary font-mono hidden md:inline">
        Update otomatis
    </div>
</div>
