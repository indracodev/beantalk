# Roadmap: Implementation Phases (Phase 1 to 8)

Pengembangan sistem dibagi menjadi 8 fase berurutan untuk menjamin fondasi yang kokoh sebelum antarmuka visual dibangun.

---

## Phase 1 — Foundation (Pondasi Backend)
- **Tujuan**: Membangun backend dasar multi-tenant dan sistem otentikasi.
- **Deliverables**:
  - Inisialisasi framework Laravel 11/12 dengan database MySQL.
  - Migrasi tabel: `tenants`, `users`, `projects`, `project_domains`, `api_keys`.
  - Autentikasi tim admin (login, logout, session security).
  - Trait `BelongsToTenant` dan middleware resolusi tenant.
  - Middleware `ResolveProjectKey` untuk validasi `X-Project-Key` dan whitelist domain.

---

## Phase 2 — Chat Core (Engine Percakapan)
- **Tujuan**: Menangani data model percakapan, visitor, dan pesan.
- **Deliverables**:
  - Migrasi tabel: `contacts`, `visitors`, `conversations`, `messages`, `message_attachments`.
  - Service `ConversationService` (create conversation, append message, assign agent).
  - Validasi idempotency pengiriman pesan via `client_message_id`.
  - Database indexing untuk polling: `idx_msg_poll` dan `idx_conv_inbox`.
  - Endpoint REST API dasar untuk klien dan agen.

---

## Phase 3 — Universal JS SDK (Client Library)
- **Tujuan**: Membangun package JavaScript terpisah yang ultra-ringan di `packages/chat-sdk/`.
- **Deliverables**:
  - Setup TypeScript + Vite bundle configuration (output IIFE & UMD).
  - Core visitor identity manager (persistensi `visitor_uuid` di `localStorage`).
  - Abstraksi `RealtimeTransport` dan implementasi `PollingTransport`.
  - Event emitter (`Chat.on`, `Chat.emit`).
  - Adaptive Polling (deteksi `visibilitychange` dan `online`/`offline`).

---

## Phase 4 — Embeddable Widget (Shadow DOM UI)
- **Tujuan**: Antarmuka widget chat yang disematkan di website host.
- **Deliverables**:
  - Implementasi Web Component Shadow DOM (`attachShadow({ mode: 'open' })`).
  - Desain floating launcher button dengan badge notifikasi unread.
  - Jendela chat responsif (drawer full-screen di layar mobile < 640px).
  - Composer input teks multiline dengan shortcut `Enter` / `Shift+Enter`.
  - Dinamisasi warna dan teks widget via token CSS Variables dari server.

---

## Phase 5 — Admin Inbox (Dashboard Agen)
- **Tujuan**: Ruang kerja terpadu bagi agen untuk merespons pesan secara realtime.
- **Deliverables**:
  - Layout 3-kolom desktop (Conversation List, Active Chat, Customer Context).
  - Realtime inbox updater tanpa reload halaman (polling interval 2,5s).
  - Panel Customer Context (Shopify product page, browser, OS, device info).
  - Fitur aksi agen: Balas pesan, Assign ke agen lain, Tutup/Buka percakapan.

---

## Phase 6 — Integration (Shopify & Snippet System)
- **Tujuan**: Memudahkan merchant memasang widget di berbagai platform.
- **Deliverables**:
  - Generator script tag snippet di admin dashboard dengan tombol 1-klik salin.
  - Connection checker otomatis (pemeriksa instalasi script di situs merchant).
  - Scanner konteks Shopify di SDK (membaca `window.Shopify`, produk, mata uang).
  - Panduan dokumentasi integrasi untuk WordPress, Laravel Blade, dan React/Next.js.

---

## Phase 7 — Realtime Enhancement (SSE & Future Sockets)
- **Tujuan**: Meningkatkan efisiensi realtime pada server yang mendukung.
- **Deliverables**:
  - Implementasi `SSETransport` (Server-Sent Events controller di Laravel).
  - Mekanisme auto-fallback: WebSocket -> SSE -> Polling.
  - Heartbeat event & auto-reconnection dengan exponential backoff.

---

## Phase 8 — Production Hardening & Deployment
- **Tujuan**: Memastikan keamanan, stabilitas, dan kemudahan deployment di shared hosting.
- **Deliverables**:
  - Rate limiting di semua endpoint publik.
  - Validasi ketat MIME type dan ukuran upload attachment.
  - Sistem webhook tenant (HMAC SHA-256 signature & delivery retry).
  - Audit logs untuk aksi administratif sensitif.
  - Command scheduled pruner (`chat:prune`) untuk membersihkan data transien.
  - Panduan instalasi shared hosting (cPanel/DirectAdmin).
