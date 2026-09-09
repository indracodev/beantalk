@extends('layouts.admin')

@section('title', 'Manajemen Tim CS')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Manajemen Tim CS & Hak Akses</h1>
            <p class="page-desc">Daftar staf operasional dan superadmin internal yang melayani obrolan pelanggan.</p>
        </div>
        <div>
            <button type="button" class="btn-primary" onclick="openModal('modalNewTeamMember')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Anggota Tim</span>
            </button>
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

<!-- MODAL TAMBAH ANGGOTA TIM -->
<div class="modal-backdrop" id="modalNewTeamMember" onclick="if(event.target===this) closeModal('modalNewTeamMember')">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Daftarkan Staf / CS Baru</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalNewTeamMember')">&times;</button>
        </div>
        <form action="{{ route('admin.team.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="staffName">Nama Lengkap</label>
                    <input type="text" id="staffName" name="name" class="form-control" placeholder="Contoh: Sarah CS" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="staffUsername">Username</label>
                    <input type="text" id="staffUsername" name="username" class="form-control" placeholder="sarah_cs (tanpa spasi)">
                    <span class="form-hint">Digunakan untuk login cepat ke dasbor.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="staffEmail">Alamat Email</label>
                    <input type="email" id="staffEmail" name="email" class="form-control" placeholder="sarah@indraco.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="staffPassword">Password Awal</label>
                    <input type="password" id="staffPassword" name="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label" for="staffRole">Peran / Role</label>
                    <select id="staffRole" name="role" class="form-control" required>
                        <option value="agent" selected>Agent (Staf CS Operasional)</option>
                        <option value="superadmin">Superadmin (Akses Penuh Manajemen)</option>
                    </select>
                    <span class="form-hint">Internal sistem: Agent hanya mengakses inbox obrolan, Superadmin memiliki kontrol penuh.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalNewTeamMember')">Batal</button>
                <button type="submit" class="btn-primary">
                    <span>Daftarkan Staf</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
