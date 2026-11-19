# 08 — Git, Deploy & Data

## Git
- Branch aktif L13: `laravel-13` (branch `laravel-7` = versi lama, jangan diubah kecuali hotfix).
- Commit/push hanya bila diminta user. Pesan commit: `type(scope): ringkasan` (bahasa Indonesia boleh).
- Jangan commit: `.env*`, `database/*.sqlite`, dump DB, `vendor/`, `node_modules/`, `storage/*`.
- Aset terkompilasi di `public/` **di-commit** (server tanpa Node).

## Deploy shared hosting (ringkas — detail di skill `deploy-shared-hosting`)
1. Server: PHP ≥ 8.3 (ext: pdo_mysql, mbstring, openssl, fileinfo, intl opsional), MySQL 8.
2. Upload kode (tanpa `vendor/` bila server punya Composer; jika tidak, sertakan `vendor/` hasil `composer install --no-dev -o`).
3. `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` tetap (dibutuhkan untuk token Telegram terenkripsi), `SESSION_ENCRYPT=true`.
4. `php artisan migrate --force` → `php artisan storage:link` → `php artisan optimize`.
5. Root `.htaccess` mengarahkan ke `public/`; `DB::prohibitDestructiveCommands()` aktif di production.

## Data
- Seeder hanya data esensial (tenant root, superadmin, konfigurasi chatbot asli di `database/seeders/data/*.json`) — tanpa data dummy.
- Data produksi dipindah lewat `php artisan beantalk:import-legacy <db_lama>` (skill `import-legacy-db`), bukan seeder.
- **APP_KEY tidak boleh berganti** setelah data diimpor (token Telegram terenkripsi dengan key tersebut).
