<!-- Kolom 2: Halaman Setiap Integrasi Web yang Paling Banyak Dikunjungi untuk Memulai Chat -->
<div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-5 shadow-apple-card flex flex-col justify-between">
    <div>
        <div class="pb-3 border-b border-apple-border/60 flex items-center justify-between">
            <div>
                <h3 class="text-[14px] font-semibold text-apple-textPrimary tracking-tight">Top Trigger Pages per Website</h3>
                <p class="text-[11.5px] text-apple-textSecondary">Halaman yang paling banyak dikunjungi pengunjung saat memulai chat.</p>
            </div>
            <span class="text-[10.5px] font-medium text-apple-textTertiary bg-black/5 px-2 py-0.5 rounded-md">By Channel</span>
        </div>

        <!-- Web Integrations Landing Pages List -->
        <div class="flex flex-col gap-3.5 mt-3.5 max-h-[380px] overflow-y-auto pr-0.5">
            @forelse($topPagesByProject as $projEntry)
                <div class="p-3 rounded-xl bg-apple-canvas/40 border border-apple-border/60 hover:bg-white hover:shadow-2xs transition">
                    <!-- Project / Channel Header -->
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-apple-border/40">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-md text-white flex items-center justify-center font-bold text-[9px] shrink-0 shadow-2xs" style="background-color: {{ $projEntry['color'] }};">
                                {{ $projEntry['initials'] }}
                            </div>
                            <div class="truncate">
                                <h4 class="font-semibold text-apple-textPrimary text-[12.5px] truncate">{{ $projEntry['project_name'] }}</h4>
                                <span class="text-[10px] text-apple-textTertiary font-mono">{{ $projEntry['domain'] }}</span>
                            </div>
                        </div>
                        <span class="text-[10.5px] font-bold text-apple-blue font-mono shrink-0 bg-apple-blue/10 px-1.5 py-0.5 rounded">
                            {{ $projEntry['total_chats'] }} chats
                        </span>
                    </div>

                    <!-- List of Top Entry Pages for this website -->
                    <div class="flex flex-col gap-2">
                        @foreach($projEntry['pages'] as $page)
                            <div>
                                <div class="flex items-start justify-between text-[11.5px] gap-2">
                                    <div class="truncate">
                                        <div class="font-medium text-apple-textPrimary truncate" title="{{ $page['title'] }}">
                                            {{ $page['title'] }}
                                        </div>
                                        <span class="text-[10px] text-apple-textTertiary font-mono truncate block">{{ $page['path'] }}</span>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="font-bold text-apple-textPrimary font-mono text-[11px]">{{ $page['volume'] }}</span>
                                        <span class="text-[9.5px] text-apple-textTertiary font-mono ml-0.5">({{ $page['share'] }})</span>
                                    </div>
                                </div>
                                <!-- Share percentage bar -->
                                <div class="w-full bg-black/5 rounded-full h-1 mt-1 overflow-hidden">
                                    <div class="h-full rounded-full transition-all" style="width: {{ $page['share'] }}; background-color: {{ $projEntry['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-apple-textTertiary text-[12px]">
                    Belum ada riwayat halaman pemicu chat.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-3 pt-2.5 border-t border-apple-border/40 text-[11px] text-apple-textTertiary flex items-center justify-between">
        <span>Total Landing Channels</span>
        <strong class="font-mono text-apple-textPrimary font-semibold">{{ count($topPagesByProject) }} Websites Terintegrasi</strong>
    </div>
</div>
