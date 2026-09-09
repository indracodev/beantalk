# Admin Dashboard: Halaman, Routing & Struktur Layouting

Dokumen ini mendefinisikan arsitektur implementasi antarmuka pengguna (UI) Dashboard Admin **BeanTalk** berbasis **Laravel Blade**, pembagian aset publik, dan modularitas tata letak (layout).

---

## 1. Arsitektur Master Layout & Modular Partials

Dashboard admin dirancang dengan prinsip **Staff Engineer & Ponytail**:
- Bebas dari tag `<style>` inline raksasa di dalam template Blade.
- Aset statis CSS dan JavaScript dipusatkan di direktori `public/`.
- Tata letak dipecah menjadi komponen parsial yang modular.

```text
resources/views/
├── layouts/
│   ├── admin.blade.php           # Master HTML shell (hanya 28 baris)
│   └── partials/
│       └── sidebar.blade.php     # Parsial sidebar navigasi yang dapat diciutkan (collapse)
├── admin/
│   ├── inbox.blade.php           # Live 3-column workspace
│   ├── integrations.blade.php    # Multi-site hub & copy script tag
│   ├── team.blade.php            # Manajemen tim CS (Superadmin only)
│   └── logs.blade.php            # Activity logs & audit trail
└── auth/
    └── login.blade.php           # Form login dual input (email/username)
```

---

## 2. Aset Statis Publik (`public/css/` & `public/js/`)

| File Aset | Peran & Tanggung Jawab |
|---|---|
| [`public/css/admin.css`](file:///c:/laragon/www/chat-me/public/css/admin.css) | Desain token (`:root`, `.theme-light`, `.theme-dark`), reset, typography, struktur sidebar, card container, tabel data, badge, form filter, dan paginasi. |
| [`public/css/inbox.css`](file:///c:/laragon/www/chat-me/public/css/inbox.css) | Tata letak 3-kolom Live Inbox, daftar percakapan, feed bubble chat pelanggan vs agen, composer balasan, dan customer drawer. |
| [`public/css/login.css`](file:///c:/laragon/www/chat-me/public/css/login.css) | Desain kartu login modern Linear-style, background glow, dan quick-account chips. |
| [`public/js/admin.js`](file:///c:/laragon/www/chat-me/public/js/admin.js) | Pengendali ciut/lebarnya sidebar (`localStorage`), pengalih tema Light/Dark (`localStorage`), dan utilitas salin clipboard. |
| [`public/js/inbox.js`](file:///c:/laragon/www/chat-me/public/js/inbox.js) | Auto-scroll chat thread ke dasar pesan, pengiriman balasan asinkronus via REST API, dan update UI optimistik. |
| [`public/js/login.js`](file:///c:/laragon/www/chat-me/public/js/login.js) | Pengisi otomatis kredensial akun uji coba dan inisialisasi default light theme. |

---

## 3. Matriks Routing Web ([`routes/web.php`](file:///c:/laragon/www/chat-me/routes/web.php))

Semua rute admin berada di bawah grup middleware `auth` dan prefix `admin`:

```php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'Admin\DashboardController@inbox')->name('dashboard');
    Route::get('inbox/{id?}', 'Admin\DashboardController@inbox')->name('inbox');
    Route::get('integrations', 'Admin\DashboardController@integrations')->name('integrations');
    Route::get('team', 'Admin\DashboardController@team')->name('team');
    Route::get('logs', 'Admin\DashboardController@logs')->name('logs');
});
```

---

## 4. Rincian 4 Halaman Utama

### 4.1. Live Inbox (`/admin/inbox` atau `/admin/inbox/{id}`)
- **Layout 3-Kolom**:
  - **Kolom 1 (Daftar Percakapan)**: Dropdown filter toko/proyek, pencarian nama/email, tab filter status (`Semua`, `Open`, `Tugas Saya`, `Selesai`), avatar pengunjung, badge channel, snippet pesan terakhir, dan timestamp.
  - **Kolom 2 (Active Thread)**: Header percakapan, drawer status tiket, alur pesan (bubble putih customer vs bubble emas agen), composer textarea, dan tombol balas instan.
  - **Kolom 3 (Detail Konteks Pelanggan)**: Kartu informasi detail pengunjung (Nama, email, channel website, IP address, user-agent browser, halaman yang sedang dibuka).
- **Interaksi Tanpa Reload**: Pesan dibalas langsung melalui endpoint REST API `POST /api/v1/admin/conversations/{id}/reply`.

### 4.2. Integrasi Multi-Website (`/admin/integrations`)
- **Hub Multi-Site**: Menampilkan kartu semua website toko yang terhubung (*Supresso, Indraco Store, SDA Store, Indraco Global*).
- **Embed Code Generator**: Menampilkan Public API Key (`pk_live_xxxx`) dan tag `<script>` embed widget siap salin dengan 1 klik.
- **Demo Toko**: Tautan langsung ke `/demo-store.html` untuk memverifikasi fungsionalitas widget pada website host simulasi.

### 4.3. Manajemen Tim CS (`/admin/team`)
- **Otorisasi Ketat**: Hanya dapat diakses oleh akun dengan role **`superadmin`**. Pengguna dengan peran `agent` akan menerima respon `403 Forbidden`.
- **Daftar Staf**: Tabel informasi nama staf, `@username`, email, role badge (`superadmin` / `agent`), indikator status online/offline, dan counter jumlah tiket percakapan yang sedang ditangani.

### 4.4. Activity Logs & Audit Trail (`/admin/logs`)
- **Audit Jejak Aktivitas**: Merekam setiap aksi penting (login, pengiriman pesan, pembaruan konfigurasi).
- **Filter Fleksibel**: Dropdown filter berdasarkan peran pelaku (`superadmin`, `agent`, `visitor`, `system`) dan pencarian berdasarkan kode aksi.
- **Paginasi**: Data disajikan dengan navigasi pagination terintegrasi.
