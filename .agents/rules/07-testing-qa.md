# 07 — Testing, Verifikasi & Definition of Done

## Menjalankan
```bash
php artisan test --compact                 # seluruh suite (SQLite in-memory, ±4 dtk)
php artisan test --filter=NamaTest         # satu kelas/metode
php artisan route:list --except-vendor     # cek routing
php artisan optimize && php artisan optimize:clear   # pastikan route/config/view bisa di-cache
```

## Aturan test
- Base `Tests\TestCase` memakai `RefreshDatabase` (migrasi sekali, tiap test di-rollback). Throttle dimatikan di test.
- Setiap test membuat fixture-nya sendiri — **jangan** bergantung pada data test lain.
- Fixture bersama per area → base class abstrak (contoh `tests/Feature/Admin/AdminTestCase.php`).
- Perbaikan bug/keamanan wajib meninggalkan satu test regresi (contoh `Laravel13HardeningTest`).
- Nama method `test_*` atau `testXxx` (PHPUnit 12 tidak membaca anotasi `@test`).

## Verifikasi di MySQL nyata (wajib untuk query/halaman baru)
Test berjalan di SQLite; perbedaan dialek (GROUP BY, fungsi tanggal) hanya tertangkap di MySQL:
1. `.env` → DB Laragon (`beantalk_l13`), `php artisan optimize`.
2. Render halaman sebagai superadmin (skrip smoke via HTTP kernel atau `php artisan serve` + curl) → semua harus 200.
3. Periksa `storage/logs/laravel.log` kosong dari ERROR.

## Definition of Done
- [ ] Seluruh test lulus (tempel ringkasan `Tests: N passed`).
- [ ] Halaman/endpoint yang disentuh di-smoke di MySQL: 200 / kode error sesuai kontrak.
- [ ] Tidak ada file melewati batas ukuran (`00-core-principles.md`).
- [ ] Tidak ada query di loop; relasi yang dirender di-eager-load.
- [ ] Input baru tervalidasi; aksi baru dijaga RBAC + tenant.
- [ ] `composer audit` bersih bila dependensi berubah.
- [ ] Koreksi dari user dicatat di `tasks/lessons.md`.
