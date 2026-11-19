---
name: security-review
description: Checklist review keamanan BeanTalk untuk perubahan yang menyentuh input pengguna, autentikasi, RBAC, data tenant, upload, secret, atau output HTML/JS. Pakai sebelum menyatakan fitur selesai.
---

# Checklist Review Keamanan

## Identitas & akses
- [ ] User hanya dari `$request->user()`; tidak ada `X-User-Id`, `tenant_id`, `role` dari input.
- [ ] Setiap query data tenant memakai `tenant_id` user / global scope; ID dari URL → `findOrFail` dalam scope tenant.
- [ ] Mutasi superadmin dijaga server (`can:superadmin` / `role:superadmin`), bukan hanya UI.
- [ ] Endpoint widget mengecek kepemilikan `visitor_uuid` (404 bila bukan pemilik).
- [ ] Ganti identitas (login, impersonate, leave) → `session()->regenerate()`.

## Input & output
- [ ] Semua field divalidasi (tipe, panjang, `in:`, regex warna, `timezone`, URL http(s)).
- [ ] Upload: `mimes` + `mimetypes` + `max`, nama file dari server.
- [ ] Blade `{{ }}`; `{!! !!}` hanya `HtmlString` buatan server; data ke JS via `Js::from()`.
- [ ] Tidak ada secret (token bot, key) yang dirender ke HTML/JSON (`$hidden`, input password kosong).

## Infrastruktur
- [ ] Throttle pada endpoint publik/berat.
- [ ] Webhook pihak ketiga diverifikasi (secret token / signature) dan selalu balas 200.
- [ ] Error tidak membocorkan detail di production (`APP_DEBUG=false`).
- [ ] `composer audit` bersih; paket baru mendukung Laravel 13 & PHP 8.3.
- [ ] Ada test regresi untuk setiap celah yang ditutup (`tests/Feature/Laravel13HardeningTest.php` sebagai contoh).
