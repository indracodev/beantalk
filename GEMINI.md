# GEMINI.md — Project Standards & Engineering Guidelines

Platform: **BeanTalk (Universal Customer Chat)**  
Core Principle: **"One Core API, One Universal SDK, Many Platforms."**

---

## 0. Document Relationship & Governance

Repositori ini dikendalikan oleh triad dokumentasi operasional yang saling melengkapi:

| Dokumen | Peran & Tanggung Jawab | Kapan Dirujuk |
|---|---|---|
| **[GEMINI.md](file:///c:/laragon/www/chat-me/GEMINI.md)** *(File ini)* | **Technical & Architectural Standards**: Aturan arsitektur, standar penulisan kode, boundary teknologi, format data/API, dan kriteria verifikasi. | Setiap kali menulis, memodifikasi, atau mereview kode backend & frontend. |
| **[AGENTS.md](file:///c:/laragon/www/chat-me/AGENTS.md)** | **Agent Operational Protocol**: Panduan perilaku AI agent, tangga keputusan Ponytail, urutan eksekusi fase (roadmap), anti-patterns terlarang, dan protokol autonomous bug fixing. | Setiap kali agen menerima instruksi baru, merencanakan eksekusi, atau memperbaiki bug. |
| **[tasks/lessons.md](file:///c:/laragon/www/chat-me/tasks/lessons.md)** | **Continuous Learning Memory**: Rekam jejak pelajaran, koreksi dari user, dan pola solusi untuk mencegah kesalahan berulang. | Dibaca di awal sesi pengerjaan dan diperbarui setiap ada koreksi teknis. |
| **[docs/](file:///c:/laragon/www/chat-me/docs/)** | **Master Specification**: PRD lengkap, skema DB DDL, kontrak REST API v1, arsitektur SDK, widget, dan deployment. | Sumber kebenaran spesifikasi teknis dan bisnis. |

> **Aturan Wajib**: Setiap baris kode yang ditulis oleh AI Agent harus memenuhi standar arsitektur di **[GEMINI.md](file:///c:/laragon/www/chat-me/GEMINI.md)** dengan mengikuti alur eksekusi di **[AGENTS.md](file:///c:/laragon/www/chat-me/AGENTS.md)**.

---

## 1. Core Engineering Principles

1. **Simplicity First**: Setiap perubahan harus sesederhana mungkin. Minimalkan jumlah baris kode dan hindari over-engineering.
2. **Staff Engineer Standard**: Sebelum menyelesaikan tugas, pastikan kode memenuhi standar staff engineer: modular, readable, maintainable, dan terdokumentasi.
3. **No Laziness / Root Cause Fix**: Temukan dan selesaikan akar masalah. Dilarang keras menggunakan solusi sementara (*band-aid patch*).
4. **Minimal Impact**: Perubahan hanya boleh menyentuh bagian yang benar-benar relevan. Hindari scope creep.
5. **Ponytail Pragmatism Active**:
   - Dahulukan fitur bawaan platform (PHP stdlib, MySQL indexed B-Tree, CSS Shadow DOM, Web APIs).
   - Jangan menambah dependensi npm / composer jika dapat diselesaikan dengan beberapa baris kode native.
   - Tandai penyederhanaan sengaja dengan: `# ponytail: [alasan], upgrade when [pemicu]`.

---

## 2. Technology Stack & Architectural Boundaries

```text
┌────────────────────────────────────────────────────────────────────────┐
│ Universal JS SDK (packages/chat-sdk)                                   │
│ • Pure TypeScript, Zero npm dependencies, Build target: IIFE / UMD     │
│ • Web Components Shadow DOM (CSS 100% terisolasi dari host)            │
│ • Size budget: < 25 KB minified & gzipped                              │
├────────────────────────────────────────────────────────────────────────┤
│ Laravel Core Server                                                    │
│ • Versi Utama: Laravel 10 (Maksimal). Batas Bawah: Laravel 7 (Minimal) │
│ • Target PHP: PHP 8.1/8.2 (Laravel 10) down to PHP 7.4/8.0 (Laravel 7) │
│ • Skeleton: Struktur Klasik (app/Http/Kernel.php & named migrations)   │
│ • Multi-tenant: Single DB, column-based tenant_id via Global Scopes   │
│ • Shared Hosting First: Zero background daemon, zero Redis/Node req    │
│ • Realtime Default: Smart Adaptive HTTP Polling (after_id range scan)  │
│ • Database First: Simpan ke MySQL sebelum publish realtime event       │
├────────────────────────────────────────────────────────────────────────┤
│ Admin Dashboard                                                        │
│ • Laravel Blade + Alpine.js / Vanilla JS                               │
│ • 3-Column layout (Conversation List, Active Chat, Customer Context)   │
│ • Realtime update tanpa full page reload                               │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Coding Standards & Guidelines

### 3.1 Backend (Laravel)
- **Controller**: Ramping (*thin controller*). Delegasikan logika bisnis ke Domain Services (`app/Services/`).
- **Validation**: Selalu gunakan Form Request Classes (`app/Http/Requests/`) untuk validasi input.
- **Data Isolation**: Model milik tenant wajib mengimplementasikan trait `BelongsToTenant` untuk mencegah kebocoran data (*cross-tenant leakage*).
- **Responses**: Gunakan format envelope standar:
  - Sukses: `{ "success": true, "data": { ... } }`
  - Gagal: `{ "success": false, "error": { "code": "...", "message": "..." } }`
- **Strict Ordering**: Pengurutan pesan wajib menggunakan `messages.id` sekuensial (bukan timestamp).
- **Idempotency**: Selalu dukung `client_message_id` pada pembuatan pesan.

### 3.2 Frontend & SDK (`packages/chat-sdk`)
- **Default UI First (Ready-Made)**: Fokus 100% pada widget bawaan siap pakai yang indah, responsif, dan premium. Custom headless UI ditunda (*YAGNI*).
- **Fixed Layout & Shape (Identik)**: Seluruh chat widget memiliki bentuk kurva, tombol pemicu, struktur bubble, dan composer yang identik di semua website. Kustomisasi per chat/project **hanya terbatas pada warna core UI (Primary Accent Color)**.
- **Zero Framework Bloat**: Dilarang memasukkan React/Vue runtime ke dalam bundle widget. Murni Vanilla TypeScript + Web Components Shadow DOM.
- **Shadow DOM Isolation**: Seluruh elemen UI widget wajib di-render di dalam `#shadow-root (open)` untuk isolasi total dari CSS website host.
- **Adaptive Polling**: Selalu dengarkan event `visibilitychange` dan `online`/`offline`. Turunkan interval polling dari 2 detik ke 15–30 detik saat tab diminimize atau tidak aktif.

---

## 4. Verification & Quality Assurance

Sebelum menandai tugas selesai:
1. Pastikan seluruh pengujian backend lolos (`php artisan test`).
2. Pastikan tidak ada runtime console error di browser saat widget dimuat.
3. Pastikan alur chat end-to-end terbukti bekerja:
   `Visitor Widget` ──► `Laravel API` ──► `MySQL` ──► `Admin Inbox` ──► `Balasan Agen` ──► `Visitor Widget`.
4. Rujuk dokumentasi lengkap di folder `docs/` untuk spesifikasi detail arsitektur, skema DB, dan API.
