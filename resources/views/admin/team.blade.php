@extends('layouts.admin')

@section('title', 'Team & RBAC Access Control')
@section('header_title', 'Team & Access Control')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 bg-apple-canvas/30 w-full">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-apple-border gap-3">
        <div>
            <h2 class="text-[17px] font-semibold text-apple-textPrimary tracking-tight">Manajemen Tim CS &amp; Access Control</h2>
            <p class="text-[12px] text-apple-textSecondary">Enforce role-based privileges (Owner, Admin, Agent) with strict server-side authorization.</p>
        </div>
        <div>
            <button type="button" onclick="openModal('modalNewTeamMember')" class="inline-flex items-center gap-1.5 bg-apple-blue hover:bg-apple-blueHover text-white px-3 py-1.5 rounded-lg text-[12px] font-medium transition shadow-apple-sm">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Invite Member</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    @php
        $currentSort = request('sort', 'role');
        $currentDir = request('dir', 'asc');
        $sortUrl = function($col) use ($currentSort, $currentDir) {
            $nextDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $nextDir]);
        };
        $sortIcon = function($col) use ($currentSort, $currentDir) {
            if ($currentSort !== $col) return '<span class="text-apple-textTertiary/40 ml-0.5 text-[9px]">↕</span>';
            return $currentDir === 'desc' 
                ? '<span class="text-apple-blue font-bold ml-0.5 text-[9px]">▼</span>' 
                : '<span class="text-apple-blue font-bold ml-0.5 text-[9px]">▲</span>';
        };
    @endphp
    <form action="{{ route('admin.team') }}" method="GET" class="bg-white border border-apple-border rounded-xl p-2.5 sm:p-3 shadow-apple-sm flex flex-col sm:flex-row items-stretch sm:items-center gap-2 text-[12px]">
        <!-- Mobile Search Row -->
        <div class="flex-1 relative">
            <input type="text" name="search" class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/50 text-apple-textPrimary placeholder:text-apple-textTertiary focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Cari nama staf, username, email..." value="{{ request('search') }}">
            <svg class="w-3.5 h-3.5 text-apple-textTertiary absolute left-2.5 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </svg>
        </div>

        <!-- Filter & Sort Controls -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <select name="role" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/50 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
            </select>

            <select name="sort" class="px-2 py-1.5 rounded-lg border border-apple-border bg-apple-canvas/50 text-apple-textPrimary focus:bg-white focus:outline-none text-[11.5px] cursor-pointer" onchange="this.form.submit()">
                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Sort: Nama</option>
                <option value="role" {{ (!request('sort') || request('sort') === 'role') ? 'selected' : '' }}>Sort: Role</option>
                <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>Sort: Status</option>
                <option value="conversations_count" {{ request('sort') === 'conversations_count' ? 'selected' : '' }}>Sort: Tiket</option>
            </select>

            @if(request('dir'))
                <input type="hidden" name="dir" value="{{ request('dir') }}">
            @endif

            <button type="submit" class="px-3 py-1.5 rounded-lg bg-apple-textPrimary text-white hover:bg-black transition font-medium text-[11.5px] shadow-2xs shrink-0">Filter</button>
            @if(request()->filled('role') || request()->filled('search') || request()->filled('sort'))
                <a href="{{ route('admin.team') }}" class="px-2 py-1.5 text-apple-textTertiary hover:text-apple-textPrimary text-[11px] shrink-0">Reset</a>
            @endif
        </div>
    </form>

    <!-- Team Members Container (Desktop Table + Mobile Cards) -->
    <div class="bg-white border border-apple-border rounded-xl shadow-apple-sm overflow-hidden">
        <!-- 1. DESKTOP / TABLET TABLE VIEW (md and up) -->
        <div class="hidden md:block overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
            <table class="w-full text-left text-[12px] min-w-[640px]">
                <thead class="bg-apple-canvas/80 text-apple-textSecondary text-[10.5px] font-medium uppercase border-b border-apple-border">
                    <tr>
                        <th class="px-4 py-2.5">
                            <a href="{{ $sortUrl('name') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>User</span> {!! $sortIcon('name') !!}
                            </a>
                        </th>
                        <th class="px-4 py-2.5">
                            <a href="{{ $sortUrl('role') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>Role</span> {!! $sortIcon('role') !!}
                            </a>
                        </th>
                        <th class="px-4 py-2.5">
                            <a href="{{ $sortUrl('status') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>Status</span> {!! $sortIcon('status') !!}
                            </a>
                        </th>
                        <th class="px-4 py-2.5">Telegram Synced</th>
                        <th class="px-4 py-2.5">
                            <a href="{{ $sortUrl('conversations_count') }}" class="inline-flex items-center hover:text-apple-textPrimary transition">
                                <span>Active Tickets</span> {!! $sortIcon('conversations_count') !!}
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

        @if($members instanceof \Illuminate\Pagination\LengthAwarePaginator && $members->hasPages())
            <div class="px-3.5 py-2.5 border-t border-apple-border flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-apple-textSecondary bg-apple-canvas/40">
                <div>
                    Menampilkan <span class="font-medium text-apple-textPrimary">{{ $members->firstItem() }}</span> - <span class="font-medium text-apple-textPrimary">{{ $members->lastItem() }}</span> dari <span class="font-medium text-apple-textPrimary">{{ $members->total() }}</span> staf
                </div>
                <div class="flex items-center gap-1.5">
                    @if($members->onFirstPage())
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white">&larr; Prev</span>
                    @else
                        <a href="{{ $members->previousPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs">&larr; Prev</a>
                    @endif

                    <span class="px-2 text-[10.5px] font-medium text-apple-textTertiary">Hal {{ $members->currentPage() }} / {{ $members->lastPage() }}</span>

                    @if($members->hasMorePages())
                        <a href="{{ $members->nextPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs">Next &rarr;</a>
                    @else
                        <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white">Next &rarr;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- MODAL TAMBAH ANGGOTA TIM -->
<div class="modal-backdrop fixed inset-0 bg-black/30 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden" id="modalNewTeamMember" onclick="if(event.target===this) closeModal('modalNewTeamMember')">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-apple-modal border border-apple-border overflow-hidden">
        <div class="px-4 py-3.5 border-b border-apple-border flex items-center justify-between bg-apple-canvas/50">
            <h4 class="font-semibold text-[14px] text-apple-textPrimary">Invite Team Member</h4>
            <button type="button" class="w-6 h-6 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-apple-textSecondary transition text-[12px]" onclick="closeModal('modalNewTeamMember')">✕</button>
        </div>
        <form action="{{ route('admin.team.store') }}" method="POST">
            @csrf
            <div class="p-4 flex flex-col gap-3.5 text-[12px]">
                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffName">Nama Lengkap</label>
                    <input type="text" id="staffName" name="name" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Contoh: Sarah CS" required>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffUsername">Username</label>
                    <input type="text" id="staffUsername" name="username" class="w-full px-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="sarah_cs (tanpa spasi)">
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Digunakan untuk login cepat ke dasbor.</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffEmail">Alamat Email</label>
                    <input type="email" id="staffEmail" name="email" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="sarah@indraco.com" required>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffTelegram">Telegram Username <span class="text-[10.5px] font-normal text-sky-600">(Sinkronisasi CS via Telegram)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1.5 text-apple-textTertiary font-mono text-[11.5px]">&#64;</span>
                        <input type="text" id="staffTelegram" name="telegram_username" class="w-full pl-7 pr-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="username_telegram">
                    </div>
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Saat staf membalas di Telegram, sistem akan otomatis mencocokkan identitas akun CS ini.</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffPassword">Password Awal</label>
                    <div class="relative">
                        <input type="password" id="staffPassword" name="password" class="w-full pl-3 pr-10 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Minimal 6 karakter" required minlength="6">
                        <button type="button" onclick="togglePasswordVisibility('staffPassword', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-apple-textTertiary hover:text-apple-textPrimary p-1 cursor-pointer" title="Tampilkan / Sembunyikan">
                            <svg class="eye-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg class="eye-off-icon w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffRole">Peran / Role</label>
                    <select id="staffRole" name="role" class="w-full px-3 py-1.5 border border-apple-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 cursor-pointer" required>
                        <option value="agent" selected>Agent (Staf CS Operasional)</option>
                        <option value="superadmin">Superadmin (Akses Penuh Manajemen)</option>
                    </select>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-apple-border flex justify-end gap-2 bg-apple-canvas/40">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-apple-border text-apple-textSecondary hover:bg-white text-[11.5px] font-medium" onclick="closeModal('modalNewTeamMember')">Batal</button>
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[11.5px] font-medium shadow-apple-sm">Daftarkan Staf</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT ANGGOTA TIM -->
<div class="modal-backdrop fixed inset-0 bg-black/30 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden" id="modalEditTeamMember" onclick="if(event.target===this) closeModal('modalEditTeamMember')">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-apple-modal border border-apple-border overflow-hidden">
        <div class="px-4 py-3.5 border-b border-apple-border flex items-center justify-between bg-apple-canvas/50">
            <h4 class="font-semibold text-[14px] text-apple-textPrimary">Edit Profil &amp; Akun Staf</h4>
            <button type="button" class="w-6 h-6 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-apple-textSecondary transition text-[12px]" onclick="closeModal('modalEditTeamMember')">✕</button>
        </div>
        <form id="formEditTeamMember" method="POST">
            @csrf
            @method('PUT')
            <div class="p-4 flex flex-col gap-3.5 text-[12px]">
                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="editStaffName">Nama Lengkap</label>
                    <input type="text" id="editStaffName" name="name" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" required>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="editStaffUsername">Username</label>
                    <input type="text" id="editStaffUsername" name="username" class="w-full px-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20">
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="editStaffEmail">Alamat Email</label>
                    <input type="email" id="editStaffEmail" name="email" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" required>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="editStaffTelegram">Telegram Username <span class="text-[10.5px] font-normal text-sky-600">(Sinkronisasi CS via Telegram)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1.5 text-apple-textTertiary font-mono text-[11.5px]">&#64;</span>
                        <input type="text" id="editStaffTelegram" name="telegram_username" class="w-full pl-7 pr-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="username_telegram">
                    </div>
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Hanya akun Telegram terdaftar yang dapat membalas tiket obrolan pelanggan via Telegram.</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="editStaffPassword">Ganti Password <span class="text-[10.5px] text-apple-textTertiary font-normal">(Kosongkan jika tidak diubah)</span></label>
                    <div class="relative">
                        <input type="password" id="editStaffPassword" name="password" class="w-full pl-3 pr-10 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Minimal 6 karakter" minlength="6">
                        <button type="button" onclick="togglePasswordVisibility('editStaffPassword', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-apple-textTertiary hover:text-apple-textPrimary p-1 cursor-pointer" title="Tampilkan / Sembunyikan">
                            <svg class="eye-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg class="eye-off-icon w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="editStaffRole">Peran / Role</label>
                    <select id="editStaffRole" name="role" class="w-full px-3 py-1.5 border border-apple-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 cursor-pointer" required>
                        <option value="agent">Agent (Staf CS Operasional)</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin (Akses Penuh Manajemen)</option>
                    </select>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-apple-border flex justify-end gap-2 bg-apple-canvas/40">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-apple-border text-apple-textSecondary hover:bg-white text-[11.5px] font-medium" onclick="closeModal('modalEditTeamMember')">Batal</button>
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[11.5px] font-medium shadow-apple-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(member) {
        const form = document.getElementById('formEditTeamMember');
        form.action = '/admin/team/' + member.id;
        document.getElementById('editStaffName').value = member.name || '';
        document.getElementById('editStaffUsername').value = member.username || '';
        document.getElementById('editStaffEmail').value = member.email || '';
        document.getElementById('editStaffTelegram').value = member.telegram_username || '';
        document.getElementById('editStaffPassword').value = '';
        document.getElementById('editStaffRole').value = member.role || 'agent';
        openModal('modalEditTeamMember');
    }
</script>
@endsection

