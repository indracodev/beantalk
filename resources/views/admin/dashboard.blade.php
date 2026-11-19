@extends('layouts.admin')

@section('title', 'Executive Dashboard')
@section('header_title', 'Executive Overview')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-24 md:pb-8 flex flex-col gap-5 sm:gap-6 bg-[#F5F5F7]/60 w-full min-h-0">

    <!-- ====================================================================== -->
    <!-- 1. HEADER & FILTER TOOLBAR                                             -->
    @include('admin.partials.dashboard.header')

    <!-- ====================================================================== -->
    <!-- 2. KEY METRIC SUMMARY (4 KPI CARDS)                                     -->
    @include('admin.partials.dashboard.kpi')

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

            @include('admin.partials.dashboard.volume-chart')

            @include('admin.partials.dashboard.volume-breakdown')

            <!-- Mini Footnote Velocity Rate -->
            <div class="mt-2.5 pt-2 border-t border-apple-border/50 flex items-center justify-between text-[11px] text-apple-textSecondary flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Resolution Rate: <strong>{{ $summary['csat']['value'] }}</strong></span>
                </div>
                <span class="text-apple-textTertiary font-mono">Velocity Peak: {{ collect($chartData)->pluck('inbound')->max() ?? 0 }} chats/hari</span>
            </div>
        </div>

        @include('admin.partials.dashboard.intent')

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

        @include('admin.partials.dashboard.channels-table')

        @include('admin.partials.dashboard.channels-cards')
    </div>

    <!-- ====================================================================== -->
    <!-- 5. DETAIL OPERASIONAL (2 KOLOM SEIMBANG)                              -->
    <!-- ====================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        @include('admin.partials.dashboard.specialists')

        @include('admin.partials.dashboard.top-pages')

    </div>

</div>

<script>
    window.BT_DASHBOARD = {{ \Illuminate\Support\Js::from([
        'exportUrl' => route('admin.dashboard.export', $isCustomDate ? ['start_date' => $startDateFormatted, 'end_date' => $endDateFormatted] : ['period' => $period]),
    ]) }};
</script>
<script src="@asset('js/pages/dashboard.js')"></script>
@endsection
