@extends('layouts.admin')

@section('title', 'Activity Logs & Audit Trail')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Activity Logs & Audit Trail</h1>
            <p class="page-desc">Rekam jejak seluruh aktivitas sistem, login staf, pengiriman pesan, dan perubahan konfigurasi.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <form action="{{ route('admin.logs') }}" method="GET" class="filter-card">
        <div class="filter-item">
            <span class="filter-label">Role:</span>
            <select name="role" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                <option value="visitor" {{ request('role') === 'visitor' ? 'selected' : '' }}>Visitor</option>
                <option value="system" {{ request('role') === 'system' ? 'selected' : '' }}>System</option>
            </select>
        </div>

        <div class="filter-item" style="flex: 1; min-width: 200px;">
            <span class="filter-label">Aksi:</span>
            <input type="text" name="action" class="filter-input" placeholder="Cari nama event/aksi..." value="{{ request('action') }}" style="width: 100%;">
        </div>

        <button type="submit" class="filter-btn">Filter</button>
        @if(request()->filled('role') || request()->filled('action'))
            <a href="{{ route('admin.logs') }}" class="reset-link">Reset</a>
        @endif
    </form>

    <!-- Table Card -->
    <div class="table-card">
        <table class="log-table">
            <thead>
                <tr>
                    <th style="width: 160px;">Waktu</th>
                    <th>User & Role</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="time-cell">
                            {{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }}
                        </td>
                        <td>
                            <div class="user-pill">
                                <strong>{{ $log->user_name ?? 'System' }}</strong>
                                <span class="role-badge role-{{ $log->user_role ?? 'system' }}">
                                    {{ $log->user_role ?? 'system' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="action-code">{{ $log->action }}</span>
                        </td>
                        <td>
                            <div class="desc-text">{{ $log->description ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="ip-text">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            Belum ada riwayat aktivitas yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="pagination-bar">
                <div>
                    Menampilkan {{ $logs->firstItem() }} sampai {{ $logs->lastItem() }} dari {{ $logs->total() }} riwayat
                </div>
                <div class="pagination-links">
                    @if($logs->onFirstPage())
                        <span class="page-btn" style="opacity: 0.5; cursor: not-allowed;">&larr; Prev</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="page-btn">&larr; Prev</a>
                    @endif

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="page-btn">Next &rarr;</a>
                    @else
                        <span class="page-btn" style="opacity: 0.5; cursor: not-allowed;">Next &rarr;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
