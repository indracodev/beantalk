@extends('layouts.admin')

@section('title', 'Audit Trail & Activity Logs')
@section('header_title', 'Audit Trail & Activity Logs')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 bg-apple-canvas/30 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-apple-border gap-2">
        <div>
            <h2 class="text-[17px] font-semibold text-apple-textPrimary tracking-tight">Activity Logs &amp; Audit Trail</h2>
            <p class="text-[12px] text-apple-textSecondary">Immutable chronological record of admin events, replies, and channel mutations.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 text-[10.5px] font-mono px-2.5 py-1 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 w-fit">
            <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
            Streaming
        </span>
    </div>

    <!-- Filter & Search Bar -->
    @php
        $currentSort = request('sort', 'id');
        $currentDir = request('dir', 'desc');
        $sortUrl = function($col) use ($currentSort, $currentDir) {
            $nextDir = ($currentSort === $col && $currentDir === 'desc') ? 'asc' : 'desc';
            return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $nextDir]);
        };
        $sortIcon = function($col) use ($currentSort, $currentDir) {
            if ($currentSort !== $col) return '<span class="text-apple-textTertiary/40 ml-0.5 text-[9px]">↕</span>';
            return $currentDir === 'desc' 
                ? '<span class="text-apple-blue font-bold ml-0.5 text-[9px]">▼</span>' 
                : '<span class="text-apple-blue font-bold ml-0.5 text-[9px]">▲</span>';
        };
    @endphp
    <form action="{{ route('admin.logs') }}" method="GET" class="bg-white border border-apple-border rounded-xl p-2.5 sm:p-3 shadow-apple-sm flex flex-col sm:flex-row items-stretch sm:items-center gap-2 text-[12px]">
        <!-- Search Input -->
        <div class="flex-1 relative">
            <input type="text" name="search" class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/50 text-apple-textPrimary placeholder:text-apple-textTertiary focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Cari aktivitas, nama user, event, IP..." value="{{ request('search') }}">
            <svg class="w-3.5 h-3.5 text-apple-textTertiary absolute left-2.5 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </svg>
        </div>

        <!-- Filter & Sort Selects -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <select name="role" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/50 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                <option value="visitor" {{ request('role') === 'visitor' ? 'selected' : '' }}>Visitor</option>
                <option value="system" {{ request('role') === 'system' ? 'selected' : '' }}>System</option>
            </select>

            <select name="sort" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/50 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                <option value="id" {{ (!request('sort') || request('sort') === 'id') ? 'selected' : '' }}>Sort: Terbaru</option>
                <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Sort: Waktu</option>
                <option value="user_role" {{ request('sort') === 'user_role' ? 'selected' : '' }}>Sort: Actor</option>
                <option value="action" {{ request('sort') === 'action' ? 'selected' : '' }}>Sort: Event</option>
                <option value="ip_address" {{ request('sort') === 'ip_address' ? 'selected' : '' }}>Sort: IP</option>
            </select>

            @if(request('dir'))
                <input type="hidden" name="dir" value="{{ request('dir') }}">
            @endif

            <button type="submit" class="px-3 py-1.5 rounded-lg bg-apple-textPrimary text-white hover:bg-black transition font-medium text-[11.5px] shadow-2xs shrink-0">Filter</button>
            @if(request()->filled('role') || request()->filled('search') || request()->filled('sort') || request()->filled('action'))
                <a href="{{ route('admin.logs') }}" class="px-2 py-1.5 text-apple-textTertiary hover:text-apple-textPrimary text-[11px] shrink-0">Reset</a>
            @endif
        </div>
    </form>

    <!-- Logs Container (Desktop Table + Mobile Cards) -->
    <div class="bg-white border border-apple-border rounded-xl shadow-apple-sm overflow-hidden">
        <!-- 1. DESKTOP TABLE VIEW (md and up) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-[12px]">
                <thead class="bg-apple-canvas/80 text-apple-textSecondary text-[10.5px] font-medium uppercase border-b border-apple-border">
                    <tr>
                        <th class="px-4 py-2.5 w-36">
                            <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>Timestamp</span> {!! $sortIcon('created_at') !!}
                            </a>
                        </th>
                        <th class="px-4 py-2.5 w-44">
                            <a href="{{ $sortUrl('user_role') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>Actor</span> {!! $sortIcon('user_role') !!}
                            </a>
                        </th>
                        <th class="px-4 py-2.5 w-40">
                            <a href="{{ $sortUrl('action') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>Event Code</span> {!! $sortIcon('action') !!}
                            </a>
                        </th>
                        <th class="px-4 py-2.5">Description</th>
                        <th class="px-4 py-2.5 w-32">
                            <a href="{{ $sortUrl('ip_address') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>IP Address</span> {!! $sortIcon('ip_address') !!}
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-apple-subtleBorder">
                    @forelse($logs as $log)
                        @php
                            $errorActions = ['rate_limit_exceeded', 'server_error', 'database_error', 'bad_gateway', 'service_unavailable', 'UNAUTHORIZED_DOMAIN'];
                            $isError = in_array($log->action, $errorActions);
                        @endphp
                        <tr class="hover:bg-black/[0.01] transition">
                            <td class="px-4 py-2.5 font-mono text-[11px] text-apple-textTertiary">
                                {{ $log->created_at ? $log->created_at->format('d M, H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-apple-textPrimary truncate max-w-[120px]">{{ $log->user_name ?? 'System' }}</span>
                                    <span class="text-[9.5px] font-mono uppercase px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary">
                                        {{ $log->user_role ?? 'sys' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($isError)
                                    <span class="font-mono text-[10.5px] text-apple-red bg-red-50 px-1.5 py-0.5 rounded border border-red-200">
                                        {{ $log->action }}
                                    </span>
                                @else
                                    <span class="font-mono text-[10.5px] text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">
                                        {{ $log->action }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-apple-textSecondary leading-snug">
                                {{ $log->description ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5 font-mono text-[11px] text-apple-textTertiary">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-apple-textTertiary">
                                Belum ada riwayat aktivitas yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. MOBILE CARD LIST VIEW (< md) -->
        <div class="md:hidden divide-y divide-apple-subtleBorder">
            @forelse($logs as $log)
                @php
                    $errorActions = ['rate_limit_exceeded', 'server_error', 'database_error', 'bad_gateway', 'service_unavailable', 'UNAUTHORIZED_DOMAIN'];
                    $isError = in_array($log->action, $errorActions);
                @endphp
                <div class="p-3.5 flex flex-col gap-2 hover:bg-black/[0.01] transition">
                    <!-- Top: Event Code & Timestamp -->
                    <div class="flex items-center justify-between gap-2">
                        @if($isError)
                            <span class="font-mono text-[10px] text-apple-red bg-red-50 px-2 py-0.5 rounded border border-red-200 font-semibold">
                                {{ $log->action }}
                            </span>
                        @else
                            <span class="font-mono text-[10px] text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 font-semibold">
                                {{ $log->action }}
                            </span>
                        @endif

                        <span class="font-mono text-[10.5px] text-apple-textTertiary">
                            {{ $log->created_at ? $log->created_at->format('d M, H:i:s') : '-' }}
                        </span>
                    </div>

                    <!-- Middle: Description -->
                    <p class="text-[12px] text-apple-textPrimary leading-snug">
                        {{ $log->description ?? '-' }}
                    </p>

                    <!-- Bottom: Actor & IP -->
                    <div class="flex items-center justify-between pt-1 border-t border-apple-subtleBorder/60 text-[10.5px]">
                        <div class="flex items-center gap-1.5 text-apple-textSecondary">
                            <span class="font-medium text-apple-textPrimary truncate max-w-[130px]">{{ $log->user_name ?? 'System' }}</span>
                            <span class="text-[9px] font-mono uppercase px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary">
                                {{ $log->user_role ?? 'sys' }}
                            </span>
                        </div>
                        <span class="font-mono text-apple-textTertiary">
                            IP: {{ $log->ip_address ?? '127.0.0.1' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-apple-textTertiary">
                    Belum ada riwayat aktivitas yang tercatat.
                </div>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="px-3.5 py-2.5 border-t border-apple-border flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-apple-textSecondary bg-apple-canvas/40">
                <div>
                    Menampilkan <span class="font-medium text-apple-textPrimary">{{ $logs->firstItem() }}</span> - <span class="font-medium text-apple-textPrimary">{{ $logs->lastItem() }}</span> dari <span class="font-medium text-apple-textPrimary">{{ $logs->total() }}</span> entri
                </div>
                <div class="flex items-center gap-1.5">
                    @if($logs->onFirstPage())
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white">&larr; Prev</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs">&larr; Prev</a>
                    @endif

                    <span class="px-2 text-[10.5px] font-medium text-apple-textTertiary">Hal {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs">Next &rarr;</a>
                    @else
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white">Next &rarr;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

