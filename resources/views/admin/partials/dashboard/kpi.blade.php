<!-- ====================================================================== -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
    
    <!-- KPI 1: Total Conversations -->
    <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[12px] font-medium text-apple-textSecondary">Total Conversations</span>
            <div class="w-7 h-7 rounded-xl bg-apple-blue/10 text-apple-blue flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect width="20" height="16" x="2" y="4" rx="2" />
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
            </div>
        </div>
        <div>
            <div class="flex items-baseline gap-2">
                <span class="text-[26px] sm:text-[28px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['totalConversations']['value'] }}</span>
                <span class="inline-flex items-center text-[10.5px] font-semibold text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.2 rounded-md">
                    {{ $summary['totalConversations']['delta'] }}
                </span>
            </div>
            <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['totalConversations']['subtext'] }}</p>
        </div>
    </div>

    <!-- KPI 2: Median First Response (FRT) -->
    <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[12px] font-medium text-apple-textSecondary">Median First Response (FRT)</span>
            <div class="w-7 h-7 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>
        <div>
            <div class="flex items-baseline gap-2">
                <span class="text-[26px] sm:text-[28px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['medianFrt']['value'] }}</span>
                <span class="inline-flex items-center text-[10.5px] font-semibold text-indigo-700 bg-indigo-500/10 border border-indigo-500/20 px-1.5 py-0.2 rounded-md">
                    {{ $summary['medianFrt']['delta'] }}
                </span>
            </div>
            <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['medianFrt']['subtext'] }}</p>
        </div>
    </div>

    <!-- KPI 3: Satisfaction Score (CSAT) -->
    <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[12px] font-medium text-apple-textSecondary">Satisfaction Score (CSAT)</span>
            <div class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>
        </div>
        <div>
            <div class="flex items-baseline gap-2">
                <span class="text-[26px] sm:text-[28px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['csat']['value'] }}</span>
                <span class="inline-flex items-center text-[10.5px] font-semibold text-amber-700 bg-amber-500/10 border border-amber-500/20 px-1.5 py-0.2 rounded-md">
                    ★ {{ $summary['csat']['delta'] }}
                </span>
            </div>
            <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['csat']['subtext'] }}</p>
        </div>
    </div>

    <!-- KPI 4: Total Unique Visitors -->
    <div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-4.5 shadow-apple-card hover:shadow-apple-popover transition duration-200 flex flex-col justify-between group">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[12px] font-medium text-apple-textSecondary">Total Unique Visitors</span>
            <div class="w-7 h-7 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
        </div>
        <div>
            <div class="flex items-baseline gap-2">
                <span class="text-[26px] sm:text-[28px] font-bold tracking-tight text-apple-textPrimary font-mono leading-none">{{ $summary['uniqueVisitors']['value'] }}</span>
                <span class="inline-flex items-center text-[10.5px] font-semibold text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.2 rounded-md">
                    {{ $summary['uniqueVisitors']['delta'] }}
                </span>
            </div>
            <p class="text-[11px] text-apple-textTertiary mt-1.5 leading-snug">{{ $summary['uniqueVisitors']['subtext'] }}</p>
        </div>
    </div>

</div>
