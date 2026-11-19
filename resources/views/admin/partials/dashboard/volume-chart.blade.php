<!-- SVG Responsive Spline Chart (macOS Pro Look) -->
<div class="relative w-full pt-4 pb-1">
    @php
        $maxDataVal = max(
            collect($chartData)->pluck('inbound')->max() ?? 0,
            collect($chartData)->pluck('resolved')->max() ?? 0,
            10
        );
        $maxY = ceil($maxDataVal / 10) * 10;
        if ($maxY < 10) $maxY = 10;
        $svgWidth = 700;
        $svgHeight = 220;
        $paddingX = 40;
        $paddingY = 25;
        $plotWidth = $svgWidth - (2 * $paddingX);
        $plotHeight = $svgHeight - (2 * $paddingY);
        $countPts = count($chartData);
        $stepX = $countPts > 1 ? ($plotWidth / ($countPts - 1)) : $plotWidth;

        // Compute points
        $inboundCoords = [];
        $resolvedCoords = [];
        foreach ($chartData as $i => $d) {
            $x = $paddingX + ($i * $stepX);
            $yInbound = $svgHeight - $paddingY - (($d['inbound'] / $maxY) * $plotHeight);
            $yResolved = $svgHeight - $paddingY - (($d['resolved'] / $maxY) * $plotHeight);
            $inboundCoords[] = ['x' => $x, 'y' => $yInbound, 'val' => $d['inbound'], 'label' => $d['label']];
            $resolvedCoords[] = ['x' => $x, 'y' => $yResolved, 'val' => $d['resolved'], 'label' => $d['label']];
        }

        // Build path strings
        $inboundPath = !empty($inboundCoords) ? ("M " . $inboundCoords[0]['x'] . " " . $inboundCoords[0]['y']) : "";
        $resolvedPath = !empty($resolvedCoords) ? ("M " . $resolvedCoords[0]['x'] . " " . $resolvedCoords[0]['y']) : "";
        for ($i = 1; $i < count($inboundCoords); $i++) {
            $prev = $inboundCoords[$i-1];
            $curr = $inboundCoords[$i];
            $cpX = ($prev['x'] + $curr['x']) / 2;
            $inboundPath .= " C $cpX {$prev['y']}, $cpX {$curr['y']}, {$curr['x']} {$curr['y']}";

            $prevR = $resolvedCoords[$i-1];
            $currR = $resolvedCoords[$i];
            $cpXR = ($prevR['x'] + $currR['x']) / 2;
            $resolvedPath .= " C $cpXR {$prevR['y']}, $cpXR {$currR['y']}, {$currR['x']} {$currR['y']}";
        }

        // Closed area path for gradient
        $inboundArea = !empty($inboundCoords) ? ($inboundPath . " L " . end($inboundCoords)['x'] . " " . ($svgHeight - $paddingY) . " L " . $inboundCoords[0]['x'] . " " . ($svgHeight - $paddingY) . " Z") : "";
    @endphp

    <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="w-full h-44 sm:h-56 overflow-visible select-none" preserveAspectRatio="none">
        <defs>
            <!-- Area Gradient Inbound -->
            <linearGradient id="inboundGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#0071E3" stop-opacity="0.22" />
                <stop offset="100%" stop-color="#0071E3" stop-opacity="0.01" />
            </linearGradient>
            <linearGradient id="resolvedGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#34C759" stop-opacity="0.18" />
                <stop offset="100%" stop-color="#34C759" stop-opacity="0.0" />
            </linearGradient>
        </defs>

        <!-- Horizontal Grid lines -->
        @for($g = 0; $g <= 4; $g++)
            @php
                $gridVal = round(($g / 4) * $maxY);
                $gridY = $svgHeight - $paddingY - (($gridVal / $maxY) * $plotHeight);
            @endphp
            <line x1="{{ $paddingX }}" y1="{{ $gridY }}" x2="{{ $svgWidth - $paddingX }}" y2="{{ $gridY }}" stroke="#E5E5EA" stroke-dasharray="3,3" stroke-width="1" />
            <text x="{{ $paddingX - 8 }}" y="{{ $gridY + 3.5 }}" fill="#86868B" font-size="9" text-anchor="end" font-family="monospace">{{ $gridVal }}</text>
        @endfor

        @if($inboundArea)
            <!-- Area fill -->
            <path d="{{ $inboundArea }}" fill="url(#inboundGrad)" />
        @endif

        @if($resolvedPath)
            <!-- Resolution Line (Green) -->
            <path d="{{ $resolvedPath }}" fill="none" stroke="#34C759" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        @endif

        @if($inboundPath)
            <!-- Inbound Line (Blue) -->
            <path d="{{ $inboundPath }}" fill="none" stroke="#0071E3" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
        @endif

        <!-- Interactive Data Points -->
        @foreach($inboundCoords as $idx => $pt)
            @php
                $rpt = $resolvedCoords[$idx];
            @endphp
            <!-- Inbound Dot -->
            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="3.5" fill="#FFFFFF" stroke="#0071E3" stroke-width="2" class="transition-all hover:r-5 cursor-pointer">
                <title>{{ $pt['label'] }}: {{ $pt['val'] }} Chats Inbound</title>
            </circle>
            <!-- Resolved Dot -->
            <circle cx="{{ $rpt['x'] }}" cy="{{ $rpt['y'] }}" r="3" fill="#FFFFFF" stroke="#34C759" stroke-width="1.8" class="transition-all hover:r-4.5 cursor-pointer">
                <title>{{ $rpt['label'] }}: {{ $rpt['val'] }} Chats Resolved</title>
            </circle>
        @endforeach
    </svg>

    <!-- X Axis Labels -->
    <div class="flex justify-between px-6 sm:px-8 mt-1 text-[10.5px] text-apple-textTertiary font-medium">
        @foreach($chartData as $idx => $d)
            <span class="{{ (!empty($d['is_today']) || $loop->last) ? 'text-apple-blue font-bold' : '' }}">
                {{ $d['label'] }}
            </span>
        @endforeach
    </div>
</div>
