@php $sort = $sort = new \App\Support\SortLink('last_seen_at'); @endphp
<!-- 1. FULL TABLE VIEW (With smooth native momentum scroll on tablet and mobile) -->
<div id="visitorsTableView" class="w-full overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
    <table class="w-full text-left text-[11.5px] sm:text-[12px] border-collapse min-w-[760px] md:min-w-[900px]">
        <thead class="bg-apple-canvas/80 text-apple-textSecondary text-[10px] sm:text-[10.5px] font-semibold uppercase tracking-wider border-b border-apple-border select-none sticky top-0 z-10 backdrop-blur-xs">
            <tr>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-52 sm:w-60">
                    <a href="{{ $sort->url('name') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan nama">
                        <span>Identitas Pengunjung</span> {{ $sort->icon('name') }}
                    </a>
                </th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-40">
                    <a href="{{ $sort->url('last_seen_at') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan aktivitas">
                        <span>Kehadiran &amp; Chat</span> {{ $sort->icon('last_seen_at') }}
                    </a>
                </th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-40">Website / Channel</th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 min-w-[180px]">Halaman Terakhir</th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-36">Perangkat &amp; OS</th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-28">
                    <a href="{{ $sort->url('ip_address') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan IP">
                        <span>IP Address</span> {{ $sort->icon('ip_address') }}
                    </a>
                </th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-24 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-apple-subtleBorder">
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
                <tr class="hover:bg-black/[0.015] transition group">
                    <!-- 1. Visitor Identity -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <div class="flex items-center gap-2.5">
                            <div class="relative shrink-0">
                                <div class="w-8 h-8 rounded-full bg-apple-canvas border border-apple-border text-[10px] font-bold text-apple-textSecondary flex items-center justify-center shadow-2xs">
                                    {{ $initials }}
                                </div>
                                @if($isOnline)
                                    <span class="w-2.5 h-2.5 rounded-full bg-apple-green border-2 border-white absolute -bottom-0.5 -right-0.5"></span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-apple-textPrimary truncate max-w-[170px]" title="{{ $v->name ?: $v->display_name }}">
                                    {{ $v->display_name }}
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono text-[9.5px] px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary border border-apple-border/60">
                                        {{ $v->customer_code_formatted }}
                                    </span>
                                    @if($v->email)
                                        <a href="mailto:{{ $v->email }}" class="text-[10px] text-apple-blue hover:underline truncate max-w-[110px]" title="{{ $v->email }}">
                                            {{ $v->email }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- 2. Presence & Chats -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        @if($isOnline)
                            <span class="inline-flex items-center gap-1 text-[10px] font-mono px-2 py-0.5 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
                                Online
                            </span>
                        @else
                            <div class="text-[11px] text-apple-textSecondary">
                                {{ $v->last_seen_at ? $v->last_seen_at->diffForHumans() : 'Belum aktif' }}
                            </div>
                            <div class="font-mono text-[9.5px] text-apple-textTertiary mt-0.5">
                                {{ $v->last_seen_at ? $v->last_seen_at->format('d M H:i') : '-' }}
                            </div>
                        @endif

                        <div class="mt-1">
                            @if($convCount > 0)
                                <a href="{{ route('admin.inbox', ['search' => $v->customer_code_formatted]) }}"
                                    class="inline-flex items-center gap-1 text-[10px] font-medium px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <span>{{ $convCount }} chat</span>
                                </a>
                            @else
                                <span class="text-[9.5px] text-apple-textTertiary font-mono">Tanpa chat</span>
                            @endif
                        </div>
                    </td>

                    <!-- 3. Website Origin -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <div class="font-medium text-apple-textPrimary truncate max-w-[130px]" title="{{ $v->project->name ?? 'Default Project' }}">
                            {{ $v->project->name ?? 'Website' }}
                        </div>
                        <div class="font-mono text-[10px] text-apple-textTertiary truncate max-w-[130px]">
                            {{ $v->project->slug ?? '-' }}
                        </div>
                    </td>

                    <!-- 4. Last Visited Page -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        @if($pageUrl || $pageTitle)
                            <div class="font-medium text-apple-textPrimary truncate max-w-[200px]" title="{{ $pageTitle ?: $pageUrl }}">
                                {{ $pageTitle ?: 'Halaman Web' }}
                            </div>
                            @if($pageUrl)
                                <a href="{{ $pageUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="text-[10px] font-mono text-apple-blue hover:underline truncate max-w-[200px] block mt-0.5" title="{{ $pageUrl }}">
                                    {{ $pageUrl }}
                                </a>
                            @endif
                        @else
                            <span class="text-apple-textTertiary text-[11px]">-</span>
                        @endif
                    </td>

                    <!-- 5. Device & OS -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <div class="flex items-center gap-1.5">
                            @if($devType === 'mobile')
                                <span class="text-[12px]" title="Mobile Smartphone">📱</span>
                            @elseif($devType === 'tablet')
                                <span class="text-[12px]" title="Tablet / iPad">📟</span>
                            @else
                                <span class="text-[12px]" title="Komputer Desktop">💻</span>
                            @endif
                            <span class="text-[11px] font-medium text-apple-textPrimary">{{ $v->os_name }}</span>
                        </div>
                        <div class="text-[10px] font-mono text-apple-textTertiary mt-0.5 truncate max-w-[120px]">
                            {{ $v->browser_name }}
                        </div>
                    </td>

                    <!-- 6. IP Address -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <span class="font-mono text-[10.5px] text-apple-textSecondary truncate max-w-[110px] block" title="{{ $v->ip_address ?? '127.0.0.1' }}">
                            {{ $v->ip_address ?? '127.0.0.1' }}
                        </span>
                    </td>

                    <!-- 7. Actions -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top text-right">
                        <button type="button" onclick="showVisitorDetail({{ $v->id }})"
                            class="px-2.5 py-1 rounded-lg border border-apple-border hover:bg-black/5 text-apple-textPrimary transition text-[11px] font-medium shadow-2xs inline-flex items-center gap-1">
                            <span>Jejak</span>
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-16 text-center text-apple-textTertiary">
                        <div class="w-10 h-10 rounded-full bg-black/5 mx-auto flex items-center justify-center text-apple-textTertiary mb-2">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <div class="font-medium text-apple-textSecondary text-[13px]">Tidak ada data pengunjung yang cocok dengan filter.</div>
                        <div class="text-[11.5px] mt-1">Coba ubah kata kunci pencarian atau reset filter di atas.</div>
                        <a href="{{ route('admin.visitors') }}" class="inline-block mt-3 px-3 py-1.5 rounded-lg border border-apple-border bg-white text-apple-textPrimary hover:bg-black/5 text-[11.5px] font-medium transition shadow-2xs">
                            Reset Semua Filter
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
