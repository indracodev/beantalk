@php $sort = $sort = new \App\Support\SortLink('role', 'asc'); @endphp
<!-- 1. DESKTOP / TABLET TABLE VIEW (md and up) -->
<div class="hidden md:block overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
    <table class="w-full text-left text-[12px] min-w-[640px]">
        <thead class="bg-apple-canvas/80 text-apple-textSecondary text-[10.5px] font-medium uppercase border-b border-apple-border">
            <tr>
                <th class="px-4 py-2.5">
                    <a href="{{ $sort->url('name') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                        <span>User</span> {{ $sort->icon('name') }}
                    </a>
                </th>
                <th class="px-4 py-2.5">
                    <a href="{{ $sort->url('role') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                        <span>Role</span> {{ $sort->icon('role') }}
                    </a>
                </th>
                <th class="px-4 py-2.5">
                    <a href="{{ $sort->url('status') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                        <span>Status</span> {{ $sort->icon('status') }}
                    </a>
                </th>
                <th class="px-4 py-2.5">Telegram Synced</th>
                <th class="px-4 py-2.5">
                    <a href="{{ $sort->url('conversations_count') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                        <span>Active Tickets</span> {{ $sort->icon('conversations_count') }}
                    </a>
                </th>
                <th class="px-4 py-2.5 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-apple-subtleBorder">
            @forelse($members as $m)
                @php
                    $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $m->name ?: 'User'), 0, 2));
                    $isOnline = ($m->status ?? 'online') === 'online';
                    $roleName = strtolower($m->role);
                @endphp
                <tr class="hover:bg-black/[0.01] transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-apple-canvas text-apple-textPrimary font-semibold text-[11px] flex items-center justify-center border border-black/5 shadow-2xs">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="font-medium text-apple-textPrimary">{{ $m->name }}</div>
                                <div class="text-[10.5px] text-apple-textTertiary font-mono">&#64;{{ $m->username ?? strtolower($initials) }} • {{ $m->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if(in_array($roleName, ['owner', 'superadmin']))
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-amber-100 text-amber-900 border border-amber-300/80">
                                {{ $m->role }}
                            </span>
                        @elseif($roleName === 'admin')
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-blue-100 text-blue-900 border border-blue-300/80">
                                Admin
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-emerald-100 text-emerald-900 border border-emerald-300/80">
                                Agent
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="inline-flex items-center gap-1.5 font-medium {{ $isOnline ? 'text-apple-green' : 'text-apple-textTertiary' }}">
                            <span class="w-2 h-2 rounded-full {{ $isOnline ? 'bg-apple-green' : 'bg-gray-300' }}"></span>
                            <span>{{ $isOnline ? 'Online' : 'Offline' }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($m->telegram_username)
                            <a href="https://t.me/{{ $m->telegram_username }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 border border-sky-200 text-[11px] font-mono hover:bg-sky-100 transition shadow-2xs">
                                <svg class="w-3 h-3 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.52 2.77-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/></svg>
                                <span>&#64;{{ $m->telegram_username }}</span>
                            </a>
                        @else
                            <span class="text-apple-textTertiary text-[11px] italic">Belum terhubung</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-apple-textSecondary font-mono">
                        {{ $m->conversations_count }} percakapan
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1.5">
                            <button type="button" onclick="openEditModal({{ json_encode(['id' => $m->id, 'name' => $m->name, 'username' => $m->username, 'email' => $m->email, 'role' => $m->role, 'telegram_username' => $m->telegram_username]) }})" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] rounded-lg border border-apple-border text-apple-textPrimary bg-white hover:bg-apple-canvas transition font-medium shadow-2xs" title="Edit Profil & Telegram">
                                <svg class="w-3 h-3 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                                <span>Edit</span>
                            </button>

                            @if($m->id !== Auth::id())
                                <form action="{{ route('admin.team.impersonate', $m->id) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Masuk dan bertindak sebagai {{ $m->name }} ({{ $m->role }})?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] rounded-lg border border-apple-border text-apple-textPrimary bg-white hover:bg-apple-canvas transition font-medium shadow-2xs" title="Login sebagai {{ $m->name }}">
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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-apple-textTertiary">
                        Tidak ada anggota tim yang sesuai dengan kriteria pencarian.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
