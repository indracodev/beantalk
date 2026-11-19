{{-- Submenu filter Inbox (Aktif/Mine/Selesai/Semua) — hanya tampil di halaman Inbox. --}}
@if (request()->routeIs('admin.inbox*'))
    @php
        $inboxStatus = $currentStatus ?? request('status', 'open');
        $projectIdParam = request('project_id');
    @endphp
    <!-- Submenu Filter Obrolan (Hanya muncul saat menu Inbox dipilih/aktif) -->
    <div
        class="sidebar-submenu ml-3.5 pl-2 my-1 border-l border-apple-border/70 flex flex-col gap-0.5">
        <!-- 1. Chat Aktif (Open) - Default Filter -->
        <a href="{{ route('admin.inbox', array_filter(['status' => 'open', 'project_id' => $projectIdParam])) }}"
            class="inbox-scope-btn no-loader sidebar-item w-full flex items-center justify-between px-2 py-1 rounded-md text-[11.5px] {{ $inboxStatus === 'open' ? 'text-apple-blue bg-apple-blue/10 font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
            title="Chat Aktif (Open)">
            <div class="flex items-center gap-2 truncate">
                <span
                    class="w-1.5 h-1.5 rounded-full {{ $inboxStatus === 'open' ? 'bg-apple-blue' : 'bg-apple-green' }} shrink-0"></span>
                <span class="sidebar-text truncate">Chat Aktif</span>
            </div>
            <span
                id="sidebarBadgeOpen" data-scope="open"
                class="sidebar-badge text-[10px] font-medium px-1.5 py-0.2 rounded-full {{ $inboxStatus === 'open' ? 'bg-apple-blue/20 text-apple-blue font-semibold' : 'text-apple-textTertiary' }}">
                {{ $counts['open'] ?? 0 }}
            </span>
        </a>

        <!-- 2. Mine (Ditugaskan ke Saya) -->
        <a href="{{ route('admin.inbox', array_filter(['status' => 'mine', 'project_id' => $projectIdParam])) }}"
            class="inbox-scope-btn no-loader sidebar-item w-full flex items-center justify-between px-2 py-1 rounded-md text-[11.5px] {{ $inboxStatus === 'mine' ? 'text-apple-blue bg-apple-blue/10 font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
            title="Ditugaskan ke Saya">
            <div class="flex items-center gap-2 truncate">
                <svg class="w-3.5 h-3.5 {{ $inboxStatus === 'mine' ? 'text-apple-blue' : 'text-apple-textTertiary' }} shrink-0"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <span class="sidebar-text truncate">Mine</span>
            </div>
            <span
                id="sidebarBadgeMine" data-scope="mine"
                class="sidebar-badge text-[10px] font-medium px-1.5 py-0.2 rounded-full {{ $inboxStatus === 'mine' ? 'bg-apple-blue/20 text-apple-blue font-semibold' : 'text-apple-textTertiary' }}">
                {{ $counts['mine'] ?? 0 }}
            </span>
        </a>

        <!-- 3. Selesai (Closed) -->
        <a href="{{ route('admin.inbox', array_filter(['status' => 'closed', 'project_id' => $projectIdParam])) }}"
            class="inbox-scope-btn no-loader sidebar-item w-full flex items-center justify-between px-2 py-1 rounded-md text-[11.5px] {{ $inboxStatus === 'closed' ? 'text-apple-blue bg-apple-blue/10 font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
            title="Obrolan Selesai">
            <div class="flex items-center gap-2 truncate">
                <svg class="w-3.5 h-3.5 {{ $inboxStatus === 'closed' ? 'text-apple-blue' : 'text-apple-textTertiary' }} shrink-0"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                <span class="sidebar-text truncate">Selesai</span>
            </div>
            <span
                id="sidebarBadgeClosed" data-scope="closed"
                class="sidebar-badge text-[10px] font-medium px-1.5 py-0.2 rounded-full {{ $inboxStatus === 'closed' ? 'bg-apple-blue/20 text-apple-blue font-semibold' : 'text-apple-textTertiary' }}">
                {{ $counts['closed'] ?? 0 }}
            </span>
        </a>

        <!-- 4. Semua (All) -->
        <a href="{{ route('admin.inbox', array_filter(['status' => 'all', 'project_id' => $projectIdParam])) }}"
            class="inbox-scope-btn no-loader sidebar-item w-full flex items-center justify-between px-2 py-1 rounded-md text-[11.5px] {{ $inboxStatus === 'all' ? 'text-apple-blue bg-apple-blue/10 font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
            title="Semua Riwayat Obrolan">
            <div class="flex items-center gap-2 truncate">
                <svg class="w-3.5 h-3.5 {{ $inboxStatus === 'all' ? 'text-apple-blue' : 'text-apple-textTertiary' }} shrink-0"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6" />
                    <line x1="8" y1="12" x2="21" y2="12" />
                    <line x1="8" y1="18" x2="21" y2="18" />
                    <line x1="3" y1="6" x2="3.01" y2="6" />
                    <line x1="3" y1="12" x2="3.01" y2="12" />
                    <line x1="3" y1="18" x2="3.01" y2="18" />
                </svg>
                <span class="sidebar-text truncate">Semua</span>
            </div>
            <span
                id="sidebarBadgeAll" data-scope="all"
                class="sidebar-badge text-[10px] font-medium px-1.5 py-0.2 rounded-full {{ $inboxStatus === 'all' ? 'bg-apple-blue/20 text-apple-blue font-semibold' : 'text-apple-textTertiary' }}">
                {{ $counts['all'] ?? 0 }}
            </span>
        </a>
    </div>
@endif
