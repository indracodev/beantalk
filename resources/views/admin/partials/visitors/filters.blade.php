<!-- Space-Saving Compact Filter & Search Toolbar (Responsive Wrap Strip) -->
@php
    $currentSort = request('sort', 'last_seen_at');
    $currentDir = strtolower(request('dir', 'desc')) === 'asc' ? 'asc' : 'desc';


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

            <button type="submit" class="px-3 py-1.5 rounded-lg bg-apple-textPrimary text-white hover:bg-apple-inkHover transition font-medium text-[11px] shadow-2xs inline-flex items-center gap-1 shrink-0">
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
