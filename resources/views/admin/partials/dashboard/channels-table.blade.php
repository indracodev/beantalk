<!-- Desktop / Tablet Table View (md:block) -->
<div class="hidden md:block overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
    <table class="w-full text-left border-collapse text-[12.5px] min-w-[680px]">
        <thead>
            <tr class="bg-apple-canvas/60 text-[10.5px] font-semibold text-apple-textTertiary uppercase tracking-wider border-b border-apple-border">
                <th class="py-2.5 px-4">Website / Domain</th>
                <th class="py-2.5 px-4">Pengunjung Aktif</th>
                <th class="py-2.5 px-4">Total Chat ({{ $period === 'today' ? 'Hari ini' : ($period === '30d' ? '30 Hari' : ($period === 'quarter' ? 'Quarter' : '7 Hari')) }})</th>
                <th class="py-2.5 px-4">Median FRT</th>
                <th class="py-2.5 px-4">Aktivitas Chat</th>
                <th class="py-2.5 px-4 text-right">Status Kanal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-apple-border/50">
            @forelse($channels as $chan)
                <tr class="hover:bg-apple-canvas/40 transition">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg text-white flex items-center justify-center font-bold text-[10px] shrink-0 shadow-2xs" style="background-color: {{ $chan['color'] ?? '#2C2C2E' }};">
                                {{ strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $chan['name']), 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-apple-textPrimary text-[13px]">{{ $chan['name'] }}</div>
                                <div class="text-[11px] text-apple-textTertiary font-mono">{{ $chan['domain'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full text-[11px] font-medium font-mono">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 {{ $chan['active_online'] > 0 ? 'animate-pulse' : '' }}"></span>
                            {{ $chan['active_online'] }} online
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-mono font-medium text-apple-textPrimary">{{ $chan['chats_7d'] }}</span>
                        <span class="text-apple-textTertiary text-[11px]">chats</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-mono text-apple-textPrimary font-semibold">{{ $chan['median_frt'] }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <span class="font-bold text-apple-textPrimary font-mono text-[12px]">{{ $chan['conversion'] }} of traffic</span>
                            <div class="text-[11px] text-apple-textSecondary">{{ $chan['revenue'] }} estimasi</div>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md text-[11px] font-semibold">
                            ✓ {{ $chan['status'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-apple-textTertiary text-[12px]">
                        Belum ada website/channel yang terhubung. <a href="{{ route('admin.integrations') }}" class="text-apple-blue font-medium hover:underline">Tambah Website Baru</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
