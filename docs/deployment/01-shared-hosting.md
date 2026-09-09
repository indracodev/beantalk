# Deployment: Shared Hosting Guide (cPanel / DirectAdmin)

Panduan praktis untuk men-deploy aplikasi Laravel Core ke shared hosting tradisional (cPanel, DirectAdmin, atau CyberPanel) tanpa SSH root dan tanpa background worker.

---

## 1. Persyaratan Server Shared Hosting

- **PHP**: Versi 8.1 / 8.2 (Rekomendasi untuk Laravel 10). Versi PHP 7.4 / 8.0 didukung penuh jika menggunakan Laravel 7 / 8.
- **Ekstensi PHP Wajib**: `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `PDO_MYSQL`, `Tokenizer`, `XML`.
- **Database**: MySQL 5.7+ / 8.0+ atau MariaDB 10.3+
- **Web Server**: Apache 2.4+ (dengan `mod_rewrite`) atau LiteSpeed / Nginx.
- **SSL**: Sertifikat SSL aktif (Let's Encrypt / HTTPS).

---

## 1.1 Matriks Kompatibilitas Versi (Laravel 10 Maksimal s/d Laravel 7 Minimal)

Arsitektur sistem dirancang dengan prinsip **Standard Classical Laravel Patterns** (Services, Form Requests, Eloquent Models, dan Traits klasik). Tidak ada kode yang terkunci pada fitur PHP 8.2+ (seperti readonly classes atau DNF types), sehingga aman di-downgrade hingga ke Laravel 7.

| Versi Laravel | Kebutuhan PHP Server | Karakteristik Kerangka | Skenario Pemakaian |
|---|:---:|---|---|
| **Laravel 10** *(Versi Utama / Maks)* | **PHP 8.1 - 8.3** | Klasik (`app/Http/Kernel.php`) | Target utama production pada hosting/cPanel modern. |
| **Laravel 9** | **PHP 8.0 - 8.2** | Klasik (`app/Http/Kernel.php`) | Opsi transisi jika server menggunakan PHP 8.0. |
| **Laravel 8** | **PHP 7.3 - 8.1** | Klasik (`app/Http/Kernel.php`) | Opsi kompatibilitas PHP 7.4 / 8.0. |
| **Laravel 7** *(Batas Bawah / Min)* | **PHP 7.2.5 - 8.0** | Klasik (`app/Http/Kernel.php`) | Server lawas klien yang belum bisa upgrade dari PHP 7.4. |

### Panduan Kompatibilitas Lintas Versi (Laravel 7 - 10):
1. **Struktur Skeleton Tetap Klasik**: Gunakan `app/Http/Kernel.php`, `app/Console/Kernel.php`, dan `app/Exceptions/Handler.php`. Jangan beralih ke format skeleton Laravel 11 (`bootstrap/app.php`).
2. **Migrations Tradisional**: Gunakan deklarasi class migrasi bernama (`class CreateMessagesTable extends Migration`), bukan anonymous class migration, agar dapat dieksekusi di Laravel 7.
3. **Throttling Route Standar**: Gunakan middleware `throttle:60,1` standar yang didukung secara universal dari Laravel 7 hingga Laravel 10.
4. **Logika Domain Portabel**: Seluruh logika di `app/Services/`, `app/Models/`, dan `packages/chat-sdk/` 100% identik tanpa perubahan.

---

## 2. Struktur Direktori Deployment

Untuk keamanan maksimal di shared hosting, **jangan pernah meletakkan file `.env` atau folder `app/` di dalam `public_html`**.

Rekomendasi struktur folder cPanel:
```text
/home/username/
├── chat-backend/             <-- Seluruh file Laravel (di luar public_html)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── storage/
│   ├── .env
│   └── artisan
└── public_html/              <-- Hanya isi dari folder public/ Laravel
    ├── index.php
    ├── .htaccess
    ├── widget.js             <-- Asset bundle Universal SDK
    └── storage/              <-- Symlink ke /home/username/chat-backend/storage/app/public
```

### Konfigurasi `public_html/index.php`:
Sesuaikan path autoload dan bootstrap:
```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Sesuaikan path ke folder chat-backend di luar public_html
if (file_exists($maintenance = __DIR__.'/../chat-backend/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../chat-backend/vendor/autoload.php';

$app = require_once __DIR__.'/../chat-backend/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

---

## 3. Konfigurasi `.env` Production

```env
APP_NAME="Universal Chat"
APP_ENV=production
APP_KEY=base64:GENERATE_VIA_ARTISAN_KEY_HERE
APP_DEBUG=false
APP_URL=https://chat.yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_chatdb
DB_USERNAME=username_chatuser
DB_PASSWORD=SecurePasswordHere123!

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Konfigurasi Chat Shared Hosting
CHAT_REALTIME_DRIVER=polling
CHAT_POLL_INTERVAL_ACTIVE=2000
CHAT_POLL_INTERVAL_IDLE=15000
CHAT_MAX_UPLOAD_SIZE=5120
```

---

## 4. Setup Cron Job (Scheduler)

Di cPanel, buka menu **Cron Jobs** dan tambahkan entri cron yang berjalan setiap 1 menit:

```bash
* * * * * cd /home/username/chat-backend && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Cron ini menangani:
- Pembersihan buffer realtime event kadaluarsa (`chat:prune`)
- Retry webhook otomatis
- Pemeliharaan kuota disk

---

## 5. Storage Symlink

Jika cPanel menyediakan Terminal SSH:
```bash
cd /home/username/chat-backend
php artisan storage:link
```

Jika **tidak ada akses SSH**, buat script sementara `public_html/symlink.php`:
```php
<?php
symlink('/home/username/chat-backend/storage/app/public', __DIR__ . '/storage');
echo "Symlink created successfully!";
```
Buka file tersebut di browser sekali, lalu **segera hapus file `symlink.php`**.
