@extends('layouts.admin')

@section('title', 'Live Inbox')

@section('breadcrumb')
    <a href="{{ route('admin.inbox') }}" class="{{ $activeConversation ? 'text-apple-textTertiary hover:text-apple-textPrimary transition text-[11.5px] font-normal no-loader' : 'font-medium text-apple-textPrimary text-[12px]' }}">Inbox Messages</a>
    @if($activeConversation && $activeConversation->project)
        <span class="text-apple-textTertiary text-[11px]">/</span>
        <span id="topbar-integration-name" class="font-medium text-apple-textPrimary text-[12px] flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full inline-block" style="background-color: {{ $activeConversation->project->widgetSetting->primary_color ?? '#0071E3' }};"></span>
            <span>{{ $activeConversation->project->name }}</span>
        </span>
    @endif
@endsection


@section('content')
    @php
        $isExplicitChat = request()->route('id') !== null;
    @endphp
    <div class="flex-1 flex w-full h-full overflow-hidden" id="inboxWorkspace">

        <!-- ========================================== -->
        <!-- PANE 1: Conversation List (Resizable)      -->
        <!-- ========================================== -->
        <section id="pane-conv-list"
            class="w-full md:w-80 {{ $isExplicitChat ? 'hidden md:flex' : 'flex' }} border-r border-apple-border flex-col glass-sidebar shrink-0 z-10 select-none overflow-hidden"
            style="min-width: 220px; max-width: 520px;">

            <!-- Search & Filter Controls -->
            <div class="p-2.5 sm:p-3 border-b border-apple-border flex flex-col gap-2 shrink-0 glass-sidebar">
                <!-- Website / Channel Selector Dropdown -->
                @php
                    $allInboxProjects = isset($projects) && is_iterable($projects) && !($projects instanceof \Illuminate\Database\Eloquent\Builder) 
                        ? $projects 
                        : (Auth::user()->relationLoaded('tenant') && Auth::user()->tenant ? Auth::user()->tenant->projects : collect());
                    
                    $selectedProjId = request('project_id');
                    $isAllSelected = empty($selectedProjId) || $selectedProjId === 'all';
                    $activeProject = null;

                    if (!$isAllSelected && is_iterable($allInboxProjects) && count($allInboxProjects) > 0) {
                        $activeProject = collect($allInboxProjects)->firstWhere('id', $selectedProjId);
                    }

                    if ($activeProject) {
                        $activeWorkspaceName = $activeProject->name;
                        $workspaceInitials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $activeWorkspaceName), 0, 2)) ?: 'WS';
                        $projectColor = $activeProject->widgetSetting->primary_color ?? '#0071E3';
                    } else {
                        $activeWorkspaceName = 'All Websites';
                        $workspaceInitials = 'ALL';
                        $projectColor = '#0071E3';
                    }

                    $sitesCount = is_countable($allInboxProjects) ? count($allInboxProjects) : 1;
                @endphp

                <div class="relative mb-0.5">
                    <button type="button" onclick="toggleWorkspaceDropdown(event)" class="workspace-box w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-white border border-apple-border/80 shadow-2xs hover:border-apple-border hover:bg-apple-canvas/40 transition cursor-pointer text-left">
                        <div class="flex items-center gap-2 overflow-hidden min-w-0">
                            <div class="w-6 h-6 rounded-lg {{ $isAllSelected ? 'bg-gradient-to-br from-blue-600 to-indigo-700' : '' }} text-white flex items-center justify-center font-bold text-[9.5px] shrink-0 shadow-2xs" style="{{ !$isAllSelected ? 'background-color: ' . $projectColor . ';' : '' }}">
                                {{ $workspaceInitials }}
                            </div>
                            <div class="truncate">
                                <div class="font-semibold text-[12px] text-apple-textPrimary truncate" id="sidebar-workspace-title">{{ $activeWorkspaceName }}</div>
                                <div class="text-[9.5px] text-apple-textTertiary truncate">{{ $sitesCount }} Connected {{ $sitesCount > 1 ? 'Sites' : 'Site' }}</div>
                            </div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-apple-textTertiary shrink-0 transition-transform" id="workspace-chevron" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </button>

                    <!-- macOS Flyout Menu for Multi-Channel Switcher -->
                    <div id="workspace-dropdown-menu" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-apple-border shadow-apple-popover rounded-xl p-1.5 z-50 text-[12px]">
                        <div class="px-2 py-1 text-[9.5px] font-semibold text-apple-textTertiary uppercase tracking-wider">
                            Filter Channel (Website)
                        </div>
                        <div class="flex flex-col gap-0.5 max-h-52 overflow-y-auto">
                            <!-- Option: All Websites -->
                            <a href="{{ route('admin.inbox', ['status' => request('status'), 'search' => request('search')]) }}" class="flex items-center justify-between px-2 py-1.5 rounded-lg {{ $isAllSelected ? 'bg-apple-blue/10 text-apple-blue font-medium' : 'text-apple-textPrimary hover:bg-black/5' }} transition no-loader">
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

                            @if(is_iterable($allInboxProjects) && count($allInboxProjects) > 0)
                                @foreach($allInboxProjects as $proj)
                                    @php
                                        $isSelected = !$isAllSelected && $activeProject && $activeProject->id == $proj->id;
                                        $projInitials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $proj->name), 0, 2)) ?: 'WS';
                                        $projColor = $proj->widgetSetting->primary_color ?? '#0071E3';
                                    @endphp
                                    <a href="{{ route('admin.inbox', ['project_id' => $proj->id, 'status' => request('status'), 'search' => request('search')]) }}" class="flex items-center justify-between px-2 py-1.5 rounded-lg {{ $isSelected ? 'bg-apple-blue/10 text-apple-blue font-medium' : 'text-apple-textPrimary hover:bg-black/5' }} transition no-loader">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-5 h-5 rounded text-white text-[9.5px] font-bold flex items-center justify-center shrink-0" style="background-color: {{ $projColor }};">{{ $projInitials }}</span>
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
                            <a href="{{ route('admin.integrations') }}" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 text-[11.5px] transition no-loader">
                                <svg class="w-3.5 h-3.5 text-apple-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Manage Channels</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-[13.5px] font-semibold tracking-tight text-apple-textPrimary">Messages</h2>
                        @if (($totalUnreadConversations ?? 0) > 0)
                            <span
                                class="text-[10px] font-semibold px-1.5 py-0.2 rounded-full bg-apple-blue/10 text-apple-blue"
                                id="inboxListUnreadBadge">
                                {{ $totalUnreadConversations }} Baru
                            </span>
                        @endif
                    </div>
                    <span
                        class="text-[10.5px] font-medium text-apple-textSecondary bg-white px-2 py-0.2 rounded-full border border-apple-border/70 shadow-2xs">
                        {{ $counts['open'] ?? 0 }} Open
                    </span>
                </div>

                <!-- Search input -->
                <form action="{{ route('admin.inbox') }}" method="GET" class="relative m-0">
                    @if (request('project_id'))
                        <input type="hidden" name="project_id" value="{{ request('project_id') }}">
                    @endif
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <input type="text" name="search" id="search-conv-input"
                        placeholder="Search customer, code, keyword..." value="{{ request('search') }}"
                        oninput="handleSearchConv(this.value)"
                        class="w-full pl-8 pr-7 py-1.5 bg-white border border-apple-border/80 rounded-lg text-[12px] placeholder:text-apple-textTertiary focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue transition shadow-2xs" />
                    <svg class="w-3.5 h-3.5 text-apple-textTertiary absolute left-2.5 top-2.5" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    @if (request('search'))
                        <a href="{{ route('admin.inbox', ['project_id' => request('project_id'), 'status' => request('status')]) }}"
                            class="absolute right-2 top-1.5 text-apple-textTertiary hover:text-apple-textPrimary text-[13px]">&times;</a>
                    @endif
                </form>

                <!-- Segmented Scope Filter -->
                <div
                    class="grid grid-cols-4 bg-black/[0.05] p-0.5 rounded-lg text-[11px] font-medium text-apple-textSecondary text-center">
                    <a href="?status=all{{ request('project_id') ? '&project_id=' . request('project_id') : '' }}"
                        class="inbox-scope-btn no-loader py-1 rounded-md transition {{ !request('status') || request('status') === 'all' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">
                        Semua
                    </a>
                    <a href="?status=open{{ request('project_id') ? '&project_id=' . request('project_id') : '' }}"
                        class="inbox-scope-btn no-loader py-1 rounded-md transition {{ request('status') === 'open' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">
                        Open
                    </a>
                    <a href="?status=mine{{ request('project_id') ? '&project_id=' . request('project_id') : '' }}"
                        class="inbox-scope-btn no-loader py-1 rounded-md transition {{ request('status') === 'mine' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">
                        Mine
                    </a>
                    <a href="?status=closed{{ request('project_id') ? '&project_id=' . request('project_id') : '' }}"
                        class="inbox-scope-btn no-loader py-1 rounded-md transition {{ request('status') === 'closed' ? 'bg-white text-apple-textPrimary shadow-2xs font-semibold' : 'hover:text-apple-textPrimary' }}">
                        Selesai
                    </a>
                </div>
            </div>

            <!-- Conversation List -->
            <div class="flex-1 p-2 flex flex-col gap-1 overflow-y-auto" id="convListContainer">
                @forelse($conversations as $conv)
                    @php
                        $isConvActive = $activeConversation && $activeConversation->id === $conv->id;
                        $isUnread = $conv->unread_agent_count > 0;
                        $displayName = $conv->visitor->display_name ?? 'Tamu';
                        $customerCode = $conv->visitor->customer_code_formatted ?? 'CUS-0000';
                        $initials = strtoupper(
                            substr(preg_replace('/[^a-zA-Z0-9]/', '', $conv->visitor->name ?: 'Tamu'), 0, 2)
                        );
                        $channelLabel = $conv->channel_label ?? 'Web Chat';
                        $siteName = $conv->project->name ?? 'Website';
                        $siteSlug = $conv->project->slug ?? 'site';
                        $projectColor = $conv->project->widgetSetting->primary_color ?? '#0071E3';
                        $timeHuman = $conv->last_message_time;
                    @endphp
                    <a href="{{ route('admin.inbox', $conv->id) }}" data-conv-id="{{ $conv->id }}"
                        data-visitor-id="{{ $conv->visitor_id }}"
                        data-project-color="{{ $projectColor }}"
                        data-site="{{ $conv->project_id }}" id="card-conv-{{ $conv->id }}"
                        class="conv-row conv-item no-loader w-full block px-2.5 py-2 rounded-lg transition {{ $isConvActive ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal border border-transparent' }}"
                        style="{{ $isConvActive ? 'border-left: 3px solid ' . $projectColor . ';' : '' }}">
                        <div class="flex gap-2.5 items-start">
                            <div class="relative shrink-0">
                                <div
                                    class="conv-avatar w-8 h-8 rounded-full bg-[#E5E5EA] text-apple-textPrimary font-semibold text-[11px] flex items-center justify-center border"
                                    style="border-color: {{ $projectColor }}45;">
                                    {{ $initials }}
                                </div>
                                <span
                                    class="w-2 h-2 rounded-full bg-apple-green absolute bottom-0 right-0 ring-1 ring-white"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="conv-name font-semibold text-apple-textPrimary truncate text-[12.5px]"
                                        title="{{ $displayName }}">
                                        {{ $displayName }}
                                    </span>
                                    <span
                                        class="conv-time text-[10.5px] text-apple-textTertiary font-mono" data-timestamp="{{ $conv->last_message_at ? $conv->last_message_at->toIso8601String() : '' }}">{{ $timeHuman }}</span>
                                </div>
                                <div class="conv-meta-row flex items-center gap-1.5 mb-1" data-conv-meta>
                                    <span
                                        class="conv-site text-[9px] font-semibold tracking-tight uppercase px-1.5 py-0.5 rounded truncate max-w-[110px]"
                                        style="background-color: {{ $projectColor }}14; color: {{ $projectColor }}; border: 1px solid {{ $projectColor }}30;">
                                        {{ $siteName }}
                                    </span>
                                    <span
                                        class="conv-cust-code text-[10px] text-apple-textTertiary font-mono">{{ $customerCode }}</span>
                                    @if ($isUnread)
                                        <span
                                            class="unread-badge ml-auto text-[9.5px] font-bold px-1.5 py-0.2 rounded-full bg-apple-blue text-white">
                                            {{ $conv->unread_agent_count > 9 ? '9+' : $conv->unread_agent_count }}
                                        </span>
                                    @endif
                                </div>
                                <p class="conv-snippet text-[11.5px] text-apple-textSecondary truncate">
                                    {{ $conv->last_message_preview ?? ($conv->latestMessage->content ?? 'Percakapan baru diinisialisasi...') }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-apple-textTertiary">
                        <svg class="w-8 h-8 mx-auto mb-2 text-apple-textTertiary/60" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <p class="font-medium text-[12.5px] text-apple-textSecondary">Belum ada percakapan</p>
                        <p class="text-[11px] mt-0.5">Pesan masuk dari widget pengunjung web akan tampil di sini secara
                            realtime.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Resizer Handle 1 (Between Conv List and Chat Feed) -->
        <div id="resizer-conv-chat" class="panel-resizer hidden md:block"
            title="Drag to resize conversation column (Double click to reset)"></div>

        <!-- ========================================== -->
        <!-- PANE 2: Active Chat Feed                   -->
        <!-- ========================================== -->
        <section id="pane-chat-thread"
            class="{{ $isExplicitChat ? 'flex' : 'hidden' }} md:flex flex-1 flex-col h-full bg-white relative overflow-hidden min-w-0 md:min-w-[320px]">
            @if ($activeConversation)
                @php
                    $activeDisplayName = $activeConversation->visitor->display_name ?? 'Tamu';
                    $activeCustomerCode = $activeConversation->visitor->customer_code_formatted ?? 'CUS-0000';
                    $activeInitials = strtoupper(
                        substr(preg_replace('/[^a-zA-Z0-9]/', '', $activeConversation->visitor->name ?: 'Tamu'), 0, 2)
                    );
                    $activeSiteName = $activeConversation->project->name ?? 'Website';
                    $activeProjectColor = $activeConversation->project->widgetSetting->primary_color ?? '#0071E3';
                    $assignedStaffName = $activeConversation->assignedUser->name ?? 'Unassigned';
                @endphp

                <!-- Thread Toolbar with Full Integration Brand Color -->
                <div id="threadHeaderToolbar" class="h-12 md:h-13 px-2.5 md:px-3.5 flex items-center justify-between z-10 shrink-0 gap-2 shadow-xs transition-colors"
                     style="background-color: {{ $activeProjectColor }}; border-bottom: 1px solid rgba(0,0,0,0.12);">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <button type="button" onclick="mobileBackToList()"
                            class="md:hidden p-1.5 -ml-1 rounded-lg text-white/90 hover:text-white hover:bg-white/15 flex items-center justify-center shrink-0 cursor-pointer active:scale-95 transition"
                            title="Kembali ke Daftar Chat">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </button>

                        <div class="relative shrink-0">
                            <div id="threadCustomerAvatar"
                                class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-xs text-white font-bold text-[11px] flex items-center justify-center border border-white/30 shadow-2xs">
                                {{ $activeInitials }}
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-apple-green absolute bottom-0 right-0 ring-2 ring-white"></span>
                        </div>

                        <div class="truncate min-w-0 flex-1 text-white">
                            <div class="flex items-center gap-1.5">
                                <h3 id="threadCustomerName" class="font-bold text-[13px] text-white truncate drop-shadow-xs">
                                    {{ $activeDisplayName }}
                                </h3>
                                <span id="threadOriginBadge" class="hidden sm:inline-block text-[9px] font-bold tracking-tight uppercase px-2 py-0.5 rounded-full truncate max-w-[120px] bg-white/20 text-white border border-white/30 backdrop-blur-xs shadow-2xs">
                                    {{ $activeSiteName }}
                                </span>
                            </div>
                            <div id="threadMetaSub" class="text-[10px] text-white/80 truncate font-medium">
                                {{ $activeCustomerCode }} • CS: <span id="assignedStaffText" class="text-white font-semibold">{{ $assignedStaffName }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Right -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <!-- Bot Active / Inactive Toggle -->
                        @php
                            $isBotActive = (bool) ($activeConversation->is_bot_active ?? true);
                        @endphp
                        <button type="button" id="btnToggleBot" onclick="handleToggleBot()"
                            class="px-2.5 py-1 text-[10.5px] rounded-lg border border-white/40 bg-white/20 hover:bg-white/30 text-white active:scale-95 transition font-semibold shadow-2xs flex items-center gap-1.5 cursor-pointer"
                            title="{{ $isBotActive ? 'Bot aktif membalas otomatis. Klik untuk menjeda bot.' : 'Bot sedang dijeda. Staf CS menangani percakapan ini. Klik untuk mengaktifkan kembali bot.' }}">
                            <span class="w-2 h-2 rounded-full {{ $isBotActive ? 'bg-emerald-300 animate-pulse' : 'bg-amber-300' }}" id="botStatusDot"></span>
                            <span id="botToggleText">{{ $isBotActive ? 'Bot: On' : 'Bot: Off' }}</span>
                        </button>

                        <!-- Assign Dropdown -->
                        <div class="relative">
                            <select id="assignCsSelect" onchange="handleAssign(this.value)"
                                class="max-w-[110px] sm:max-w-[135px] px-2 py-1 text-[10.5px] rounded-lg border border-white/40 bg-white text-apple-textPrimary hover:bg-white/95 focus:outline-none focus:ring-2 focus:ring-white/50 font-medium shadow-2xs cursor-pointer truncate">
                                <option value="">Tugaskan CS</option>
                                @foreach ($staffMembers as $staff)
                                    <option value="{{ $staff->id }}"
                                        {{ $activeConversation->assigned_user_id == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ $staff->role }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Toggle / Resolve Button -->
                        <button type="button" id="btnToggleStatus" onclick="handleToggleStatus()"
                            class="px-2.5 py-1 text-[10.5px] rounded-lg border border-white/40 bg-white {{ $activeConversation->status === 'open' ? 'text-apple-red hover:bg-red-50' : 'text-apple-green hover:bg-emerald-50' }} active:scale-95 transition font-semibold shadow-2xs cursor-pointer">
                            <span>{{ $activeConversation->status === 'open' ? 'Resolve' : 'Reopen' }}</span>
                        </button>

                        <!-- Inspector Toggle for Customer Context -->
                        <button type="button" onclick="toggleInspectorMode()"
                            class="p-1.5 rounded-lg border border-white/30 bg-white/20 hover:bg-white/30 text-white transition shadow-2xs cursor-pointer"
                            title="Detail Customer">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 16v-4" />
                                <path d="M12 8h.01" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Active Page Context Sticky Banner -->
                <div class="bg-apple-canvas/90 border-b border-apple-border/60 px-3 py-1 flex items-center justify-between text-[10.5px] shrink-0">
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-apple-textTertiary font-medium shrink-0">Page:</span>
                        <span class="font-medium text-apple-textPrimary flex items-center gap-1 truncate">
                            <svg class="w-3 h-3 shrink-0" style="color: {{ $activeProjectColor }};" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                            <span id="activePageTitle" class="truncate">{{ $activeConversation->page_title ?? 'Halaman Toko' }}</span>
                        </span>
                    </div>
                    <span class="font-semibold shrink-0 ml-2 text-[10px]" style="color: {{ $activeProjectColor }};">● Live</span>
                </div>

                <!-- Message History Feed -->
                <div id="chatThreadBody" class="flex-1 min-h-0 overflow-y-auto p-3 sm:p-4 flex flex-col gap-2.5 bg-apple-canvas/20">
                    @php
                        $lastGroupDate = null;
                    @endphp

                    @forelse($activeConversation->messages as $msg)
                        @php
                            $msgCreatedAt = $msg->created_at ? \Carbon\Carbon::parse($msg->created_at) : null;
                            $msgDateKey = $msgCreatedAt ? $msgCreatedAt->format('Y-m-d') : 'unknown';
                            if ($msgCreatedAt) {
                                if ($msgCreatedAt->isToday()) {
                                    $dateLabel = 'Hari Ini';
                                } elseif ($msgCreatedAt->isYesterday()) {
                                    $dateLabel = 'Kemarin';
                                } elseif ($msgCreatedAt->isCurrentYear()) {
                                    $dateLabel = $msgCreatedAt->translatedFormat('d F') ?: $msgCreatedAt->format('d M');
                                } else {
                                    $dateLabel = $msgCreatedAt->translatedFormat('d F Y') ?: $msgCreatedAt->format('d M Y');
                                }
                            } else {
                                $dateLabel = 'Hari Ini';
                            }
                        @endphp

                        @if ($lastGroupDate !== $msgDateKey)
                            @php
                                $lastGroupDate = $msgDateKey;
                            @endphp
                            <!-- WhatsApp Style Date Divider -->
                            <div class="flex items-center justify-center my-1.5 select-none" data-date-divider="{{ $msgDateKey }}">
                                <span class="text-[10.5px] font-medium text-apple-textSecondary bg-white/95 border border-apple-border/80 px-3 py-0.5 rounded-full shadow-2xs">
                                    {{ $dateLabel }}
                                </span>
                            </div>
                        @endif

                        @if ($msg->sender_type === 'visitor')
                            <!-- Visitor Bubble -->
                            <div class="flex flex-col items-start max-w-[85%] sm:max-w-[70%]"
                                data-id="{{ $msg->id }}" data-date-key="{{ $msgDateKey }}" data-created-at="{{ $msg->created_at ? $msg->created_at->toIso8601String() : '' }}">
                                <span class="text-[10.5px] text-apple-textTertiary mb-0.5 pl-1">{{ $msg->sender_name ?: $activeDisplayName }} (Visitor)</span>
                                <div class="bubble-visitor bg-white border border-apple-border/80 text-apple-textPrimary px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed">
                                    {{ $msg->content }}
                                </div>
                                <span class="msg-time-display text-[9.5px] text-apple-textTertiary mt-0.5 pl-1 font-mono" data-created-at="{{ $msg->created_at ? $msg->created_at->toIso8601String() : '' }}">{{ $msgCreatedAt ? $msgCreatedAt->format('H:i') : '-' }}</span>
                            </div>
                        @elseif ($msg->sender_type === 'bot')
                            <!-- Bot Bubble -->
                            <div class="flex flex-col items-end self-end max-w-[85%] sm:max-w-[70%]"
                                data-id="{{ $msg->id }}" data-date-key="{{ $msgDateKey }}" data-created-at="{{ $msg->created_at ? $msg->created_at->toIso8601String() : '' }}">
                                <span class="text-[10.5px] text-indigo-600 font-medium mb-0.5 pr-1 flex items-center gap-1">
                                    <span>🤖 {{ $msg->sender_name ?: 'BeanBot' }}</span>
                                    <span class="text-[9px] px-1 py-0.2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded font-semibold">BOT AUTO</span>
                                </span>
                                <div class="bubble-bot bg-indigo-600 text-white px-3 py-2 text-[12.5px] rounded-2xl shadow-apple-sm leading-relaxed" style="border-bottom-right-radius: 4px;">
                                    {{ $msg->content }}
                                </div>
                                <span class="msg-time-display text-[9.5px] text-apple-textTertiary mt-0.5 pr-1 font-mono" data-created-at="{{ $msg->created_at ? $msg->created_at->toIso8601String() : '' }}">{{ $msgCreatedAt ? $msgCreatedAt->format('H:i') : '-' }} • Bot Replied</span>
                            </div>
                        @else
                            <!-- Agent Bubble -->
                            <div class="flex flex-col items-end self-end max-w-[85%] sm:max-w-[70%]"
                                data-id="{{ $msg->id }}" data-date-key="{{ $msgDateKey }}" data-created-at="{{ $msg->created_at ? $msg->created_at->toIso8601String() : '' }}">
                                <span class="text-[10.5px] text-apple-textTertiary mb-0.5 pr-1">{{ $msg->sender_name ?: $msg->user->name ?? 'Staff CS' }}</span>
                                <div class="bubble-agent bg-apple-blue text-white px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed">
                                    {{ $msg->content }}
                                </div>
                                <span class="msg-time-display text-[9.5px] text-apple-textTertiary mt-0.5 pr-1 font-mono" data-created-at="{{ $msg->created_at ? $msg->created_at->toIso8601String() : '' }}">{{ $msgCreatedAt ? $msgCreatedAt->format('H:i') : '-' }} • Sent</span>
                            </div>
                        @endif
                    @empty
                        <div class="flex-1 flex flex-col items-center justify-center text-apple-textTertiary p-6 text-center">
                            <p class="text-[12px]">Belum ada pesan dalam percakapan ini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Modern Mobile & Desktop Chat Composer -->
                <div class="p-2 sm:p-3 border-t border-apple-border glass-acrylic shrink-0 bg-white z-10">
                    <form id="replyForm" onsubmit="handleSendReply(event)" class="m-0">
                        <div class="flex flex-col gap-1.5">
                            <!-- Quick Canned Response Chips -->
                            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-[10.5px] text-apple-textTertiary py-0.5">
                                <span class="text-[9.5px] font-medium text-apple-textTertiary uppercase shrink-0">Canned:</span>
                                <button type="button" onclick="insertCannedResponse('/shipping')"
                                    class="hover:text-apple-textPrimary font-mono px-1.5 py-0.5 rounded bg-black/5 hover:bg-black/10 transition shrink-0 cursor-pointer">/shipping</button>
                                <button type="button" onclick="insertCannedResponse('/stock')"
                                    class="hover:text-apple-textPrimary font-mono px-1.5 py-0.5 rounded bg-black/5 hover:bg-black/10 transition shrink-0 cursor-pointer">/stock</button>
                                <button type="button" onclick="insertCannedResponse('/greeting')"
                                    class="hover:text-apple-textPrimary font-mono px-1.5 py-0.5 rounded bg-black/5 hover:bg-black/10 transition shrink-0 cursor-pointer">/greeting</button>
                            </div>

                            <!-- Textarea & Prominent Send Button -->
                            <div class="flex items-end gap-1.5 bg-apple-canvas/70 border border-apple-border rounded-xl p-1 focus-within:ring-2 focus-within:ring-apple-blue/20 focus-within:border-apple-blue focus-within:bg-white transition shadow-2xs">
                                <textarea id="replyInput" rows="1"
                                    placeholder="Balas {{ $activeDisplayName }} (Enter kirim, Shift+Enter baris baru)..."
                                    onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); handleSendReply(event); }"
                                    class="flex-1 max-h-24 min-h-[36px] py-1.5 px-2 text-[12.5px] text-apple-textPrimary placeholder:text-apple-textTertiary resize-none focus:outline-none bg-transparent leading-relaxed"
                                    required></textarea>
                                
                                <button type="submit" id="btnSubmitReply"
                                    class="h-9 px-3 bg-apple-blue text-white rounded-lg font-medium text-[11.5px] flex items-center justify-center gap-1.5 hover:bg-apple-blueHover active:scale-95 transition shrink-0 shadow-apple-sm cursor-pointer"
                                    title="Kirim Pesan">
                                    <span>Kirim</span>
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="22" y1="2" x2="11" y2="13"></line>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <!-- Empty Placeholder When No Chat Selected -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-apple-textTertiary">
                    <div
                        class="w-12 h-12 rounded-2xl bg-apple-canvas flex items-center justify-center mb-3 text-apple-textTertiary">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-apple-textPrimary">Pilih Percakapan</h3>
                    <p class="text-[12px] text-apple-textSecondary max-w-sm mt-1">Pilih salah satu tiket percakapan dari
                        daftar di sebelah kiri untuk melihat pesan dan mulai membalas.</p>
                </div>
            @endif
        </section>

        <!-- Resizer Handle 2 (Between Chat Feed and Customer Context) -->
        @if ($activeConversation)
            <div id="resizer-chat-context" class="panel-resizer hidden lg:block"
                title="Drag to resize customer context column (Double click to reset)"></div>

            <!-- Mobile Inspector Backdrop Overlay -->
            <div id="inspectorBackdrop" class="fixed inset-0 bg-black/30 backdrop-blur-xs z-30 lg:hidden hidden"
                onclick="closeInspectorMobile()"></div>

            <!-- ========================================== -->
            <!-- PANE 3: Customer Context Session           -->
            <!-- ========================================== -->
            <aside id="pane-context-inspector"
                class="fixed lg:static inset-y-0 right-0 z-40 lg:z-10 w-80 lg:w-72 border-l border-apple-border bg-white flex flex-col shrink-0 overflow-hidden select-none transition-transform lg:transition-[width] duration-200 ease-out hidden lg:flex shadow-2xl lg:shadow-none"
                style="min-width: 48px; max-width: 420px;">

                <!-- Mode A: Full Inspector Content -->
                <div class="inspector-full-content flex-1 flex flex-col h-full overflow-hidden">
                    <div
                        class="h-12 px-3 border-b border-apple-border flex items-center justify-between shrink-0 bg-white">
                        <span class="font-semibold text-[12.5px] text-apple-textPrimary">Customer Session</span>
                        <div class="flex items-center gap-1.5">
                            <span
                                class="text-[9.5px] font-mono text-apple-green bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                Live
                            </span>
                            <!-- Close button on Mobile/Tablet -->
                            <button type="button" onclick="closeInspectorMobile()"
                                class="lg:hidden p-1.5 rounded-md text-apple-textTertiary hover:text-apple-textPrimary hover:bg-black/5"
                                title="Tutup Detail">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                            <!-- Minimize button on Desktop -->
                            <button type="button" onclick="toggleInspectorMode()"
                                class="hidden lg:inline-flex p-1 rounded text-apple-textTertiary hover:text-apple-textPrimary hover:bg-black/5"
                                title="Minimize Panel">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="13 17 18 12 13 7" />
                                    <polyline points="6 17 11 12 6 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-3 flex flex-col gap-3.5 text-[11.5px]">
                        <!-- Customer Profile -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span
                                    class="text-[9.5px] font-semibold uppercase tracking-wider text-apple-textTertiary">Customer
                                    Profile</span>
                                <button type="button" onclick="promptEditCustomerName()"
                                    class="text-[10px] text-apple-blue hover:underline font-medium">Ubah Nama</button>
                            </div>
                            <div
                                class="rounded-xl border border-apple-border/80 bg-apple-canvas/40 p-2.5 flex flex-col gap-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="text-apple-textSecondary">Full Name</span>
                                    <span id="contextCustomerName"
                                        class="font-medium text-apple-textPrimary">{{ $activeConversation->visitor->display_name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-apple-textSecondary">Email</span>
                                    <span
                                        class="font-medium text-apple-textPrimary truncate max-w-[130px]">{{ $activeConversation->visitor->email ?? 'Belum ada' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-apple-textSecondary">Customer Code</span>
                                    <span
                                        class="font-mono text-[10px] text-apple-textTertiary">{{ $activeConversation->visitor->customer_code_formatted }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Live Browsing State -->
                        <div>
                            <div
                                class="text-[9.5px] font-semibold uppercase tracking-wider text-apple-textTertiary mb-1.5">
                                Live Session State</div>
                            <div
                                class="rounded-xl border border-apple-border/80 bg-apple-canvas/40 p-2.5 flex flex-col gap-1.5">
                                <div>
                                    <div class="text-apple-textSecondary text-[10.5px]">Origin Channel</div>
                                    <div class="font-semibold text-apple-textPrimary truncate flex items-center gap-1.5 mt-0.5">
                                        <span class="w-2 h-2 rounded-full inline-block shrink-0" style="background-color: {{ $activeProjectColor }};"></span>
                                        <span>{{ $activeConversation->project->name }}</span>
                                        <span class="text-[9px] font-semibold uppercase px-1 py-0.2 rounded" style="background-color: {{ $activeProjectColor }}14; color: {{ $activeProjectColor }};">{{ $activeConversation->channel_label }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-apple-textSecondary text-[10.5px]">Active Path</div>
                                    <div class="font-mono text-[10px] text-apple-blue break-all">
                                        {{ $activeConversation->page_url ?? '/' }}</div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-apple-textSecondary">Status Tiket</span>
                                    <span
                                        class="font-semibold {{ $activeConversation->status === 'open' ? 'text-apple-green' : 'text-apple-textTertiary' }} uppercase text-[10px]">{{ $activeConversation->status }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Device Telemetry -->
                        <div>
                            <div
                                class="text-[9.5px] font-semibold uppercase tracking-wider text-apple-textTertiary mb-1.5">
                                Device Telemetry</div>
                            <div
                                class="rounded-xl border border-apple-border/80 bg-apple-canvas/40 p-2.5 flex flex-col gap-1 text-[11px]">
                                <div class="flex justify-between">
                                    <span class="text-apple-textSecondary">IP Address</span>
                                    <span
                                        class="font-mono text-apple-textTertiary">{{ $activeConversation->visitor->ip_address ?? '127.0.0.1' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-apple-textSecondary">Visitor UUID</span>
                                    <span
                                        class="font-mono text-apple-textTertiary text-[10px]">#{{ substr($activeConversation->visitor->visitor_uuid ?? 'anon', 0, 12) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-apple-textSecondary">User Agent</span>
                                    <span class="text-apple-textPrimary truncate max-w-[130px]"
                                        title="{{ $activeConversation->visitor->user_agent }}">{{ Str::limit($activeConversation->visitor->user_agent ?? 'Web Browser', 22) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-apple-textSecondary">Terakhir Aktif</span>
                                    <span
                                        class="text-apple-textPrimary">{{ $activeConversation->visitor->last_seen_at ? $activeConversation->visitor->last_seen_at->diffForHumans() : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mode B: Minimalist Compact Rail (Icon-Only strip when minimized) -->
                <div class="inspector-compact-rail flex-1 hidden flex-col items-center py-3 gap-3 bg-apple-canvas/40">
                    <button type="button" onclick="toggleInspectorMode()"
                        class="p-1.5 rounded-lg hover:bg-black/5 text-apple-textSecondary"
                        title="Expand Customer Session">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="11 17 6 12 11 7" />
                            <polyline points="18 17 13 12 18 7" />
                        </svg>
                    </button>
                    <div class="w-7 h-7 rounded-full bg-white border border-apple-border flex items-center justify-center font-semibold text-[10px] text-apple-textPrimary shadow-2xs"
                        title="{{ $activeDisplayName }}">
                        {{ $activeInitials }}
                    </div>
                    <div class="w-2 h-2 rounded-full bg-apple-green" title="Session Active"></div>
                    <span class="text-[9px] font-mono font-medium text-apple-blue rotate-90 my-2">LIVE</span>
                </div>

            </aside>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        const activeConversationId = {{ $activeConversation ? $activeConversation->id : 'null' }};
        const currentUserName = "{{ Auth::user()->name }}";
        let initialLastMessageId =
            {{ $activeConversation && $activeConversation->messages->isNotEmpty() ? $activeConversation->messages->last()->id : 0 }};
        let initialMaxTenantMessageId = {{ $maxMessageId ?? 0 }};
        let conversationStatus = "{{ $activeConversation ? $activeConversation->status : 'open' }}";

        // Canned Response Shortcut Helper
        function insertCannedResponse(shortcut) {
            const textarea = document.getElementById('replyInput');
            if (!textarea) return;
            if (shortcut === '/shipping') {
                textarea.value = 'Pesanan sebelum jam 15:00 WIB dikirim pada hari yang sama!';
            } else if (shortcut === '/stock') {
                textarea.value = 'Stok produk ini siap kirim dari gudang pusat Surabaya!';
            } else if (shortcut === '/greeting') {
                textarea.value = 'Halo! Ada yang bisa kami bantu seputar produk dan pesanan Anda?';
            }
            textarea.focus();
        }

        // Instant filter search on client side
        function handleSearchConv(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('#convListContainer .conv-row').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? 'block' : 'none';
            });
        }

        // Mobile navigation switch
        function mobileBackToList() {
            const convList = document.getElementById('pane-conv-list');
            const chatPane = document.getElementById('pane-chat-thread');
            const inspector = document.getElementById('pane-context-inspector');
            const inspectorBackdrop = document.getElementById('inspectorBackdrop');
            const bottomNav = document.getElementById('mobile-bottom-nav');

            if (convList) {
                convList.classList.remove('hidden');
                convList.classList.add('flex');
            }
            if (chatPane) {
                chatPane.classList.add('hidden');
                chatPane.classList.remove('flex');
            }
            if (inspector) {
                inspector.classList.remove('open-mobile', 'flex');
                inspector.classList.add('hidden');
            }
            if (inspectorBackdrop) {
                inspectorBackdrop.classList.add('hidden');
            }
            if (bottomNav) {
                bottomNav.classList.remove('hidden');
                bottomNav.classList.add('flex');
            }
            if (window.history && window.history.pushState) {
                window.history.pushState(null, '',
                    '{{ route('admin.inbox', ['project_id' => request('project_id'), 'status' => request('status')]) }}'
                    );
            }
        }
    </script>
    <script src="{{ asset('js/inbox.js') }}?v={{ file_exists(public_path('js/inbox.js')) ? filemtime(public_path('js/inbox.js')) : time() }}"></script>
@endpush
