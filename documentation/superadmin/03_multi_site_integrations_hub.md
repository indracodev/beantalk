# Modul Integrasi Multi-Website & Hub Kunci API (Superadmin)

## 1. Deskripsi Fitur
Modul Integrasi Multi-Website merupakan pusat pengelolaan seluruh properti digital (website toko, landing page, aplikasi web portal) yang dilayani oleh BeanTalk. Melalui hub ini, Superadmin dapat mendaftarkan website baru, menentukan domain yang diizinkan (*whitelisted domains* untuk mencegah pencurian kuota API melalui *CORS/Origin Protection*), mengelola kunci publik proyek (*Project API Key*), serta menghasilkan kode sematan (*Embed Script Tag*) siap pakai untuk ditempelkan pada website tujuan (Shopify, WordPress, WooCommerce, Laravel Blade, dsb).

---

## 2. Hak Akses & Role Permission
- **Role:** `superadmin` (Superadmin)
- **Middleware:** `web`, `auth`
- **Keamanan:** Hanya Superadmin yang diizinkan membuat proyek baru, melihat atau meregenerasi kunci API rahasia. Agen biasa hanya memiliki akses baca terbatas jika ditugaskan.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php)
  - `integrations()`: Menampilkan daftar seluruh proyek website yang terintegrasi.
  - `storeIntegration()`: Mendaftarkan website baru dan menghasilkan kunci API otomatis.
  - `integrationDetail()`: Menampilkan detail pengaturan dan kode sematan suatu proyek.
  - `regenerateKey()`: Meregenerasi kunci proyek jika terjadi kebocoran kredensial.
- View: `resources/views/admin/integrations.blade.php` dan `resources/views/admin/integration-detail.blade.php`
- Route:
  - `GET /admin/integrations` (`name('admin.integrations')`)
  - `POST /admin/integrations` (`name('admin.integrations.store')`)
  - `GET /admin/integrations/{id}` (`name('admin.integrations.detail')`)
  - `POST /admin/integrations/{id}/regenerate` (`name('admin.integrations.regenerate')`)

### Skema Tabel Database Terkait:
1. **Tabel `projects`:**
   - `id`: Identifier primer.
   - `tenant_id`: Foreign key multi-tenant.
   - `name`: Nama website toko (cth: *Indraco Store Official*).
   - `slug`: Identifier URL unik (cth: *indracostore*).
   - `platform`: Jenis CMS/Platform (`shopify`, `woocommerce`, `wordpress`, `custom_html`).
   - `status`: Status aktifasi (`active`, `inactive`).
2. **Tabel `project_domains`:**
   - `id`, `project_id`, `domain` (cth: `indracostore.com`, `staging.indracostore.com`).
   - Mencegah inisialisasi chat widget dari domain tidak resmi.
3. **Tabel `api_keys`:**
   - `id`, `project_id`, `key` (Format: `pk_live_[random_hash]`), `type` (`public`), `is_active`.

---

## 4. Flowchart Alur Pendaftaran Proyek & Pembuatan Kode Embed

```mermaid
flowchart TD
    Start([Superadmin Buka Menu Integrasi /admin/integrations]) --> ViewProjectList[Tampilkan Daftar Website yang Sudah Terdaftar]
    
    ViewProjectList --> ActionChoice{Pilih Tindakan Superadmin}
    
    %% Alur Registrasi Website Baru
    ActionChoice -- Tambah Website Baru --> OpenCreateModal[Buka Modal Tambah Proyek Integrasi]
    OpenCreateModal --> InputProjectData[Isi: Nama Website, Platform Shopify/WooCommerce, dan Domain Whitelist]
    InputProjectData --> SubmitProject[POST ke /admin/integrations]
    
    SubmitProject --> ValidateProjectInput{Validasi Nama & Domain?}
    ValidateProjectInput -- Gagal --> ShowValidationError[Tampilkan Pesan Validasi Error]
    ValidateProjectInput -- Sukses --> SaveProjectDB[(Simpan Record ke Tabel projects & project_domains)]
    
    SaveProjectDB --> GenerateUniqueKey[Generate API Public Key Unik: pk_live_...]
    GenerateUniqueKey --> SaveApiKeyDB[(Simpan Kunci ke Tabel api_keys)]
    SaveApiKeyDB --> CreateDefaultWidgetSettings[(Inisialisasi Pengaturan Widget Bawaan ke widget_settings)]
    CreateDefaultWidgetSettings --> LogCreateActivity[AuditLogger Catat: integration.created]
    LogCreateActivity --> RedirectDetail[Buka Halaman Detail Integrasi]

    %% Alur Lihat Kode Sematan & Regenerasi Kunci
    ActionChoice -- Kelola Proyek Eksisting --> SelectProject[Pilih Salah Satu Proyek Website]
    SelectProject --> LoadDetailView[DashboardController::integrationDetail Ambil Data Proyek, Domain, & API Key]
    LoadDetailView --> RenderEmbedStudio[Tampilkan Studio Kode Sematan & Konfigurasi Widget]
    
    RenderEmbedStudio --> CheckRegenerateAction{Superadmin Butuh Ganti Kunci API?}
    CheckRegenerateAction -- Ya, Kunci Lama Bocor --> ConfirmRegenerate[Konfirmasi Dialog: Regenerate API Key]
    ConfirmRegenerate --> PostRegenerate[POST ke /admin/integrations/{id}/regenerate]
    PostRegenerate --> DeactivateOldKey[Nonaktifkan Kunci Lama & Buat Kunci Baru]
    DeactivateOldKey --> LogRegenActivity[AuditLogger Catat: api_key.regenerated]
    LogRegenActivity --> UpdateCodeSnippet[Perbarui Kode Sematan dengan Kunci Baru]

    CheckRegenerateAction -- Tidak / Copy Code --> CopyEmbedCode[Salin Kode HTML Embed Script Tag]
    CopyEmbedCode --> PasteToWebsite[Tempelkan ke Template Website Sebelum Tag Penutup Body]

    UpdateCodeSnippet & PasteToWebsite --> End([Widget Berhasil Aktif di Website Klien])
```

---

## 5. Rincian Antarmuka & Langkah Integrasi

### A. Mendaftarkan Website Toko Baru:
1. Akses menu **Integrasi Multi-Site** di bilah menu samping.
2. Klik tombol **Tambah Website Baru (+)** di sudut kanan atas.
3. Masukkan:
   - **Nama Proyek:** Misalnya `Supresso Coffee Global`.
   - **Tipe Platform:** Pilih *Shopify*, *WooCommerce*, atau *Custom HTML / Framework*.
   - **Domain Resmi:** Masukkan URL website utama (misal: `https://supresso.com`).
4. Klik tombol **Simpan & Buat Proyek**.

### B. Memasang Widget di Website Toko:
1. Pada kartu proyek yang baru dibuat, klik **Buka Detail Integrasi**.
2. Cari kotak **Snippet Kode Sematan (Embed Code)**:
   ```html
   <!-- BeanTalk Universal Customer Chat Widget -->
   <script 
     src="https://chat.indraco.com/chat-widget.js" 
     data-project-key="pk_live_8fbc29194b1e77d29" 
     async defer>
   </script>
   ```
3. Klik tombol **Salin Kode (Copy to Clipboard)**.
4. Buka pengelola template website Anda (contoh: pada Shopify, buka menu `Online Store` > `Themes` > `Edit code` > `theme.liquid`), lalu tempelkan kode tersebut tepat sebelum tag `</body>`.
5. Buka website toko Anda; widget BeanTalk akan langsung muncul mengambang di pojok layar secara otomatis!
