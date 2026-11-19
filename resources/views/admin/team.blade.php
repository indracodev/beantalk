@extends('layouts.admin')

@section('title', 'Team & RBAC Access Control')
@section('header_title', 'Team & Access Control')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 w-full">
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

            <button type="submit" class="px-3 py-1.5 rounded-lg bg-apple-textPrimary text-white hover:bg-apple-inkHover transition font-medium text-[11.5px] shadow-2xs shrink-0">Filter</button>
            @if(request()->filled('role') || request()->filled('search') || request()->filled('sort'))
                <a href="{{ route('admin.team') }}" class="px-2 py-1.5 text-apple-textTertiary hover:text-apple-textPrimary text-[11px] shrink-0">Reset</a>
            @endif
        </div>
    </form>

    <!-- Team Members Container (Desktop Table + Mobile Cards) -->
    <div class="bg-white border border-apple-border rounded-xl shadow-apple-sm overflow-hidden">
        @include('admin.partials.team.table')

        @include('admin.partials.team.cards')

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

@include('admin.partials.team.create-modal')

@include('admin.partials.team.edit-modal')

<script src="@asset('js/pages/team.js')"></script>
@endsection

