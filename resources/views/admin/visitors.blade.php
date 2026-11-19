@extends('layouts.admin')

@section('title', 'Pengunjung Terlacak (Visitor Tracking)')
@section('header_title', 'Pengunjung Terlacak')

@section('content')
<div class="flex-1 h-full max-h-full min-h-0 min-w-0 overflow-y-auto overscroll-y-contain p-3 sm:p-4 md:p-5 pb-28 md:pb-12 flex flex-col gap-3 sm:gap-3.5 w-full" id="visitorsMainScroll">
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

    @include('admin.partials.visitors.kpi')

    <!-- Traffic Analytics & Chart Hub (Compact 12-Column Grid Layout) -->
    <div id="trafficHubSection" class="flex flex-col gap-2.5 transition-all duration-200 shrink-0">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-2.5 sm:gap-3 items-stretch">
            
            @include('admin.partials.visitors.traffic-trend')

            @include('admin.partials.visitors.traffic-breakdown')
        </div>
    </div>

    @include('admin.partials.visitors.filters')

    <!-- Visitors Container (Adaptive: Table on Desktop/Tabs with Native Momentum Scroll + Smart Card Feed for Mobile/Tablet) -->
    <div class="bg-white border border-apple-border rounded-xl shadow-apple-sm overflow-hidden flex flex-col">
        <x-admin.list-toolbar :paginator="$visitors" title="Daftar Pengunjung Terlacak" noun="pengunjung" mode-fn="setVisitorViewMode" scroll-hint="Geser ke kanan untuk melihat Halaman, Perangkat, IP &amp; Aksi" />

        @include('admin.partials.visitors.table')

        @include('admin.partials.visitors.cards')

        <x-admin.pagination :paginator="$visitors" noun="pengunjung" />
    </div>
</div>

@include('admin.partials.visitors.detail-modal')

<script src="@asset('js/pages/visitors.js')"></script>

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
