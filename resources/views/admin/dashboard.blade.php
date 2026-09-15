@extends('layouts.admin')

@section('title', 'Executive Dashboard')
@section('header_title', 'Executive Overview')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-24 md:pb-8 flex flex-col gap-5 sm:gap-6 bg-[#F5F5F7]/60 w-full min-h-0">

    <!-- ====================================================================== -->
    <!-- 1. HEADER & FILTER TOOLBAR                                             -->
    <!-- ====================================================================== -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-4 border-b border-apple-border gap-3 sm:gap-4 shrink-0">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-[19px] sm:text-[21px] font-semibold text-apple-textPrimary tracking-tight">Executive Dashboard</h1>
                
                <!-- Live Telemetry Streaming Status Pill -->
                <div class="inline-flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 px-2.5 py-0.5 rounded-full text-[11px] font-medium shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ count($projects) }} {{ count($projects) === 1 ? 'Site' : 'Sites' }} Streaming</span>
                </div>
            </div>
            <p class="text-[12.5px] text-apple-textSecondary mt-0.5">Real-time support engagement, conversation velocity, and multi-tenant telemetry.</p>
        </div>

        <!-- Filter & Action Controls -->
        <div class="flex items-center gap-2 self-start lg:self-auto flex-wrap">
            <!-- Period Quick Presets Selector -->
            <div class="flex items-center bg-black/5 p-0.5 rounded-xl text-[12px] font-medium text-apple-textSecondary shadow-inner">
                @php
                    $curPeriod = $period ?? request('period', '7d');
                @endphp
                <a href="?period=today" class="px-2.5 py-1 rounded-lg transition {{ $curPeriod === 'today' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">Today</a>
                <a href="?period=7d" class="px-2.5 py-1 rounded-lg transition {{ $curPeriod === '7d' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">7 Days</a>
                <a href="?period=30d" class="px-2.5 py-1 rounded-lg transition {{ $curPeriod === '30d' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">30 Days</a>
                <a href="?period=quarter" class="px-2.5 py-1 rounded-lg transition {{ $curPeriod === 'quarter' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">Quarter</a>
            </div>

            <!-- Custom Calendar Date Range Picker -->
            <div class="relative inline-block text-left" id="datePickerDropdownContainer">
                <button type="button" onclick="toggleDatePickerMenu()" class="inline-flex items-center gap-1.5 {{ !empty($isCustomDate) ? 'bg-apple-blue/10 border-apple-blue/40 text-apple-blue font-semibold' : 'bg-white border-apple-border text-apple-textPrimary hover:bg-apple-canvas font-medium' }} border px-3 py-1.5 rounded-xl text-[12px] transition shadow-apple-sm active:scale-95 cursor-pointer">
                    <svg class="w-3.5 h-3.5 {{ !empty($isCustomDate) ? 'text-apple-blue' : 'text-apple-textSecondary' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>
                        @if(!empty($isCustomDate))
                            {{ $startDateLabel }} &ndash; {{ $endDateLabel }}
                        @else
                            Rentang Tanggal
                        @endif
                    </span>
                    <svg class="w-3 h-3 text-apple-textTertiary ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>

                <!-- Date Range Calendar Dropdown Popover -->
                <div id="datePickerMenu" class="hidden absolute right-0 mt-1.5 w-72 bg-white/95 backdrop-blur-md border border-apple-border rounded-2xl shadow-apple-popover p-3.5 z-30 text-[12px]">
                    <div class="font-semibold text-[12.5px] text-apple-textPrimary mb-2.5 pb-1.5 border-b border-apple-border/60 flex items-center justify-between">
                        <span>Pilih Rentang Tanggal</span>
                        @if(!empty($isCustomDate))
                            <a href="{{ route('admin.dashboard') }}" class="text-[10.5px] text-apple-red hover:underline font-normal">Reset ke Default</a>
                        @endif
                    </div>
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-col gap-2.5 m-0">
                        <div>
                            <label class="block text-[10.5px] font-medium text-apple-textSecondary mb-1">Tanggal Mulai (Start):</label>
                            <input type="date" name="start_date" value="{{ $startDateFormatted }}" required class="w-full bg-apple-canvas/60 border border-apple-border rounded-lg px-2.5 py-1.5 text-[12px] text-apple-textPrimary focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20">
                        </div>
                        <div>
                            <label class="block text-[10.5px] font-medium text-apple-textSecondary mb-1">Tanggal Selesai (End):</label>
                            <input type="date" name="end_date" value="{{ $endDateFormatted }}" required class="w-full bg-apple-canvas/60 border border-apple-border rounded-lg px-2.5 py-1.5 text-[12px] text-apple-textPrimary focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20">
                        </div>
                        <button type="submit" class="w-full mt-1 bg-apple-blue hover:bg-apple-blueHover text-white font-medium py-1.5 rounded-lg transition shadow-2xs text-[12px] cursor-pointer">
                            Tampilkan Grafik Rentang Tanggal
                        </button>
                    </form>
                </div>
            </div>

            <!-- Export Action Button -->
            <div class="relative inline-block text-left" id="exportDropdownContainer">
                <button type="button" onclick="toggleExportMenu()" class="inline-flex items-center gap-1.5 bg-white border border-apple-border hover:bg-apple-canvas text-apple-textPrimary px-3 py-1.5 rounded-xl text-[12px] font-medium transition shadow-apple-sm active:scale-95 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Export</span>
                    <svg class="w-3 h-3 text-apple-textTertiary ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-1.5 w-44 bg-white/95 backdrop-blur-md border border-apple-border rounded-xl shadow-apple-popover p-1 z-30 text-[12px]">
                    <a href="javascript:void(0)" onclick="handleExportReport('csv')" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-black/5 text-apple-textPrimary transition">
                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <span>Export CSV (.csv)</span>
                    </a>
                    <a href="javascript:void(0)" onclick="handleExportReport('pdf')" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-black/5 text-apple-textPrimary transition">
                        <svg class="w-3.5 h-3.5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M6 14h12v8H6z"></path></svg>
                        <span>Print Report (.pdf)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================================================================== -->
    <!-- 2. KEY METRIC SUMMARY (4 KPI CARDS)                                     -->
    <!-- ====================================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        
        <!-- KPI 1: Total Conversations -->
        <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-apple-textSecondary">Total Conversations</span>
                <div class="w-7 h-7 rounded-xl bg-apple-blue/10 text-apple-blue flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <rect width="20" height="16" x="2" y="4" rx="2" />
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-[26px] sm:text-[28px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['totalConversations']['value'] }}</span>
                    <span class="inline-flex items-center text-[10.5px] font-semibold text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.2 rounded-md">
                        {{ $summary['totalConversations']['delta'] }}
                    </span>
                </div>
                <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['totalConversations']['subtext'] }}</p>
            </div>
        </div>

        <!-- KPI 2: Median First Response (FRT) -->
        <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-apple-textSecondary">Median First Response (FRT)</span>
                <div class="w-7 h-7 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-[26px] sm:text-[28px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['medianFrt']['value'] }}</span>
                    <span class="inline-flex items-center text-[10.5px] font-semibold text-indigo-700 bg-indigo-500/10 border border-indigo-500/20 px-1.5 py-0.2 rounded-md">
                        {{ $summary['medianFrt']['delta'] }}
                    </span>
                </div>
                <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['medianFrt']['subtext'] }}</p>
            </div>
        </div>

        <!-- KPI 3: Satisfaction Score (CSAT) -->
        <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-apple-textSecondary">Satisfaction Score (CSAT)</span>
                <div class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-[26px] sm:text-[28px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['csat']['value'] }}</span>
                    <span class="inline-flex items-center text-[10.5px] font-semibold text-amber-700 bg-amber-500/10 border border-amber-500/20 px-1.5 py-0.2 rounded-md">
                        ★ {{ $summary['csat']['delta'] }}
                    </span>
                </div>
                <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['csat']['subtext'] }}</p>
            </div>
        </div>

        <!-- KPI 4: Assisted Cart Revenue -->
        <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-apple-textSecondary">Assisted Cart Revenue</span>
                <div class="w-7 h-7 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-[20px] sm:text-[22px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['assistedRevenue']['value'] }}</span>
                    <span class="inline-flex items-center text-[10.5px] font-semibold text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.2 rounded-md">
                        {{ $summary['assistedRevenue']['delta'] }}
                    </span>
                </div>
                <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['assistedRevenue']['subtext'] }}</p>
            </div>
        </div>

    </div>

    <!-- ====================================================================== -->
    <!-- 3. DATA VISUALISASI (2 KOLOM: 2:1 RASIO)                              -->
    <!-- ====================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Kiri (2/3): Grafik Volume & Resolution Velocity (Time-Series) -->
        <div class="lg:col-span-2 bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-5 shadow-apple-card flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-apple-border/60 gap-2">
                <div>
                    <h3 class="text-[14px] font-semibold text-apple-textPrimary tracking-tight">{{ $chartTitle ?? 'Volume & Resolution Velocity' }}</h3>
                    <p class="text-[11.5px] text-apple-textSecondary">{{ $chartSubtitle ?? 'Tren volume pesan masuk (inbound) vs percakapan terselesaikan (resolved).' }}</p>
                </div>
                <!-- Legend Indicators -->
                <div class="flex items-center gap-3 text-[11.5px] self-start sm:self-auto">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0071E3] shadow-xs"></span>
                        <span class="text-apple-textPrimary font-medium">Inbound Chats</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#34C759] shadow-xs"></span>
                        <span class="text-apple-textPrimary font-medium">Resolved Sessions</span>
                    </div>
                </div>
            </div>

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

            <!-- Breakdown Grid & Overview (Adaptive per Periode) -->
            @if($curPeriod === '7d')
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

            <!-- Mini Footnote Velocity Rate -->
            <div class="mt-2.5 pt-2 border-t border-apple-border/50 flex items-center justify-between text-[11px] text-apple-textSecondary flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Resolution Rate: <strong>{{ $summary['csat']['value'] }}</strong></span>
                </div>
                <span class="text-apple-textTertiary font-mono">Velocity Peak: {{ collect($chartData)->pluck('inbound')->max() ?? 0 }} chats/hari</span>
            </div>
        </div>

        <!-- Kanan (1/3): Breakdown Kategori / Intent Pengunjung -->
        <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-5 shadow-apple-card flex flex-col justify-between">
            <div>
                <div class="pb-3 border-b border-apple-border/60 flex items-center justify-between">
                    <div>
                        <h3 class="text-[14px] font-semibold text-apple-textPrimary tracking-tight">Intent Breakdown</h3>
                        <p class="text-[11.5px] text-apple-textSecondary">Distribusi niat pengunjung</p>
                    </div>
                    <span class="text-[10.5px] font-mono text-apple-textTertiary bg-black/5 px-2 py-0.5 rounded-md">N = 1,482</span>
                </div>

                <!-- Intent Categories Progress Distribution -->
                <div class="flex flex-col gap-3.5 mt-3.5">
                    @foreach($intentBreakdown as $intent)
                        <div>
                            <div class="flex items-center justify-between text-[12px] mb-1">
                                <span class="font-medium text-apple-textPrimary truncate max-w-[200px]" title="{{ $intent['label'] }}">
                                    {{ $intent['label'] }}
                                </span>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-apple-textTertiary font-mono text-[11px]">{{ $intent['count'] }} chats</span>
                                    <span class="font-bold text-apple-textPrimary font-mono text-[12px]">{{ $intent['percentage'] }}%</span>
                                </div>
                            </div>
                            <!-- Bar -->
                            <div class="w-full bg-black/5 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $intent['percentage'] }}%; background-color: {{ $intent['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Insight Note -->
            <div class="mt-4 p-2.5 rounded-xl bg-apple-blue/5 border border-apple-blue/15 flex items-start gap-2 text-[11.5px]">
                <span class="text-apple-blue font-bold text-[13px] leading-tight">💡</span>
                <div class="text-apple-textSecondary leading-snug">
                    <strong class="text-apple-textPrimary">Top trigger pengunjung:</strong> Capsule Compatibility &amp; Mesin Kopi Nespresso®.
                </div>
            </div>
        </div>

    </div>

    <!-- ====================================================================== -->
    <!-- 4. TABEL SALURAN MULTI-STOREFRONT (CHANNEL HEALTH MATRIX)             -->
    <!-- ====================================================================== -->
    <div class="bg-white border border-apple-border/80 rounded-2xl shadow-apple-card overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-apple-border/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-[14.5px] font-semibold text-apple-textPrimary tracking-tight">Channel Health &amp; Multi-Storefront Telemetry</h3>
                <p class="text-[12px] text-apple-textSecondary">Matriks performa seluruh domain website terhubung dalam satu tenant.</p>
            </div>
            <a href="{{ route('admin.integrations') }}" class="inline-flex items-center gap-1 text-[12px] font-medium text-apple-blue hover:text-apple-blueHover self-start sm:self-auto">
                <span>Manage Channels</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>

        <!-- Desktop Table View (md:block) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-[12.5px]">
                <thead>
                    <tr class="bg-apple-canvas/60 text-[10.5px] font-semibold text-apple-textTertiary uppercase tracking-wider border-b border-apple-border">
                        <th class="py-2.5 px-4">Website / Domain</th>
                        <th class="py-2.5 px-4">Pengunjung Aktif</th>
                        <th class="py-2.5 px-4">Total Chat ({{ $curPeriod === 'today' ? 'Hari ini' : ($curPeriod === '30d' ? '30 Hari' : ($curPeriod === 'quarter' ? 'Quarter' : '7 Hari')) }})</th>
                        <th class="py-2.5 px-4">Median FRT</th>
                        <th class="py-2.5 px-4">Aktivitas Chat</th>
                        <th class="py-2.5 px-4 text-right">Status Kanal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-apple-border/50">
                    @forelse($channels as $chan)
                        <tr class="hover:bg-apple-canvas/40 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg text-white flex items-center justify-center font-bold text-[10px] shrink-0 shadow-2xs" style="background-color: {{ $chan['color'] ?? '#2C2C2E' }};">
                                        {{ strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $chan['name']), 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-apple-textPrimary text-[13px]">{{ $chan['name'] }}</div>
                                        <div class="text-[11px] text-apple-textTertiary font-mono">{{ $chan['domain'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full text-[11px] font-medium font-mono">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 {{ $chan['active_online'] > 0 ? 'animate-pulse' : '' }}"></span>
                                    {{ $chan['active_online'] }} online
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-medium text-apple-textPrimary">{{ $chan['chats_7d'] }}</span>
                                <span class="text-apple-textTertiary text-[11px]">chats</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono text-apple-textPrimary font-semibold">{{ $chan['median_frt'] }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div>
                                    <span class="font-bold text-apple-textPrimary font-mono text-[12px]">{{ $chan['conversion'] }} of traffic</span>
                                    <div class="text-[11px] text-apple-textSecondary">{{ $chan['revenue'] }} estimasi</div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md text-[11px] font-semibold">
                                    ✓ {{ $chan['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-apple-textTertiary text-[12px]">
                                Belum ada website/channel yang terhubung. <a href="{{ route('admin.integrations') }}" class="text-apple-blue font-medium hover:underline">Tambah Website Baru</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List View (md:hidden) -->
        <div class="md:hidden divide-y divide-apple-border/60">
            @forelse($channels as $chan)
                <div class="p-3.5 flex flex-col gap-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md text-white flex items-center justify-center font-bold text-[9px]" style="background-color: {{ $chan['color'] ?? '#2C2C2E' }};">
                                {{ strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $chan['name']), 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-apple-textPrimary text-[13px] leading-tight">{{ $chan['name'] }}</h4>
                                <span class="text-[10.5px] text-apple-textTertiary font-mono">{{ $chan['domain'] }}</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full text-[10.5px] font-medium font-mono">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 {{ $chan['active_online'] > 0 ? 'animate-pulse' : '' }}"></span>
                            {{ $chan['active_online'] }}
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 bg-apple-canvas/60 p-2 rounded-xl text-[11px]">
                        <div>
                            <span class="text-apple-textTertiary block text-[9.5px]">Chats</span>
                            <strong class="font-mono text-apple-textPrimary">{{ $chan['chats_7d'] }}</strong>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[9.5px]">Median FRT</span>
                            <strong class="font-mono text-apple-textPrimary">{{ $chan['median_frt'] }}</strong>
                        </div>
                        <div>
                            <span class="text-apple-textTertiary block text-[9.5px]">Share</span>
                            <strong class="font-mono text-apple-textPrimary">{{ $chan['conversion'] }}</strong>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[11.5px] pt-1">
                        <span class="text-apple-textSecondary">Estimasi:</span>
                        <strong class="font-mono text-emerald-700 font-semibold">{{ $chan['revenue'] }}</strong>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-apple-textTertiary text-[12px]">
                    Belum ada website/channel terhubung.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ====================================================================== -->
    <!-- 5. DETAIL OPERASIONAL (2 KOLOM SEIMBANG)                              -->
    <!-- ====================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Kolom 1: Leaderboard Staf CS & SLA (Support Specialists) -->
        <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-5 shadow-apple-card flex flex-col justify-between">
            <div>
                <div class="pb-3 border-b border-apple-border/60 flex items-center justify-between">
                    <div>
                        <h3 class="text-[14px] font-semibold text-apple-textPrimary tracking-tight">Support Specialists SLA &amp; CSAT</h3>
                        <p class="text-[11.5px] text-apple-textSecondary">Distribusi tiket dan kepatuhan waktu respon individu.</p>
                    </div>
                    <a href="{{ route('admin.team') }}" class="text-[11.5px] font-medium text-apple-blue hover:text-apple-blueHover">Team &rarr;</a>
                </div>

                <!-- Specialist Cards -->
                <div class="flex flex-col gap-2.5 mt-3.5">
                    @foreach($specialists as $spec)
                        <div class="p-2.5 sm:p-3 rounded-xl bg-apple-canvas/40 border border-apple-border/60 hover:bg-white hover:shadow-2xs transition flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                <div class="w-8 h-8 rounded-full {{ $spec['avatar_color'] }} font-bold text-[11px] flex items-center justify-center shrink-0 border border-black/5 shadow-2xs">
                                    {{ $spec['initials'] }}
                                </div>
                                <div class="truncate">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-semibold text-apple-textPrimary text-[12.5px] truncate">{{ $spec['name'] }}</span>
                                        <span class="text-[10px] text-apple-textSecondary px-1.5 py-0.2 rounded bg-black/5">{{ $spec['role'] }}</span>
                                    </div>
                                    <div class="text-[10.5px] text-apple-textTertiary truncate mt-0.5">{{ $spec['scope'] }}</div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="flex items-center gap-2 justify-end">
                                    <span class="text-[11.5px] font-bold text-amber-700 bg-amber-500/10 px-1.5 py-0.2 rounded font-mono">{{ $spec['csat'] }}</span>
                                    <span class="text-[11.5px] font-semibold text-emerald-700 font-mono">{{ $spec['sla'] }} SLA</span>
                                </div>
                                <span class="text-[10.5px] text-apple-textTertiary font-mono block mt-0.5">{{ $spec['resolved_count'] }} tiket selesai</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-apple-border/40 text-[11px] text-apple-textTertiary text-right font-mono">
                Median SLA Compliance: <strong>99.3%</strong> across all channels
            </div>
        </div>

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

    </div>

</div>

<!-- Simple Vanilla Script for Export & Tooltips -->
<script>
    function toggleDatePickerMenu() {
        const menu = document.getElementById('datePickerMenu');
        if (menu) menu.classList.toggle('hidden');
    }

    function toggleExportMenu() {
        const menu = document.getElementById('exportMenu');
        if (menu) menu.classList.toggle('hidden');
    }

    document.addEventListener('click', (e) => {
        const exportContainer = document.getElementById('exportDropdownContainer');
        const exportMenu = document.getElementById('exportMenu');
        if (exportContainer && exportMenu && !exportContainer.contains(e.target)) {
            exportMenu.classList.add('hidden');
        }

        const dateContainer = document.getElementById('datePickerDropdownContainer');
        const dateMenu = document.getElementById('datePickerMenu');
        if (dateContainer && dateMenu && !dateContainer.contains(e.target)) {
            dateMenu.classList.add('hidden');
        }
    });

    function handleExportReport(type) {
        const menu = document.getElementById('exportMenu');
        if (menu) menu.classList.add('hidden');
        if (type === 'pdf') {
            window.print();
        } else {
            @if(!empty($isCustomDate))
                window.location.href = "{{ route('admin.dashboard.export') }}?start_date={{ $startDateFormatted }}&end_date={{ $endDateFormatted }}";
            @else
                window.location.href = "{{ route('admin.dashboard.export') }}?period={{ $curPeriod }}";
            @endif
        }
    }
</script>
@endsection
