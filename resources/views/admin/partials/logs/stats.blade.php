<!-- Quick Stats Ribbon (KPI) - Responsive Grid across Mobile, Tablet & Desktop -->
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5 sm:gap-3">
    <!-- Total Logs -->
    <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
        <div>
            <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Total Entri</div>
            <div class="text-[17px] sm:text-[18px] font-bold text-apple-textPrimary tracking-tight mt-0.5">{{ number_format($logStats->total_logs ?? 0) }}</div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-black/5 flex items-center justify-center text-apple-textSecondary shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
    </div>

    <!-- Hari Ini -->
    <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
        <div>
            <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Hari Ini</div>
            <div class="text-[17px] sm:text-[18px] font-bold text-apple-blue tracking-tight mt-0.5">{{ number_format($logStats->today_logs ?? 0) }}</div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-apple-blue/10 flex items-center justify-center text-apple-blue shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
    </div>

    <!-- Superadmin -->
    <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
        <div>
            <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Superadmin</div>
            <div class="text-[17px] sm:text-[18px] font-bold text-purple-700 tracking-tight mt-0.5">{{ number_format($logStats->superadmin_logs ?? 0) }}</div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
        </div>
    </div>

    <!-- CS Agents -->
    <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between">
        <div>
            <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Staf CS</div>
            <div class="text-[17px] sm:text-[18px] font-bold text-amber-700 tracking-tight mt-0.5">{{ number_format($logStats->agent_logs ?? 0) }}</div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
            </svg>
        </div>
    </div>

    <!-- Warning / Security -->
    <div class="bg-white border border-apple-border/80 rounded-xl p-3 shadow-apple-sm flex items-center justify-between col-span-2 sm:col-span-1 md:col-span-1">
        <div>
            <div class="text-[10px] sm:text-[10.5px] font-medium text-apple-textTertiary uppercase tracking-wider">Warning &amp; Error</div>
            <div class="text-[17px] sm:text-[18px] font-bold {{ ($logStats->warning_logs ?? 0) > 0 ? 'text-apple-red' : 'text-apple-green' }} tracking-tight mt-0.5">
                {{ number_format($logStats->warning_logs ?? 0) }}
            </div>
        </div>
        <div class="w-8 h-8 rounded-lg {{ ($logStats->warning_logs ?? 0) > 0 ? 'bg-red-50 text-apple-red' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
    </div>
</div>
