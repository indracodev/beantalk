@extends('layouts.admin')

@section('title', 'Pengunjung Terlacak (Visitor Tracking)')
@section('header_title', 'Pengunjung Terlacak')

@section('content')
<div class="flex-1 h-full max-h-full min-h-0 min-w-0 overflow-y-auto overscroll-y-contain p-3 sm:p-4 md:p-5 pb-28 md:pb-12 flex flex-col gap-3 sm:gap-3.5 bg-apple-canvas/30 w-full" id="visitorsMainScroll">
    <!-- Compact Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-apple-border gap-2 shrink-0">
        <div class="flex items-center gap-2.5">
            <h2 class="text-[16px] font-semibold text-apple-textPrimary tracking-tight">Pengunjung Terlacak &amp; Analitik Trafik</h2>
            <span class="inline-flex items-center gap-1.5 text-[10px] font-mono px-2 py-0.5 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30">
                <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
                LIVE Tracker
            </span>
        </div>
        <div class="flex items-center gap-2">
            <!-- Toggle Traffic Chart Button -->
            <button type="button" onclick="toggleTrafficHub()" id="btnToggleTrafficHub"
                class="px-2.5 py-1 rounded-lg border border-apple-border bg-white hover:bg-apple-canvas/60 text-apple-textSecondary hover:text-apple-textPrimary text-[11px] font-medium transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5 text-apple-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span id="txtToggleTrafficHub">Sembunyikan Grafik</span>
            </button>
        </div>
    </div>

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

    <!-- Traffic Analytics & Chart Hub (Compact 12-Column Grid Layout) -->
    <div id="trafficHubSection" class="flex flex-col gap-2.5 transition-all duration-200 shrink-0">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-2.5 sm:gap-3 items-stretch">
            
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
                                <stop offset="0%" stop-color="#0071E3" stop-opacity="0.22" />
                                <stop offset="100%" stop-color="#0071E3" stop-opacity="0.0" />
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
                            <path d="{{ $visPath }}" fill="none" stroke="#0071E3" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        @endif

                        <!-- Spline: Obrolan / Chat (Purple) -->
                        @if($chatPath)
                            <path d="{{ $chatPath }}" fill="none" stroke="#8E24AA" stroke-width="1.8" stroke-dasharray="3,3" stroke-linecap="round" stroke-linejoin="round" />
                        @endif

                        <!-- Interactive Data Points -->
                        @foreach($visCoords as $idx => $pt)
                            <g class="cursor-pointer group">
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="3" fill="#0071E3" stroke="#FFFFFF" stroke-width="1.5" class="transition-all duration-150 group-hover:r-4.5" />
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
        </div>
    </div>

    <!-- Space-Saving Compact Filter & Search Toolbar (Responsive Wrap Strip) -->
    @php
        $currentSort = request('sort', 'last_seen_at');
        $currentDir = strtolower(request('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortUrl = function($col) use ($currentSort, $currentDir) {
            $nextDir = ($currentSort === $col && $currentDir === 'desc') ? 'asc' : 'desc';
            return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $nextDir]);
        };

        $sortIcon = function($col) use ($currentSort, $currentDir) {
            if ($currentSort !== $col) {
                return '<span class="text-apple-textTertiary/40 ml-1 text-[9.5px]">↕</span>';
            }
            return $currentDir === 'desc'
                ? '<span class="text-apple-blue font-bold ml-1 text-[9.5px]">▼</span>'
                : '<span class="text-apple-blue font-bold ml-1 text-[9.5px]">▲</span>';
        };

        $hasFilters = request()->filled('search') || request()->filled('project_id') || request()->filled('status') || request()->filled('device') || request()->filled('browser') || request()->filled('os') || request()->filled('date_range') || (request('sort') && request('sort') !== 'last_seen_at') || (request('dir') && request('dir') !== 'desc') || (request('per_page') && request('per_page') != 25);
    @endphp

    <div class="bg-white border border-apple-border/80 rounded-xl p-2 sm:p-2.5 shadow-2xs shrink-0">
        <form action="{{ route('admin.visitors') }}" method="GET" id="visitorsFilterForm" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 m-0 text-[11.5px] flex-wrap">
            <!-- Search bar (Flexible) -->
            <div class="flex-1 relative min-w-[200px]">
                <input type="text" name="search" id="visitorsSearchInput"
                    class="w-full pl-7.5 pr-7 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/40 text-apple-textPrimary placeholder:text-apple-textTertiary focus:bg-white focus:outline-none focus:ring-1 focus:ring-apple-blue/30 text-[11.5px] transition shadow-2xs"
                    placeholder="Cari CUS-xxxx, nama, email, IP, atau UUID..."
                    value="{{ request('search') }}">
                <svg class="w-3.5 h-3.5 text-apple-textTertiary absolute left-2 top-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                @if(request('search'))
                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-2 top-1.5 text-apple-textTertiary hover:text-apple-textPrimary text-[13px] font-bold">&times;</a>
                @endif
            </div>

            <!-- Compact Selects Strip -->
            <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                <!-- Channel / Website -->
                <select name="project_id" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11px] cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua Website</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>

                <!-- Status Kehadiran -->
                <select name="status" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11px] cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>🟢 Online (&lt; 5m)</option>
                    <option value="today" {{ request('status') === 'today' ? 'selected' : '' }}>Aktif Hari Ini</option>
                    <option value="chatting" {{ request('status') === 'chatting' ? 'selected' : '' }}>Memiliki Chat</option>
                    <option value="identified" {{ request('status') === 'identified' ? 'selected' : '' }}>Teridentifikasi</option>
                    <option value="anonymous" {{ request('status') === 'anonymous' ? 'selected' : '' }}>Anonim</option>
                </select>

                <!-- Device -->
                <select name="device" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11px] cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua Perangkat</option>
                    <option value="mobile" {{ request('device') === 'mobile' ? 'selected' : '' }}>📱 Mobile</option>
                    <option value="tablet" {{ request('device') === 'tablet' ? 'selected' : '' }}>📟 Tablet</option>
                    <option value="desktop" {{ request('device') === 'desktop' ? 'selected' : '' }}>💻 Desktop</option>
                </select>

                <!-- Browser -->
                <select name="browser" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11px] cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua Browser</option>
                    <option value="Chrome" {{ request('browser') === 'Chrome' ? 'selected' : '' }}>Chrome</option>
                    <option value="Safari" {{ request('browser') === 'Safari' ? 'selected' : '' }}>Safari</option>
                    <option value="Firefox" {{ request('browser') === 'Firefox' ? 'selected' : '' }}>Firefox</option>
                    <option value="Edge" {{ request('browser') === 'Edge' ? 'selected' : '' }}>Edge</option>
                    <option value="Opera" {{ request('browser') === 'Opera' ? 'selected' : '' }}>Opera</option>
                    <option value="Lainnya" {{ request('browser') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>

                <!-- OS -->
                <select name="os" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11px] cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua OS</option>
                    <option value="Windows" {{ request('os') === 'Windows' ? 'selected' : '' }}>Windows</option>
                    <option value="macOS" {{ request('os') === 'macOS' ? 'selected' : '' }}>macOS</option>
                    <option value="iOS" {{ request('os') === 'iOS' ? 'selected' : '' }}>iOS</option>
                    <option value="Android" {{ request('os') === 'Android' ? 'selected' : '' }}>Android</option>
                    <option value="Linux" {{ request('os') === 'Linux' ? 'selected' : '' }}>Linux</option>
                    <option value="Lainnya" {{ request('os') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>

                <!-- Urutkan -->
                <div class="flex items-center gap-0.5">
                    <select name="sort" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11px] cursor-pointer" onchange="this.form.submit()">
                        <option value="last_seen_at" {{ (!request('sort') || request('sort') === 'last_seen_at') ? 'selected' : '' }}>Terakhir Dilihat</option>
                        <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Pertama Dilihat</option>
                        <option value="conversations_count" {{ request('sort') === 'conversations_count' ? 'selected' : '' }}>Jumlah Chat</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama</option>
                        <option value="customer_code" {{ request('sort') === 'customer_code' ? 'selected' : '' }}>Kode</option>
                    </select>
                    <input type="hidden" name="dir" id="filterDirInput" value="{{ $currentDir }}">
                    <button type="button" onclick="toggleSortDirection()"
                        class="p-1.5 rounded-lg border border-apple-border bg-apple-canvas/40 hover:bg-white text-apple-textSecondary hover:text-apple-textPrimary transition shrink-0"
                        title="Balik Arah Urutan ({{ strtoupper($currentDir) }})">
                        <span class="text-[10px] font-bold">{{ $currentDir === 'desc' ? '▼' : '▲' }}</span>
                    </button>
                </div>

                <!-- Per Halaman -->
                <select name="per_page" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11px] cursor-pointer" onchange="this.form.submit()">
                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 baris</option>
                    <option value="25" {{ (!request('per_page') || request('per_page') == 25) ? 'selected' : '' }}>25 baris</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 baris</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 baris</option>
                </select>

                <button type="submit" class="px-3 py-1.5 rounded-lg bg-apple-textPrimary text-white hover:bg-black transition font-medium text-[11px] shadow-2xs inline-flex items-center gap-1 shrink-0">
                    <span>Cari</span>
                </button>

                @if($hasFilters)
                    <a href="{{ route('admin.visitors') }}" class="px-2.5 py-1.5 rounded-lg border border-apple-border hover:bg-black/5 text-apple-textSecondary hover:text-apple-textPrimary transition font-medium text-[11px] shrink-0 text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Visitors Container (Adaptive: Table on Desktop/Tabs with Native Momentum Scroll + Smart Card Feed for Mobile/Tablet) -->
    <div class="bg-white border border-apple-border rounded-xl shadow-apple-sm overflow-hidden flex flex-col">
        <!-- Subheader Toolbar with View Switcher (Table vs Card) -->
        <div class="px-3.5 sm:px-4 py-2.5 border-b border-apple-border bg-apple-canvas/50 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-apple-textPrimary text-[12px] sm:text-[12.5px]">Daftar Pengunjung Terlacak</span>
                <span class="text-[10.5px] font-medium px-2 py-0.2 rounded-full bg-white border border-apple-border/70 text-apple-textSecondary shadow-2xs">
                    {{ $visitors->total() }} pengunjung
                </span>
            </div>

            <!-- View Mode Switcher -->
            <div class="flex items-center bg-black/5 p-0.5 rounded-lg text-[11px] font-medium text-apple-textSecondary shadow-inner shrink-0">
                <button type="button" onclick="setVisitorViewMode('table')" id="btnViewTable"
                    class="px-2.5 py-1 rounded-md transition inline-flex items-center gap-1.5" title="Tampilan Tabel">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18"></path>
                    </svg>
                    <span class="text-[10.5px] sm:text-[11px]">Tabel</span>
                </button>
                <button type="button" onclick="setVisitorViewMode('cards')" id="btnViewCards"
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
                <span>Geser ke kanan untuk melihat Halaman, Perangkat, IP &amp; Aksi</span>
            </div>
            <button type="button" onclick="setVisitorViewMode('cards')" class="text-apple-blue hover:text-apple-blueHover font-medium inline-flex items-center gap-0.5 shrink-0 ml-2">
                <span>Mode Kartu</span> &rarr;
            </button>
        </div>

        <!-- 1. FULL TABLE VIEW (With smooth native momentum scroll on tablet and mobile) -->
        <div id="visitorsTableView" class="w-full overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
            <table class="w-full text-left text-[11.5px] sm:text-[12px] border-collapse min-w-[760px] md:min-w-[900px]">
                <thead class="bg-apple-canvas/80 text-apple-textSecondary text-[10px] sm:text-[10.5px] font-semibold uppercase tracking-wider border-b border-apple-border select-none sticky top-0 z-10 backdrop-blur-xs">
                    <tr>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-52 sm:w-60">
                            <a href="{{ $sortUrl('name') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan nama">
                                <span>Identitas Pengunjung</span> {!! $sortIcon('name') !!}
                            </a>
                        </th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-40">
                            <a href="{{ $sortUrl('last_seen_at') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan aktivitas">
                                <span>Kehadiran &amp; Chat</span> {!! $sortIcon('last_seen_at') !!}
                            </a>
                        </th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-40">Website / Channel</th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 min-w-[180px]">Halaman Terakhir</th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-36">Perangkat &amp; OS</th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-28">
                            <a href="{{ $sortUrl('ip_address') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan IP">
                                <span>IP Address</span> {!! $sortIcon('ip_address') !!}
                            </a>
                        </th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-24 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-apple-subtleBorder">
                    @forelse($visitors as $v)
                        @php
                            $isOnline = $v->is_online;
                            $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $v->name ?: 'TM'), 0, 2)) ?: 'TM';
                            $convCount = $v->conversations_count ?? 0;
                            $latestConv = $v->conversations->first();
                            $pageUrl = $latestConv->page_url ?? null;
                            $pageTitle = $latestConv->page_title ?? null;
                            $devType = $v->device_type;
                        @endphp
                        <tr class="hover:bg-black/[0.015] transition group">
                            <!-- 1. Visitor Identity -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <div class="flex items-center gap-2.5">
                                    <div class="relative shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-apple-canvas border border-apple-border text-[10px] font-bold text-apple-textSecondary flex items-center justify-center shadow-2xs">
                                            {{ $initials }}
                                        </div>
                                        @if($isOnline)
                                            <span class="w-2.5 h-2.5 rounded-full bg-apple-green border-2 border-white absolute -bottom-0.5 -right-0.5"></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-apple-textPrimary truncate max-w-[170px]" title="{{ $v->name ?: $v->display_name }}">
                                            {{ $v->display_name }}
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="font-mono text-[9.5px] px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary border border-apple-border/60">
                                                {{ $v->customer_code_formatted }}
                                            </span>
                                            @if($v->email)
                                                <a href="mailto:{{ $v->email }}" class="text-[10px] text-apple-blue hover:underline truncate max-w-[110px]" title="{{ $v->email }}">
                                                    {{ $v->email }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. Presence & Chats -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                @if($isOnline)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono px-2 py-0.5 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
                                        Online
                                    </span>
                                @else
                                    <div class="text-[11px] text-apple-textSecondary">
                                        {{ $v->last_seen_at ? $v->last_seen_at->diffForHumans() : 'Belum aktif' }}
                                    </div>
                                    <div class="font-mono text-[9.5px] text-apple-textTertiary mt-0.5">
                                        {{ $v->last_seen_at ? $v->last_seen_at->format('d M H:i') : '-' }}
                                    </div>
                                @endif

                                <div class="mt-1">
                                    @if($convCount > 0)
                                        <a href="{{ route('admin.inbox', ['search' => $v->customer_code_formatted]) }}"
                                            class="inline-flex items-center gap-1 text-[10px] font-medium px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                            </svg>
                                            <span>{{ $convCount }} chat</span>
                                        </a>
                                    @else
                                        <span class="text-[9.5px] text-apple-textTertiary font-mono">Tanpa chat</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 3. Website Origin -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <div class="font-medium text-apple-textPrimary truncate max-w-[130px]" title="{{ $v->project->name ?? 'Default Project' }}">
                                    {{ $v->project->name ?? 'Website' }}
                                </div>
                                <div class="font-mono text-[10px] text-apple-textTertiary truncate max-w-[130px]">
                                    {{ $v->project->slug ?? '-' }}
                                </div>
                            </td>

                            <!-- 4. Last Visited Page -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                @if($pageUrl || $pageTitle)
                                    <div class="font-medium text-apple-textPrimary truncate max-w-[200px]" title="{{ $pageTitle ?: $pageUrl }}">
                                        {{ $pageTitle ?: 'Halaman Web' }}
                                    </div>
                                    @if($pageUrl)
                                        <a href="{{ $pageUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="text-[10px] font-mono text-apple-blue hover:underline truncate max-w-[200px] block mt-0.5" title="{{ $pageUrl }}">
                                            {{ $pageUrl }}
                                        </a>
                                    @endif
                                @else
                                    <span class="text-apple-textTertiary text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- 5. Device & OS -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <div class="flex items-center gap-1.5">
                                    @if($devType === 'mobile')
                                        <span class="text-[12px]" title="Mobile Smartphone">📱</span>
                                    @elseif($devType === 'tablet')
                                        <span class="text-[12px]" title="Tablet / iPad">📟</span>
                                    @else
                                        <span class="text-[12px]" title="Komputer Desktop">💻</span>
                                    @endif
                                    <span class="text-[11px] font-medium text-apple-textPrimary">{{ $v->os_name }}</span>
                                </div>
                                <div class="text-[10px] font-mono text-apple-textTertiary mt-0.5 truncate max-w-[120px]">
                                    {{ $v->browser_name }}
                                </div>
                            </td>

                            <!-- 6. IP Address -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <span class="font-mono text-[10.5px] text-apple-textSecondary truncate max-w-[110px] block" title="{{ $v->ip_address ?? '127.0.0.1' }}">
                                    {{ $v->ip_address ?? '127.0.0.1' }}
                                </span>
                            </td>

                            <!-- 7. Actions -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top text-right">
                                <button type="button" onclick="showVisitorDetail({{ $v->id }})"
                                    class="px-2.5 py-1 rounded-lg border border-apple-border hover:bg-black/5 text-apple-textPrimary transition text-[11px] font-medium shadow-2xs inline-flex items-center gap-1">
                                    <span>Jejak</span>
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center text-apple-textTertiary">
                                <div class="w-10 h-10 rounded-full bg-black/5 mx-auto flex items-center justify-center text-apple-textTertiary mb-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                </div>
                                <div class="font-medium text-apple-textSecondary text-[13px]">Tidak ada data pengunjung yang cocok dengan filter.</div>
                                <div class="text-[11.5px] mt-1">Coba ubah kata kunci pencarian atau reset filter di atas.</div>
                                <a href="{{ route('admin.visitors') }}" class="inline-block mt-3 px-3 py-1.5 rounded-lg border border-apple-border bg-white text-apple-textPrimary hover:bg-black/5 text-[11.5px] font-medium transition shadow-2xs">
                                    Reset Semua Filter
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. RESPONSIVE CARD GRID VIEW (Optimized for Mobile & Tablet iPads) -->
        <div id="visitorsCardsView" class="hidden p-3 sm:p-4 bg-apple-canvas/20">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
                @forelse($visitors as $v)
                    @php
                        $isOnline = $v->is_online;
                        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $v->name ?: 'TM'), 0, 2)) ?: 'TM';
                        $convCount = $v->conversations_count ?? 0;
                        $latestConv = $v->conversations->first();
                        $pageUrl = $latestConv->page_url ?? null;
                        $pageTitle = $latestConv->page_title ?? null;
                        $devType = $v->device_type;
                    @endphp
                    <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs hover:shadow-apple-sm transition flex flex-col justify-between gap-3">
                        <!-- Top Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="relative shrink-0">
                                    <div class="w-9 h-9 rounded-full bg-apple-canvas border border-apple-border text-[11px] font-bold text-apple-textSecondary flex items-center justify-center shadow-2xs">
                                        {{ $initials }}
                                    </div>
                                    @if($isOnline)
                                        <span class="w-2.5 h-2.5 rounded-full bg-apple-green border-2 border-white absolute -bottom-0.5 -right-0.5"></span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-apple-textPrimary text-[12.5px] leading-tight truncate">
                                        {{ $v->display_name }}
                                    </h4>
                                    <span class="font-mono text-[9px] px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary border border-apple-border/60 inline-block mt-0.5">
                                        {{ $v->customer_code_formatted }}
                                    </span>
                                </div>
                            </div>

                            @if($isOnline)
                                <span class="inline-flex items-center gap-1 text-[9.5px] font-mono px-2 py-0.5 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 font-semibold shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
                                    Live
                                </span>
                            @else
                                <span class="font-mono text-[10px] text-apple-textTertiary shrink-0">
                                    {{ $v->last_seen_at ? $v->last_seen_at->diffForHumans() : '-' }}
                                </span>
                            @endif
                        </div>

                        <!-- Card Body: Channel & Page Footprint -->
                        <div class="bg-apple-canvas/40 p-2.5 rounded-lg flex flex-col gap-1.5 text-[11.5px]">
                            <div class="flex items-center justify-between text-[10.5px]">
                                <span class="text-apple-textTertiary">Channel:</span>
                                <span class="font-medium text-apple-textPrimary truncate max-w-[160px]">{{ $v->project->name ?? 'Default' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[10.5px]">
                                <span class="text-apple-textTertiary">Perangkat:</span>
                                <span class="text-apple-textSecondary font-mono">{{ $v->os_name }} • {{ $v->browser_name }}</span>
                            </div>
                            @if($pageUrl || $pageTitle)
                                <div class="pt-1 border-t border-apple-subtleBorder text-[10px]">
                                    <span class="text-apple-textTertiary block">Halaman Terakhir:</span>
                                    <span class="text-apple-textPrimary font-medium truncate block" title="{{ $pageTitle ?: $pageUrl }}">{{ $pageTitle ?: $pageUrl }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="flex items-center justify-between pt-1 border-t border-apple-subtleBorder text-[11px]">
                            <span class="font-mono text-[10px] text-apple-textTertiary truncate max-w-[120px]">
                                IP: {{ $v->ip_address ?? '127.0.0.1' }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                @if($convCount > 0)
                                    <a href="{{ route('admin.inbox', ['search' => $v->customer_code_formatted]) }}"
                                        class="px-2 py-1 rounded-md bg-purple-50 text-purple-700 border border-purple-200 font-medium text-[10.5px] hover:bg-purple-100 transition inline-flex items-center gap-1">
                                        <span>Chat ({{ $convCount }})</span>
                                    </a>
                                @endif
                                <button type="button" onclick="showVisitorDetail({{ $v->id }})"
                                    class="px-2.5 py-1 rounded-md border border-apple-border bg-white text-apple-textPrimary font-medium text-[10.5px] hover:bg-black/5 transition shadow-2xs">
                                    Detail
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center text-apple-textTertiary bg-white rounded-xl border border-apple-border">
                        Belum ada data pengunjung terlacak yang cocok dengan filter.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 3. COMPREHENSIVE RESPONSIVE PAGINATION BAR -->
        <div class="px-4 py-3 border-t border-apple-border flex flex-col sm:flex-row items-center justify-between gap-3 text-[11.5px] text-apple-textSecondary bg-apple-canvas/40">
            <!-- Left Info -->
            <div class="text-center sm:text-left">
                @if($visitors->total() > 0)
                    Menampilkan <span class="font-semibold text-apple-textPrimary">{{ $visitors->firstItem() }}</span> &ndash;
                    <span class="font-semibold text-apple-textPrimary">{{ $visitors->lastItem() }}</span> dari
                    <span class="font-semibold text-apple-textPrimary">{{ number_format($visitors->total()) }}</span> pengunjung
                @else
                    Menampilkan <span class="font-semibold text-apple-textPrimary">0</span> pengunjung
                @endif
            </div>

            <!-- Right Pagination Navigation -->
            @if($visitors->hasPages())
                <div class="flex items-center gap-1 flex-wrap justify-center">
                    {{-- Previous Page Link --}}
                    @if ($visitors->onFirstPage())
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white font-medium text-[11px]">&larr; Prev</span>
                    @else
                        <a href="{{ $visitors->previousPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs font-medium text-[11px]">&larr; Prev</a>
                    @endif

                    {{-- Page Numbers (Compact Window on Tablet & Desktop) --}}
                    <div class="hidden sm:flex items-center gap-1">
                        @php
                            $start = max(1, $visitors->currentPage() - 2);
                            $end = min($visitors->lastPage(), $visitors->currentPage() + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $visitors->url(1) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">1</a>
                            @if($start > 2)
                                <span class="px-1 text-apple-textTertiary">...</span>
                            @endif
                        @endif

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $visitors->currentPage())
                                <span class="w-7 h-7 flex items-center justify-center rounded-md bg-apple-textPrimary text-white font-semibold text-[11px] shadow-2xs">{{ $page }}</span>
                            @else
                                <a href="{{ $visitors->url($page) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($end < $visitors->lastPage())
                            @if($end < $visitors->lastPage() - 1)
                                <span class="px-1 text-apple-textTertiary">...</span>
                            @endif
                            <a href="{{ $visitors->url($visitors->lastPage()) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">{{ $visitors->lastPage() }}</a>
                        @endif
                    </div>

                    {{-- Mobile Page Counter Badge --}}
                    <span class="sm:hidden px-2 text-[11px] font-medium text-apple-textTertiary">
                        Hal {{ $visitors->currentPage() }} / {{ $visitors->lastPage() }}
                    </span>

                    {{-- Next Page Link --}}
                    @if ($visitors->hasMorePages())
                        <a href="{{ $visitors->nextPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs font-medium text-[11px]">Next &rarr;</a>
                    @else
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white font-medium text-[11px]">Next &rarr;</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 4. VISITOR FOOTPRINT DETAIL MODAL -->
<div id="modalVisitorDetail" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/40 backdrop-blur-xs hidden transition-opacity">
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

<script>
    function toggleTrafficHub() {
        var hub = document.getElementById('trafficHubSection');
        var btnTxt = document.getElementById('txtToggleTrafficHub');
        if (!hub) return;
        var isHidden = hub.classList.toggle('hidden');
        if (btnTxt) {
            btnTxt.innerText = isHidden ? 'Tampilkan Grafik' : 'Sembunyikan Grafik';
        }
        try {
            localStorage.setItem('beantalk_traffic_hub_hidden', isHidden ? 'true' : 'false');
        } catch(e) {}
    }

    // Restore traffic hub visibility preference
    (function() {
        try {
            var isHubHidden = localStorage.getItem('beantalk_traffic_hub_hidden') === 'true';
            var hub = document.getElementById('trafficHubSection');
            var btnTxt = document.getElementById('txtToggleTrafficHub');
            if (hub && isHubHidden) {
                hub.classList.add('hidden');
                if (btnTxt) btnTxt.innerText = 'Tampilkan Grafik';
            }
        } catch(e) {}
    })();

    function toggleSortDirection() {
        var dirInput = document.getElementById('filterDirInput');
        if (dirInput) {
            dirInput.value = dirInput.value === 'asc' ? 'desc' : 'asc';
            document.getElementById('visitorsFilterForm').submit();
        }
    }

    // Responsive View Switcher (Table vs Card Feed)
    function setVisitorViewMode(mode) {
        var tableView = document.getElementById('visitorsTableView');
        var cardsView = document.getElementById('visitorsCardsView');
        var btnTable = document.getElementById('btnViewTable');
        var btnCards = document.getElementById('btnViewCards');
        var scrollHint = document.getElementById('tableScrollHint');

        if (!tableView || !cardsView) return;

        if (mode === 'cards') {
            tableView.classList.add('hidden');
            cardsView.classList.remove('hidden');
            if (scrollHint) scrollHint.classList.add('hidden');

            if (btnCards) {
                btnCards.className = 'px-2.5 py-1 rounded-md bg-white text-apple-textPrimary shadow-2xs font-semibold inline-flex items-center gap-1.5';
            }
            if (btnTable) {
                btnTable.className = 'px-2.5 py-1 rounded-md text-apple-textSecondary hover:text-apple-textPrimary inline-flex items-center gap-1.5';
            }
            try { localStorage.setItem('beantalk_visitors_view_mode', 'cards'); } catch(e) {}
        } else {
            cardsView.classList.add('hidden');
            tableView.classList.remove('hidden');
            if (scrollHint) scrollHint.classList.remove('hidden');

            if (btnTable) {
                btnTable.className = 'px-2.5 py-1 rounded-md bg-white text-apple-textPrimary shadow-2xs font-semibold inline-flex items-center gap-1.5';
            }
            if (btnCards) {
                btnCards.className = 'px-2.5 py-1 rounded-md text-apple-textSecondary hover:text-apple-textPrimary inline-flex items-center gap-1.5';
            }
            try { localStorage.setItem('beantalk_visitors_view_mode', 'table'); } catch(e) {}
        }
    }

    // Auto-detect view on load
    (function() {
        try {
            var savedMode = localStorage.getItem('beantalk_visitors_view_mode');
            if (savedMode) {
                setVisitorViewMode(savedMode);
            } else if (window.innerWidth < 768) {
                setVisitorViewMode('cards');
            } else {
                setVisitorViewMode('table');
            }
        } catch(e) {
            setVisitorViewMode('table');
        }
    })();

    // AJAX Detail Fetcher for Visitor Footprint Modal
    function showVisitorDetail(visitorId) {
        openModal('modalVisitorDetail');
        var loading = document.getElementById('modalVisitorLoading');
        var content = document.getElementById('modalVisitorContent');
        if (loading) loading.classList.remove('hidden');
        if (content) content.classList.add('hidden');

        fetch('/admin/visitors/' + visitorId, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (!res.success || !res.data) throw new Error('Data tidak ditemukan');
            var d = res.data;

            document.getElementById('modalVisitorTitle').innerText = d.display_name;
            document.getElementById('modalVisitorCode').innerText = d.customer_code + ' • UUID: ' + d.visitor_uuid;

            document.getElementById('detVisitorName').innerText = d.name;
            document.getElementById('detVisitorEmail').innerText = d.email || '-';
            document.getElementById('detVisitorProject').innerText = d.project.name || '-';
            document.getElementById('detVisitorDevice').innerText = (d.device_type === 'mobile' ? '📱 Mobile' : (d.device_type === 'tablet' ? '📟 Tablet' : '💻 Desktop')) + ' (' + d.os_name + ')';
            document.getElementById('detVisitorBrowser').innerText = d.browser_name;
            document.getElementById('detVisitorIp').innerText = d.ip_address || '-';
            document.getElementById('detVisitorLastSeen').innerText = d.last_seen + ' (' + d.last_seen_human + ')';
            document.getElementById('detVisitorUserAgent').innerText = d.user_agent || '-';

            // Presence pill
            var presenceEl = document.getElementById('detVisitorPresence');
            if (d.is_online) {
                presenceEl.innerHTML = '<span class="inline-flex items-center gap-1 text-[10px] font-mono px-2 py-0.5 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span> Online Sekarang</span>';
            } else {
                presenceEl.innerHTML = '<span class="text-[11px] text-apple-textTertiary font-mono">Offline</span>';
            }

            // Conversations List
            var convContainer = document.getElementById('detConversationsList');
            var convBadge = document.getElementById('detConvCountBadge');
            convContainer.innerHTML = '';

            if (d.conversations && d.conversations.length > 0) {
                convBadge.innerText = d.conversations.length + ' tiket';
                d.conversations.forEach(function(c) {
                    var statusColor = c.status === 'open' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-black/5 text-apple-textSecondary border-black/10';
                    var item = document.createElement('div');
                    item.className = 'p-2.5 rounded-xl border border-apple-border/70 bg-white flex items-start justify-between gap-2 text-[11px]';
                    item.innerHTML = `
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="font-mono text-[9px] px-1.5 py-0.2 rounded border font-semibold ${statusColor}">#${c.id} • ${c.status.toUpperCase()}</span>
                                <span class="text-[10px] text-apple-textTertiary font-mono">${c.last_message_at || '-'}</span>
                            </div>
                            <div class="text-apple-textPrimary font-medium truncate">${c.page_title || c.page_url || 'Chat Session'}</div>
                            <div class="text-[10.5px] text-apple-textTertiary truncate mt-0.5">${c.last_message_preview || '-'}</div>
                        </div>
                        <a href="${c.inbox_url}" class="px-2.5 py-1 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[10.5px] font-medium transition shadow-2xs shrink-0 self-center">
                            Buka di Inbox
                        </a>
                    `;
                    convContainer.appendChild(item);
                });
            } else {
                convBadge.innerText = '0 tiket';
                convContainer.innerHTML = '<div class="p-3 text-center text-apple-textTertiary bg-white rounded-lg border border-apple-border text-[11px]">Pengunjung ini belum memulai percakapan chat.</div>';
            }

            if (loading) loading.classList.add('hidden');
            if (content) content.classList.remove('hidden');
        })
        .catch(function(err) {
            if (loading) {
                loading.innerHTML = '<span class="text-apple-red">Gagal memuat data jejak pengunjung.</span>';
            }
        });
    }
</script>

<style>
    /* Sleek touch-friendly custom scrollbar */
    #visitorsTableView::-webkit-scrollbar {
        height: 6px;
    }
    #visitorsTableView::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 9999px;
    }
    #visitorsTableView::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.15);
        border-radius: 9999px;
    }
    #visitorsTableView::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.25);
    }
</style>
@endsection
