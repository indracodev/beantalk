---
name: fix-bug
description: Protokol perbaikan bug mandiri BeanTalk — reproduksi, temukan akar masalah di semua pemanggil, perbaiki sekali di sumber, tinggalkan test regresi, verifikasi di MySQL. Pakai untuk setiap laporan bug atau test yang gagal.
---

# Memperbaiki Bug

1. **Bukti dulu**: baca `storage/logs/laravel.log`, pesan error, respons network. Jangan menebak.
2. **Reproduksi** dengan test yang gagal (`tests/Feature/...`) sebelum mengubah kode.
3. **Akar masalah**: `grep` semua pemanggil fungsi/query yang terlibat. Perbaiki di titik bersama (service/scope/model),
   bukan menambal satu controller. Contoh nyata: notifikasi pesan pertama tenant baru gagal karena `since_message_id=0`
   dianggap handshake → perbaikan di satu kondisi `has('since_message_id')`.
4. **Cek jebakan yang sudah dikenal** di `rules/01-stack-laravel13.md` (Carbon 3, ofMany, firstOrCreate, @json, report()).
5. Jalankan seluruh suite + smoke MySQL bila menyentuh query/halaman.
6. Jangan menandai selesai tanpa bukti (tempel ringkasan test).
7. Bila bug berasal dari koreksi user → catat pola di `tasks/lessons.md`.
