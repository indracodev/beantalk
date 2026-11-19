<!-- ========================================== -->
<section id="pane-conv-list"
    class="w-full md:w-80 {{ $isExplicitChat ? 'hidden md:flex' : 'flex' }} border-r border-apple-border flex-col glass-sidebar shrink-0 z-10 overflow-hidden"
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
            @php
                $activeStatusKey = $currentStatus ?? request('status', 'open');
                $badgeLabel = ($counts['open'] ?? 0) . ' Aktif';
                if ($activeStatusKey === 'mine') {
                    $badgeLabel = ($counts['mine'] ?? 0) . ' Mine';
                } elseif ($activeStatusKey === 'closed') {
                    $badgeLabel = ($counts['closed'] ?? 0) . ' Selesai';
                } elseif ($activeStatusKey === 'all') {
                    $badgeLabel = ($counts['all'] ?? 0) . ' Semua';
                }
            @endphp
            <span id="inboxActiveFilterCountBadge"
                data-active-status="{{ $activeStatusKey }}"
                class="text-[10.5px] font-medium text-apple-textSecondary bg-white px-2 py-0.2 rounded-full border border-apple-border/70 shadow-2xs">
                {{ $badgeLabel }}
            </span>
        </div>

        <!-- Search input -->
        <form action="{{ route('admin.inbox') }}" method="GET" class="relative m-0">
            @if (request('project_id'))
                <input type="hidden" name="project_id" value="{{ request('project_id') }}">
            @endif
            <input type="hidden" name="status" value="{{ $activeStatusKey }}">
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
                <a href="{{ route('admin.inbox', array_filter(['project_id' => request('project_id'), 'status' => request('status')])) }}"
                    class="absolute right-2 top-1.5 text-apple-textTertiary hover:text-apple-textPrimary text-[13px]">&times;</a>
            @endif
        </form>
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
            <a href="{{ route('admin.inbox', array_filter(['id' => $conv->id, 'status' => request('status'), 'project_id' => request('project_id')])) }}" data-conv-id="{{ $conv->id }}"
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
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="conv-name font-semibold text-apple-textPrimary truncate text-[12.5px]"
                                    title="{{ $displayName }}">
                                    {{ $displayName }}
                                </span>
                                <span class="conv-ticket-badge text-[9.5px] font-mono font-semibold px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary border border-black/10 shrink-0">
                                    #{{ $conv->id }}
                                </span>
                            </div>
                            <span
                                class="conv-time text-[10.5px] text-apple-textTertiary font-mono" data-timestamp="{{ $conv->last_message_at ? $conv->last_message_at->toIso8601String() : '' }}">{{ $timeHuman }}</span>
                        </div>
                        <div class="conv-meta-row flex items-center gap-1.5 mb-1 flex-wrap" data-conv-meta>
                            <span
                                class="conv-site text-[9px] font-semibold tracking-tight uppercase px-1.5 py-0.5 rounded truncate max-w-[95px]"
                                style="background-color: {{ $projectColor }}14; color: {{ $projectColor }}; border: 1px solid {{ $projectColor }}30;">
                                {{ $siteName }}
                            </span>
                            <span
                                class="conv-cust-code text-[10px] text-apple-textTertiary font-mono">{{ $customerCode }}</span>
                            <span class="conv-status-pill text-[9px] font-semibold px-1.5 py-0.2 rounded border shrink-0 {{ $conv->status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' }}">
                                {{ $conv->status === 'open' ? 'Open' : 'Selesai' }}
                            </span>
                            @if (isset($visitorTicketCounts[$conv->visitor_id]) && $visitorTicketCounts[$conv->visitor_id] > 1)
                                <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-200 shrink-0" title="{{ $visitorTicketCounts[$conv->visitor_id] }} tiket total dari pelanggan ini">
                                    {{ $visitorTicketCounts[$conv->visitor_id] }} Tiket
                                </span>
                            @endif
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
