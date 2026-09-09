# BeanTalk (Universal Customer Chat) — Documentation Hub

Platform **BeanTalk** — Universal Customer Chat multi-tenant yang dirancang dengan filosofi:
> **"One Core API, One Universal SDK, Many Platforms."**

Sistem ini didesain agar dapat berjalan di lingkungan **Shared Hosting** (PHP 8.2+, MySQL, Apache/Nginx) tanpa ketergantungan wajib pada Redis, Supervisor, WebSocket daemon, atau Docker, namun siap di-scale ke VPS/WebSocket di masa depan tanpa mengubah API SDK.

---

## 🖥️ Interactive UI Preview (Demo Langsung)

Anda dapat membuka file preview interaktif langsung di browser untuk melihat dan berinteraksi dengan kedua antarmuka secara live:
- **[preview.html](file:///c:/laragon/www/chat-me/preview.html)** *(Buka langsung di browser Anda via file lokal atau `http://chat-me.test/preview.html`)*
  - **View 1: 🏢 Admin Dashboard**: Tampilan 3-kolom Linear-style, filter per project, chat feed, customer context.
  - **View 2: 🛍️ Website Toko + Widget**: Simulasi toko Shopify dengan widget floating interaktif.
  - **View 3: ⚡ Live Simulation (Side-by-Side)**: Uji coba kirim pesan dari sisi pembeli dan balas langsung dari sisi CS dalam 1 layar!
  - **Color Picker Dinamis**: Uji coba ganti warna brand (Supresso Dark Gold, Indraco Brown, Global Navy, SDA Red) yang membuktikan bentuk widget tetap baku dan hanya warnanya yang berganti.

---

## 🗂️ Struktur & Indeks Dokumentasi Modular

Dokumentasi telah diorganisir ke dalam 10 sub-folder modular:

```text
docs/
├── README.md                              # Hub utama dokumentasi (file ini)
│
├── prd/                                   # Product Requirement Documents (PRD)
│   ├── 01-product-requirement-document.md # PRD Master, Persona, FR/NFR & KPI
│   ├── 02-user-stories-and-use-cases.md   # Cerita pengguna & skenario Gherkin
│   └── 03-scope-matrix-mvp-vs-future.md   # Matriks MVP vs Future Roadmap (Ponytail)
│
├── architecture/                          # Blueprint Arsitektur Sistem
│   ├── 01-overview.md                     # Visi produk, pemisahan layer & flow
│   ├── 02-realtime-transport.md           # Abstraksi Polling, SSE, WebSocket & fallback
│   ├── 03-multi-tenancy.md                # Isolasi tenant, RBAC & scope resolver
│   ├── 04-multi-project-real-world-flow.md# Skenario Riil 4 Web (Indracostore, Supresso, Global, SDA)
│   └── 05-design-system-and-reusable-components.md # Desain Sistem & Komponen Reusable (Anti-Hardcode)
│
├── database/                              # Desain Basis Data & Kinerja
│   ├── 01-schema-and-entities.md          # 18 Entitas lengkap, DDL & foreign keys
│   └── 02-indexing-and-performance.md     # Strategi indeks B-Tree polling & auto-pruner
│
├── api/                                   # Spesifikasi REST API v1
│   ├── 01-overview-and-auth.md            # Autentikasi, format respon & rate limit
│   ├── 02-client-endpoints.md             # Endpoint public visitor, polling & attachments
│   ├── 03-admin-endpoints.md              # Endpoint inbox, reply, assign & status
│   └── 04-webhooks.md                     # Webhook signature SHA-256 & retry policy
│
├── sdk/                                   # Universal JavaScript SDK
│   ├── 01-architecture-and-bundle.md      # Struktur packages/chat-sdk & bundle target
│   ├── 02-api-and-events.md               # API Chat.init, methods & typed event emitter
│   ├── 03-adaptive-polling.md             # Smart throttling berdasarkan visibilitas tab
│   └── 04-client-context-and-media-compression.md # Deteksi Halaman & Kompresi Foto Lokal (Canvas)
│
├── widget/                                # Embeddable Chat Widget UI
│   ├── 01-shadow-dom-and-isolation.md     # Isolasi CSS via Web Components Shadow DOM
│   └── 02-ui-components-and-theming.md    # Floating trigger, composer & token CSS vars
│
├── ui/                                    # Panduan Visual & Anti-AI Slop
│   └── 01-visual-design-and-theming-guide.md # Standar Estetika Linear/Vercel/Intercom
│
├── admin-dashboard/                       # Dashboard Admin Laravel
│   ├── 01-inbox-layout.md                 # 3-Kolom inbox, responsive drawer & realtime
│   ├── 02-project-and-widget-settings.md  # Live preview studio & connection verifier
│   └── 03-pages-and-routing.md            # Arsitektur 4 Halaman Blade, Asset Publik & Routing
│
├── integrations/                          # Panduan Integrasi Platform Host
│   ├── 01-html-and-frameworks.md          # HTML, WordPress, Laravel Blade & React/Vue/Next
│   └── 02-shopify.md                      # Kompatibilitas Shopify tanpa private backend
│
├── deployment/                            # Deployment & Keamanan Produksi
│   ├── 01-shared-hosting.md               # Panduan cPanel/DirectAdmin, cron & .htaccess
│   ├── 02-vps-and-scaling.md              # Migrasi ke VPS, Octane, Redis & Reverb
│   ├── 03-security-and-hardening.md       # Public key security, sanitasi upload & audit
│   ├── 04-local-setup-and-run-guide.md    # Panduan Menjalankan dari Awal (Step-by-step Lokal)
│   └── 05-server-deployment-guide.md      # Panduan Lengkap Deployment Server (PHP 8.1+ / VPS / cPanel)
│
└── roadmap/                               # Rencana Kerja & Kriteria Keberhasilan
    ├── 01-implementation-phases.md        # Urutan 8 fase pengerjaan (Phase 1-8)
    └── 02-definition-of-done.md           # Kriteria DoD & checklist pengujian end-to-end
```

---

## 🏛️ Arsitektur Aplikasi Internal & Konfigurasi Branch

1. **Branch `main` (Aplikasi Internal Perusahaan)**:
   - **2 Peran Inti (RBAC)**: **`superadmin`** (akses penuh & kelola staf) dan **`agent`** (staf operasional CS).
   - **Otentikasi Ganda**: Mendukung login via **Email** atau **Username** (`superadmin`, `sarah`, `budi`).
   - **Default 100% Light Theme**: Tampilan bawaan bersih bernuansa light dengan aksen brand emas (`#C59B27`) dan tombol toggle manual Dark Mode.
   - **4 Halaman Admin Blade Interaktif**: Live Inbox (`/admin/inbox`), Integrasi Multi-Website (`/admin/integrations`), Manajemen Tim CS (`/admin/team`), dan Activity Logs (`/admin/logs`).
   - **Struktur Layouting & Asset Publik**: Aset CSS/JS terpusat di `public/css/` dan `public/js/`, layout Blade modular dengan parsial `layouts/partials/sidebar.blade.php`.
2. **Branch `saas-version` (Pencadangan SaaS Multi-Tenant)**:
   - Menyimpan versi multi-tenant 3-role hirarkis (`owner`/`superadmin`, `admin`, `agent`).


---

## ✂️ Penerapan Filosofi Ponytail (Pragmatic Engineering)

Sistem ini menerapkan prinsip **Ponytail** secara konsisten di seluruh modul:

1. **YAGNI & Ladder First**: Jika fitur bawaan standar (misal: CSS custom properties, Shadow DOM, MySQL composite indexes, PHP standard library) mampu menyelesaikan kebutuhan, dilarang menambah framework atau microservice yang tidak diminta.
2. **Default Transport: Adaptive Polling**: Tidak memaksakan daemon Node.js / WebSocket / Redis di shared hosting. Polling cerdas (2s aktif, 15s inactive, stop saat widget tutup) menggunakan query terindeks `WHERE id > :after_id` membutuhkan CPU < 2ms dan 100% tahan banting.
3. **Penyimpanan Single Source of Truth**: Database MySQL Laravel adalah satu-satunya sumber kebenaran. Realtime transport hanyalah pipa akselerasi notifikasi.
4. **Deliberate Shortcuts (`# ponytail:`)**: Setiap keputusan teknis yang disederhanakan secara sengaja diberi penanda batasan kapasitas dan kapan harus di-upgrade.
