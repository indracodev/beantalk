---
name: deploy-shared-hosting
description: Langkah rilis BeanTalk Laravel 13 ke shared hosting / cPanel (PHP 8.3+, MySQL, tanpa daemon) termasuk env produksi, migrasi, storage link, dan cache. Pakai saat menyiapkan atau melakukan deploy.
---

# Deploy ke Shared Hosting

## Prasyarat server
- PHP ≥ 8.3 (pdo_mysql, mbstring, openssl, fileinfo, tokenizer, ctype, curl, dom), MySQL 8 / MariaDB 10.6+.
- Document root diarahkan ke `public/` (atau root `.htaccess` repo yang me-rewrite ke `public/`).

## Langkah
1. Di lokal: `composer install --no-dev --optimize-autoloader` (jika server tanpa Composer, upload `vendor/`).
2. Upload kode (kecuali `.env`, `storage/logs/*`, `node_modules/`).
3. Buat `.env` produksi dari `.env.example`:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda
   APP_KEY=<sama dengan sumber data bila mengimpor DB lama>
   DB_*=...
   SESSION_DRIVER=file
   SESSION_ENCRYPT=true
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```
4. Terminal cPanel / SSH:
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize          # config, route, view, event cache
   ```
5. Permission tulis: `storage/` dan `bootstrap/cache/`.
6. Cek: `/up` (200), `/login`, widget `/chat.js`, `POST /api/v1/client/session/init` dengan key aktif.
7. Telegram: daftarkan ulang webhook dari halaman integrasi (HTTPS wajib) agar secret token terpasang.

## Rollback
- Simpan backup DB sebelum `migrate`. Kode lama tetap di branch `laravel-7`.
- Setelah ubah `.env`: `php artisan optimize:clear && php artisan optimize`.
