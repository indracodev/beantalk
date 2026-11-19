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
            @endphp
            <a href="?period=today" class="px-2.5 py-1 rounded-lg transition {{ $period === 'today' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">Today</a>
            <a href="?period=7d" class="px-2.5 py-1 rounded-lg transition {{ $period === '7d' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">7 Days</a>
            <a href="?period=30d" class="px-2.5 py-1 rounded-lg transition {{ $period === '30d' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">30 Days</a>
            <a href="?period=quarter" class="px-2.5 py-1 rounded-lg transition {{ $period === 'quarter' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">Quarter</a>
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
