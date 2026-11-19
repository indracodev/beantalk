---
name: import-legacy-db
description: Memindahkan data BeanTalk dari database versi Laravel 7 ke skema Laravel 13 dengan perintah beantalk:import-legacy (salin per tabel urut FK, enkripsi token Telegram). Pakai saat migrasi data dev/produksi.
---

# Impor Data dari DB Laravel 7

Prasyarat: DB lama dan DB baru berada di server MySQL yang sama; DB baru sudah `php artisan migrate`.

```bash
# .env → DB_DATABASE=<db_baru>
php artisan migrate --force
php artisan beantalk:import-legacy <db_lama>          # gagal bila DB baru sudah berisi
php artisan beantalk:import-legacy <db_lama> --force  # kosongkan tabel tujuan lalu impor ulang
php artisan optimize:clear
```

Perilaku:
- DB sumber hanya dibaca. Tabel disalin berurutan FK (`ImportLegacyDatabaseCommand::TABLES`), kolom = irisan sumber & tujuan, chunk 500.
- `widget_settings.telegram_bot_token` dienkripsi dengan `APP_KEY` aktif → **APP_KEY jangan diganti** setelahnya.
- Tabel framework lama (`migrations`, `password_resets`, `failed_jobs`) tidak disalin.

Verifikasi:
```sql
SELECT (SELECT COUNT(*) FROM <db_lama>.messages), (SELECT COUNT(*) FROM <db_baru>.messages);
```
Lalu login, buka Inbox & detail integrasi (token Telegram tetap bekerja: tombol "Tes Koneksi").

Alternatif (export/import dump langsung): import dump lama ke DB baru **sebelum** migrasi tidak didukung — skema L13 dikonsolidasi.
Gunakan perintah ini, atau import dump ke DB terpisah lalu jalankan perintah ini dari DB tersebut.
