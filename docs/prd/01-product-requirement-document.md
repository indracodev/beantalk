# PRD: Universal Customer Chat Platform

| Metadata | Keterangan |
|---|---|
| **Product Name** | Universal Customer Chat (Chat-Me) |
| **Document Version** | 1.0.0 (MVP) |
| **Status** | Approved for Development |
| **Target Launch** | Phase 1–8 Roadmap |
| **Core Principle** | *"One Core API, One Universal SDK, Many Platforms."* |

---

## 1. Executive Summary & Problem Statement

### 1.1 Latar Belakang & Masalah
Pemilik situs web (UMKM, toko online Shopify, pengguna WordPress, dan aplikasi web kustom) membutuhkan live chat untuk meningkatkan konversi penjualan dan melayani pelanggan secara cepat. Namun, solusi yang ada saat ini (seperti Intercom, Crisp, Chatwoot, Zendesk) memiliki masalah:
1. **Harga Sangat Mahal**: Biaya bulanan membengkak seiring bertambahnya agen atau jumlah kontak.
2. **Setup Rumit & Butuh Server Khusus**: Platform open-source seperti Chatwoot membutuhkan Docker, Redis, Node.js, dan VPS mandiri dengan RAM minimal 4GB—mustahil dijalankan di shared hosting murah cPanel.
3. **Fragmentasi Platform**: Vendor sering kali membuat plugin/app terpisah yang kaku untuk masing-masing CMS, sulit disesuaikan dan membebani performa web host.

### 1.2 Solusi Produk
Membangun platform **Universal Customer Chat** multi-tenant yang ringan, mandiri, dan:
- Menyediakan **Universal JavaScript SDK** tunggal yang dapat disematkan di mana saja (Shopify, WordPress, Laravel, React/Vue, HTML biasa).
- Menggunakan backend **Laravel Core** yang dirancang khusus agar **100% kompatibel dengan shared hosting** (PHP 8.2+, MySQL, Apache/Nginx, zero background daemon).
- Menghadirkan **Admin Dashboard Inbox 3-kolom modern** dengan densitas informasi tinggi seperti Linear dan Intercom.
- Memiliki jalur peningkatan (*upgrade path*) mulus ke VPS/WebSocket tanpa perlu menulis ulang SDK.

---

## 2. Product Personas

```text
┌─────────────────────────┐  ┌─────────────────────────┐  ┌─────────────────────────┐
│ Persona 1: Merchant     │  │ Persona 2: CS / Agent   │  │ Persona 3: Visitor      │
│ (Website Owner)         │  │ (Support Operator)      │  │ (End Customer)          │
├─────────────────────────┤  ├─────────────────────────┤  ├─────────────────────────┤
│ • Ingin pasang cepat    │  │ • Butuh inbox cepat     │  │ • Butuh respons instan  │
│ • Hosting cPanel murah  │  │ • Tahu konteks halaman  │  │ • Tidak mau form rumit  │
│ • Butuh multi-project   │  │ • Keyboard shortcut     │  │ • Ringan di smartphone  │
└─────────────────────────┘  └─────────────────────────┘  └─────────────────────────┘
```

### 2.1 Persona 1: Toko Online / Merchant (Budi - Solo Founder)
- **Kebutuhan**: Memasang live chat di toko Shopify dan landing page tanpa menyewa VPS mahal.
- **Goal**: Cukup salin satu tag `<script>`, warna widget langsung cocok dengan brand tokonya.

### 2.2 Persona 2: Customer Support Agent (Sarah - CS)
- **Kebutuhan**: Menjawab puluhan chat tanpa tab browser lambat, mengetahui produk apa yang sedang dilihat pengunjung.
- **Goal**: Membalas via shortcut keyboard (`Enter`), melihat konteks perangkat dan riwayat chat secara instan di panel samping.

### 2.3 Persona 3: Pengunjung Situs (Jane - Pembeli)
- **Kebutuhan**: Bertanya stok produk atau ongkos kirim saat berada di halaman produk.
- **Goal**: Mengklik tombol chat mengambang yang langsung terbuka tanpa jeda (*zero lag*), tidak membuat HP lemot.

---

## 3. Core Value Propositions

1. **Ultra-Lightweight Universal SDK**: Ukuran bundle terkompilasi `< 25 KB` gzipped, zero runtime dependency, terisolasi sempurna dengan Shadow DOM.
2. **Shared Hosting First**: Berjalan sempurna di hosting $2/bulan via Adaptive HTTP Polling (`after_id` indexed query). Tidak ada dependency ke Docker, Redis, atau Node.js untuk MVP.
3. **Native Shopify Compatibility**: Deteksi otomatis halaman produk dan toko tanpa perlu otorisasi OAuth Shopify private API yang rumit.
4. **Staff-Engineer Grade Architecture**: Pemisahan layer yang bersih (UI -> SDK -> Transport -> API -> Core Service -> Database) dengan jaminan *Zero Message Loss* (Database First).

---

## 4. Functional Requirements (FR)

### FR-1: Multi-Tenancy & Project Management
- **FR-1.1**: Setiap akun tenant dapat memiliki banyak Project (misal: Toko A, Toko B).
- **FR-1.2**: Setiap project memiliki Public API Key (`pk_live_...`) dan daftar Allowed Domains (whitelist).
- **FR-1.3**: Role-Based Access Control (RBAC): `Owner`, `Admin`, `Agent`.

### FR-2: Visitor Identification & Privacy
- **FR-2.1**: Pengunjung anonim mendapatkan `visitor_uuid` yang disimpan di browser `localStorage`.
- **FR-2.2**: Pengunjung dapat memberikan nama & email kapan saja tanpa merusak riwayat pesan anonim sebelumnya (Contact Merging).
- **FR-2.3**: Deteksi otomatis metadata pengunjung: Perangkat, Browser, OS, URL Halaman Aktif, Judul Halaman, dan IP.

### FR-3: Conversation & Message Engine
- **FR-3.1**: Dukungan tipe pesan: teks, gambar (JPG, PNG, WEBP), dokumen (PDF), dan pesan sistem.
- **FR-3.2**: Pengurutan pesan wajib matematis dan deterministik menggunakan sequential `messages.id`.
- **FR-3.3**: Dukungan idempotency menggunakan `client_message_id` untuk mencegah pesan ganda saat koneksi tidak stabil.
- **FR-3.4**: Status pesan lengkap: `sent`, `delivered`, `read`, `failed`.

### FR-4: Realtime Transport Abstraction
- **FR-4.1**: Polling adaptif cerdas: 2 detik saat tab aktif, 15-30 detik saat tab di-minimize, jeda saat offline.
- **FR-4.2**: Endpoint polling `GET /api/v1/realtime/poll?after_id=X` hanya mentransfer diff pesan baru (<2ms query time).
- **FR-4.3**: Abstraksi transport modular di SDK: Polling (default), SSE (opsional), WebSocket (future scale).

### FR-5: Embeddable Chat Widget
- **FR-5.1**: Menggunakan Web Components Shadow DOM (`attachShadow`) agar styling tidak terpengaruh CSS host.
- **FR-5.2**: Tombol pemicu mengambang (*floating trigger*) dengan badge angka unread.
- **FR-5.3**: Composer pesan multiline dengan shortcut `Enter` (kirim) dan `Shift + Enter` (baris baru).
- **FR-5.4**: Tampilan responsif: mengambang di desktop, drawer layar penuh di perangkat mobile (<640px).
- **FR-5.5**: Kustomisasi warna utama, teks greeting, posisi (kanan/kiri), dan avatar dari dashboard.

### FR-6: Admin Dashboard Inbox
- **FR-6.1**: Layout desktop 3-kolom: Daftar Percakapan (25%), Panel Chat Aktif (50%), Customer Context (25%).
- **FR-6.2**: Pembaruan inbox realtime tanpa reload halaman penuh (*no full page reload*).
- **FR-6.3**: Pengalihan penugasan agen (*assign conversation*) dan pergantian status (`open`, `pending`, `closed`).
- **FR-6.4**: Studio pengaturan widget dengan live preview interaktif.
- **FR-6.5**: Generator snippet instalasi dan verifikator koneksi script otomatis.

### FR-7: Webhooks & Integrations
- **FR-7.1**: Dispatch webhook event (`message.created`, `conversation.closed`, dll) dengan signature HMAC-SHA256.
- **FR-7.2**: Log pengiriman webhook dan mekanisme auto-retry dengan timeout 5 detik.

---

## 5. Non-Functional Requirements (NFR)

| Kategori | Parameter | Target Batas |
|---|---|---|
| **Performa Klien** | Bundle Size SDK | `< 25 KB` minified & gzipped |
| **Performa Klien** | Initial Load Time | `< 50 ms` di browser mobile standar |
| **Performa Server** | Polling Query Latency | `< 5 ms` per request di MySQL shared hosting |
| **Kompatibilitas** | Server Requirements | Laravel 7–10 (PHP 7.4+ hingga 8.2+), MySQL 5.7/8.0+, Apache/Nginx (No Redis, No Node.js) |
| **Keandalan** | Delivery Guarantee | *Zero Message Loss* (Database Transaction First) |
| **Keamanan** | Data Isolation | 100% strict tenant scoping via Global Scopes |
| **Keamanan** | Upload Sanitization | MIME verification, random filename hash, max 5MB |
| **Aksesibilitas** | Kontras & Keyboard | Standar WCAG AA, focus navigation, screen-reader status |

---

## 6. Success Metrics & KPIs

1. **Time-to-First-Message**: Pengunjung baru dapat membuka widget dan mengirim pesan dalam `< 3 detik`.
2. **Merchant Setup Time**: Pemilik web dapat memasang script dan memverifikasi koneksi dalam `< 5 menit`.
3. **Resource Efficiency**: Server shared hosting standar ($2–$5/bulan) mampu melayani 500 active concurrent visitors tanpa lonjakan memory/CPU error 503.
4. **Inbox Speed**: Agen dapat beralih antar percakapan di admin dashboard dalam `< 100 ms` tanpa jeda rendering.
