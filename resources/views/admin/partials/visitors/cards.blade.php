<!-- 2. RESPONSIVE CARD GRID VIEW (Optimized for Mobile & Tablet iPads) -->
<div id="visitorsCardsView" class="hidden p-3 sm:p-4 bg-apple-canvas/20">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
        @forelse($visitors as $v)
            @php
                $isOnline = $v->is_online;
                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $v->name ?: 'TM'), 0, 2)) ?: 'TM';
                $convCount = $v->conversations_count ?? 0;
                $latestConv = $v->conversations->first();
                $pageUrl = $latestConv->page_url ?? null;
                $pageTitle = $latestConv->page_title ?? null;
                $devType = $v->device_type;
            @endphp
            <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs hover:shadow-apple-sm transition flex flex-col justify-between gap-3">
                <!-- Top Header -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-full bg-apple-canvas border border-apple-border text-[11px] font-bold text-apple-textSecondary flex items-center justify-center shadow-2xs">
                                {{ $initials }}
                            </div>
                            @if($isOnline)
                                <span class="w-2.5 h-2.5 rounded-full bg-apple-green border-2 border-white absolute -bottom-0.5 -right-0.5"></span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-semibold text-apple-textPrimary text-[12.5px] leading-tight truncate">
                                {{ $v->display_name }}
                            </h4>
                            <span class="font-mono text-[9px] px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary border border-apple-border/60 inline-block mt-0.5">
                                {{ $v->customer_code_formatted }}
                            </span>
                        </div>
                    </div>

                    @if($isOnline)
                        <span class="inline-flex items-center gap-1 text-[9.5px] font-mono px-2 py-0.5 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 font-semibold shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
                            Live
                        </span>
                    @else
                        <span class="font-mono text-[10px] text-apple-textTertiary shrink-0">
                            {{ $v->last_seen_at ? $v->last_seen_at->diffForHumans() : '-' }}
                        </span>
                    @endif
                </div>

                <!-- Card Body: Channel & Page Footprint -->
                <div class="bg-apple-canvas/40 p-2.5 rounded-lg flex flex-col gap-1.5 text-[11.5px]">
                    <div class="flex items-center justify-between text-[10.5px]">
                        <span class="text-apple-textTertiary">Channel:</span>
                        <span class="font-medium text-apple-textPrimary truncate max-w-[160px]">{{ $v->project->name ?? 'Default' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[10.5px]">
                        <span class="text-apple-textTertiary">Perangkat:</span>
                        <span class="text-apple-textSecondary font-mono">{{ $v->os_name }} • {{ $v->browser_name }}</span>
                    </div>
                    @if($pageUrl || $pageTitle)
                        <div class="pt-1 border-t border-apple-subtleBorder text-[10px]">
                            <span class="text-apple-textTertiary block">Halaman Terakhir:</span>
                            <span class="text-apple-textPrimary font-medium truncate block" title="{{ $pageTitle ?: $pageUrl }}">{{ $pageTitle ?: $pageUrl }}</span>
                        </div>
                    @endif
                </div>

                <!-- Card Footer -->
                <div class="flex items-center justify-between pt-1 border-t border-apple-subtleBorder text-[11px]">
                    <span class="font-mono text-[10px] text-apple-textTertiary truncate max-w-[120px]">
                        IP: {{ $v->ip_address ?? '127.0.0.1' }}
                    </span>

                    <div class="flex items-center gap-1.5">
                        @if($convCount > 0)
                            <a href="{{ route('admin.inbox', ['search' => $v->customer_code_formatted]) }}"
                                class="px-2 py-1 rounded-md bg-purple-50 text-purple-700 border border-purple-200 font-medium text-[10.5px] hover:bg-purple-100 transition inline-flex items-center gap-1">
                                <span>Chat ({{ $convCount }})</span>
                            </a>
                        @endif
                        <button type="button" onclick="showVisitorDetail({{ $v->id }})"
                            class="px-2.5 py-1 rounded-md border border-apple-border bg-white text-apple-textPrimary font-medium text-[10.5px] hover:bg-black/5 transition shadow-2xs">
                            Detail
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-8 text-center text-apple-textTertiary bg-white rounded-xl border border-apple-border">
                Belum ada data pengunjung terlacak yang cocok dengan filter.
            </div>
        @endforelse
    </div>
</div>
