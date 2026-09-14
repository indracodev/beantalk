# Lessons Learned & Architecture Patterns

Dokumen ini mencatat koreksi, pola desain, dan aturan teknis yang dipelajari selama proses pengembangan platform **Universal Customer Chat (chat-me)** untuk mencegah kesalahan terulang di masa depan. Dokumen ini menjadi pilar memori yang melengkapi **[GEMINI.md](file:///c:/laragon/www/chat-me/GEMINI.md)** (standar teknis) dan **[AGENTS.md](file:///c:/laragon/www/chat-me/AGENTS.md)** (protokol agen).

---

## Pattern History & Lessons

### 1. Fondasi Arsitektur & Realtime
- **Pelajaran**: Jangan pernah mencoba memaksakan WebSocket daemon di lingkungan shared hosting.
- **Pola**: Gunakan abstraksi `RealtimeTransport` dengan `PollingTransport` sebagai default. Polling cerdas dengan indexed query `WHERE id > :after_id` menghemat resource dan 100% stabil di shared hosting.

### 2. Struktur Dokumentasi Modular
- **Pelajaran**: Pisahkan dokumentasi ke dalam sub-folder spesifik (misal: `docs/prd/`, `docs/architecture/`, `docs/database/`, `docs/api/`, dll.) agar tidak ada file raksasa yang sulit dipelihara saat proyek berkembang.
- **Pola**: Setiap sub-folder memiliki fokus tunggal dengan referensi saling terhubung di `docs/README.md`.

### 3. Batasan Versi Framework (Laravel 10 Maksimal s/d Laravel 7 Minimal)
- **Pelajaran**: Banyak shared hosting/cPanel lawas klien masih terkunci di PHP 7.4 atau PHP 8.0, sementara server modern menggunakan PHP 8.1/8.2.
- **Pola**: Wajib menggunakan arsitektur skeleton klasik (`app/Http/Kernel.php`, migrasi class tradisional, throttling route standar `throttle:60,1`) dan PHP syntax yang kompatibel dengan PHP 7.4 s/d 8.2. Dilarang keras mengunci kode pada skeleton eksklusif Laravel 11/12 (`bootstrap/app.php`) atau fitur PHP 8.2+ yang tidak backward-compatible.

### 4. Prioritas UI: Default Widget First & Bentuk Baku (Fixed Shape)
- **Pelajaran**: Mencoba membangun headless SDK untuk custom UI eksternal di tahap awal mengaburkan fokus dan membuang waktu. 99% merchant hanya ingin menempelkan satu baris script dan langsung mendapatkan widget chat yang cantik dan berfungsi.
- **Pola**: Terapkan Ponytail Ladder (YAGNI). Fokus 100% pada satu bundle widget bawaan (`chat-widget.js`) dengan Shadow DOM yang terpoles sempurna. **Bentuk, sudut radius, dan struktur tata letak bersifat tetap dan identik di semua web**, sedangkan kustomisasi per chat/project **hanya sebatas warna core UI (Primary Brand Color)**.

### 5. Multi-Site Integration Hub & Pemisahan Toolbar Prototype
- **Pelajaran**: Menambah channel/integrasi website baru harus sesederhana klik *"Create Integration"*, masukkan domain, dan pilih warna core UI (HEX/Color Picker) untuk langsung mendapatkan 1-baris script tag embed. Selain itu, pada file preview, controller demo (bar hitam atas) harus dibedakan secara visual dari Topbar Admin asli (bar putih SaaS) agar tidak membingungkan atau terlihat menumpuk berantakan.
- **Pola**:
  1. Buat **Integrations Hub** terpadu di Admin Dashboard untuk memantau seluruh website yang terkoneksi, status online, kode Public Key, dan warna aksen.
  2. Gunakan **Top Navbar Admin Standar Staff Engineer (Linear / Vercel style)**: 52px, Workspace Badge, tab navigasi (Inbox, Integrations, Contacts, Settings), Quick Search bar `⌘K`, tombol `+ Create Integration`, dan avatar status CS.
  3. Bar controller prototype di atasnya dibuat sangat tipis (`38px`), bernuansa gelap terisolasi, dan berlabel eksplisit *"Sandbox Demo (Hanya di File Prototype)"*.

### 6. Collapsible Left Sidebar Navigation (OmniChat / Staff Engineer SaaS Style)
- **Pelajaran**: Agen CS membutuhkan fleksibilitas ruang horizontal saat menangani percakapan multi-channel. Navigasi samping (*left sidebar*) harus dapat di-minimize ke mode *icon-only rail* tanpa menghilangkan identitas akun maupun akses cepat ke menu penting.
- **Pola**:
  1. Gunakan **Collapsible Left Sidebar** (`width: 220px` saat expanded, `width: 68px` saat minimized).
  2. Transisi mulus `transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1)`.
  3. Saat minimized, sembunyikan label teks & badge, pertahankan ikon di tengah dengan tooltip hover, dan putar tombol toggle 180°.
  4. Letakkan menu operasional utama di atas (`Inbox`, `Integrations`, `Team`, `Archive`, `Tags`, `Analytics`), serta `Settings` & Profil Pengguna (`John Doe` / `Sarah` Online) di bagian footer sidebar.

### 7. Larangan Keras Emoji Sistem & Kewajiban Pure Vector SVG
- **Pelajaran**: Penggunaan emoji bawaan sistem operasi (seperti 📥, 🔌, 👥, 📁, 🏷️, 📊, ⚙️, 💬, 🔍, 📎, 🛒, 📸) membuat tampilan tidak konsisten antar OS (Apple, Windows, Android merender emoji berbeda), memicu masalah baseline font alignment, dan menurunkan persepsi profesionalitas software enterprise.
- **Pola**:
  1. **Strictly Ban System Emojis** di seluruh komponen produksi (Admin Dashboard, Chat Widget, dan Hub Integrations).
  2. Gunakan **Pure Vector SVG Inline** (`viewBox="0 0 24 24"`, stroke line-art `1.8px - 2.2px`, `currentColor`, zero external font dependencies).
  3. Menjamin tampilan 100% konsisten, tajam (*pixel-perfect*), ringan, dan berkelas dunia setara Linear/Stripe.

### 8. Arsitektur Navigasi Widget 2-Tahap: Halaman Awal ➔ Chat Utama
- **Pelajaran**: Membuka langsung ruang obrolan kosong tanpa greeting yang ramah sering membingungkan pengunjung website toko. Pengunjung memerlukan halaman sambutan yang jelas (*Welcome Hub*), kepastian status agen online, preview pesan sebelumnya, serta saluran kontak alternatif jika mereka meninggalkan website.
- **Pola**:
  1. **Stage 1 (Halaman Awal / Welcome Hub)**:
     - Header lengkung dengan sapaan hangat ("Hallo! Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini!").
     - **Card 1 (Your Conversation)**: Indikator denyut hijau real-time `● Live Chat Available`, avatar brand, nama toko, cuplikan pesan terakhir, dan chevron `>` yang mengarahkan pembeli ke ruang obrolan utama saat diklik.
     - **Card 2 (Reach Us Anywhere Else)**: Fallback direct channel (WhatsApp resmi `#25D366`, Facebook Messenger `#1877F2`, dan Instagram DM) agar obrolan tetap bisa berlanjut di luar website.
     - Footer netral `Powered by chat-me` dengan logo SVG.
  2. **Stage 2 (Chat Utama / Active Thread)**:
     - Header dilengkapi tombol Back (`←` SVG) di sudut kiri atas untuk kembali ke Halaman Awal secara mulus kapan saja tanpa memutus koneksi chat atau menghilangkan riwayat pesan.
     - Thread pesan, context card produk, kompresi foto klien, dan composer bar.

### 9. Penegakan RBAC Hierarkis Multi-Tenant (Owner, Admin, Agent)
- **Pelajaran**: Membiarkan seluruh pengguna dashboard memiliki hak setara sangat berbahaya bagi data tenant e-commerce. Staf customer service (Agent) tidak boleh diizinkan menambah/menghapus integrasi web toko, mengubah API keys, atau mengotak-atik akun staf lain.
- **Pola**:
  1. Terapkan **3-Tier Hierarchical RBAC**: `owner`, `admin`, `agent`.
  2. **Strict Server-Side Middleware Guard**: Setiap mutasi dilindungi middleware `CheckRole` (`role:owner,admin` untuk integrasi & staf, `role:owner` untuk ubah role/hapus staf). Menolak request tidak sah dengan kode `403 FORBIDDEN` format envelope standar.
  3. **UI State & Sandbox Role Switcher**: Tampilkan badge peran visual (`Owner` emas, `Admin` indigo, `Agent` zamrud), redupkan tombol terlarang dengan tooltip pencegahan, dan tampilkan notifikasi toast informatif saat akses ditolak tanpa merusak alur aplikasi (*zero crash*).

### 10. Audit Trail & Real-time Activity Logging Multi-Role (Zero-Daemon Shared Hosting)
- **Pelajaran**: Dalam operasional customer service multi-agen dan toko online, ketertelusuran tindakan (*auditability*) mutlak diperlukan untuk mencegah sengketa ("siapa yang mengubah setting integrasi?", "siapa yang membalas chat ini?", "siapa yang mengundang staf baru?"). Namun, mewajibkan message queue (Redis/RabbitMQ) atau background worker daemon untuk logging akan melanggar prinsip *Shared Hosting First*.
- **Pola**:
  1. **Tabel MySQL Terindeks Cepat (`activity_logs`)**: Skema mencakup `tenant_id`, `user_id`, `role`, `action`, `entity_type`, `entity_id`, `description`, `details` (JSON), `ip_address`, dan `user_agent`. Menggunakan komposit index `(tenant_id, created_at)` dan `(tenant_id, role, created_at)` sehingga operasi `INSERT` langsung memakan waktu < 1ms tanpa beban tambahan.
  2. **Zero-Daemon Synchronous Helper (`ActivityLogger::log(...)`)**: Secara otomatis mengekstrak user aktif, role, IP, dan User-Agent dari request runtime Laravel, serta mendukung logging aksi anonim/sistem/visitor (`role: 'visitor'`).
  3. **Event Hooks di Controller Kunci**: Pasang hook langsung pada `IntegrationController@store` (`integration.created`), `TeamController@store` (`team.invited`), `TeamController@updateRole` (`role.updated`), `TeamController@destroy` (`team.removed`), dan `ConversationController@reply` (`message.replied`).
  4. **REST API Endpoint Tervalidasi**: Expose `GET /api/v1/admin/activity-logs` dengan filter `role`, `user_id`, `action`, dan paginasi standar envelope `{ success: true, data: { ... } }`.
  5. **UI Hub Interaktif dengan Quick Filters**: Buat sub-view Activity Logs di dashboard dengan filter chip real-time (`All`, `Owner`, `Admin`, `Agent`, `Visitor`) dan kemampuan *live append* saat simulasi interaksi dijalankan.

### 11. Universal JS SDK Bundling & Web Component Shadow DOM Isolation
- **Pelajaran**: Memasukkan framework UI runtime besar (seperti React/Vue) ke dalam widget embed pihak ketiga memperlambat kecepatan toko online merchant, membengkakkan ukuran script hingga ratusan kilobyte, dan berisiko memicu bentrokan CSS dengan tema website toko.
- **Pola**:
  1. **Zero-Dependency Pure TypeScript**: Gunakan Web APIs asli (`attachShadow({ mode: 'open' })`, `fetch`, `localStorage`, `crypto.randomUUID`) dan bundling via `esbuild` untuk menghasilkan file tunggal IIFE `public/chat-widget.js` yang sangat ringan (< 37 KB uncompressed, < 10 KB gzipped).
  2. **Shadow DOM Total Isolation**: Seluruh style CSS disuntikkan ke dalam `#shadow-root (open)` dengan `:host` reset, menjamin widget 100% kebal dari bentrokan CSS milik tema website toko host.
  3. **Auto-Boot Script Tag**: Menyediakan auto-initialization via tag `<script src="/chat-widget.js" data-project-key="pk_live_xxx">` serta API programatik `window.ChatWidget` (`open()`, `close()`, `toggle()`, `sendMessage()`).
  4. **Smart Adaptive Polling**: Mengadaptasi interval polling (2.5s saat widget terbuka & tab aktif; 15s saat tab diminimize atau widget tertutup) dengan indexed range scan `after_id`.

### 12. Identitas Brand Utama: BeanTalk (Universal Customer Chat)
- **Pelajaran**: Penamaan brand produk SaaS harus berkarakter kuat, mudah diingat, dan memiliki sentuhan personal yang ramah (*approachable*) bagi merchant dan pembeli. Brand generic seperti "chat-me" di-upgrade menjadi **BeanTalk**.
- **Pola**:
  1. **Konsistensi Lintas Modul**: Perbarui seluruh representasi brand ke **BeanTalk** di seluruh titik sentuh: Admin Sidebar, Top Toolbar Sandbox, Welcome Hub Widget (`Powered by BeanTalk`), SDK global namespace (`window.BeanTalk`), serta dokumentasi master (`GEMINI.md`, `AGENTS.md`, `docs/`).
  2. **Backward Compatibility**: Pertahankan alias transisi (`window.ChatWidget`, `#universal-chat-root`) di client library SDK agar kode integrasi sebelumnya tetap berjalan tanpa kendala (*zero breaking changes*).

### 13. Modernisasi Framework: Upgrade ke Laravel 10 (PHP 8.1 s/d 8.4 LTS)
- **Pelajaran**: Lingkungan development pengguna sering kali memiliki PHP versi mutakhir (seperti PHP 8.2 atau PHP 8.4). Menjaga framework di Laravel 7 menimbulkan peringatan *deprecation* (seperti `ReturnTypeWillChange` dan `implicitly nullable parameter`).
- **Pola**:
  1. **Pertahankan Classic Skeleton**: Tetap gunakan struktur klasik (`app/Http/Kernel.php`, named migrations) sesuai kontrak [GEMINI.md](file:///c:/laragon/www/chat-me/GEMINI.md) agar tetap 100% kompatibel di Shared Hosting dan tidak memerlukan perubahan struktur folder radikal.
  2. **Ganti Paket Legacy dengan Core Native**: Hapus `fruitcake/laravel-cors` dan `fideloper/proxy`, ganti dengan `\Illuminate\Http\Middleware\HandleCors` dan `\Illuminate\Http\Middleware\TrustProxies`.
  3. **Migrasi Schema PHPUnit 10**: Jalankan `vendor/bin/phpunit --migrate-configuration` untuk memodernisasi tag `<coverage>` dan `<source>`.
  4. **Zero-Deprecation Verification**: Menjamin seluruh unit/feature tests lulus 100% baik pada PHP 8.2 maupun PHP 8.4 global.

### 14. Penguncian Platform PHP di Composer (`config.platform.php`)
- **Pelajaran**: Jika `composer update` dijalankan di mesin development dengan versi PHP lebih tinggi (misal PHP 8.4) tanpa mendefinisikan `config.platform.php`, Composer dapat mengunduh dependensi (seperti komponen Symfony 7.x) yang menuntut PHP >= 8.2. Hal ini menyebabkan error fatal `platform_check.php` saat dijalankan di server dengan PHP 8.1.
- **Pola**:
  1. **Tentukan Baseline Platform**: Selalu tambahkan `"config": { "platform": { "php": "8.1.0" } }` di `composer.json` agar Composer menyelesaikan dependensi ke versi LTS (seperti Symfony 6.4) yang didukung resmi oleh target minimum server (PHP 8.1 s/d 8.4).
  2. **Verifikasi `platform_check.php`**: Pastikan baris `PHP_VERSION_ID >= 80100` di `vendor/composer/platform_check.php` sehingga berjalan mulus di PHP 8.1.x lokal maupun server produksi tanpa hambatan.

### 15. Kustomisasi Saluran Tambahan ("Find Us Somewhere Else") & Route Cache
- **Pelajaran**: Tombol saluran sosial dan marketplace pada Stage 1 (Welcome Screen) widget sangat krusial bagi pengunjung website, namun setiap toko memiliki channel resmi yang berbeda-beda (ada yang hanya memakai WhatsApp & Shopee, ada yang memakai Instagram & Tokopedia). Selain itu, saat mendaftarkan route baru di Laravel dengan route caching aktif (`bootstrap/cache/routes-v7.php`), route baru tidak akan terbaca sebelum cache diperbarui.
- **Pola**:
  1. **Dynamic Social Channels Schema**: Simpan konfigurasi saluran dalam kolom JSON `social_channels` dan teks judul kustom `find_us_title` pada tabel `widget_settings`.
  2. **Normalisasi Otomatis**: Normalisasi nomor telepon WhatsApp menjadi URL standar `https://wa.me/62...`, username Instagram menjadi `https://instagram.com/...`, dan Telegram menjadi `https://t.me/...` di backend secara transparan.
  3. **Conditional Rendering di SDK**: Widget hanya merender saluran yang dicentang aktif dan memiliki URL valid. Jika tidak ada saluran yang diaktifkan, kartu saluran disembunyikan secara otomatis agar tampilan tetap bersih.
  4. **Route Optimization Hygiene**: Selalu jalankan `php artisan optimize` / `php artisan route:clear` segera setelah mendaftarkan route web baru untuk memastikan route terdaftar pada cache aplikasi.

### 16. Alur Multi-Stage Widget (Welcome -> Identity Screen -> Active Chat) & Custom Support Title
- **Pelajaran**: Menampilkan form nama langsung di Welcome Hub bersamaan dengan kartu chat dan saluran media sosial membuat tampilan penuh sesak. Pengunjung website lebih menyukai alur interaksi terpandu (*step-by-step onboarding*): klik tombol obrolan ➔ perkenalan nama ➔ masuk ruang obrolan.
- **Pola**:
  1. **3-Stage Navigation State**: Pisahkan tampilan widget menjadi `stage-welcome`, `stage-identity`, dan `stage-chat`.
  2. **Interlocking Triggers**: Klik kartu dukungan di `stage-welcome` memicu `goToStage('identity')`. Tombol *"Lanjut"* atau tombol Enter pada keyboard menyimpan nama dan beralih ke `goToStage('chat')`.
  3. **Customizable Support Title**: Sediakan opsi `support_title` di database dan admin dashboard sehingga pemilik website bebas memberi nama tombol layanan (misal: "Customer Support", "Live Support", "Layanan Pelanggan").

### 17. Handshake Polling & Pencegahan Notifikasi Palsu untuk Pesan yang Sudah Dibaca
- **Pelajaran**: Saat melakukan polling realtime di dashboard, jika endpoint `/admin/inbox/feed/updates` tidak membedakan antara *koneksi pertama (handshake baseline sync)* dan *polling berkala*, server akan mengambil seluruh pesan riwayat masa lalu dan menganggapnya sebagai pesan masuk baru (`has_new_incoming: true`). Akibatnya, setiap kali admin membuka/me-refresh halaman apa pun, notifikasi toast dan suara chime berbunyi berulang-ulang untuk chat lama yang sebenarnya sudah dibaca.
- **Pola**:
  1. **Handshake vs Polling**: Periksa keberadaan parameter `$request->has('since_message_id')`. Jika parameter tidak ada (koneksi awal), kembalikan `has_new_incoming: false` dan jadikan `max_message_id` sebagai baseline.
  2. **Gating Percakapan Belum Dibaca**: Saat mencari pesan baru (`id > since_message_id`), tambahkan kondisi `whereHas('conversation', fn($q) => $q->where('unread_agent_count', '>', 0))`. Jika tiket chat sudah dibuka atau dibaca oleh CS (`unread_agent_count == 0`), jangan pernah membunyikan chime atau memunculkan toast popup!
  3. **Client-side Deduplication**: Simpan `lastNotifiedMsgId` di `sessionStorage` agar ID pesan yang sama tidak pernah memicu notifikasi lebih dari satu kali meskipun terjadi *network retry*.

### 18. Standar Terminologi Platform Web Universal (Non-Store Centric)
- **Pelajaran**: Platform BeanTalk dirancang sebagai *Universal Customer Chat* untuk segala jenis situs (SaaS, profil perusahaan, portal edukasi, organisasi, maupun web app), bukan hanya toko online e-commerce. Penggunaan kata "toko", "store", "Store Support", atau "pelanggan toko" membingungkan pengguna non-e-commerce.
- **Pola**:
  1. **Universal Vocabulary**: Gunakan istilah "Website", "Halaman Utama", "Customer Support", "Live Support", atau "Pengunjung", bukan "Toko", "Beranda Toko", atau "Store Support".
  2. **Script Attribute Aliasing**: Dukung atribut sematan universal seperti `data-brand-name` dan `data-support-title` dengan tetap mempertahankan fallback backward-compatible `data-store-name`.
  3. **Default Label Netral**: Gunakan `'Customer Support'` sebagai default teks layanan di seluruh migration, model, view, dan SDK.

### 19. Scoping Global Swiss Loader (Navigasi Utama vs Workspace Internal)
- **Pelajaran**: Pemisahan eksekusi loader global Swiss antara navigasi utama (menu Sidebar/Bottom-Nav Inbox, Websites, Team, Logs) dan workspace interaktif internal (klik kartu percakapan chat, ganti filter scope status `Semua/Open/Mine/Selesai`, pencarian tiket) harus dilakukan berdasarkan *asal klik (click origin)*, bukan pemblokiran rute URL secara membabi-buta (`href.includes('/admin/inbox')`). Jika diblokir berdasarkan rute, klik menu "Inbox" dari sidebar/bottom-nav tidak akan menampilkan loader sama sekali.
- **Pola**:
  1. **Capture Phase Click Interceptor**: Pasang listener `document.addEventListener('click', handler, true)` (capture phase) pada `admin.js` agar klik navigasi menu utama tidak terblokir oleh `stopPropagation` elemen anak.
  2. **Navigasi Utama Prioritas Tinggi**: Deteksi klik menu utama (`link.closest('#main-sidebar')`, `link.closest('#mobile-bottom-nav')`, `#navItemInbox`, `#bottomNavItemInbox`, `.sidebar-item`) dan panggil `BeanTalkLoader.show('Memuat Inbox...')`.
  3. **Pengecualian Khusus Workspace Internal**: Jika klik berasal dari dalam workspace obrolan (`#inboxWorkspace`, `#convListContainer`, `#pane-conv-list`, `.conv-item`, `.conv-row`, `.inbox-scope-btn`, `[data-conv-id]`, `.no-loader`), hapus flag `sessionStorage` dan jangan panggil loader, sehingga perpindahan antar ruang chat terasa instan seperti SPA tanpa kedip.
  4. **Asset Versioning**: Selalu sertakan query string timestamp `?v={{ filemtime(...) }}` pada pemanggilan file CSS dan JS di layout blade agar perbaikan logika loader langsung diterapkan browser tanpa tertahan browser cache.


