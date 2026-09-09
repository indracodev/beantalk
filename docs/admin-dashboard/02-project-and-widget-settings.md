# Admin Dashboard: Project Management & Widget Studio

Bagian ini mendokumentasikan antarmuka konfigurasi project, widget styling studio, pembuatan API Key, dan verifikasi koneksi script.

---

## 1. Project Management

Admin dan Owner dapat mengelola banyak project di bawah satu akun Tenant.

### Field Entitas Project:
- **Project Name**: Nama project (misal: "Shopify Store US", "Landing Page Utama")
- **Project Slug**: URL-friendly identifier
- **Allowed Domains**: Daftar domain yang diizinkan memanggil API (mencegah pencurian widget key)
- **Status**: Active / Inactive

### Navigasi Tab dalam Project:
Untuk meminimalkan transisi halaman (*reduce page transitions*), seluruh konfigurasi project disatukan dalam satu workspace ber-tab:
```text
Project: Shopify US
├── [ Overview ]      -> Metrik ringkas, status koneksi, traffic chat hari ini
├── [ Widget Studio ] -> Konfigurasi warna, teks, greeting, dan preview realtime
├── [ Installation ]  -> Generator kode snippet HTML & verifikasi koneksi
├── [ API Keys ]      -> Kunci publik & manajemen domain
└── [ Webhooks ]      -> Pengaturan webhook eksternal
```

---

## 2. Widget Studio (Live Preview Configurator)

Halaman pengaturan widget menampilkan formulir input di sisi kiri dan simulasi widget live interaktif di sisi kanan.

```text
┌───────────────────────────────────────┬───────────────────────────────────────┐
│ Formulir Pengaturan                   │ Simulasi Interaktif (Live Preview)    │
│                                       │                                       │
│ Widget Title:                         │  ┌─────────────────────────────────┐  │
│ [ Customer Support                  ] │  │ [Avatar] Customer Support   [X] │  │
│                                       │  ├─────────────────────────────────┤  │
│ Greeting Message:                     │  │ Agent:                          │  │
│ [ Hi there! How can we help you?    ] │  │ Hi there! How can we help you?  │  │
│                                       │  │                                 │  │
│ Primary Brand Color:                  │  │                                 │  │
│ [ #0F172A ]  [ Color Picker ]         │  │                                 │  │
│                                       │  ├─────────────────────────────────┤  │
│ Position:                             │  │ [ Type a message...           ] │  │
│ (•) Bottom Right    ( ) Bottom Left   │  └─────────────────────────────────┘  │
│                                       │                     💬                │
│ [ ] Hide Powered by Branding          │                                       │
│                                       │ Perubahan warna & teks langsung       │
│ [ Save Changes ]                      │ ter-update di preview tanpa simpan.   │
└───────────────────────────────────────┴───────────────────────────────────────┘
```

Perubahan pada input formulir langsung mengupdate style preview via CSS variable tanpa perlu reload browser.

---

## 3. Installation Snippet & Connection Checker

### 3.1 Snippet Generator
Menghasilkan tag script siap salin:
```html
<!-- Universal Customer Chat -->
<script
  src="https://chat.example.com/widget.js"
  data-key="pk_live_839df4a18c"
  async>
</script>
```

### 3.2 Live Connection Checker (Pemeriksa Koneksi Otomatis)
Setelah merchant memasang kode di website mereka, dashboard menyediakan tombol **"Verify Installation"**.

Sistem melakukan:
1. Pengecekan HTTP Ping ke domain website target.
2. Memeriksa apakah ada request dari domain tersebut ke endpoint `POST /api/v1/identify` dalam 10 menit terakhir.
3. Menampilkan status indikator visual:
   - `✓ Script detected`
   - `✓ Public key recognized`
   - `✓ Domain authorized`
   - `✓ Ready for visitors`

Jika gagal, sistem menampilkan saran diagnosa langsung (misal: "Domain `shop.mybrand.com` belum dimasukkan ke Whitelist Domain").
