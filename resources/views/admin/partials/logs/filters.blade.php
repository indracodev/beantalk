<!-- Filter, Search & Sort Control Panel -->
@php
    $currentSort = request('sort', 'created_at');
    $currentDir = strtolower(request('dir', 'desc')) === 'asc' ? 'asc' : 'desc';


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
