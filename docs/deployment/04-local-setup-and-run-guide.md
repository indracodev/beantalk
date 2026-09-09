# Panduan Menjalankan BeanTalk dari Awal (Step-by-Step Setup Guide)

Dokumen ini adalah panduan lengkap langkah demi langkah (*from scratch*) untuk menginisialisasi, mengonfigurasi, dan menjalankan platform **BeanTalk (Universal Customer Chat)** di lingkungan lokal Anda (Laragon / Windows / macOS / Linux).

---

## 1. Prasyarat Lingkungan (Prerequisites)

Pastikan perangkat Anda telah terpasang:
- **PHP**: Versi **8.1**, **8.2**, atau **8.4** (Laragon sudah menyediakan PHP 8.2 & 8.4).
- **Composer**: Versi 2.x (`composer --version`).
- **MySQL**: Versi 5.7+ atau 8.0+ (Port 3306 bawaan Laragon aktif).
- **Node.js & npm**: Versi 18+ atau 20+ (`node -v` & `npm -v` untuk kompilasi JS SDK).

---

## 2. Langkah-Langkah Menjalankan dari Awal

Buka terminal Anda (PowerShell, Command Prompt, atau Terminal Laragon), lalu masuk ke folder proyek:
```bash
cd c:\laragon\www\chat-me
```

### Langkah 1: Konfigurasi File Environment (`.env`)
Salin file template `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Buka file `.env` dan pastikan konfigurasi database sesuai dengan MySQL Laragon Anda:
```env
APP_NAME=BeanTalk
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://chat-me.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beantalk
DB_USERNAME=root
DB_PASSWORD=
```
> *Catatan: Pastikan database `beantalk` sudah dibuat di MySQL (bisa melalui HeidiSQL / phpMyAdmin di Laragon).*

---

### Langkah 2: Instalasi Dependensi Backend (Composer)
Jalankan instalasi paket Laravel 10:
```bash
composer install
```
*(Atau jika menggunakan PHP spesifik Laragon: `C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar install`)*

---

### Langkah 3: Generate Application Key
Buat kunci enkripsi aplikasi Laravel:
```bash
php artisan key:generate
```

---

### Langkah 4: Migrasi Database & Seeding Data Awal
Eksekusi migrasi 9 tabel multi-tenant dan isi database dengan data realistis 4 website toko, 4 role akun CS, dan audit trail log:
```bash
php artisan migrate:fresh --seed
```

**Kredensial Akun Bawaan (Bisa Login via Username ATAU Email):**
| Role | Username | Email | Password |
|---|---|---|---|
| **Superadmin** | `hendri` | `hendri@indraco.com` | `password` |
| **Admin** | `admin` | `admin@indraco.com` | `password` |
| **Agent 1** | `sarah` | `sarah@indraco.com` | `password` |
| **Agent 2** | `budi` | `budi@indraco.com` | `password` |

Halaman login tersedia di: **`http://chat-me.test/login`** atau **`http://127.0.0.1:8000/login`**.

---

### Langkah 5: Build Universal JS SDK (BeanTalk Widget)
Kompilasi client SDK TypeScript menjadi file bundle mandiri `public/chat-widget.js`:
```bash
cd packages\chat-sdk
npm install
node build.js
cd ..\..
```
Output build akan otomatis didistribusikan ke:
- `public/chat-widget.js`
- `public/vendor/chat/chat-widget.js`

---

### Langkah 6: Jalankan Server Aplikasi

Pilih salah satu metode berikut:

#### Opsi A: Menggunakan Virtual Host Bawaan Laragon (Paling Praktis)
1. Buka Laragon ➔ Klik **Start All** (Apache/Nginx & MySQL menyala).
2. Akses langsung melalui domain virtual host:
   - Dashboard Preview: **`http://chat-me.test/preview.html`**
   - Toko Demo Supresso (Widget Embed): **`http://chat-me.test/demo-store.html`**

#### Opsi B: Menggunakan Built-in Artisan Server
Jalankan perintah serve di terminal:
```bash
php artisan serve
```
Server akan aktif di `http://127.0.0.1:8000`. Buka di browser:
- Dashboard Preview: **`http://127.0.0.1:8000/preview.html`**
- Toko Demo Supresso: **`http://127.0.0.1:8000/demo-store.html`**

---

### Langkah 7: Menjalankan Pengujian Otomatis (Testing)
Untuk memastikan seluruh 21 skenario REST API, RBAC, dan audit trail berjalan 100% tanpa error:
```bash
php vendor/bin/phpunit
```
**Hasil yang diharapkan**:
```text
OK (21 tests, 85 assertions)
```

---

## 3. Kredensial Akun Pengujian (Seeded Accounts)

Berikut adalah akun staf tenant yang otomatis dibuat oleh seeder untuk menguji hak akses RBAC:

| Nama | Email | Password | Role / Hak Akses |
|---|---|---|---|
| **Hendri** | `owner@indraco.com` | `secret123` | **Owner** (Akses penuh: Ubah role, hapus tim, integrasi) |
| **System Admin** | `admin@indraco.com` | `secret123` | **Admin** (Kelola channel integrasi, undang staf CS) |
| **Sarah** | `sarah@indraco.com` | `secret123` | **Agent** (Fokus membalas dan menangani chat pelanggan) |
| **Budi** | `budi@indraco.com` | `secret123` | **Agent** (Spesialis B2B chat ekspor) |

---

## 4. Public Keys Toko Bawaan (Seeded Projects)

Untuk pengujian widget di berbagai toko:
- **Supresso Coffee**: `pk_live_supresso_8819` (Warna aksen: `#1E1E1E`)
- **Indraco Store**: `pk_live_indraco_5521` (Warna aksen: `#6F4E37`)
- **Indraco Global B2B**: `pk_live_global_3309` (Warna aksen: `#0F2C59`)
- **SDA Store Surabaya**: `pk_live_sda_1190` (Warna aksen: `#D9230F`)
