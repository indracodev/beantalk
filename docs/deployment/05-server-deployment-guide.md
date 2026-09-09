# Panduan Deployment Server BeanTalk (PHP 8.1+ / Ubuntu / VPS / cPanel)

Dokumen ini adalah panduan operasional langkah demi langkah (*step-by-step*) untuk melakukan deployment platform **BeanTalk (Universal Customer Chat)** ke server produksi (VPS Linux Ubuntu/Debian, CentOS/AlmaLinux, ataupun Shared Hosting/cPanel) yang menggunakan **PHP 8.1** (misalnya `PHP 8.1.34`).

---

## 1. Prasyarat Server (Server Requirements)

Pastikan server Anda memenuhi spesifikasi berikut:

- **Sistem Operasi**: Ubuntu 20.04/22.04 LTS, Debian 11/12, AlmaLinux/CentOS, atau cPanel Linux.
- **PHP**: Versi **8.1.x** (didukung hingga 8.2 & 8.4).
  - Ekstensi wajib: `php8.1-cli`, `php8.1-fpm`, `php8.1-mysql`, `php8.1-mbstring`, `php8.1-xml`, `php8.1-bcmath`, `php8.1-curl`, `php8.1-zip`, `php8.1-tokenizer`, `php8.1-fileinfo`.
- **MySQL / MariaDB**: MySQL 8.0+ atau MariaDB 10.5+ (Support `utf8mb4_unicode_ci`).
- **Composer**: Versi 2.x (`composer --version`).
- **Web Server**: Nginx (disarankan) atau Apache 2.4+.
- **Git**: Untuk clone repositori kode.

> **PENTING Mengenai Kompatibilitas PHP 8.1**:  
> File `composer.json` repositori ini telah dikonfigurasi dengan:
> ```json
> "config": {
>     "platform": {
>         "php": "8.1.0"
>     }
> }
> ```
> Konfigurasi ini menjamin seluruh dependensi (termasuk Symfony 6.4 LTS) terkunci pada versi yang **100% kompatibel dengan PHP 8.1.x**, sehingga tidak akan memicu error `Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.2.0"`.

---

## 2. Urutan Eksekusi di Server (Step-by-Step)

Jalankan langkah-langkah berikut secara berurutan:

### Langkah 1: Clone atau Upload Proyek ke Server

Masuk via SSH ke server Anda, lalu clone repositori ke direktori web:

```bash
# Contoh untuk Ubuntu / VPS:
cd /var/www
git clone <URL_REPOSITORY_ANDA> beantalk
cd beantalk

# Atau jika Anda menggunakan folder chat-me:
cd /var/www/chat-me
```

---

### Langkah 2: Konfigurasi File Lingkungan (`.env`)

Salin file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Buka dan sesuaikan konfigurasi `.env` untuk server produksi:

```bash
nano .env
```

Sesuaikan parameter kunci berikut:

```env
APP_NAME=BeanTalk
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://chat.domainanda.com

LOG_CHANNEL=daily
LOG_LEVEL=error

# Konfigurasi MySQL Server
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beantalk
DB_USERNAME=beantalk_user
DB_PASSWORD=PasswordKuatMySQL123!

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

Simpan file (`Ctrl + O`, lalu `Enter`, kemudian `Ctrl + X` di nano).

---

### Langkah 3: Buat Database MySQL di Server

Masuk ke MySQL server dan buat database `beantalk` beserta user khususnya:

```bash
mysql -u root -p
```

Jalankan query SQL berikut:

```sql
-- 1. Buat Database dengan charset utf8mb4
CREATE DATABASE IF NOT EXISTS beantalk 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 2. Buat User baru (atau gunakan user root/cPanel Anda)
CREATE USER IF NOT EXISTS 'beantalk_user'@'127.0.0.1' IDENTIFIED BY 'PasswordKuatMySQL123!';

-- 3. Berikan Hak Akses Penuh ke DB beantalk
GRANT ALL PRIVILEGES ON beantalk.* TO 'beantalk_user'@'127.0.0.1';
FLUSH PRIVILEGES;

-- 4. Keluar
EXIT;
```

---

### Langkah 4: Install Dependensi Composer

Jalankan instalasi paket backend tanpa paket development (`--no-dev`) dan dengan autoloader teroptimasi:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

> Output harus menampilkan `Generating optimized autoload files` dan `Discovering packages` dengan status **DONE**.

---

### Langkah 5: Generate Application Key

Buat encryption key unik Laravel untuk server Anda:

```bash
php artisan key:generate --force
```

---

### Langkah 6: Jalankan Migrasi Database & Seeder Data Awal

Eksekusi migrasi 9 tabel dan seeder data awal BeanTalk:

```bash
php artisan migrate --seed --force
```

**Data Bawaan yang Terbentuk Otomatis:**
- **Tenant**: Indraco Group (`tenant_id = 1`)
- **4 Pengguna (RBAC) — Login dapat menggunakan Email ATAU Username**:
  - **Superadmin**: Username: `hendri` | Email: `hendri@indraco.com` | Password: `password`
  - **Admin**: Username: `admin` | Email: `admin@indraco.com` | Password: `password`
  - **Agent 1**: Username: `sarah` | Email: `sarah@indraco.com` | Password: `password`
  - **Agent 2**: Username: `budi` | Email: `budi@indraco.com` | Password: `password`
- **4 Proyek Multi-Web**:
  1. **Supresso Coffee**: Public API Key: `pk_live_supresso_8819`
  2. **Indraco Store**: Public API Key: `pk_live_indraco_5521`
  3. **Indraco Global B2B**: Public API Key: `pk_live_global_3309`
  4. **SDA Store Surabaya**: Public API Key: `pk_live_sda_1190`

---

### Langkah 7: Hubungkan Storage Symlink

Buat symlink direktori `public/storage` ke `storage/app/public` untuk melayani aset upload/lampiran chat:

```bash
php artisan storage:link
```

---

### Langkah 8: Atur Hak Akses Direktori (Permissions)

Web server (Nginx/Apache yang berjalan sebagai user `www-data` atau user cPanel) memerlukan izin tulis (*write permission*) pada direktori `storage` dan `bootstrap/cache`:

```bash
# Ubah kepemilikan ke www-data (khusus VPS Ubuntu/Debian)
sudo chown -R www-data:www-data /var/www/beantalk/storage /var/www/beantalk/bootstrap/cache

# Atur permission folder menjadi 775 dan file menjadi 664
sudo chmod -R 775 /var/www/beantalk/storage /var/www/beantalk/bootstrap/cache
```

*(Untuk cPanel / Shared hosting, pastikan folder `storage` dan `bootstrap/cache` memiliki permission `755` atau `775` atas nama user cPanel Anda).*

---

### Langkah 9: Optimasi Cache Laravel Produksi

Untuk kecepatan respon API di bawah 50ms, cache konfigurasi, routing, dan view:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> *Tip: Jika di kemudian hari Anda mengubah file `.env`, jalankan `php artisan config:clear && php artisan config:cache`.*

---

## 3. Konfigurasi Web Server

### Opsi A: Nginx (Sangat Disarankan)

Buat file virtual host Nginx:
```bash
sudo nano /etc/nginx/sites-available/beantalk.conf
```

Isi konfigurasi berikut (sesuaikan `server_name` dan path PHP-FPM 8.1):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name chat.domainanda.com;
    root /var/www/beantalk/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;

    # Universal JS SDK & Static Asset Caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi dan reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/beantalk.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Pasang SSL Let's Encrypt:
```bash
sudo certbot --nginx -d chat.domainanda.com
```

---

### Opsi B: Apache / Shared Hosting (cPanel)

Pastikan DocumentRoot domain Anda diarahkan ke folder `/public` (bukan root folder proyek).

Di dalam folder `public/`, file `.htaccess` bawaan Laravel sudah siap digunakan:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## 4. Verifikasi Kesehatan Server (Health Check)

Setelah seluruh langkah di atas selesai, jalankan pengujian endpoint API publik BeanTalk dari terminal:

```bash
curl -X POST https://chat.domainanda.com/api/v1/widget/session/init \
  -H "Content-Type: application/json" \
  -d '{"project_key": "pk_live_supresso_8819", "page_url": "https://supresso.com", "page_title": "Supresso Premium Coffee"}'
```

**Respon Sukses yang Diharapkan (HTTP 200):**
```json
{
  "success": true,
  "data": {
    "visitor_token": "...",
    "conversation_id": 1,
    "project": {
      "name": "Supresso Coffee",
      "accent_color": "#C59B27"
    }
  }
}
```

Uji ketersediaan file SDK widget publik di browser atau curl:
```bash
curl -I https://chat.domainanda.com/chat-widget.js
```
Harus mengembalikan status **HTTP 200 OK** dengan `content-type: application/javascript`.

---

## 5. Perawatan & Pemeliharaan (Maintenance)

Jika Anda ingin melakukan update kode di kemudian hari:

```bash
# 1. Aktifkan mode maintenance
php artisan down --secret="akses-rahasia-admin"

# 2. Tarik kode terbaru
git pull origin main

# 3. Jalankan migrasi jika ada schema baru
php artisan migrate --force

# 4. Refresh cache
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Nonaktifkan mode maintenance
php artisan up
```
