@extends('layouts.admin')

@section('title', 'Manajemen Tim CS')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Manajemen Tim CS & Hak Akses</h1>
            <p class="page-desc">Daftar staf operasional dan superadmin internal yang melayani obrolan pelanggan.</p>
        </div>
    </div>

    <div class="table-card">
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
