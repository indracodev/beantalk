<!-- 2. RESPONSIVE CARD GRID VIEW (Optimized for Mobile & Tablet iPads) -->
<div id="logsCardsView" class="hidden p-3 sm:p-4 bg-apple-canvas/20">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
        @forelse($logs as $log)
            @php
                $actionName = $log->action ?? 'unknown';
                $isError = strpos($actionName, 'error') !== false ||
                           strpos($actionName, 'exceeded') !== false ||
                           strpos($actionName, 'UNAUTHORIZED') !== false;

                $isIntegration = strpos($actionName, 'integration') !== false;
                $isWidget = strpos($actionName, 'widget') !== false;
                $isTeam = strpos($actionName, 'team') !== false || strpos($actionName, 'role') !== false;
                $isChat = strpos($actionName, 'message') !== false || strpos($actionName, 'chat') !== false;

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
            <div class="bg-white border border-apple-border rounded-xl p-3.5 shadow-2xs hover:shadow-apple-sm transition flex flex-col justify-between gap-2.5">
                <!-- Card Header -->
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-[10px] px-2 py-0.5 rounded border font-semibold truncate max-w-[170px] {{ $badgeClass }}" title="{{ $log->action }}">
                        {{ $log->action }}
                    </span>

                    <span class="font-mono text-[10.5px] text-apple-textTertiary shrink-0">
                        {{ $log->created_at ? $log->created_at->format('d M, H:i') : '-' }}
                    </span>
                </div>

                <!-- Card Body (Description) -->
                <div class="text-[12px] text-apple-textPrimary leading-snug break-words">
                    {{ $log->description ?? '-' }}
                </div>

                <!-- Payload Disclosure if present -->
                @if($hasProps)
                    <div class="pt-1">
                        <button type="button" onclick="toggleLogPayload('crd-{{ $log->id }}')"
                            class="inline-flex items-center gap-1 text-[10.5px] font-mono text-apple-blue hover:underline">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                            <span>Payload JSON</span>
                            <span id="payload-arrow-crd-{{ $log->id }}" class="text-[8px] font-bold">▼</span>
                        </button>
                        <div id="payload-box-crd-{{ $log->id }}" class="hidden mt-1.5 p-2 bg-[#1E1E1E] text-[#D4D4D4] rounded-lg font-mono text-[10px] overflow-x-auto shadow-inner">
                            <pre class="m-0 leading-tight"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    </div>
                @endif

                <!-- Card Footer -->
                <div class="flex items-center justify-between pt-2 border-t border-apple-subtleBorder text-[11px]">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <div class="w-5 h-5 rounded-full bg-apple-canvas border border-apple-border text-[9px] font-semibold text-apple-textSecondary flex items-center justify-center shrink-0">
                            {{ $actorInitials }}
                        </div>
                        <span class="font-medium text-apple-textPrimary truncate max-w-[100px]">{{ $log->user_name ?? 'System' }}</span>
                        <span class="text-[8.5px] font-mono uppercase px-1 py-0.2 rounded shrink-0 {{ $roleBadge }}">
                            {{ $roleName }}
                        </span>
                    </div>

                    <span class="font-mono text-apple-textTertiary text-[10px] shrink-0" title="IP Address">
                        {{ $log->ip_address ?? '127.0.0.1' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full p-8 text-center text-apple-textTertiary bg-white rounded-xl border border-apple-border">
                Belum ada riwayat aktivitas yang cocok dengan filter.
            </div>
        @endforelse
    </div>
</div>
