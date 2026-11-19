---
name: add-migration
description: Mengubah skema database BeanTalk dengan migrasi Laravel 13 yang aman untuk data produksi (anonymous class, index, FK, model casts/fillable, cache invalidation, test). Pakai setiap menambah tabel/kolom/index.
---

# Menambah / Mengubah Skema

1. Buat migrasi baru (jangan edit migrasi yang sudah jalan di produksi):
   `php artisan make:migration add_<kolom>_to_<tabel>_table`
2. Isi dengan anonymous class, `up()` & `down()` bertipe `void`:
   - FK: `foreignId('x_id')->constrained()->cascadeOnDelete()` / `nullOnDelete()`.
   - Kolom yang difilter/diurutkan → index komposit bernama, diawali `tenant_id`/`project_id`.
   - Default yang aman untuk baris lama (kolom baru `nullable()` atau ber-`default`).
   - Secret → kolom `text` + cast `encrypted`.
3. Model: tambah ke `$fillable`, `casts()`, relasi bertipe; tambahkan ke `$hidden` bila rahasia.
4. Bila kolom memengaruhi widget (project/domain/widget_settings) → cache key sudah ter-flush via `FlushesProjectKeyCache`;
   mass update tanpa event wajib `ApiKey::forgetProjectCache($projectId)`.
5. Bila tabel baru perlu ikut migrasi data lama → tambahkan ke `ImportLegacyDatabaseCommand::TABLES` (urut FK).
6. Verifikasi: `php artisan migrate` (MySQL Laragon) → `php artisan migrate:rollback --step=1` → `migrate` lagi → `php artisan test --compact`.
7. Produksi: `DB::prohibitDestructiveCommands()` aktif; hanya `php artisan migrate --force`.
