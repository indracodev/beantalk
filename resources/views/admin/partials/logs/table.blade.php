@php $sort = $sort = new \App\Support\SortLink('created_at'); @endphp
<!-- 1. FULL TABLE VIEW (With smooth native momentum scroll on tablet and mobile) -->
<div id="logsTableView" class="w-full overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
    <table class="w-full text-left text-[11.5px] sm:text-[12px] border-collapse min-w-[700px] sm:min-w-[780px] md:min-w-[860px]">
        <thead class="bg-apple-canvas/80 text-apple-textSecondary text-[10px] sm:text-[10.5px] font-semibold uppercase tracking-wider border-b border-apple-border select-none sticky top-0 z-10 backdrop-blur-xs">
            <tr>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-32 sm:w-40">
                    <a href="{{ $sort->url('created_at') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan waktu">
                        <span>Waktu (WIB)</span> {{ $sort->icon('created_at') }}
                    </a>
                </th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-36 sm:w-48">
                    <a href="{{ $sort->url('user_name') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan aktor">
                        <span>Aktor / Pelaku</span> {{ $sort->icon('user_name') }}
                    </a>
                </th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-36 sm:w-44">
                    <a href="{{ $sort->url('action') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan event">
                        <span>Aksi / Event</span> {{ $sort->icon('action') }}
                    </a>
                </th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 min-w-[200px]">Deskripsi Aktivitas</th>
                <th class="px-3 sm:px-4 py-2.5 sm:py-3 w-28 sm:w-36">
                    <a href="{{ $sort->url('ip_address') }}" class="inline-flex items-center hover:text-apple-textPrimary transition" title="Klik untuk mengurutkan IP">
                        <span>IP Address</span> {{ $sort->icon('ip_address') }}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-apple-subtleBorder">
            @forelse($logs as $log)
                @php
                    $actionName = $log->action ?? 'unknown';
                    $isError = strpos($actionName, 'error') !== false ||
                               strpos($actionName, 'exceeded') !== false ||
                               strpos($actionName, 'UNAUTHORIZED') !== false ||
                               strpos($actionName, 'failed') !== false;

                    $isIntegration = strpos($actionName, 'integration') !== false;
                    $isWidget = strpos($actionName, 'widget') !== false;
                    $isTeam = strpos($actionName, 'team') !== false || strpos($actionName, 'role') !== false;
                    $isChat = strpos($actionName, 'message') !== false || strpos($actionName, 'chat') !== false;

                    // Badge styling
                    $badgeClass = 'bg-black/5 text-apple-textSecondary border-black/10';
                    if ($isError) {
                        $badgeClass = 'bg-red-50 text-apple-red border-red-200';
                    } elseif ($isIntegration) {
                        $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                    } elseif ($isWidget) {
                        $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                    } elseif ($isTeam) {
                        $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
                    } elseif ($isChat) {
                        $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                    }

                    // Role styling
                    $roleName = $log->user_role ?? 'system';
                    $roleBadge = 'bg-black/5 text-apple-textSecondary';
                    if ($roleName === 'superadmin') {
                        $roleBadge = 'bg-purple-100 text-purple-900 border border-purple-300/60 font-semibold';
                    } elseif ($roleName === 'agent') {
                        $roleBadge = 'bg-amber-100 text-amber-900 border border-amber-300/60 font-medium';
                    } elseif ($roleName === 'visitor') {
                        $roleBadge = 'bg-blue-100 text-blue-900 border border-blue-300/60 font-medium';
                    }

                    $actorInitials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $log->user_name ?: 'SYS'), 0, 2)) ?: 'SY';
                    $hasProps = !empty($log->properties) && is_array($log->properties);
                @endphp
                <tr class="hover:bg-black/[0.015] transition group">
                    <!-- Timestamp -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <div class="font-mono text-[11px] text-apple-textPrimary font-medium">
                            {{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }}
                        </div>
                        <div class="text-[10px] text-apple-textTertiary mt-0.5">
                            {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                        </div>
                    </td>

                    <!-- Actor -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-apple-canvas border border-apple-border text-[9.5px] font-semibold text-apple-textSecondary flex items-center justify-center shrink-0">
                                {{ $actorInitials }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium text-apple-textPrimary truncate max-w-[130px]" title="{{ $log->user_name ?? 'System' }}">
                                    {{ $log->user_name ?? 'System' }}
                                </div>
                                <span class="text-[9px] font-mono uppercase px-1.5 py-0.2 rounded mt-0.5 inline-block {{ $roleBadge }}">
                                    {{ $roleName }}
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- Event Code -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <span class="font-mono text-[10.5px] px-2 py-0.5 rounded border inline-block break-all {{ $badgeClass }}">
                            {{ $log->action }}
                        </span>
                    </td>

                    <!-- Description & JSON Payload Toggle -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <div class="text-apple-textPrimary leading-relaxed break-words">
                            {{ $log->description ?? '-' }}
                        </div>

                        @if($hasProps)
                            <div class="mt-1.5">
                                <button type="button" onclick="toggleLogPayload('tbl-{{ $log->id }}')"
                                    class="inline-flex items-center gap-1 text-[10.5px] font-mono text-apple-blue hover:text-apple-blueHover hover:underline">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="16 18 22 12 16 6"></polyline>
                                        <polyline points="8 6 2 12 8 18"></polyline>
                                    </svg>
                                    <span>Payload Data</span>
                                    <span id="payload-arrow-tbl-{{ $log->id }}" class="text-[8px] font-bold">▼</span>
                                </button>

                                <div id="payload-box-tbl-{{ $log->id }}" class="hidden mt-1.5 p-2 bg-[#1E1E1E] text-[#D4D4D4] rounded-lg font-mono text-[11px] overflow-x-auto max-w-xl shadow-inner">
                                    <pre class="m-0 leading-tight"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </div>
                            </div>
                        @endif
                    </td>

                    <!-- IP Address -->
                    <td class="px-3 sm:px-4 py-2.5 sm:py-3 align-top">
                        <div class="font-mono text-[11px] text-apple-textPrimary flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-apple-green shrink-0"></span>
                            <span>{{ $log->ip_address ?? '127.0.0.1' }}</span>
                        </div>
                        @if($log->user_agent)
                            <div class="text-[9.5px] text-apple-textTertiary truncate max-w-[120px] mt-0.5" title="{{ $log->user_agent }}">
                                {{ Str::limit($log->user_agent, 20) }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-16 text-center text-apple-textTertiary">
                        <div class="w-10 h-10 rounded-full bg-black/5 mx-auto flex items-center justify-center text-apple-textTertiary mb-2">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </div>
                        <div class="font-medium text-apple-textSecondary text-[13px]">Tidak ada log yang cocok dengan filter.</div>
                        <div class="text-[11.5px] mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</div>
                        <a href="{{ route('admin.logs') }}" class="inline-block mt-3 px-3 py-1.5 rounded-lg border border-apple-border bg-white text-apple-textPrimary hover:bg-black/5 text-[11.5px] font-medium transition shadow-2xs">
                            Reset Semua Filter
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
