<!-- Breakdown Grid & Overview (Adaptive per Periode) -->
@if($period === '7d')
    <!-- 7-Day Day-by-Day Grid (Senin - Minggu) -->
    <div class="mt-3 pt-3 border-t border-apple-border/60">
        <div class="grid grid-cols-7 gap-1.5 text-center">
            @foreach($weeklyChartData as $wDay)
                <div class="p-1.5 rounded-lg {{ $wDay['is_today'] ? 'bg-apple-blue/10 border border-apple-blue/30 font-semibold' : 'bg-apple-canvas/50 border border-apple-border/40' }} transition">
                    <div class="text-[10px] {{ $wDay['is_today'] ? 'text-apple-blue font-bold' : 'text-apple-textSecondary' }}">
                        {{ $wDay['day_name'] }}
                    </div>
                    <div class="text-[12px] font-mono font-bold text-apple-textPrimary mt-0.5">
                        {{ $wDay['inbound'] }}
                    </div>
                    <div class="text-[9px] font-mono text-apple-textTertiary">
                        {{ $wDay['resolved'] }} res
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@elseif(!empty($isCustomDate) && count($chartData) <= 7)
    <!-- Custom Date Range (<= 7 Hari) Grid Harian -->
    <div class="mt-3 pt-3 border-t border-apple-border/60">
        <div class="grid grid-cols-{{ count($chartData) }} gap-1.5 text-center">
            @foreach($chartData as $cDay)
                <div class="p-1.5 rounded-lg bg-apple-canvas/50 border border-apple-border/40 transition">
                    <div class="text-[10px] text-apple-textSecondary font-medium truncate">
                        {{ $cDay['label'] }}
                    </div>
                    <div class="text-[12px] font-mono font-bold text-apple-textPrimary mt-0.5">
                        {{ $cDay['inbound'] }}
                    </div>
                    <div class="text-[9px] font-mono text-apple-textTertiary">
                        {{ $cDay['resolved'] }} res
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <!-- Overview Metrik (Hari Ini / 30 Hari / Kuartal / Rentang Tanggal Panjang) -->
    <div class="mt-3 pt-3 border-t border-apple-border/60">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
            <div class="p-2 rounded-xl bg-apple-canvas/50 border border-apple-border/40">
                <div class="text-[10.5px] text-apple-textSecondary">Total Inbound</div>
                <div class="text-[14px] font-bold font-mono text-apple-blue mt-0.5">{{ number_format($chartTotalInbound) }}</div>
            </div>
            <div class="p-2 rounded-xl bg-apple-canvas/50 border border-apple-border/40">
                <div class="text-[10.5px] text-apple-textSecondary">Total Resolved</div>
                <div class="text-[14px] font-bold font-mono text-emerald-600 mt-0.5">{{ number_format($chartTotalResolved) }}</div>
            </div>
            <div class="p-2 rounded-xl bg-apple-canvas/50 border border-apple-border/40">
                <div class="text-[10.5px] text-apple-textSecondary">Rata-rata Harian</div>
                <div class="text-[14px] font-bold font-mono text-apple-textPrimary mt-0.5">{{ $chartAvgInbound }} <span class="text-[10px] font-normal text-apple-textTertiary">/hari</span></div>
            </div>
            <div class="p-2 rounded-xl bg-apple-canvas/50 border border-apple-border/40">
                <div class="text-[10.5px] text-apple-textSecondary">Puncak Volume</div>
                <div class="text-[13px] font-bold font-mono text-indigo-600 mt-0.5 truncate" title="{{ $chartPeakDate }}">{{ $chartPeakDate }}</div>
            </div>
        </div>
    </div>
@endif
