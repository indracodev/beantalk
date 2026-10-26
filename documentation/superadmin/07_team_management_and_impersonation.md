# Modul Manajemen Tim CS & Impersonasi (Superadmin)

## 1. Deskripsi Fitur
Modul Manajemen Tim CS memberikan kewenangan eksklusif kepada Superadmin untuk mengelola staf Customer Support perusahaan. Fungsionalitas mencakup:
1. **Manajemen Akun Staf (Team CRUD):** Menambahkan agen CS baru, mengedit informasi profil, mereset kata sandi, dan memantau status kehadiran (*Online, Away, Offline*).
2. **Dukungan Otentikasi Ganda:** Agen dapat login menggunakan alamat email maupun username unik mereka (contoh: `@sarah`, `@budi`).
3. **Impersonasi Pengguna (Login-As):** Mengambil alih sesi akun agen CS secara instan tanpa memerlukan password mereka untuk kebutuhan verifikasi operasional atau pembagian beban kerja darurat, dengan tombol pengembalian sesi (*revert*) instan ke akun Superadmin.

---

## 2. Hak Akses & Role Permission
- **Role:** `superadmin` (Superadmin)
- **Middleware:** `web`, `auth`
- **Aturan Proteksi:** Superadmin tidak dapat menghapus atau mengimpersonasi akun miliknya sendiri.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php)
  - `team()`: Menampilkan daftar seluruh staf CS.
  - `storeTeam()`: Mendaftarkan agen CS baru.
  - `updateTeam()`: Mengubah nama, status kehadiran, atau kata sandi agen.
  - `impersonate()`: Memulai sesi login-as ke agen terpilih.
  - `leaveImpersonation()`: Mengembalikan sesi ke Superadmin semula.
- View: `resources/views/admin/team.blade.php`
- Route:
  - `GET /admin/team` (`name('admin.team')`)
  - `POST /admin/team` (`name('admin.team.store')`)
  - `PUT /admin/team/{id}` (`name('admin.team.update')`)
  - `POST /admin/team/{id}/impersonate` (`name('admin.team.impersonate')`)
  - `MATCH ['get', 'post'] /admin/impersonate/leave` (`name('admin.impersonate.leave')`)

### Logika Sesi Impersonasi:
1. Saat Superadmin mengklik *Login As*, sistem menyimpan ID Superadmin asli ke session:
   ```php
   session()->put('impersonator_id', auth()->id());
   ```
2. Mengubah otentikasi aktif menggunakan `Auth::loginUsingId($agent->id)`.
3. Menampilkan banner kuning di atas dasbor: *"Anda sedang login sebagai [Nama Agen] - [Kembali ke Superadmin]"*.
4. Saat tombol kembali diklik (`leaveImpersonation`), sistem mengambil ID dari `session('impersonator_id')`, melakukan login ulang ke akun Superadmin asli, dan menghapus kunci sesi tersebut.

---

## 4. Flowchart Alur Manajemen Tim & Impersonasi

```mermaid
flowchart TD
    Start([Superadmin Buka Menu Manajemen Tim /admin/team]) --> DisplayTeamList[Tampilkan Daftar Staf CS & Metrik Kehadiran]
    
    DisplayTeamList --> SelectAction{Pilih Tindakan Superadmin:}
    
    %% Alur Tambah Staf Baru
    SelectAction -- Tambah Anggota CS Baru --> OpenAddModal[Buka Form Tambah Staf]
    OpenAddModal --> InputStaffData[Masukkan Nama, Username, Email, Password, dan Role = 'agent']
    InputStaffData --> SubmitStaff[POST ke /admin/team]
    SubmitStaff --> ValidateStaffInput{Validasi Email & Username Unik?}
    ValidateStaffInput -- Duplikat / Tidak Valid --> ShowStaffError[Tampilkan Pesan Validasi Error]
    ValidateStaffInput -- Valid --> HashStaffPassword[Hash Bcrypt Password & Set Status = 'online']
    HashStaffPassword --> SaveStaffDB[(Simpan ke Tabel users)]
    SaveStaffDB --> LogCreateStaff[AuditLogger Catat: team.member_created]
    LogCreateStaff --> RefreshTeamUI[Refresh Tabel Staf CS]

    %% Alur Edit / Reset Password
    SelectAction -- Edit Staf / Reset Password --> OpenEditModal[Buka Modal Edit Data Staf]
    OpenEditModal --> UpdateFields[Ubah Nama / Status / Password Baru]
    UpdateFields --> PutStaff[PUT ke /admin/team/{id}]
    PutStaff --> SaveUpdateDB[(Update Record User di Database)]
    SaveUpdateDB --> RefreshTeamUI

    %% Alur Impersonasi (Login-As)
    SelectAction -- Login As (Impersonasi) --> SelectTargetAgent[Klik Tombol 'Login As' pada Baris Agen]
    SelectTargetAgent --> CheckTargetSelf{Apakah Target Adalah Diri Sendiri?}
    CheckTargetSelf -- Ya --> PreventSelf[Tolak: Tidak Dapat Mengimpersonasi Diri Sendiri]
    CheckTargetSelf -- Tidak --> SetImpersonateSession[Simpan ID Superadmin ke Session 'impersonator_id']
    SetImpersonateSession --> LoginAsAgent[Auth::loginUsingId ke ID Agen Target]
    LoginAsAgent --> LogImpersonateActivity[AuditLogger Catat: auth.impersonate_started]
    LogImpersonateActivity --> RedirectAgentInbox[Buka Inbox dengan Sudut Pandang Agen & Munculkan Banner Kuning]

    %% Alur Kembali ke Akun Asal
    RedirectAgentInbox --> ClickLeaveBanner{Superadmin Klik 'Kembalikan Sesi Superadmin'?}
    ClickLeaveBanner -- Ya --> ReadOriginalSession[Ambil ID dari Session 'impersonator_id']
    ReadOriginalSession --> RestoreSuperadmin[Auth::loginUsingId ke Akun Superadmin Asli]
    RestoreSuperadmin --> ClearSessionKey[Hapus Session 'impersonator_id']
    ClearSessionKey --> ReturnSuperadminDashboard[Kembali ke Halaman Admin Utama]

    RefreshTeamUI & ReturnSuperadminDashboard --> End([Selesai])
```

---

## 5. Rincian Antarmuka & Langkah Operasional

### A. Mendaftarkan Agen CS Baru:
1. Buka menu **Manajemen Tim** di sidebar.
2. Klik tombol **Tambah Staf CS Baru**.
3. Masukkan:
   - **Nama Lengkap:** Misal: `Sarah Amanda`.
   - **Username:** Misal: `sarah` (tanpa spasi).
   - **Email:** Misal: `sarah@indraco.com`.
   - **Kata Sandi:** Minimal 8 karakter.
4. Klik tombol **Simpan Anggota Tim**. Akun langsung aktif dan siap digunakan login.

### B. Prosedur Melakukan Impersonasi:
1. Pada daftar agen di tabel, cari nama agen yang ingin diperiksa.
2. Klik tombol **Login As (Impersonate)**.
3. Layar langsung dialihkan ke antarmuka obrolan dengan sudut pandang agen tersebut.
4. Di bagian paling atas layar akan muncul pita kuning peringatan.
5. Setelah selesai memeriksa atau membalas pesan darurat, klik tombol **Kembalikan Akun Superadmin** pada pita kuning tersebut.
