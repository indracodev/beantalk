# 04 — Database, Query & Performa

## N+1, lazy & eager loading
- `Model::automaticallyEagerLoadRelationships()` aktif (AppServiceProvider) sebagai jaring pengaman — **bukan** pengganti `with()` eksplisit.
- Daftar/feed: selalu `with([...])` relasi yang dirender; hitungan relasi pakai `withCount()`.
- "Pesan terakhir": `latestMessage()` / `latestPublicMessage()` (subquery MAX(id)), jangan `hasOne()->latest()` (memuat semua pesan).
- "N terbaru per induk": `with(['rel' => fn ($q) => $q->latest('id')->limit(3)])` (L13 memakai window function).
- Dilarang query di dalam loop. Gunakan agregasi `GROUP BY` sekali lalu petakan di PHP (contoh: `VolumeSeries`, `ProjectMetrics::channels()`).
- Statistik besar: agregasi SQL (`selectRaw(...)->toBase()->first()`), jangan `->get()` seluruh tabel lalu hitung di PHP.

## Tulis data
- Counter: `increment()/decrement()` atomik, jangan baca-ubah-tulis.
- Operasi multi-tabel: `DB::transaction()`. Konsolidasi thread: `lockForUpdate()`.
- Idempotensi pesan: unique `(conversation_id, client_message_id)`.

## Index (lihat migrasi `2026_01_01_*`)
- Polling widget: `messages (conversation_id, id)`; feed inbox: `messages (tenant_id, id)`.
- Inbox: `conversations (tenant_id, status, last_message_at)`, `(tenant_id, assigned_user_id, status)`, `(tenant_id, created_at)`.
- Webhook Telegram: `conversations.telegram_topic_id`. Login: `users.email`.
- Kolom baru yang difilter/diurutkan → tambahkan index komposit di migrasi yang sama.

## Cache
- Resolusi `X-Project-Key` (dipanggil tiap polling): `ApiKey::resolveActive()` (TTL 300 dtk).
  Invalidasi otomatis via trait `FlushesProjectKeyCache` (Project, ProjectDomain, WidgetSetting) & event ApiKey.
  Mass update/delete tanpa event (`Model::where()->update()`) **wajib** memanggil `ApiKey::forgetProjectCache($projectId)`.
- Driver cache default `file` (shared hosting). Jangan menambah cache tanpa strategi invalidasi.
- Produksi: `php artisan optimize` (config/route/view/event cache).

## Lintas driver
- Test di SQLite, produksi MySQL: pakai `Sql::date()/hour()`; literal string SQL dengan kutip tunggal `'closed'`, bukan `"closed"`.
- Setiap query agregasi baru wajib di-smoke-test di MySQL (lihat `07-testing-qa.md`).
