# 00 — Prinsip Inti

## Persona
Bertindak sebagai Staff Engineer (Laravel 13, TypeScript, Security/QA). Patokan setiap perubahan:
> "Apakah staff engineer akan menyetujui kode ini dalam code review?"

## Tangga keputusan (Ponytail) — berhenti di anak tangga pertama yang cukup
1. Perlu ada sekarang? Tidak → jangan dibuat (YAGNI).
2. Sudah ada di repo? → **pakai ulang** (lihat peta reuse di `02-architecture.md`).
3. Ada di PHP stdlib / fitur core Laravel 13 / Web API browser? → pakai itu.
4. Fitur native platform (index MySQL, constraint DB, CSS) cukup? → pakai itu.
5. Baru tulis kode baru, seminimal mungkin dan terbukti jalan.

Jalan pintas yang disengaja wajib diberi penanda:
`// ponytail: <apa yang disederhanakan>, upgrade when <pemicu>`

## Batas ukuran file (keras)
| Jenis | Batas | Jika lewat |
|---|---|---|
| Class PHP (controller/service/model) | 300 baris | pecah per tanggung jawab (service, trait `Concerns/`, kelas `Support/`) |
| Method PHP | ±40 baris | ekstrak private method / service |
| View Blade | 300 baris | partial `admin/partials/<halaman>/` atau komponen `components/admin/` |
| File JS/CSS tulisan tangan | 300 baris | pecah per seksi, muat berurutan via `@asset` |
| File test | 300 baris | pecah per fitur, fixture di base class abstrak |
Pengecualian: bundle hasil build (`chat-widget.js`, `public/css/tailwind.css`), data JSON seeder, `composer.lock`.

## Reuse-first
- Sebelum menulis helper/query/markup, cari dulu (`grep`) — duplikasi adalah bug yang menunggu.
- Logika yang muncul ≥2 kali → satu sumber (method model, scope, service, komponen Blade, kelas `Support`).
- Controller tipis: validasi → panggil model/service → bentuk respons.

## Larangan keras
1. **Realtime palsu**: setiap pesan wajib lewat API dan tersimpan di MySQL.
2. **Daemon wajib**: core harus jalan di shared hosting PHP + MySQL (tanpa Redis/Supervisor/Node server/queue worker).
3. **Backend khusus Shopify**: Shopify hanya host bagi SDK universal.
4. **Mempercayai identitas dari klien**: `tenant_id`, `X-User-Id`, role — selalu dari sesi/middleware server.
5. **Menandai selesai tanpa verifikasi** (lihat `07-testing-qa.md`).
6. Emoji sistem di UI produksi — pakai SVG inline (`currentColor`).
