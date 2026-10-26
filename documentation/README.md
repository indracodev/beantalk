# Dokumentasi Sistem BeanTalk (Universal Customer Chat)

Selamat datang di repositori dokumentasi teknis dan operasional **BeanTalk**. Dokumentasi ini disusun secara komprehensif berbasis peran pengguna (*role-based*), mencakup arsitektur sistem, spesifikasi REST API, skema database, aturan bisnis perpesanan, hingga flowchart alur kerja mendalam menggunakan diagram Mermaid.

---

## 1. Ikhtisar Platform & Filosofi Arsitektur

BeanTalk adalah platform *Universal Customer Chat* multi-tenant dan multi-website yang dirancang dengan prinsip:
> **"One Core API, One Universal SDK, Many Platforms."**

Sistem ini didesain agar dapat beroperasi secara optimal di lingkungan **Shared Hosting** (PHP 8.2+, MySQL, Apache/Nginx) tanpa ketergantungan wajib pada daemon background, WebSocket server mandiri, Redis, atau Docker, namun siap ditingkatkan (*scale*) ke arsitektur VPS/WebSocket di masa depan tanpa mengubah kontrak API SDK.

### Komponen Utama Sistem:
1. **Universal JavaScript SDK (`packages/chat-sdk`):**
   - Pure TypeScript, zero npm runtime dependencies, ukuran bundle < 25 KB.
   - Menggunakan **Web Components Shadow DOM** (`#shadow-root (open)`) untuk menjamin 100% isolasi gaya CSS dari website host (Shopify, WordPress, WooCommerce, Laravel Blade, React, Vue, Next.js).
2. **Laravel Core Backend (`app/`):**
   - REST API v1 (`routes/api.php`) dan Admin Dashboard Controller (`routes/web.php`).
   - Pendekatan *Shared Hosting First*: Menggunakan *Smart Adaptive HTTP Polling* berbasis pemindaian indeks B-Tree MySQL (`messages.id > after_id`).
   - Isolasi data multi-tenant otomatis melalui trait `BelongsToTenant`.
3. **Admin & CS Live Workspace (`resources/views/admin/`):**
   - Dashboard analitik eksekutif dan Live Inbox 3-kolom bergaya *Linear/Intercom* (Daftar Percakapan, Ruang Obrolan Aktif, dan Konteks Pelanggan).

---

## 2. Matriks Hak Akses Berdasarkan Peran (Role-Based Access Control)

Sistem BeanTalk mendefinisikan 3 aktor utama:

| Modul & Fungsionalitas | Superadmin (`superadmin`) | CS Agent (`agent`) | Customer / Visitor (`customer`) |
| :--- | :---: | :---: | :---: |
| **Executive Dashboard & Export** | Akses Penuh (Filter Periode & Ekspor CSV) | Tidak Ada Akses | Tidak Ada Akses |
| **Live Unified Inbox** | Akses Penuh (Semua Project & Agen) | Akses Operasional Percakapan | Tidak Ada Akses |
| **Live Chat & Adaptive Polling** | Mengirim Balasan & Monitor | Mengirim Balasan & Monitor | Mengirim Pesan via Widget |
| **Status Percakapan (Open/Pending/Resolved)**| Update Status Bebas | Update Status Operasional | Otomatis Terbuka saat Chat Baru |
| **Penugasan Percakapan (Assign)** | Assign ke Semua Agen | Assign ke Diri Sendiri / Rekan | Tidak Ada Akses |
| **Toggle Bot & Smart Auto-Yielding** | Mengaktifkan / Menjeda Bot | Mengaktifkan / Menjeda Bot | Menerima Balasan Bot Otomatis |
| **Multi-Site Integrations Hub** | CRUD Project & Regenerasi API Key | Read Only (Lihat Project) | Handshake via `X-Project-Key` |
| **Widget Customizer & Studio** | Konfigurasi Warna Brand, Posisi & FAQ | Read Only | Menerima Pengaturan Widget |
| **Telegram Alerts & Webhook** | Konfigurasi Token, Webhook & Uji Notifikasi | Read Only Notifikasi | Teruskan Chat ke Telegram |
| **Team Management & Impersonasi** | Tambah Agen, Reset Password, Login-As | Edit Profil Sendiri | Tidak Ada Akses |
| **Audit Trail & Activity Logs** | Akses Penuh Log Sistem & IP | Tidak Ada Akses | Tidak Ada Akses |
| **Upload Lampiran Gambar (Media Canvas)** | Unduh & Tinjau Lampiran | Unduh & Tinjau Lampiran | Kompresi Canvas & Upload |

---

## 3. Struktur Direktori Dokumentasi

Dokumentasi rinci per fitur disusun dalam folder masing-masing role:

```
documentation/
├── README.md                                          # Master Panduan & Arsitektur BeanTalk
├── superadmin/                                        # Dokumentasi Role Superadmin
│   ├── 01_dashboard_and_kpi_analytics.md             # Dashboard Analitik Eksekutif & Ekspor Laporan
│   ├── 02_unified_inbox_management.md                 # Manajemen Inbox Terpusat 3-Kolom
│   ├── 03_multi_site_integrations_hub.md              # Pengelolaan Multi-Website & Kunci API
│   ├── 04_chat_widget_customizer.md                   # Studio Kustomisasi Warna Brand & Layout Widget
│   ├── 05_smart_bot_and_faq_engine.md                 # Konfigurasi Bot Penjawab Otomatis & Aturan FAQ
│   ├── 06_telegram_bot_integration.md                 # Integrasi Bot Notifikasi & Balasan Dua Arah Telegram
│   ├── 07_team_management_and_impersonation.md        # Manajemen Akun CS & Fitur Impersonasi (Login-As)
│   └── 08_activity_logs_and_audit_trail.md            # Audit Trail Log Aktivitas & Pelacakan Alamat IP
├── agent/                                             # Dokumentasi Role Customer Support (CS Agent)
│   ├── 01_agent_workspace_and_inbox.md               # Ruang Kerja Inbox CS & Penanganan Multi-Chat
│   ├── 02_live_chat_and_adaptive_polling.md          # Komunikasi Realtime & Smart Polling Adaptif
│   ├── 03_canned_responses_and_messaging.md          # Balasan Cepat, Pratinjau Gambar & Format Pesan
│   ├── 04_bot_handoff_and_yielding.md                 # Handoff Bot ke Manusia & Fitur Auto-Yielding
│   ├── 05_conversation_status_and_assignment.md      # Manajemen Status Tiket & Penugasan Percakapan
│   ├── 06_customer_context_and_crm.md                # Data Konteks Pelanggan, URL Aktif & Histori Obrolan
│   └── 07_agent_presence_and_profile.md              # Pengaturan Status Kehadiran (Online/Away) & Akun
└── customer/                                          # Dokumentasi Role Pelanggan / Pengunjung Web
    ├── 01_widget_floating_launcher.md                 # Floating Launcher Widget & Isolasi Shadow DOM
    ├── 02_session_initialization_and_handshake.md     # Inisialisasi Sesi Pengunjung & Deteksi Konteks Halaman
    ├── 03_customer_profile_and_prechat.md             # Pengisian Profil Pelanggan (Nama, Email, Telepon)
    ├── 04_realtime_messaging_and_polling.md           # Pengiriman Pesan & Sinkronisasi Balasan Realtime
    ├── 05_media_upload_and_compression.md             # Pengunggahan Foto dengan Kompresi Canvas Sisi Klien
    └── 06_smart_bot_interaction_and_faq.md            # Interaksi Bot FAQ Cerdas & Permintaan CS Manusia
```

---

## 4. Panduan Navigasi Cepat

Untuk mempelajari fungsi spesifik masing-masing role, silakan buka dokumen terkait:
- [Dokumentasi Lengkap Superadmin](file:///C:/laragon/www/%23Project2026/beantalk/documentation/superadmin/01_dashboard_and_kpi_analytics.md)
- [Dokumentasi Lengkap CS Agent](file:///C:/laragon/www/%23Project2026/beantalk/documentation/agent/01_agent_workspace_and_inbox.md)
- [Dokumentasi Lengkap Customer / Visitor](file:///C:/laragon/www/%23Project2026/beantalk/documentation/customer/01_widget_floating_launcher.md)
