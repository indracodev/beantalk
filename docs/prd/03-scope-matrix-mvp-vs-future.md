# PRD: Scope Matrix (MVP vs. Future Roadmap)

Penerapan prinsip **Ponytail (YAGNI & Ruthless Pragmatism)**: Memisahkan secara tegas apa yang **harus ada di MVP** agar produk berfungsi 100% di shared hosting, dan apa yang **sengaja ditunda** ke fase berikutnya untuk mencegah over-engineering.

---

## 1. Feature Scope Comparison Matrix

| Area Fitur | Cakupan MVP (Fase 1–8) | Ditunda / Future Roadmap | Alasan Penundaan (Ponytail Rationale) |
|---|---|---|---|
| **Realtime Engine** | **Adaptive HTTP Polling** (2s/15s) + Arsitektur Abstraksi | Laravel Reverb WebSocket, Soketi Daemon, Redis Pub/Sub | Shared hosting tidak mengizinkan background daemon permanen. Polling MySQL <2ms sudah mencukupi untuk MVP. |
| **Integrasi Platform** | **Universal JS SDK 1-Tag** + Deteksi Klien Shopify | Shopify OAuth App Bridge, Shopify Private App API, Sync Keranjang Belanja | Setup Shopify App Bridge rumit dan butuh approval review Shopify. Deteksi client-side `window.Shopify` sudah 100% fungsional untuk chat. |
| **Channel Komunikasi** | **Website Live Chat** (Universal Widget) | WhatsApp Business API, Telegram Bot, Instagram DM, Facebook Messenger | Fokus utama MVP adalah *website customer chat*. Multichannel di awal memicu bloat webhook adapter dan biaya akun pihak ketiga. |
| **Widget UI Strategy** | **Built-in Default UI Premium** (Shadow DOM + Vanilla TS) | Headless Custom UI Library / Component SDK eksternal | Fokus 100% pada widget siap pakai yang cantik dan siap pasang. Kebutuhan custom headless UI sangat jarang di MVP dan ditunda. |
| **Admin Inbox UI** | **Laravel Blade + Alpine.js / Vanilla JS** (3-Kolom) | Next.js decoupled frontend SPA, GraphQL Admin API | Memisahkan frontend admin menjadi SPA tersendiri membutuhkan dua repository dan deployment terpisah. Blade + Alpine monolitik jauh lebih cepat dirawat. |
| **Otentikasi Admin** | **Laravel Session Web Guard** + Rate Limiting | OAuth2 Server, Social Login (Google/GitHub SSO), SAML Enterprise | Belum ada kebutuhan enterprise SSO di MVP; session cookies bawaan Laravel terbukti paling aman dan battle-tested. |
| **Kecerdasan Buatan** | Canned Responses (Balasan Cepat Shortcut `/`) | AI Chatbot (OpenAI/Anthropic RAG), Auto-reply bot pintar | AI agent membutuhkan database vector, embedding pipeline, dan biaya token API yang belum esensial untuk chat dasar. |
| **Billing & SaaS** | Database Tenant Scoping Manual | Integrasi Stripe/Midtrans, Paket Langganan Otomatis, White-labeling | Validasi produk dan retensi merchant lebih penting sebelum membangun billing engine multi-tier yang kompleks. |

---

## 2. In-Scope MVP Highlights

1. **Multi-Tenant Foundation**: 1 database, scoping otomatis kolom `tenant_id`, multi-project per tenant.
2. **Universal SDK**: Satu script tag yang dapat di-embed di HTML, WordPress, Shopify, dan SPA.
3. **Embeddable Shadow DOM Widget**: 100% kebal benturan styling CSS dari website host.
4. **Adaptive Polling Core**: Mengambil diff pesan baru berdasarkan `WHERE id > :after_id` dengan konsumsi server minimal.
5. **3-Column Inbox Admin**: Responsif desktop & mobile, auto-refresh realtime, panel customer context.
6. **Zero External Worker Guarantee**: Semua pengiriman pesan tersimpan secara atomik di MySQL tanpa ketergantungan antrean Redis.

---

## 3. Future Roadmap Trigger Points

- **Upgrade ke WebSocket**: Saat satu instance server melayani lebih dari 5.000 pengunjung aktif bersamaan (dapat dialihkan ke Laravel Reverb cukup dengan mengubah `.env`).
- **Integrasi WhatsApp/Telegram**: Ditambahkan setelah fondasi Core API stabil pada Fase 8.
- **Shopify Deep Cart Sync**: Ditambahkan saat merchant meminta integrasi pesanan langsung di sidebar inbox.
