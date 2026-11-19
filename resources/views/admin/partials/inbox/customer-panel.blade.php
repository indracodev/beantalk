<!-- ========================================== -->
<aside id="pane-context-inspector"
    class="fixed lg:static inset-y-0 right-0 z-40 lg:z-10 w-80 lg:w-72 border-l border-apple-border bg-white flex flex-col shrink-0 overflow-hidden transition-transform lg:transition-[width] duration-200 ease-out hidden lg:flex shadow-2xl lg:shadow-none"
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

            <!-- Riwayat Semua Tiket dari Pelanggan Ini -->
            @php
                $allVisitorTickets = $customerAllTickets ?? collect();
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[9.5px] font-semibold uppercase tracking-wider text-apple-textTertiary">
                        Riwayat Tiket Pelanggan ({{ $allVisitorTickets->count() }})
                    </span>
                </div>
                <div class="flex flex-col gap-1.5">
                    @forelse($allVisitorTickets as $vt)
                        @php
                            $isVtActive = $vt->id === $activeConversation->id;
                        @endphp
                        <a href="{{ route('admin.inbox', $vt->id) }}"
                            class="rounded-xl border p-2 flex flex-col gap-1 transition no-loader {{ $isVtActive ? 'bg-apple-blue/5 border-apple-blue/40 ring-1 ring-apple-blue/20' : 'bg-apple-canvas/40 border-apple-border/80 hover:bg-apple-canvas hover:border-apple-border' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-[11px] {{ $isVtActive ? 'text-apple-blue' : 'text-apple-textPrimary' }}">
                                        Tiket #{{ $vt->id }}
                                    </span>
                                    @if($isVtActive)
                                        <span class="text-[8.5px] font-bold px-1.5 py-0.2 rounded bg-apple-blue text-white">Aktif</span>
                                    @endif
                                </div>
                                <span class="text-[9px] font-semibold uppercase px-1.5 py-0.2 rounded border {{ $vt->status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' }}">
                                    {{ $vt->status === 'open' ? 'Open' : 'Selesai' }}
                                </span>
                            </div>
                            <p class="text-[10.5px] text-apple-textSecondary truncate">
                                {{ $vt->last_message_preview ?: 'Percakapan baru...' }}
                            </p>
                            <div class="flex items-center justify-between text-[9.5px] text-apple-textTertiary font-mono">
                                <span>{{ $vt->created_at ? $vt->created_at->format('d M Y, H:i') : '-' }}</span>
                                <span>{{ $vt->channel_label }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="text-[11px] text-apple-textTertiary p-2 text-center bg-apple-canvas/40 rounded-xl border border-apple-border/60">Tidak ada tiket lain</div>
                    @endforelse
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
