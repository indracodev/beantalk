@extends('layouts.admin')

@section('title', 'Live Inbox')
@section('header_title', 'Inbox Messages')


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
            <div class="p-3 border-b border-apple-border flex flex-col gap-2 shrink-0 glass-sidebar">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-[14px] font-semibold tracking-tight text-apple-textPrimary">Messages</h2>
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
                            substr(preg_replace('/[^a-zA-Z0-9]/', '', $conv->visitor->name ?: 'Tamu'), 0, 2),
                        );
                        $channelLabel = $conv->channel_label ?? 'Web Chat';
                        $siteName = $conv->project->name ?? 'Website';
                        $siteSlug = $conv->project->slug ?? 'site';
                        $timeHuman = $conv->last_message_at
                            ? \Carbon\Carbon::parse($conv->last_message_at)->format('H:i')
                            : '-';
                    @endphp
                    <a href="{{ route('admin.inbox', $conv->id) }}" data-conv-id="{{ $conv->id }}"
                        data-visitor-id="{{ $conv->visitor_id }}"
                        data-site="{{ $conv->project_id }}" id="card-conv-{{ $conv->id }}"
                        class="conv-row conv-item no-loader w-full block px-2.5 py-2 rounded-lg transition {{ $isConvActive ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal border border-transparent' }}">
                        <div class="flex gap-2.5 items-start">
                            <div class="relative shrink-0">
                                <div
                                    class="conv-avatar w-8 h-8 rounded-full bg-[#E5E5EA] text-apple-textPrimary font-semibold text-[11px] flex items-center justify-center border border-black/5">
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
                                        class="conv-time text-[10.5px] text-apple-textTertiary font-mono">{{ $timeHuman }}</span>
                                </div>
                                <div class="conv-meta-row flex items-center gap-1.5 mb-1" data-conv-meta>
                                    <span
                                        class="conv-site text-[9px] font-medium tracking-tight uppercase px-1.5 py-0.2 rounded bg-neutral-200/70 text-neutral-800 truncate max-w-[110px]">
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
                        substr(preg_replace('/[^a-zA-Z0-9]/', '', $activeConversation->visitor->name ?: 'Tamu'), 0, 2),
                    );
                    $activeSiteName = $activeConversation->project->name ?? 'Website';
                    $assignedStaffName = $activeConversation->assignedUser->name ?? 'Unassigned';
                @endphp

                <!-- Thread Toolbar -->
                <div class="h-11 md:h-12 border-b border-apple-border glass-acrylic px-2.5 md:px-3.5 flex items-center justify-between z-10 shrink-0 gap-2">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <button type="button" onclick="mobileBackToList()"
                            class="md:hidden p-1.5 -ml-1 rounded-lg text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 flex items-center justify-center shrink-0 cursor-pointer active:scale-95 transition"
                            title="Kembali ke Daftar Chat">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </button>

                        <div class="relative shrink-0">
                            <div id="threadCustomerAvatar"
                                class="w-7 h-7 rounded-full bg-neutral-200 text-apple-textPrimary font-semibold text-[11px] flex items-center justify-center border border-black/5">
                                {{ $activeInitials }}
                            </div>
                            <span class="w-2 h-2 rounded-full bg-apple-green absolute bottom-0 right-0 ring-1 ring-white"></span>
                        </div>

                        <div class="truncate min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <h3 id="threadCustomerName" class="font-semibold text-[12.5px] text-apple-textPrimary truncate">
                                    {{ $activeDisplayName }}
                                </h3>
                                <span id="threadOriginBadge" class="hidden sm:inline-block text-[9px] font-medium tracking-tight uppercase px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary truncate max-w-[90px]">
                                    {{ $activeSiteName }}
                                </span>
                            </div>
                            <div id="threadMetaSub" class="text-[10px] text-apple-textTertiary truncate">
                                {{ $activeCustomerCode }} • CS: <span id="assignedStaffText">{{ $assignedStaffName }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Right -->
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- Assign Dropdown -->
                        <div class="relative">
                            <select id="assignCsSelect" onchange="handleAssign(this.value)"
                                class="max-w-[105px] sm:max-w-[130px] px-1.5 py-1 text-[10.5px] rounded-lg border border-apple-border bg-white text-apple-textPrimary hover:bg-apple-canvas focus:outline-none focus:ring-1 focus:ring-apple-blue font-medium shadow-2xs cursor-pointer truncate">
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
                            class="px-2 py-1 text-[10.5px] rounded-lg border border-apple-border/80 bg-white {{ $activeConversation->status === 'open' ? 'text-apple-red hover:bg-red-50' : 'text-apple-green hover:bg-emerald-50' }} active:scale-95 transition font-medium shadow-2xs">
                            <span>{{ $activeConversation->status === 'open' ? 'Resolve' : 'Reopen' }}</span>
                        </button>

                        <!-- Inspector Toggle for Customer Context -->
                        <button type="button" onclick="toggleInspectorMode()"
                            class="p-1.5 rounded-lg border border-apple-border bg-white text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas transition shadow-2xs"
                            title="Detail Customer">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 16v-4" />
                                <path d="M12 8h.01" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Active Page Context Sticky Banner -->
                <div class="bg-apple-canvas/80 border-b border-apple-border/60 px-3 py-1 flex items-center justify-between text-[10.5px] shrink-0">
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-apple-textTertiary font-medium shrink-0">Page:</span>
                        <span class="font-medium text-apple-textPrimary flex items-center gap-1 truncate">
                            <svg class="w-3 h-3 text-apple-blue shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                            <span id="activePageTitle" class="truncate">{{ $activeConversation->page_title ?? 'Halaman Toko' }}</span>
                        </span>
                    </div>
                    <span class="text-apple-blue font-medium shrink-0 ml-2 text-[10px]">● Live</span>
                </div>

                <!-- Message History Feed -->
                <div id="chatThreadBody" class="flex-1 min-h-0 overflow-y-auto p-3 sm:p-4 flex flex-col gap-2.5 bg-apple-canvas/20">
                    <div class="flex items-center justify-center my-1">
                        <span class="text-[10px] text-apple-textTertiary font-mono px-2 py-0.5 rounded-full bg-black/5">
                            {{ \Carbon\Carbon::parse($activeConversation->created_at)->format('d M Y, H:i') }}
                        </span>
                    </div>

                    @forelse($activeConversation->messages as $msg)
                        @if ($msg->sender_type === 'visitor')
                            <!-- Visitor Bubble -->
                            <div class="flex flex-col items-start max-w-[85%] sm:max-w-[70%]"
                                data-id="{{ $msg->id }}">
                                <span class="text-[10.5px] text-apple-textTertiary mb-0.5 pl-1">{{ $msg->sender_name ?: $activeDisplayName }} (Visitor)</span>
                                <div class="bubble-visitor bg-white border border-apple-border/80 text-apple-textPrimary px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed">
                                    {{ $msg->content }}
                                </div>
                                <span class="text-[9.5px] text-apple-textTertiary mt-0.5 pl-1 font-mono">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}</span>
                            </div>
                        @else
                            <!-- Agent Bubble -->
                            <div class="flex flex-col items-end self-end max-w-[85%] sm:max-w-[70%]"
                                data-id="{{ $msg->id }}">
                                <span class="text-[10.5px] text-apple-textTertiary mb-0.5 pr-1">{{ $msg->sender_name ?: $msg->user->name ?? 'Staff CS' }}</span>
                                <div class="bubble-agent bg-apple-blue text-white px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed">
                                    {{ $msg->content }}
                                </div>
                                <span class="text-[9.5px] text-apple-textTertiary mt-0.5 pr-1 font-mono">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }} • Sent</span>
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
                                    <div class="font-semibold text-apple-textPrimary truncate">
                                        {{ $activeConversation->project->name }}
                                        ({{ $activeConversation->channel_label }})</div>
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
