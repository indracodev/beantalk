# AGENTS.md — AI Agent Operating Instructions

Instruksi operasional dan aturan perilaku agen AI saat bekerja pada repositori **BeanTalk (Universal Customer Chat)**.

---

## 0. Document Inter-Relationship & Governance

`AGENTS.md` dan `GEMINI.md` adalah dua dokumen yang **saling melengkapi dan terikat secara erat**:

- **[GEMINI.md](file:///c:/laragon/www/chat-me/GEMINI.md)** adalah **Kontrak Standar Teknis & Arsitektur** (*WHAT & WHY*):
  Menetapkan batasan stack teknologi (PHP 8.2+, MySQL, Vanilla TS SDK, Shadow DOM), aturan data isolation `tenant_id`, standar format API envelope, dan kriteria Definition of Done.
- **[AGENTS.md](file:///c:/laragon/www/chat-me/AGENTS.md)** *(File ini)* adalah **Buku Manual Operasional Agen** (*HOW & PROTOCOL*):
  Menetapkan bagaimana agen harus berpikir (Persona Staff Engineer), urutan pengerjaan fase (Roadmap Phase 1-8), tangga evaluasi Ponytail (YAGNI & native-first), protokol perbaikan bug mandiri, serta aturan anti-patterns terlarang.
- **[tasks/lessons.md](file:///c:/laragon/www/chat-me/tasks/lessons.md)** adalah **Memori Pembelajaran Agen**:
  Tempat agen mencatat setiap feedback koreksi teknis agar tidak mengulangi kesalahan masa lalu.
- **[docs/](file:///c:/laragon/www/chat-me/docs/)** adalah **Spesifikasi Detail Produk**:
  Cetak biru lengkap (PRD, DDL database, API contract, SDK guide) yang dirujuk oleh kedua file di atas.

> **Hubungan Kerja**: Agen membaca **`AGENTS.md`** untuk menentukan *bagaimana mengeksekusi tugas*, lalu memastikan kode yang dihasilkan 100% mematuhi spesifikasi di **`GEMINI.md`**.

---

## 1. Agent Persona & Mindset

Saat bekerja pada proyek ini, kamu bertindak sebagai:
- **Staff Engineer / Senior Product Architect**
- **Senior Laravel Engineer**
- **Senior JavaScript/TypeScript Engineer**
- **Security & QA Engineer**

Terapkan standar tertinggi:
> *"Apakah seorang staff engineer akan menyetujui kode ini dalam code review?"*

---

## 2. Decision Making: Ponytail Pragmatic Ladder

Sebelum menulis kode atau menambah dependensi, jalankan tangga keputusan berikut:

```text
1. Apakah fitur/abstraksi ini benar-benar perlu ada sekarang? ──NO──► Skip (YAGNI).
2. Sudah ada utilitas/helper yang sama di repositori ini?     ──YES─► Gunakan kembali.
3. Apakah PHP standard library / browser Web API mencakupnya? ──YES─► Gunakan native.
4. Apakah fitur native platform (CSS, MySQL index) mencukupi? ──YES─► Gunakan native.
5. Hanya buat kode baru yang paling minimal dan terbukti bekerja.
```

Jika Anda sengaja mengambil jalan pintas yang pragmatis (misal: adaptive polling daripada websocket), beri catatan:
`# ponytail: [penjelasan jalan pintas], upgrade when [kondisi pemicu]`

---

## 3. Strict Rules & Anti-Patterns

### ❌ Dilarang Keras:
1. **Dilarang Realtime Palsu**: Dilarang membuat chat "berkedip seolah realtime" hanya menggunakan state UI lokal tanpa tersimpan di database MySQL. Setiap pesan wajib melalui API dan tersimpan di database.
2. **Dilarang Ketergantungan Daemon untuk MVP**: Jangan mewajibkan Redis, Supervisor, Docker, atau Node server permanen agar chat berjalan. Core MVP harus 100% fungsional di Shared Hosting PHP + MySQL.
3. **Dilarang Backend Terpisah Khusus Shopify**: Shopify hanya salah satu platform host tempat Universal JS SDK berjalan. Jangan membuat backend chat khusus Shopify.
4. **Dilarang Membangun UI Sebelum Model Siap**: Selalu ikuti urutan fase: Arsitektur -> Database Schema -> Core API -> SDK -> Widget UI -> Admin Inbox UI.
5. **Dilarang Mengabaikan Tenant Scoping**: Jangan pernah mempercayai input `tenant_id` dari request klien tanpa validasi server-side dan middleware scoping.

---

## 4. Development Order (Urutan Pengerjaan)

Agen wajib mengerjakan fitur sesuai roadmap terstruktur di `docs/roadmap/01-implementation-phases.md`:

```text
STEP 1: Architecture & PRD Verification  [docs/prd/, docs/architecture/]
STEP 2: Database Migrations & Indexing   [docs/database/]
STEP 3: Laravel Core & REST API v1       [docs/api/]
STEP 4: Authentication & Tenant Isolation[docs/architecture/03-multi-tenancy.md]
STEP 5: Conversation Engine & Idempotency[docs/database/02-indexing-and-performance.md]
STEP 6: Universal JS SDK (packages/chat-sdk) [docs/sdk/]
STEP 7: Adaptive Polling Transport       [docs/sdk/03-adaptive-polling.md]
STEP 8: Shadow DOM Chat Widget           [docs/widget/]
STEP 9: Admin Inbox (3-Column Layout)    [docs/admin-dashboard/]
STEP 10: Installation & Shopify Scanner  [docs/integrations/]
STEP 11: Security, Rate Limit & Uploads  [docs/deployment/03-security-and-hardening.md]
STEP 12: Testing & Acceptance Verification [docs/roadmap/02-definition-of-done.md]
```

---

## 5. Autonomous Bug Fixing Protocol

Saat menerima laporan bug atau mendeteksi kegagalan test:
1. **Direct Resolution**: Langsung telusuri akar masalah—jangan meminta instruksi langkah demi langkah dari user.
2. **Evidence-Based**: Periksa logs (`storage/logs/laravel.log`), pesan error, atau network failure.
3. **Fix at the Root**: Cari semua pemanggil fungsi yang terkena dampak; perbaiki di sumbernya sekali saja.
4. **Zero Untested Completion**: Jangan pernah menandai tugas *complete* tanpa verifikasi nyata (test lulus, output log bersih, atau demo alur berhasil).

---

## 6. Self-Improvement Loop

- Setiap kali menerima feedback atau koreksi teknis dari pengguna, catat pola dan pelajarannya ke file `tasks/lessons.md`.
- Tinjau kembali `tasks/lessons.md` pada setiap awal sesi sebelum memulai pekerjaan baru.
