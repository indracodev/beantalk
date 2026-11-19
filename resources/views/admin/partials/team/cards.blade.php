<!-- 2. MOBILE CARD LIST VIEW (< md) -->
<div class="md:hidden divide-y divide-apple-subtleBorder">
    @forelse($members as $m)
        @php
            $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $m->name ?: 'User'), 0, 2));
            $isOnline = ($m->status ?? 'online') === 'online';
            $roleName = strtolower($m->role);
        @endphp
        <div class="p-3.5 flex flex-col gap-2.5 hover:bg-black/[0.01] transition">
            <!-- Top: Avatar, Name, Role & Status -->
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative shrink-0">
                        <div class="w-8 h-8 rounded-full bg-apple-canvas text-apple-textPrimary font-semibold text-[11.5px] flex items-center justify-center border border-black/5 shadow-2xs">
                            {{ $initials }}
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full {{ $isOnline ? 'bg-apple-green' : 'bg-gray-300' }} absolute -bottom-0.5 -right-0.5 ring-2 ring-white"></span>
                    </div>
                    <div class="truncate">
                        <div class="font-semibold text-[13px] text-apple-textPrimary truncate">{{ $m->name }}</div>
                        <div class="text-[10.5px] text-apple-textTertiary font-mono truncate">&#64;{{ $m->username ?? strtolower($initials) }} • {{ $m->email }}</div>
                    </div>
                </div>

                <!-- Role Badge -->
                <div>
                    @if(in_array($roleName, ['owner', 'superadmin']))
                        <span class="px-2 py-0.5 rounded text-[9.5px] font-semibold uppercase bg-amber-100 text-amber-900 border border-amber-300/80 shrink-0">
                            {{ $m->role }}
                        </span>
                    @elseif($roleName === 'admin')
                        <span class="px-2 py-0.5 rounded text-[9.5px] font-semibold uppercase bg-blue-100 text-blue-900 border border-blue-300/80 shrink-0">
                            Admin
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[9.5px] font-semibold uppercase bg-emerald-100 text-emerald-900 border border-emerald-300/80 shrink-0">
                            Agent
                        </span>
                    @endif
                </div>
            </div>

            @if($m->telegram_username)
                <div class="flex items-center gap-1.5">
                    <span class="text-[10.5px] text-apple-textTertiary">Telegram:</span>
                    <a href="https://t.me/{{ $m->telegram_username }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200 text-[10.5px] font-mono">
                        <svg class="w-2.5 h-2.5 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.52 2.77-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/></svg>
                        <span>&#64;{{ $m->telegram_username }}</span>
                    </a>
                </div>
            @endif

            <!-- Bottom: Stats & Quick Actions -->
            <div class="flex items-center justify-between pt-1 border-t border-apple-subtleBorder/60 text-[11px]">
                <div class="flex items-center gap-1.5 text-apple-textSecondary">
                    <span class="w-1.5 h-1.5 rounded-full bg-apple-blue"></span>
                    <span class="font-medium text-apple-textPrimary font-mono">{{ $m->conversations_count }}</span>
                    <span class="text-apple-textTertiary">Tiket</span>
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="openEditModal({{ json_encode(['id' => $m->id, 'name' => $m->name, 'username' => $m->username, 'email' => $m->email, 'role' => $m->role, 'telegram_username' => $m->telegram_username]) }})" class="inline-flex items-center gap-1 px-2 py-1 text-[10.5px] rounded-lg border border-apple-border text-apple-textPrimary bg-white hover:bg-apple-canvas transition font-medium shadow-2xs">
                        <span>Edit</span>
                    </button>

                    @if($m->id !== Auth::id())
                        <form action="{{ route('admin.team.impersonate', $m->id) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Masuk dan bertindak sebagai {{ $m->name }} ({{ $m->role }})?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-[10.5px] rounded-lg border border-apple-border text-apple-textPrimary bg-apple-canvas/60 hover:bg-white transition font-medium shadow-2xs">
                                <svg class="w-3 h-3 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                <span>Login As</span>
                            </button>
                        </form>
                    @else
                        <span class="text-[10.5px] text-apple-textTertiary font-medium px-2 py-0.5 rounded bg-black/5">Akun Anda</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-apple-textTertiary">
            Tidak ada anggota tim yang sesuai dengan kriteria pencarian.
        </div>
    @endforelse
</div>
