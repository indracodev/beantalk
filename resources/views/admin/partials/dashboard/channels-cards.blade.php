<!-- Mobile Card List View (md:hidden) -->
<div class="md:hidden divide-y divide-apple-border/60">
    @forelse($channels as $chan)
        <div class="p-3.5 flex flex-col gap-2.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md text-white flex items-center justify-center font-bold text-[9px]" style="background-color: {{ $chan['color'] ?? '#2C2C2E' }};">
                        {{ strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $chan['name']), 0, 2)) }}
                    </div>
                    <div>
                        <h4 class="font-semibold text-apple-textPrimary text-[13px] leading-tight">{{ $chan['name'] }}</h4>
                        <span class="text-[10.5px] text-apple-textTertiary font-mono">{{ $chan['domain'] }}</span>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full text-[10.5px] font-medium font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 {{ $chan['active_online'] > 0 ? 'animate-pulse' : '' }}"></span>
                    {{ $chan['active_online'] }}
                </span>
            </div>
            <div class="grid grid-cols-3 gap-2 bg-apple-canvas/60 p-2 rounded-xl text-[11px]">
                <div>
                    <span class="text-apple-textTertiary block text-[9.5px]">Chats</span>
                    <strong class="font-mono text-apple-textPrimary">{{ $chan['chats_7d'] }}</strong>
                </div>
                <div>
                    <span class="text-apple-textTertiary block text-[9.5px]">Median FRT</span>
                    <strong class="font-mono text-apple-textPrimary">{{ $chan['median_frt'] }}</strong>
                </div>
                <div>
                    <span class="text-apple-textTertiary block text-[9.5px]">Share</span>
                    <strong class="font-mono text-apple-textPrimary">{{ $chan['conversion'] }}</strong>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11.5px] pt-1">
                <span class="text-apple-textSecondary">Estimasi:</span>
                <strong class="font-mono text-emerald-700 font-semibold">{{ $chan['revenue'] }}</strong>
            </div>
        </div>
    @empty
        <div class="p-6 text-center text-apple-textTertiary text-[12px]">
            Belum ada website/channel terhubung.
        </div>
    @endforelse
</div>
