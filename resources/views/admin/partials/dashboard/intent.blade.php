<!-- Kanan (1/3): Breakdown Kategori / Intent Pengunjung -->
<div class="bg-white border border-apple-border/80 rounded-2xl p-4 sm:p-5 shadow-apple-card flex flex-col justify-between">
    <div>
        <div class="pb-3 border-b border-apple-border/60 flex items-center justify-between">
            <div>
                <h3 class="text-[14px] font-semibold text-apple-textPrimary tracking-tight">Intent Breakdown</h3>
                <p class="text-[11.5px] text-apple-textSecondary">Distribusi niat pengunjung</p>
            </div>
            <span class="text-[10.5px] font-mono text-apple-textTertiary bg-black/5 px-2 py-0.5 rounded-md">N = 1,482</span>
        </div>

        <!-- Intent Categories Progress Distribution -->
        <div class="flex flex-col gap-3.5 mt-3.5">
            @foreach($intentBreakdown as $intent)
                <div>
                    <div class="flex items-center justify-between text-[12px] mb-1">
                        <span class="font-medium text-apple-textPrimary truncate max-w-[200px]" title="{{ $intent['label'] }}">
                            {{ $intent['label'] }}
                        </span>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-apple-textTertiary font-mono text-[11px]">{{ $intent['count'] }} chats</span>
                            <span class="font-bold text-apple-textPrimary font-mono text-[12px]">{{ $intent['percentage'] }}%</span>
                        </div>
                    </div>
                    <!-- Bar -->
                    <div class="w-full bg-black/5 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" style="width: {{ $intent['percentage'] }}%; background-color: {{ $intent['color'] }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer Insight Note -->
    <div class="mt-4 p-2.5 rounded-xl bg-apple-blue/5 border border-apple-blue/15 flex items-start gap-2 text-[11.5px]">
        <span class="text-apple-blue font-bold text-[13px] leading-tight">💡</span>
        <div class="text-apple-textSecondary leading-snug">
            <strong class="text-apple-textPrimary">Top trigger pengunjung:</strong> Capsule Compatibility &amp; Mesin Kopi Nespresso®.
        </div>
    </div>
</div>
