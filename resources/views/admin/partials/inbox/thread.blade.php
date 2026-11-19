<!-- ========================================== -->
<section id="pane-chat-thread"
    class="{{ $isExplicitChat ? 'flex' : 'hidden' }} md:flex flex-1 flex-col h-full bg-white relative overflow-hidden min-w-0 md:min-w-[320px]">
    @if ($activeConversation)

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
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h3 id="threadCustomerName" class="font-bold text-[13px] text-white truncate drop-shadow-xs">
                            {{ $activeDisplayName }}
                        </h3>
                        <span class="text-[9.5px] font-mono font-bold px-2 py-0.5 rounded-full bg-white/20 text-white border border-white/30 backdrop-blur-xs">
                            Tiket #{{ $activeConversation->id }}
                        </span>
                        <span id="threadStatusPill" class="text-[9px] font-bold tracking-wider uppercase px-2 py-0.5 rounded-full {{ $activeConversation->status === 'open' ? 'bg-emerald-400/30 text-emerald-100 border border-emerald-300/40' : 'bg-white/20 text-white/80 border border-white/30' }}">
                            {{ $activeConversation->status === 'open' ? 'OPEN' : 'SELESAI' }}
                        </span>
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
