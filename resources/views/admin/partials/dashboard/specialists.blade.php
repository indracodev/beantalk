<!-- Kolom 1: Leaderboard Staf CS & SLA (Support Specialists) -->
<div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-5 shadow-apple-card flex flex-col justify-between">
    <div>
        <div class="pb-3 border-b border-apple-border/60 flex items-center justify-between">
            <div>
                <h3 class="text-[14px] font-semibold text-apple-textPrimary tracking-tight">Support Specialists SLA &amp; CSAT</h3>
                <p class="text-[11.5px] text-apple-textSecondary">Distribusi tiket dan kepatuhan waktu respon individu.</p>
            </div>
            <a href="{{ route('admin.team') }}" class="text-[11.5px] font-medium text-apple-blue hover:text-apple-blueHover">Team &rarr;</a>
        </div>

        <!-- Specialist Cards -->
        <div class="flex flex-col gap-2.5 mt-3.5">
            @foreach($specialists as $spec)
                <div class="p-2.5 sm:p-3 rounded-xl bg-apple-canvas/40 border border-apple-border/60 hover:bg-white hover:shadow-2xs transition flex items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-full {{ $spec['avatar_color'] }} font-bold text-[11px] flex items-center justify-center shrink-0 border border-black/5 shadow-2xs">
                            {{ $spec['initials'] }}
                        </div>
                        <div class="truncate">
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-apple-textPrimary text-[12.5px] truncate">{{ $spec['name'] }}</span>
                                <span class="text-[10px] text-apple-textSecondary px-1.5 py-0.2 rounded bg-black/5">{{ $spec['role'] }}</span>
                            </div>
                            <div class="text-[10.5px] text-apple-textTertiary truncate mt-0.5">{{ $spec['scope'] }}</div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="flex items-center gap-2 justify-end">
                            <span class="text-[11.5px] font-bold text-amber-700 bg-amber-500/10 px-1.5 py-0.2 rounded font-mono">{{ $spec['csat'] }}</span>
                            <span class="text-[11.5px] font-semibold text-emerald-700 font-mono">{{ $spec['sla'] }} SLA</span>
                        </div>
                        <span class="text-[10.5px] text-apple-textTertiary font-mono block mt-0.5">{{ $spec['resolved_count'] }} tiket selesai</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-3 pt-2.5 border-t border-apple-border/40 text-[11px] text-apple-textTertiary text-right font-mono">
        Median SLA Compliance: <strong>99.3%</strong> across all channels
    </div>
</div>
