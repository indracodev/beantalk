# 03 — Keamanan & Multi-Tenancy

## Isolasi tenant
- Tenant aktif hanya di-bind oleh middleware: `EnforceTenantScope` (staf, dari user sesi) dan `ResolveProjectKey` (widget, dari API key).
- Model ber-`tenant_id` memakai trait `BelongsToTenant` (global scope + auto-isi saat create).
- Query di controller web tetap eksplisit `where('tenant_id', $request->user()->tenant_id)` → `findOrFail` (404 untuk tenant lain).
- **Tidak pernah** membaca `tenant_id`, `user_id`, role dari input/header klien. `X-User-Id` dilarang dipakai.

## Autentikasi & RBAC
- Role: `superadmin`, `agent`. Mutasi integrasi & manajemen tim **wajib** dijaga di server:
  web → `->middleware('can:superadmin')`; API → `->middleware('role:superadmin')`. Menyembunyikan tombol di UI tidak cukup.
- Login: `User::findByLogin()` + `Hash::check()`, lalu `session()->regenerate()`. Login As (impersonate) juga regenerate sesi.
- Throttle per-route (`throttle:15,1` login, upload 30/menit, dsb.).

## Validasi input (batas kepercayaan)
- Warna: `regex:/^#[0-9A-Fa-f]{3,8}$/`. Zona waktu: `timezone`. Enum: `in:`.
- URL yang dirender ke widget/admin (kanal sosial, ikon kustom) hanya `http(s)://` → `SocialChannels::isHttpUrl()`.
- Upload widget: `mimes` + `mimetypes` + `max`. Nama file selalu dibuat server; simpan di disk `public` per folder percakapan.
- Output Blade selalu `{{ }}`; `{!! !!}` hanya untuk `HtmlString` buatan server. Data ke JS lewat `Js::from()` / `@json`, bukan string mentah.

## Secret
- Token bot Telegram: cast `encrypted`, `$hidden`, **tidak pernah** dirender ke HTML (input password kosong = pertahankan nilai lama).
- Webhook Telegram diverifikasi `X-Telegram-Bot-Api-Secret-Token` (`TelegramService::webhookSecret()`), selalu balas 200.
- `.env`, kredensial, dump DB tidak pernah di-commit.

## Header & sesi
- `SecurityHeaders` global: X-Robots-Tag noindex, X-Frame-Options SAMEORIGIN, nosniff, `X-XSS-Protection: 0`, Referrer-Policy, Permissions-Policy, COOP, HSTS (HTTPS).
- `SESSION_ENCRYPT=true`, cookie `httponly`, CSRF default L13 (form wajib `@csrf`, fetch kirim `X-CSRF-TOKEN`).
- Whitelist domain widget: bypass `localhost/127.0.0.1/chat-me.test` hanya di luar production.
