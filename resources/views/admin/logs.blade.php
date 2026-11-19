@extends('layouts.admin')

@section('title', 'Audit Trail & Activity Logs')
@section('header_title', 'Audit Trail & Activity Logs')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-apple-border gap-2">
        <div>
            <h2 class="text-[17px] font-semibold text-apple-textPrimary tracking-tight">Activity Logs &amp; Audit Trail</h2>
            <p class="text-[12px] text-apple-textSecondary">Catatan kronologis immutable seluruh aksi admin, staf CS, konfigurasi bot, dan mutasi channel.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 text-[10.5px] font-mono px-2.5 py-1 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 w-fit">
            <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
            Real-time Audit Trail
        </span>
    </div>

    @include('admin.partials.logs.stats')

    @include('admin.partials.logs.filters')

    <!-- Logs Container (Adaptive: Table on Desktop/Tabs with Native Horizontal Momentum Scroll + Smart Card Feed for Mobile/Tablet) -->
    <div class="bg-white border border-apple-border rounded-xl shadow-apple-sm overflow-hidden flex flex-col">
        <x-admin.list-toolbar :paginator="$logs" title="Riwayat Log Aktivitas" noun="entri" mode-fn="setLogViewMode" scroll-hint="Geser ke kanan untuk melihat detail Aktor, Event &amp; IP lengkap" />

        @include('admin.partials.logs.table')

        @include('admin.partials.logs.cards')

        <x-admin.pagination :paginator="$logs" noun="entri" />
    </div>
</div>

<script src="@asset('js/pages/logs.js')"></script>

<style>
    /* Sleek touch-friendly custom scrollbar for responsive table on tablet/mobile */
    #logsTableView::-webkit-scrollbar {
        height: 6px;
    }
    #logsTableView::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 9999px;
    }
    #logsTableView::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.15);
        border-radius: 9999px;
    }
    #logsTableView::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.25);
    }
</style>
@endsection
