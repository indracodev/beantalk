<!-- Left: Main Time-Series Spline Chart Card (lg:col-span-8) -->
<div class="lg:col-span-8 bg-white border border-apple-border rounded-xl p-3 sm:p-3.5 shadow-2xs flex flex-col justify-between gap-2">
    <!-- Chart Top Bar & Date Range Selector -->
    <div class="flex items-center justify-between pb-1.5 border-b border-apple-subtleBorder gap-2">
        <div class="flex items-center gap-2">
            <div class="w-5 h-5 rounded-md bg-apple-blue/10 text-apple-blue flex items-center justify-center">
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
            </div>
            <h3 class="text-[12.5px] font-semibold text-apple-textPrimary tracking-tight">Tren Kunjungan &amp; Obrolan</h3>
        </div>

        <!-- Range Selector (7d, 14d, 30d) -->
        <div class="flex items-center gap-0.5 bg-black/5 p-0.5 rounded-lg text-[10px] font-medium text-apple-textSecondary">
            <a href="{{ request()->fullUrlWithQuery(['chart_range' => '7d']) }}"
                class="px-2 py-0.5 rounded-md transition {{ ($trafficStats['chartRange'] ?? '') === '7d' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">
                7H
            </a>
            <a href="{{ request()->fullUrlWithQuery(['chart_range' => '14d']) }}"
                class="px-2 py-0.5 rounded-md transition {{ (!request('chart_range') || request('chart_range') === '14d') ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">
                14H
            </a>
            <a href="{{ request()->fullUrlWithQuery(['chart_range' => '30d']) }}"
                class="px-2 py-0.5 rounded-md transition {{ request('chart_range') === '30d' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">
                30H
            </a>
        </div>
    </div>

    <!-- Inline Metrics Chips inside Chart -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 bg-apple-canvas/40 border border-apple-border/60 rounded-lg p-2 text-[10.5px]">
        <div>
            <span class="text-apple-textTertiary text-[9px] uppercase font-semibold block">Puncak Trafik</span>
            <div class="font-bold text-apple-blue font-mono text-[11.5px] truncate" title="{{ $trafficStats['peakDateLabel'] }}">
                {{ $trafficStats['peakDateLabel'] }}
            </div>
        </div>
        <div>
            <span class="text-apple-textTertiary text-[9px] uppercase font-semibold block">Rata-rata</span>
            <div class="font-bold text-apple-textPrimary font-mono text-[11.5px]">
                {{ $trafficStats['avgDailyVisitors'] }} <span class="text-[9px] font-normal text-apple-textTertiary">/ hari</span>
            </div>
        </div>
        <div>
            <span class="text-apple-textTertiary text-[9px] uppercase font-semibold block">Total Sesi</span>
            <div class="font-bold text-apple-textPrimary font-mono text-[11.5px]">
                {{ number_format($trafficStats['totalPeriodVisitors']) }} <span class="text-[9px] font-normal text-apple-textTertiary">visit</span>
            </div>
        </div>
        <div>
            <span class="text-apple-textTertiary text-[9px] uppercase font-semibold block">Konversi Chat</span>
            <div class="font-bold text-purple-700 font-mono text-[11.5px]">
                {{ $trafficStats['chatConversionRate'] }}%
            </div>
        </div>
    </div>

    <!-- SVG Responsive Area Spline Chart -->
    @php
        $trendList = $trafficStats['chartTrendData'] ?? [];
        $maxVal = max(
            collect($trendList)->pluck('visitors')->max() ?? 0,
            collect($trendList)->pluck('chats')->max() ?? 0,
            5
        );
        $maxY = ceil($maxVal / 5) * 5;
        if ($maxY < 5) $maxY = 5;
        $svgW = 700;
        $svgH = 135;
        $padX = 30;
        $padY = 18;
        $pW = $svgW - (2 * $padX);
        $pH = $svgH - (2 * $padY);
        $ptsCount = count($trendList);
        $stepX = $ptsCount > 1 ? ($pW / ($ptsCount - 1)) : $pW;

        $visCoords = [];
        $chatCoords = [];
        foreach ($trendList as $i => $d) {
            $x = $padX + ($i * $stepX);
            $yVis = $svgH - $padY - (($d['visitors'] / $maxY) * $pH);
            $yChat = $svgH - $padY - (($d['chats'] / $maxY) * $pH);
            $visCoords[] = ['x' => $x, 'y' => $yVis, 'val' => $d['visitors'], 'label' => $d['label'], 'date' => $d['date']];
            $chatCoords[] = ['x' => $x, 'y' => $yChat, 'val' => $d['chats'], 'label' => $d['label'], 'date' => $d['date']];
        }

        $visPath = !empty($visCoords) ? ("M " . $visCoords[0]['x'] . " " . $visCoords[0]['y']) : "";
        $chatPath = !empty($chatCoords) ? ("M " . $chatCoords[0]['x'] . " " . $chatCoords[0]['y']) : "";
        for ($i = 1; $i < count($visCoords); $i++) {
            $prev = $visCoords[$i-1];
            $curr = $visCoords[$i];
            $cpX = ($prev['x'] + $curr['x']) / 2;
            $visPath .= " C $cpX {$prev['y']}, $cpX {$curr['y']}, {$curr['x']} {$curr['y']}";

            $prevC = $chatCoords[$i-1];
            $currC = $chatCoords[$i];
            $cpXC = ($prevC['x'] + $currC['x']) / 2;
            $chatPath .= " C $cpXC {$prevC['y']}, $cpXC {$currC['y']}, {$currC['x']} {$currC['y']}";
        }

        $visArea = !empty($visCoords) ? ($visPath . " L " . end($visCoords)['x'] . " " . ($svgH - $padY) . " L " . $visCoords[0]['x'] . " " . ($svgH - $padY) . " Z") : "";
    @endphp

    <div class="relative w-full">
        <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" class="w-full h-28 sm:h-36 overflow-visible select-none" preserveAspectRatio="none">
            <defs>
                <linearGradient id="visitorAreaGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color: var(--apple-blue); stop-opacity: 0.22" />
                    <stop offset="100%" style="stop-color: var(--apple-blue); stop-opacity: 0.0" />
                </linearGradient>
            </defs>

            <!-- Horizontal Grid Lines -->
            @for ($g = 0; $g <= 3; $g++)
                @php
                    $gridY = $svgH - $padY - (($g / 3) * $pH);
                    $gridVal = round(($g / 3) * $maxY);
                @endphp
                <line x1="{{ $padX }}" y1="{{ $gridY }}" x2="{{ $svgW - $padX }}" y2="{{ $gridY }}" stroke="#E5E5EA" stroke-width="1" stroke-dasharray="{{ $g == 0 ? 'none' : '3,3' }}" />
                <text x="{{ $padX - 6 }}" y="{{ $gridY + 3 }}" fill="#8E8E93" font-size="8" font-family="ui-monospace, monospace" text-anchor="end">{{ $gridVal }}</text>
            @endfor

            <!-- Filled Area for Visitors -->
            @if($visArea)
                <path d="{{ $visArea }}" fill="url(#visitorAreaGrad)" />
            @endif

            <!-- Spline: Pengunjung (Blue) -->
            @if($visPath)
                <path d="{{ $visPath }}" fill="none" style="stroke: var(--apple-blue)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            @endif

            <!-- Spline: Obrolan / Chat (Purple) -->
            @if($chatPath)
                <path d="{{ $chatPath }}" fill="none" stroke="#8E24AA" stroke-width="1.8" stroke-dasharray="3,3" stroke-linecap="round" stroke-linejoin="round" />
            @endif

            <!-- Interactive Data Points -->
            @foreach($visCoords as $idx => $pt)
                <g class="cursor-pointer group">
                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="3" style="fill: var(--apple-blue)" stroke="#FFFFFF" stroke-width="1.5" class="transition-all duration-150 group-hover:r-4.5" />
                    <title>{{ $pt['label'] }}: {{ $pt['val'] }} pengunjung terlacak</title>
                </g>
            @endforeach

            @foreach($chatCoords as $idx => $pt)
                <g class="cursor-pointer group">
                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="2.5" fill="#8E24AA" stroke="#FFFFFF" stroke-width="1" class="transition-all duration-150 group-hover:r-4" />
                    <title>{{ $pt['label'] }}: {{ $pt['val'] }} obrolan dibuka</title>
                </g>
            @endforeach

            <!-- X-Axis Labels -->
            @php
                $skipStep = count($visCoords) > 15 ? 2 : 1;
            @endphp
            @foreach($visCoords as $idx => $pt)
                @if($idx % $skipStep == 0 || $idx == count($visCoords) - 1)
                    <text x="{{ $pt['x'] }}" y="{{ $svgH - 3 }}" fill="#8E8E93" font-size="8" font-family="ui-sans-serif, system-ui" text-anchor="middle">
                        {{ $pt['label'] }}
                    </text>
                @endif
            @endforeach
        </svg>
    </div>

    <!-- Chart Legend -->
    <div class="flex items-center justify-between text-[10px] text-apple-textSecondary pt-1 border-t border-apple-subtleBorder">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-apple-blue inline-block"></span>
                <span class="font-medium text-apple-textPrimary">Pengunjung</span>
            </span>
            <span class="inline-flex items-center gap-1">
                <span class="w-2 h-0.5 bg-purple-700 inline-block"></span>
                <span class="font-medium text-apple-textPrimary">Obrolan Dibuka</span>
            </span>
        </div>
        <span class="text-apple-textTertiary font-mono hidden sm:inline">Sorot titik grafik untuk rincian harian</span>
    </div>
</div>
