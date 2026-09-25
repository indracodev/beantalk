@extends('layouts.admin')

@section('title', 'Audit Trail & Activity Logs')
@section('header_title', 'Audit Trail & Activity Logs')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 bg-apple-canvas/30 w-full">
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

    <!-- Quick Stats Ribbon (KPI) - Responsive Grid across Mobile, Tablet & Desktop -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5 sm:gap-3">
        <!-- Total Logs -->
        <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Total Entri</div>
                <div class="text-[17px] sm:text-[18px] font-bold text-apple-textPrimary tracking-tight mt-0.5">{{ number_format($logStats->total_logs ?? 0) }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-black/5 flex items-center justify-center text-apple-textSecondary shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
        </div>

        <!-- Hari Ini -->
        <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Hari Ini</div>
                <div class="text-[17px] sm:text-[18px] font-bold text-apple-blue tracking-tight mt-0.5">{{ number_format($logStats->today_logs ?? 0) }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-apple-blue/10 flex items-center justify-center text-apple-blue shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>

        <!-- Superadmin -->
        <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Superadmin</div>
                <div class="text-[17px] sm:text-[18px] font-bold text-purple-700 tracking-tight mt-0.5">{{ number_format($logStats->superadmin_logs ?? 0) }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
        </div>

        <!-- CS Agents -->
        <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Staf CS</div>
                <div class="text-[17px] sm:text-[18px] font-bold text-amber-700 tracking-tight mt-0.5">{{ number_format($logStats->agent_logs ?? 0) }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>

        <!-- Warning / Security -->
        <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between col-span-2 sm:col-span-1 md:col-span-1">
            <div>
                <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Warning &amp; Error</div>
                <div class="text-[17px] sm:text-[18px] font-bold {{ ($logStats->warning_logs ?? 0) > 0 ? 'text-apple-red' : 'text-apple-green' }} tracking-tight mt-0.5">
                    {{ number_format($logStats->warning_logs ?? 0) }}
                </div>
            </div>
            <div class="w-8 h-8 rounded-lg {{ ($logStats->warning_logs ?? 0) > 0 ? 'bg-red-50 text-apple-red' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter, Search & Sort Control Panel -->
    @php
        $currentSort = request('sort', 'created_at');
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

        $hasFilters = request()->filled('search') || request()->filled('role') || request()->filled('action') || request()->filled('date_range') || (request('sort') && request('sort') !== 'created_at') || (request('dir') && request('dir') !== 'desc') || (request('per_page') && request('per_page') != 25);
    @endphp

    <div class="bg-white border border-apple-border/80 rounded-xl p-3 sm:p-4 shadow-apple-sm flex flex-col gap-3">
        <form action="{{ route('admin.logs') }}" method="GET" id="logsFilterForm" class="flex flex-col gap-2.5 m-0 text-[12px]">
            <!-- Row 1: Search bar with integrated actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="flex-1 relative">
                    <input type="text" name="search" id="logsSearchInput"
                        class="w-full pl-8 pr-8 py-2 rounded-lg border border-apple-border bg-apple-canvas/40 text-apple-textPrimary placeholder:text-apple-textTertiary focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 text-[12px] sm:text-[12.5px] transition shadow-2xs"
                        placeholder="Cari deskripsi, nama user, event action, IP address, atau model..."
                        value="{{ request('search') }}">
                    <svg class="w-3.5 h-3.5 text-apple-textTertiary absolute left-2.5 top-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    @if(request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-2.5 top-2.5 text-apple-textTertiary hover:text-apple-textPrimary text-[14px] font-bold">&times;</a>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 rounded-lg bg-apple-textPrimary text-white hover:bg-black active:scale-[0.98] transition font-medium text-[12px] shadow-2xs inline-flex items-center justify-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Cari</span>
                    </button>

                    @if($hasFilters)
                        <a href="{{ route('admin.logs') }}" class="px-3 py-2 rounded-lg border border-apple-border hover:bg-black/5 text-apple-textSecondary hover:text-apple-textPrimary transition font-medium text-[12px] shrink-0 text-center">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Row 2: Filter Selects (Responsive across Mobile, Tablet, & Desktop) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 pt-2 border-t border-apple-subtleBorder">
                <!-- 1. Filter Role -->
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] sm:text-[10.5px] font-semibold text-apple-textTertiary uppercase tracking-wider">Actor Role</label>
                    <select name="role" class="w-full px-2.5 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                        <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent (CS)</option>
                        <option value="visitor" {{ request('role') === 'visitor' ? 'selected' : '' }}>Visitor</option>
                        <option value="system" {{ request('role') === 'system' ? 'selected' : '' }}>System</option>
                    </select>
                </div>

                <!-- 2. Filter Action Event -->
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] sm:text-[10.5px] font-semibold text-apple-textTertiary uppercase tracking-wider">Kategori Event</label>
                    <select name="action" class="w-full px-2.5 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                        <option value="">Semua Event</option>
                        <optgroup label="Grup Aksi">
                            <option value="integration.*" {{ request('action') === 'integration.*' ? 'selected' : '' }}>Integrasi Channel (*)</option>
                            <option value="widget.*" {{ request('action') === 'widget.*' ? 'selected' : '' }}>Widget &amp; Bot (*)</option>
                            <option value="team.*" {{ request('action') === 'team.*' ? 'selected' : '' }}>Tim &amp; RBAC (*)</option>
                            <option value="message.*" {{ request('action') === 'message.*' ? 'selected' : '' }}>Pesan Chat (*)</option>
                            <option value="chat.*" {{ request('action') === 'chat.*' ? 'selected' : '' }}>Tiket Obrolan (*)</option>
                        </optgroup>
                        @if(isset($actionList) && count($actionList) > 0)
                            <optgroup label="Aksi Terdata">
                                @foreach($actionList as $act)
                                    <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <!-- 3. Filter Rentang Waktu -->
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] sm:text-[10.5px] font-semibold text-apple-textTertiary uppercase tracking-wider">Periode Waktu</label>
                    <select name="date_range" class="w-full px-2.5 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                        <option value="">Semua Waktu</option>
                        <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="7d" {{ request('date_range') === '7d' ? 'selected' : '' }}>7 Hari Terakhir</option>
                        <option value="30d" {{ request('date_range') === '30d' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    </select>
                </div>

                <!-- 4. Pilihan Sort Kolom -->
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] sm:text-[10.5px] font-semibold text-apple-textTertiary uppercase tracking-wider">Urutkan</label>
                    <div class="flex items-center gap-1">
                        <select name="sort" class="w-full px-2.5 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                            <option value="created_at" {{ (!request('sort') || request('sort') === 'created_at') ? 'selected' : '' }}>Waktu</option>
                            <option value="user_name" {{ request('sort') === 'user_name' ? 'selected' : '' }}>Nama Actor</option>
                            <option value="user_role" {{ request('sort') === 'user_role' ? 'selected' : '' }}>Role</option>
                            <option value="action" {{ request('sort') === 'action' ? 'selected' : '' }}>Event Code</option>
                            <option value="ip_address" {{ request('sort') === 'ip_address' ? 'selected' : '' }}>IP Address</option>
                            <option value="id" {{ request('sort') === 'id' ? 'selected' : '' }}>ID</option>
                        </select>
                        <input type="hidden" name="dir" id="filterDirInput" value="{{ $currentDir }}">
                        <button type="button" onclick="toggleSortDirection()"
                            class="p-1.5 rounded-lg border border-apple-border bg-apple-canvas/40 hover:bg-white text-apple-textSecondary hover:text-apple-textPrimary transition shrink-0"
                            title="Balik Arah Urutan ({{ strtoupper($currentDir) }})">
                            <span class="text-[11px] font-bold">{{ $currentDir === 'desc' ? '▼' : '▲' }}</span>
                        </button>
                    </div>
                </div>

                <!-- 5. Limit Baris per Halaman -->
                <div class="flex flex-col gap-1 col-span-2 sm:col-span-1">
                    <label class="text-[10px] sm:text-[10.5px] font-semibold text-apple-textTertiary uppercase tracking-wider">Per Halaman</label>
                    <select name="per_page" class="w-full px-2.5 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/30 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 entri</option>
                        <option value="25" {{ (!request('per_page') || request('per_page') == 25) ? 'selected' : '' }}>25 entri (Default)</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 entri</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 entri</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Container (Adaptive: Table on Desktop/Tabs with Native Horizontal Momentum Scroll + Smart Card Feed for Mobile/Tablet) -->
    <div class="bg-white border border-apple-border rounded-xl shadow-apple-sm overflow-hidden flex flex-col">
        <!-- Subheader Toolbar with View Switcher (Table vs Card) -->
        <div class="px-3.5 sm:px-4 py-2.5 border-b border-apple-border bg-apple-canvas/50 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-apple-textPrimary text-[12px] sm:text-[12.5px]">Riwayat Log Aktivitas</span>
                <span class="text-[10.5px] font-medium px-2 py-0.2 rounded-full bg-white border border-apple-border/70 text-apple-textSecondary shadow-2xs">
                    {{ $logs->total() }} entri
                </span>
            </div>

            <!-- View Mode Switcher (Seamless toggle between Table & Cards) -->
            <div class="flex items-center bg-black/5 p-0.5 rounded-lg text-[11px] font-medium text-apple-textSecondary shadow-inner shrink-0">
                <button type="button" onclick="setLogViewMode('table')" id="btnViewTable"
                    class="px-2.5 py-1 rounded-md transition inline-flex items-center gap-1.5" title="Tampilan Tabel (Tablet/Desktop)">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18"></path>
                    </svg>
                    <span class="text-[10.5px] sm:text-[11px]">Tabel</span>
                </button>
                <button type="button" onclick="setLogViewMode('cards')" id="btnViewCards"
                    class="px-2.5 py-1 rounded-md transition inline-flex items-center gap-1.5" title="Tampilan Kartu (Mobile/Tablet)">
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
                <span>Geser ke kanan untuk melihat detail Aktor, Event &amp; IP lengkap</span>
            </div>
            <button type="button" onclick="setLogViewMode('cards')" class="text-apple-blue hover:text-apple-blueHover font-medium inline-flex items-center gap-0.5 shrink-0 ml-2">
                <span>Mode Kartu</span> &rarr;
            </button>
        </div>

        <!-- 1. FULL TABLE VIEW (With smooth native momentum scroll on tablet and mobile) -->
        <div id="logsTableView" class="w-full overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
            <table class="w-full text-left text-[11.5px] sm:text-[12px] border-collapse min-w-[700px] sm:min-w-[780px] md:min-w-[860px]">
                <thead class="bg-apple-canvas/80 text-apple-textSecondary text-[10px] sm:text-[10.5px] font-semibold uppercase tracking-wider border-b border-apple-border select-none sticky top-0 z-10 backdrop-blur-xs">
                    <tr>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-32 sm:w-40">
                            <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan waktu">
                                <span>Waktu (WIB)</span> {!! $sortIcon('created_at') !!}
                            </a>
                        </th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-36 sm:w-48">
                            <a href="{{ $sortUrl('user_name') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan aktor">
                                <span>Aktor / Pelaku</span> {!! $sortIcon('user_name') !!}
                            </a>
                        </th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-36 sm:w-44">
                            <a href="{{ $sortUrl('action') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan event">
                                <span>Aksi / Event</span> {!! $sortIcon('action') !!}
                            </a>
                        </th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 min-w-[200px]">Deskripsi Aktivitas</th>
                        <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-28 sm:w-36">
                            <a href="{{ $sortUrl('ip_address') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan IP">
                                <span>IP Address</span> {!! $sortIcon('ip_address') !!}
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-apple-subtleBorder">
                    @forelse($logs as $log)
                        @php
                            $actionName = $log->action ?? 'unknown';
                            $isError = strpos($actionName, 'error') !== false ||
                                       strpos($actionName, 'exceeded') !== false ||
                                       strpos($actionName, 'UNAUTHORIZED') !== false ||
                                       strpos($actionName, 'failed') !== false;

                            $isIntegration = strpos($actionName, 'integration') !== false;
                            $isWidget = strpos($actionName, 'widget') !== false;
                            $isTeam = strpos($actionName, 'team') !== false || strpos($actionName, 'role') !== false;
                            $isChat = strpos($actionName, 'message') !== false || strpos($actionName, 'chat') !== false;

                            // Badge styling
                            $badgeClass = 'bg-black/5 text-apple-textSecondary border-black/10';
                            if ($isError) {
                                $badgeClass = 'bg-red-50 text-apple-red border-red-200';
                            } elseif ($isIntegration) {
                                $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                            } elseif ($isWidget) {
                                $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                            } elseif ($isTeam) {
                                $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
                            } elseif ($isChat) {
                                $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                            }

                            // Role styling
                            $roleName = $log->user_role ?? 'system';
                            $roleBadge = 'bg-black/5 text-apple-textSecondary';
                            if ($roleName === 'superadmin') {
                                $roleBadge = 'bg-purple-100 text-purple-900 border border-purple-300/60 font-semibold';
                            } elseif ($roleName === 'agent') {
                                $roleBadge = 'bg-amber-100 text-amber-900 border border-amber-300/60 font-medium';
                            } elseif ($roleName === 'visitor') {
                                $roleBadge = 'bg-blue-100 text-blue-900 border border-blue-300/60 font-medium';
                            }

                            $actorInitials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $log->user_name ?: 'SYS'), 0, 2)) ?: 'SY';
                            $hasProps = !empty($log->properties) && is_array($log->properties);
                        @endphp
                        <tr class="hover:bg-black/[0.015] transition group">
                            <!-- Timestamp -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <div class="font-mono text-[11px] text-apple-textPrimary font-medium">
                                    {{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }}
                                </div>
                                <div class="text-[10px] text-apple-textTertiary mt-0.5">
                                    {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                </div>
                            </td>

                            <!-- Actor -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-apple-canvas border border-apple-border text-[9.5px] font-semibold text-apple-textSecondary flex items-center justify-center shrink-0">
                                        {{ $actorInitials }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-apple-textPrimary truncate max-w-[130px]" title="{{ $log->user_name ?? 'System' }}">
                                            {{ $log->user_name ?? 'System' }}
                                        </div>
                                        <span class="text-[9px] font-mono uppercase px-1.5 py-0.2 rounded mt-0.5 inline-block {{ $roleBadge }}">
                                            {{ $roleName }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Event Code -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <span class="font-mono text-[10.5px] px-2 py-0.5 rounded border inline-block break-all {{ $badgeClass }}">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Description & JSON Payload Toggle -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <div class="text-apple-textPrimary leading-relaxed break-words">
                                    {{ $log->description ?? '-' }}
                                </div>

                                @if($hasProps)
                                    <div class="mt-1.5">
                                        <button type="button" onclick="toggleLogPayload('tbl-{{ $log->id }}')"
                                            class="inline-flex items-center gap-1 text-[10.5px] font-mono text-apple-blue hover:text-apple-blueHover hover:underline">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="16 18 22 12 16 6"></polyline>
                                                <polyline points="8 6 2 12 8 18"></polyline>
                                            </svg>
                                            <span>Payload Data</span>
                                            <span id="payload-arrow-tbl-{{ $log->id }}" class="text-[8px] font-bold">▼</span>
                                        </button>

                                        <div id="payload-box-tbl-{{ $log->id }}" class="hidden mt-1.5 p-2 bg-[#1E1E1E] text-[#D4D4D4] rounded-lg font-mono text-[11px] overflow-x-auto max-w-xl shadow-inner">
                                            <pre class="m-0 leading-tight"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                                <div class="font-mono text-[11px] text-apple-textPrimary flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-apple-green shrink-0"></span>
                                    <span>{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </div>
                                @if($log->user_agent)
                                    <div class="text-[9.5px] text-apple-textTertiary truncate max-w-[120px] mt-0.5" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent, 20) }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center text-apple-textTertiary">
                                <div class="w-10 h-10 rounded-full bg-black/5 mx-auto flex items-center justify-center text-apple-textTertiary mb-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                </div>
                                <div class="font-medium text-apple-textSecondary text-[13px]">Tidak ada log yang cocok dengan filter.</div>
                                <div class="text-[11.5px] mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</div>
                                <a href="{{ route('admin.logs') }}" class="inline-block mt-3 px-3 py-1.5 rounded-lg border border-apple-border bg-white text-apple-textPrimary hover:bg-black/5 text-[11.5px] font-medium transition shadow-2xs">
                                    Reset Semua Filter
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. RESPONSIVE CARD GRID VIEW (Optimized for Mobile & Tablet iPads) -->
        <div id="logsCardsView" class="hidden p-3 sm:p-4 bg-apple-canvas/20">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
                @forelse($logs as $log)
                    @php
                        $actionName = $log->action ?? 'unknown';
                        $isError = strpos($actionName, 'error') !== false ||
                                   strpos($actionName, 'exceeded') !== false ||
                                   strpos($actionName, 'UNAUTHORIZED') !== false;

                        $isIntegration = strpos($actionName, 'integration') !== false;
                        $isWidget = strpos($actionName, 'widget') !== false;
                        $isTeam = strpos($actionName, 'team') !== false || strpos($actionName, 'role') !== false;
                        $isChat = strpos($actionName, 'message') !== false || strpos($actionName, 'chat') !== false;

                        $badgeClass = 'bg-black/5 text-apple-textSecondary border-black/10';
                        if ($isError) {
                            $badgeClass = 'bg-red-50 text-apple-red border-red-200';
                        } elseif ($isIntegration) {
                            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                        } elseif ($isWidget) {
                            $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                        } elseif ($isTeam) {
                            $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
                        } elseif ($isChat) {
                            $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                        }

                        $roleName = $log->user_role ?? 'system';
                        $roleBadge = 'bg-black/5 text-apple-textSecondary';
                        if ($roleName === 'superadmin') {
                            $roleBadge = 'bg-purple-100 text-purple-900 border border-purple-300/60 font-semibold';
                        } elseif ($roleName === 'agent') {
                            $roleBadge = 'bg-amber-100 text-amber-900 border border-amber-300/60 font-medium';
                        } elseif ($roleName === 'visitor') {
                            $roleBadge = 'bg-blue-100 text-blue-900 border border-blue-300/60 font-medium';
                        }

                        $actorInitials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $log->user_name ?: 'SYS'), 0, 2)) ?: 'SY';
                        $hasProps = !empty($log->properties) && is_array($log->properties);
                    @endphp
                    <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs hover:shadow-apple-sm transition flex flex-col justify-between gap-2.5">
                        <!-- Card Header -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded border font-semibold truncate max-w-[170px] {{ $badgeClass }}" title="{{ $log->action }}">
                                {{ $log->action }}
                            </span>

                            <span class="font-mono text-[10.5px] text-apple-textTertiary shrink-0">
                                {{ $log->created_at ? $log->created_at->format('d M, H:i') : '-' }}
                            </span>
                        </div>

                        <!-- Card Body (Description) -->
                        <div class="text-[12px] text-apple-textPrimary leading-snug break-words">
                            {{ $log->description ?? '-' }}
                        </div>

                        <!-- Payload Disclosure if present -->
                        @if($hasProps)
                            <div class="pt-1">
                                <button type="button" onclick="toggleLogPayload('crd-{{ $log->id }}')"
                                    class="inline-flex items-center gap-1 text-[10.5px] font-mono text-apple-blue hover:underline">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="16 18 22 12 16 6"></polyline>
                                        <polyline points="8 6 2 12 8 18"></polyline>
                                    </svg>
                                    <span>Payload JSON</span>
                                    <span id="payload-arrow-crd-{{ $log->id }}" class="text-[8px] font-bold">▼</span>
                                </button>
                                <div id="payload-box-crd-{{ $log->id }}" class="hidden mt-1.5 p-2 bg-[#1E1E1E] text-[#D4D4D4] rounded-lg font-mono text-[10px] overflow-x-auto shadow-inner">
                                    <pre class="m-0 leading-tight"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </div>
                            </div>
                        @endif

                        <!-- Card Footer -->
                        <div class="flex items-center justify-between pt-2 border-t border-apple-subtleBorder text-[11px]">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <div class="w-5 h-5 rounded-full bg-apple-canvas border border-apple-border text-[9px] font-semibold text-apple-textSecondary flex items-center justify-center shrink-0">
                                    {{ $actorInitials }}
                                </div>
                                <span class="font-medium text-apple-textPrimary truncate max-w-[100px]">{{ $log->user_name ?? 'System' }}</span>
                                <span class="text-[8.5px] font-mono uppercase px-1 py-0.2 rounded shrink-0 {{ $roleBadge }}">
                                    {{ $roleName }}
                                </span>
                            </div>

                            <span class="font-mono text-apple-textTertiary text-[10px] shrink-0" title="IP Address">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center text-apple-textTertiary bg-white rounded-xl border border-apple-border">
                        Belum ada riwayat aktivitas yang cocok dengan filter.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 3. COMPREHENSIVE RESPONSIVE PAGINATION BAR -->
        <div class="px-4 py-3 border-t border-apple-border flex flex-col sm:flex-row items-center justify-between gap-3 text-[11.5px] text-apple-textSecondary bg-apple-canvas/40">
            <!-- Left Info -->
            <div class="text-center sm:text-left">
                @if($logs->total() > 0)
                    Menampilkan <span class="font-semibold text-apple-textPrimary">{{ $logs->firstItem() }}</span> &ndash;
                    <span class="font-semibold text-apple-textPrimary">{{ $logs->lastItem() }}</span> dari
                    <span class="font-semibold text-apple-textPrimary">{{ number_format($logs->total()) }}</span> entri
                @else
                    Menampilkan <span class="font-semibold text-apple-textPrimary">0</span> entri
                @endif
            </div>

            <!-- Right Pagination Navigation -->
            @if($logs->hasPages())
                <div class="flex items-center gap-1 flex-wrap justify-center">
                    {{-- Previous Page Link --}}
                    @if ($logs->onFirstPage())
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white font-medium text-[11px]">&larr; Prev</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs font-medium text-[11px]">&larr; Prev</a>
                    @endif

                    {{-- Page Numbers (Compact Window on Tablet & Desktop) --}}
                    <div class="hidden sm:flex items-center gap-1">
                        @php
                            $start = max(1, $logs->currentPage() - 2);
                            $end = min($logs->lastPage(), $logs->currentPage() + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $logs->url(1) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">1</a>
                            @if($start > 2)
                                <span class="px-1 text-apple-textTertiary">...</span>
                            @endif
                        @endif

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $logs->currentPage())
                                <span class="w-7 h-7 flex items-center justify-center rounded-md bg-apple-textPrimary text-white font-semibold text-[11px] shadow-2xs">{{ $page }}</span>
                            @else
                                <a href="{{ $logs->url($page) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($end < $logs->lastPage())
                            @if($end < $logs->lastPage() - 1)
                                <span class="px-1 text-apple-textTertiary">...</span>
                            @endif
                            <a href="{{ $logs->url($logs->lastPage()) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">{{ $logs->lastPage() }}</a>
                        @endif
                    </div>

                    {{-- Mobile Page Counter Badge --}}
                    <span class="sm:hidden px-2 text-[11px] font-medium text-apple-textTertiary">
                        Hal {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                    </span>

                    {{-- Next Page Link --}}
                    @if ($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs font-medium text-[11px]">Next &rarr;</a>
                    @else
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white font-medium text-[11px]">Next &rarr;</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleSortDirection() {
        var dirInput = document.getElementById('filterDirInput');
        if (dirInput) {
            dirInput.value = dirInput.value === 'asc' ? 'desc' : 'asc';
            document.getElementById('logsFilterForm').submit();
        }
    }

    function toggleLogPayload(id) {
        var box = document.getElementById('payload-box-' + id);
        var arrow = document.getElementById('payload-arrow-' + id);
        if (box) {
            if (box.classList.contains('hidden')) {
                box.classList.remove('hidden');
                if (arrow) arrow.innerText = '▲';
            } else {
                box.classList.add('hidden');
                if (arrow) arrow.innerText = '▼';
            }
        }
    }

    // Responsive View Switcher (Table vs Card Feed)
    function setLogViewMode(mode) {
        var tableView = document.getElementById('logsTableView');
        var cardsView = document.getElementById('logsCardsView');
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
            try { localStorage.setItem('beantalk_logs_view_mode', 'cards'); } catch(e) {}
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
            try { localStorage.setItem('beantalk_logs_view_mode', 'table'); } catch(e) {}
        }
    }

    // Auto-detect best view on load based on screen size or user preference
    (function() {
        try {
            var savedMode = localStorage.getItem('beantalk_logs_view_mode');
            if (savedMode) {
                setLogViewMode(savedMode);
            } else if (window.innerWidth < 768) {
                // Default to comfortable card feed on mobile phones
                setLogViewMode('cards');
            } else {
                // Default to table on tablets and desktop
                setLogViewMode('table');
            }
        } catch(e) {
            setLogViewMode('table');
        }
    })();
</script>

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
