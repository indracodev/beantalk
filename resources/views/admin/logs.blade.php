@extends('layouts.admin')

@section('title', 'Activity Logs & Audit Trail')

@push('styles')
<style>
    .page-container {
        flex: 1;
        overflow-y: auto;
        padding: 32px;
        max-width: 1100px;
        margin: 0 auto;
        width: 100%;
    }

    .page-header {
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text-main);
        margin-bottom: 6px;
    }

    .page-desc {
        font-size: 13px;
        color: var(--text-sub);
    }

    .filter-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        box-shadow: var(--card-shadow);
    }

    .filter-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-sub);
    }

    .filter-select, .filter-input {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--bg-canvas);
        color: var(--text-main);
        font-size: 13px;
        outline: none;
        transition: border-color 0.15s;
    }

    .filter-select:focus, .filter-input:focus {
        border-color: var(--accent);
    }

    .filter-btn {
        padding: 8px 16px;
        border-radius: 6px;
        background: var(--accent);
        color: #FFFFFF;
        border: none;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
    }

    .filter-btn:hover {
        background: var(--accent-hover);
    }

    .reset-link {
        font-size: 12px;
        color: var(--text-muted);
        text-decoration: none;
        padding: 6px 10px;
    }

    .reset-link:hover {
        color: var(--text-main);
    }

    .table-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }

    .log-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .log-table th {
        padding: 12px 18px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        background: var(--bg-canvas);
        border-bottom: 1px solid var(--border);
    }

    .log-table td {
        padding: 13px 18px;
        font-size: 13px;
        border-bottom: 1px solid var(--border);
        color: var(--text-main);
        vertical-align: middle;
    }

    .log-table tr:last-child td {
        border-bottom: none;
    }

    .time-cell {
        font-size: 12px;
        color: var(--text-muted);
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .user-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .role-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .role-superadmin {
        background: rgba(197, 155, 39, 0.15);
        color: #B2881D;
    }

    .role-agent {
        background: rgba(59, 130, 246, 0.12);
        color: #2563EB;
    }

    .role-visitor {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }

    .role-system {
        background: rgba(148, 163, 184, 0.18);
        color: #475569;
    }

    .action-code {
        display: inline-block;
        font-family: monospace;
        font-size: 12px;
        padding: 2px 6px;
        border-radius: 4px;
        background: var(--bg-subtle);
        color: var(--text-main);
        border: 1px solid var(--border);
    }

    .desc-text {
        color: var(--text-sub);
        max-width: 320px;
        line-height: 1.4;
    }

    .ip-text {
        font-family: monospace;
        font-size: 11px;
        color: var(--text-muted);
    }

    .empty-state {
        padding: 48px;
        text-align: center;
        color: var(--text-muted);
        font-size: 14px;
    }

    .pagination-bar {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-canvas);
        font-size: 12px;
        color: var(--text-sub);
    }

    .pagination-links {
        display: flex;
        gap: 6px;
    }

    .page-btn {
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--bg-surface);
        color: var(--text-main);
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
    }

    .page-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .page-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #FFFFFF;
    }
</style>
@endpush

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
