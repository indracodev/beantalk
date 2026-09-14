<!-- Collapsible Apple-style Sidebar Partial -->
<aside id="main-sidebar"
    class="fixed md:static inset-y-0 left-0 -translate-x-full md:translate-x-0 h-full w-60 glass-sidebar border-r border-apple-border flex flex-col justify-between shrink-0 z-40 md:z-30 transition-transform md:transition-[width] duration-200 ease-out">

    <div class="flex flex-col h-full overflow-hidden">
        <!-- Top Brand & Minimize Toggle Header -->
        <div class="h-12 border-b border-apple-border flex items-center justify-between px-3 shrink-0">
            <a href="{{ route('admin.inbox') }}" data-loading-msg="Memuat Inbox..." class="flex items-center gap-2.5 overflow-hidden text-inherit no-underline">
                <div class="w-6 h-6 rounded-md bg-gradient-to-b from-[#333336] to-[#1D1D1F] flex items-center justify-center text-white font-semibold text-[11px] shadow-xs shrink-0">
                    B
                </div>
                <span class="font-semibold text-apple-textPrimary tracking-tight text-[13px] truncate sidebar-text">BeanTalk</span>
            </a>

            <div class="flex items-center gap-1">
                <!-- Mobile Close Drawer Button (Visible on Mobile Only) -->
                <button type="button" onclick="closeSidebarMobile()"
                    class="md:hidden p-1.5 rounded-md text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 active:scale-95 transition"
                    title="Tutup Menu">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <!-- Sidebar Collapse / Expand Button (Apple macOS Desktop Style) -->
                <button type="button" onclick="toggleSidebarCollapse()"
                    class="hidden md:inline-flex p-1.5 rounded-md text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 active:scale-95 transition"
                    title="Toggle Sidebar (Icon Only)">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="3" />
                        <path d="M9 3v18" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Scrollable Navigation Content -->
        <div class="p-2 flex-1 flex flex-col gap-4 overflow-y-auto overflow-x-hidden">

            <!-- Workspace Selector with Channel Switcher Dropdown -->
            @php
                $allSidebarProjects = isset($projects) && is_iterable($projects) && !($projects instanceof \Illuminate\Database\Eloquent\Builder) 
                    ? $projects 
                    : (Auth::user()->relationLoaded('tenant') && Auth::user()->tenant ? Auth::user()->tenant->projects : (isset($projects) ? $projects : collect()));
                
                $selectedProjId = request('project_id');
                $isAllSelected = empty($selectedProjId) || $selectedProjId === 'all';
                $activeProject = null;

                if (!$isAllSelected && is_iterable($allSidebarProjects) && count($allSidebarProjects) > 0) {
                    $activeProject = collect($allSidebarProjects)->firstWhere('id', $selectedProjId);
                }

                if ($activeProject) {
                    $activeWorkspaceName = $activeProject->name;
                    $workspaceInitials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $activeWorkspaceName), 0, 2)) ?: 'WS';
                } else {
                    $tenantName = Auth::user()->relationLoaded('tenant') && Auth::user()->tenant ? Auth::user()->tenant->name : 'All Channels';
                    $activeWorkspaceName = 'All Websites';
                    $workspaceInitials = 'ALL';
                }

                $sidebarSitesCount = is_countable($allSidebarProjects) ? count($allSidebarProjects) : (isset($connectedSitesCount) ? $connectedSitesCount : 1);
                $sidebarTeamCount = isset($staffMembers) ? count($staffMembers) : (isset($teamMembers) ? (is_countable($teamMembers) ? count($teamMembers) : 0) : (isset($teamCount) ? $teamCount : 1));
            @endphp
            <div class="relative">
                <button type="button" onclick="toggleWorkspaceDropdown(event)" class="workspace-box w-full flex items-center justify-between px-2 py-1.5 rounded-xl bg-white border border-apple-border shadow-apple-sm hover:border-apple-border/80 hover:bg-apple-canvas/40 transition cursor-pointer text-left">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="w-6 h-6 rounded-lg {{ $isAllSelected ? 'bg-gradient-to-br from-blue-600 to-indigo-700' : 'bg-[#2C2C2E]' }} text-white flex items-center justify-center font-medium text-[10px] shrink-0 shadow-2xs">
                            {{ $workspaceInitials }}
                        </div>
                        <div class="truncate sidebar-text">
                            <div class="font-medium text-[12px] text-apple-textPrimary truncate" id="sidebar-workspace-title">{{ $activeWorkspaceName }}</div>
                            <div class="text-[10px] text-apple-textTertiary truncate sidebar-subtext">{{ $sidebarSitesCount }} Connected {{ $sidebarSitesCount > 1 ? 'Sites' : 'Site' }}</div>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-apple-textTertiary shrink-0 sidebar-text transition-transform" id="workspace-chevron" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                </button>

                <!-- macOS Flyout Menu for Multi-Channel Switcher -->
                <div id="workspace-dropdown-menu" class="hidden absolute top-full left-0 mt-1 w-60 bg-white border border-apple-border shadow-apple-popover rounded-xl p-1.5 z-50 text-[12px]">
                    <div class="px-2 py-1 text-[10px] font-semibold text-apple-textTertiary uppercase tracking-wider">
                        Filter Channel (Website)
                    </div>
                    <div class="flex flex-col gap-0.5 max-h-52 overflow-y-auto">
                        <!-- Option: All Websites -->
                        <a href="{{ route('admin.inbox', ['status' => request('status'), 'search' => request('search')]) }}" data-loading-msg="Memuat Semua Channel..." class="flex items-center justify-between px-2 py-1.5 rounded-lg {{ $isAllSelected ? 'bg-apple-blue/10 text-apple-blue font-medium' : 'text-apple-textPrimary hover:bg-black/5' }} transition">
                            <div class="flex items-center gap-2 truncate">
                                <span class="w-5 h-5 rounded {{ $isAllSelected ? 'bg-apple-blue text-white' : 'bg-black/5 text-apple-textSecondary' }} text-[9px] font-bold flex items-center justify-center shrink-0">ALL</span>
                                <span class="truncate text-[11.5px]">All Websites (Semua Channel)</span>
                            </div>
                            @if($isAllSelected)
                                <svg class="w-3.5 h-3.5 text-apple-blue shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @endif
                        </a>

                        @if(is_iterable($allSidebarProjects) && count($allSidebarProjects) > 0)
                            @foreach($allSidebarProjects as $proj)
                                @php
                                    $isSelected = !$isAllSelected && $activeProject && $activeProject->id == $proj->id;
                                    $projInitials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $proj->name), 0, 2)) ?: 'WS';
                                @endphp
                                <a href="{{ route('admin.inbox', ['project_id' => $proj->id, 'status' => request('status'), 'search' => request('search')]) }}" data-loading-msg="Memuat {{ $proj->name }}..." class="flex items-center justify-between px-2 py-1.5 rounded-lg {{ $isSelected ? 'bg-apple-blue/10 text-apple-blue font-medium' : 'text-apple-textPrimary hover:bg-black/5' }} transition">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="w-5 h-5 rounded {{ $isSelected ? 'bg-apple-blue text-white' : 'bg-black/5 text-apple-textSecondary' }} text-[9.5px] font-bold flex items-center justify-center shrink-0">{{ $projInitials }}</span>
                                        <span class="truncate text-[11.5px]">{{ $proj->name }}</span>
                                    </div>
                                    @if($isSelected)
                                        <svg class="w-3.5 h-3.5 text-apple-blue shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        @endif
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                        <div class="my-1 border-t border-apple-border/60"></div>
                        <a href="{{ route('admin.integrations') }}" data-loading-msg="Memuat Integrations..." class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 text-[11.5px] transition">
                            <svg class="w-3.5 h-3.5 text-apple-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Manage Channels</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Navigation Group -->
            <nav class="flex flex-col gap-0.5">
                <div class="px-2 pb-1 text-[9.5px] font-semibold tracking-wider text-apple-textTertiary uppercase sidebar-text">
                    Navigation
                </div>

                <!-- Executive Dashboard -->
                <a href="{{ route('admin.dashboard') }}" id="navItemDashboard" data-loading-msg="Memuat Dashboard..."
                    class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.dashboard*') ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
                    title="Dashboard">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard*') ? 'text-apple-blue' : 'text-apple-textSecondary' }} shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                        </svg>
                        <span class="sidebar-text">Dashboard</span>
                    </div>
                    <span class="sidebar-badge text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-700">LIVE</span>
                </a>

                <!-- Live Inbox -->
                <a href="{{ route('admin.inbox') }}" id="navItemInbox" data-loading-msg="Memuat Inbox..."
                    class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.inbox*') ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
                    title="Inbox">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.inbox*') ? 'text-apple-blue' : 'text-apple-textSecondary' }} shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="20" height="16" x="2" y="4" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                        <span class="sidebar-text">Inbox</span>
                    </div>
                    <span class="sidebar-badge text-[10.5px] font-medium px-1.5 py-0.2 rounded-full {{ ($totalUnreadConversations ?? 0) > 0 ? 'bg-apple-red text-white pulse' : 'text-apple-textTertiary' }}" id="sidebarUnreadBadge" style="{{ ($totalUnreadConversations ?? 0) > 0 ? 'display: inline-flex;' : 'display: none;' }}">
                        {{ ($totalUnreadConversations ?? 0) > 99 ? '99+' : ($totalUnreadConversations ?? 0) }}
                    </span>
                </a>

                @if (Auth::user()->isSuperAdmin())
                    <!-- Integrations / Channels -->
                    <a href="{{ route('admin.integrations') }}" id="navItemIntegrations" data-loading-msg="Memuat Integrations..."
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.integrations*') ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
                        title="Integrations">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.integrations*') ? 'text-apple-blue' : 'text-apple-textSecondary' }} shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span class="sidebar-text">Integrations</span>
                        </div>
                        <span class="sidebar-badge text-[10.5px] font-medium text-apple-textTertiary" id="sidebar-integrations-count">{{ $sidebarSitesCount }}</span>
                    </a>

                    <!-- Team & RBAC -->
                    <a href="{{ route('admin.team') }}" id="navItemTeam" data-loading-msg="Memuat Team..."
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.team*') ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
                        title="Team & RBAC">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.team*') ? 'text-apple-blue' : 'text-apple-textSecondary' }} shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span class="sidebar-text">Team & RBAC</span>
                        </div>
                        <span class="sidebar-badge text-[10px] font-medium px-1.5 py-0.2 rounded-md bg-amber-500/10 text-amber-700">{{ $sidebarTeamCount }}</span>
                    </a>

                    <!-- Activity Logs -->
                    <a href="{{ route('admin.logs') }}" id="navItemLogs" data-loading-msg="Memuat Activity Logs..."
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.logs*') ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal' }} transition"
                        title="Activity Logs">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.logs*') ? 'text-apple-blue' : 'text-apple-textSecondary' }} shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                            </svg>
                            <span class="sidebar-text">Activity Logs</span>
                        </div>
                        <span class="sidebar-badge w-1.5 h-1.5 rounded-full bg-apple-green"></span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Bottom User Profile Card -->
        <div class="p-2 border-t border-apple-border/80 bg-white/60">
            <div class="profile-box flex items-center justify-between px-1.5 py-1 rounded-lg">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="relative shrink-0">
                        <div id="user-avatar-badge"
                            class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-semibold text-[10.5px] flex items-center justify-center border border-amber-300/60 shadow-2xs">
                            {{ strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', Auth::user()->name ?? 'User'), 0, 2)) }}
                        </div>
                        <span class="w-2 h-2 rounded-full bg-apple-green absolute bottom-0 right-0 ring-1 ring-white"></span>
                    </div>
                    <div class="truncate sidebar-text">
                        <div id="user-display-name" class="font-medium text-[12px] text-apple-textPrimary truncate">
                            {{ Auth::user()->name }}
                        </div>
                        <div id="user-display-role" class="text-[10px] text-apple-textTertiary truncate">
                            {{ ucfirst(Auth::user()->role) }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1 sidebar-text">
                    <form action="{{ route('logout') }}" method="POST" class="m-0 inline">
                        @csrf
                        <button type="submit" class="p-1 text-apple-textTertiary hover:text-apple-red rounded transition" title="Logout / Keluar">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>

