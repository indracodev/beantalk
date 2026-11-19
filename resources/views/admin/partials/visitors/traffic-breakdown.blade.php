<!-- Right: Breakdown Metrics Stack (lg:col-span-4) -->
<div class="lg:col-span-4 flex flex-col gap-2.5 justify-between">
    <!-- Subcard 1: Perangkat & OS -->
    <div class="bg-white border border-apple-border rounded-xl p-3 shadow-2xs flex-1 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-1.5 border-b border-apple-subtleBorder">
                <span class="font-semibold text-apple-textPrimary text-[11.5px] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                        <rect x="9" y="9" width="6" height="6"></rect>
                    </svg>
                    Perangkat &amp; Platform
                </span>
                <span class="text-[10px] font-mono text-apple-textTertiary">{{ $trafficStats['deviceTotal'] }} unit</span>
            </div>

            @php
                $devTotal = max(1, $trafficStats['deviceTotal']);
                $pctDesktop = round(($trafficStats['deviceCounts']['desktop'] / $devTotal) * 100);
                $pctMobile = round(($trafficStats['deviceCounts']['mobile'] / $devTotal) * 100);
                $pctTablet = round(($trafficStats['deviceCounts']['tablet'] / $devTotal) * 100);
            @endphp
            <div class="flex flex-col gap-1.5 mt-2 text-[10.5px]">
                <div class="flex flex-col gap-0.5">
                    <div class="flex justify-between items-center text-apple-textSecondary">
                        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-apple-blue"></span> Desktop</span>
                        <span class="font-mono text-apple-textPrimary">{{ $trafficStats['deviceCounts']['desktop'] }} ({{ $pctDesktop }}%)</span>
                    </div>
                    <div class="w-full h-1 rounded-full bg-apple-canvas overflow-hidden">
                        <div class="h-full bg-apple-blue rounded-full" style="width: {{ $pctDesktop }}%;"></div>
                    </div>
                </div>

                <div class="flex flex-col gap-0.5">
                    <div class="flex justify-between items-center text-apple-textSecondary">
                        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Mobile</span>
                        <span class="font-mono text-apple-textPrimary">{{ $trafficStats['deviceCounts']['mobile'] }} ({{ $pctMobile }}%)</span>
                    </div>
                    <div class="w-full h-1 rounded-full bg-apple-canvas overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pctMobile }}%;"></div>
                    </div>
                </div>

                <div class="flex flex-col gap-0.5">
                    <div class="flex justify-between items-center text-apple-textSecondary">
                        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Tablet</span>
                        <span class="font-mono text-apple-textPrimary">{{ $trafficStats['deviceCounts']['tablet'] }} ({{ $pctTablet }}%)</span>
                    </div>
                    <div class="w-full h-1 rounded-full bg-apple-canvas overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" style="width: {{ $pctTablet }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-apple-subtleBorder flex flex-col gap-1.5 text-[9.5px] mt-2">
            <div class="flex flex-wrap items-center gap-1">
                <span class="text-apple-textTertiary font-semibold min-w-[46px]">OS:</span>
                @forelse($trafficStats['osCounts'] as $osName => $osCnt)
                    <a href="{{ request()->fullUrlWithQuery(['os' => request('os') === $osName ? null : $osName]) }}"
                        class="px-1.5 py-0.5 rounded transition font-mono inline-flex items-center gap-1 {{ request('os') === $osName ? 'bg-apple-blue text-white font-bold shadow-2xs' : 'bg-apple-canvas border border-apple-border/70 text-apple-textSecondary hover:text-apple-textPrimary hover:border-apple-border' }}"
                        title="Filter OS: {{ $osName }}">
                        <span>{{ $osName }}</span>
                        <span class="{{ request('os') === $osName ? 'text-white/80' : 'text-apple-textTertiary' }}">({{ $osCnt }})</span>
                    </a>
                @empty
                    <span class="text-apple-textTertiary">-</span>
                @endforelse
            </div>
            <div class="flex flex-wrap items-center gap-1">
                <span class="text-apple-textTertiary font-semibold min-w-[46px]">Browser:</span>
                @forelse($trafficStats['browserCounts'] as $bName => $bCnt)
                    <a href="{{ request()->fullUrlWithQuery(['browser' => request('browser') === $bName ? null : $bName]) }}"
                        class="px-1.5 py-0.5 rounded transition font-mono inline-flex items-center gap-1 {{ request('browser') === $bName ? 'bg-apple-blue text-white font-bold shadow-2xs' : 'bg-apple-canvas border border-apple-border/70 text-apple-textSecondary hover:text-apple-textPrimary hover:border-apple-border' }}"
                        title="Filter Browser: {{ $bName }}">
                        <span>{{ $bName }}</span>
                        <span class="{{ request('browser') === $bName ? 'text-white/80' : 'text-apple-textTertiary' }}">({{ $bCnt }})</span>
                    </a>
                @empty
                    <span class="text-apple-textTertiary">-</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Subcard 2: Pangsa Website & Jam Sibuk -->
    <div class="bg-white border border-apple-border rounded-xl p-3 shadow-2xs flex-1 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-1.5 border-b border-apple-subtleBorder">
                <span class="font-semibold text-apple-textPrimary text-[11.5px] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                    </svg>
                    Pangsa Website
                </span>
                <span class="text-[10px] font-mono text-apple-textTertiary">{{ count($trafficStats['channelShare']) }} channel</span>
            </div>

            <div class="flex flex-col gap-1.5 mt-2 text-[10.5px]">
                @forelse(array_slice($trafficStats['channelShare'], 0, 3) as $ch)
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between items-center text-apple-textSecondary">
                            <span class="truncate font-medium text-apple-textPrimary max-w-[150px]" title="{{ $ch['name'] }}">{{ $ch['name'] }}</span>
                            <span class="font-mono text-apple-textSecondary">{{ $ch['visitors'] }} ({{ $ch['percentage'] }}%)</span>
                        </div>
                        <div class="w-full h-1 rounded-full bg-apple-canvas overflow-hidden">
                            <div class="h-full bg-apple-blue rounded-full" style="width: {{ $ch['percentage'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-[10px] text-apple-textTertiary py-1 text-center">Belum ada website</div>
                @endforelse
            </div>
        </div>

        <div class="pt-1.5 border-t border-apple-subtleBorder flex items-center justify-between text-[9.5px] mt-1.5">
            <span class="text-apple-textTertiary">Jam Ramai:</span>
            <div class="flex items-center gap-1 font-mono text-apple-textSecondary">
                <span class="px-1 py-0.2 rounded bg-apple-canvas" title="06:00 - 12:00">Pagi: {{ $trafficStats['timeOfDay']['morning'] ?? 0 }}</span>
                <span class="px-1 py-0.2 rounded bg-apple-canvas" title="12:00 - 18:00">Siang: {{ $trafficStats['timeOfDay']['afternoon'] ?? 0 }}</span>
                <span class="px-1 py-0.2 rounded bg-apple-canvas" title="18:00 - 24:00">Malam: {{ $trafficStats['timeOfDay']['evening'] ?? 0 }}</span>
            </div>
        </div>
    </div>

</div>
