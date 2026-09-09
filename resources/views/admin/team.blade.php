@extends('layouts.admin')

@section('title', 'Manajemen Tim CS')

@push('styles')
<style>
    .page-container {
        flex: 1;
        overflow-y: auto;
        padding: 32px;
        max-width: 1000px;
        margin: 0 auto;
        width: 100%;
    }

    .page-header {
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
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

    .team-table-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }

    .team-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .team-table th {
        padding: 12px 18px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        background: var(--bg-canvas);
        border-bottom: 1px solid var(--border);
    }

    .team-table td {
        padding: 14px 18px;
        font-size: 13px;
        border-bottom: 1px solid var(--border);
        color: var(--text-main);
    }

    .team-table tr:last-child td {
        border-bottom: none;
    }

    .user-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: var(--accent);
    }

    .user-name-title {
        font-weight: 700;
    }

    .user-username {
        font-size: 11px;
        color: var(--text-muted);
    }

    .role-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-block;
        text-transform: uppercase;
    }

    .role-superadmin {
        background: rgba(197, 155, 39, 0.15);
        color: #B2881D;
    }

    .role-agent {
        background: rgba(59, 130, 246, 0.12);
        color: #2563EB;
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-dot-online {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
    }

    .status-dot-offline {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #94A3B8;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid var(--border);
        background: var(--bg-surface);
        color: var(--text-main);
        cursor: pointer;
    }

    .btn-action:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Manajemen Tim CS & Hak Akses</h1>
            <p class="page-desc">Daftar staf operasional dan superadmin internal yang melayani obrolan pelanggan.</p>
        </div>
    </div>

    <div class="team-table-card">
        <table class="team-table">
            <thead>
                <tr>
                    <th>Nama & Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Tiket Aktif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $m)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="avatar-box">
                                    {{ strtoupper(substr($m->username ?? $m->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="user-name-title">{{ $m->name }}</div>
                                    <div class="user-username">&#64;{{ $m->username }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $m->email }}</td>
                        <td>
                            <span class="role-badge {{ $m->role === 'superadmin' ? 'role-superadmin' : 'role-agent' }}">
                                {{ $m->role }}
                            </span>
                        </td>
                        <td>
                            <div class="status-indicator">
                                <span class="{{ $m->status === 'online' ? 'status-dot-online' : 'status-dot-offline' }}"></span>
                                <span>{{ ucfirst($m->status) }}</span>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $m->conversations_count }}</strong> percakapan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
