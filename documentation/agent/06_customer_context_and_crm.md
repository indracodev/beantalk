# Modul Konteks Pelanggan & CRM Terintegrasi (CS Agent)

## 1. Deskripsi Fitur
Modul Konteks Pelanggan & CRM menyajikan data intelijen pengunjung secara komprehensif pada panel kanan ruang kerja inbox. Agen CS dapat mengetahui latar belakang teknis pengunjung, halaman produk toko yang sedang aktif dilihat saat ini, riwayat kunjungan, serta melengkapi kontak data pelanggan (Nama, Email, Nomor Telepon/WhatsApp) untuk kebutuhan tindak lanjut (*CRM follow-up*) atau integrasi dengan divisi penjualan.

---

## 2. Hak Akses & Role Permission
- **Role:** `agent` (Customer Support Agent)
- **Middleware:** `web`, `auth`
- **Tujuan Bisnis:** Memungkinkan agen CS memberikan penawaran dan bantuan yang sangat personal (*highly personalized assistance*) karena mengetahui persis produk apa yang sedang dipertimbangkan oleh calon pembeli.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php) (Metode `updateCustomerName()`)
- Model: [Visitor.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Models/Visitor.php)
- Model: [Conversation.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Models/Conversation.php)
- Route: `PUT /admin/inbox/{id}/customer` (`name('admin.inbox.customer')`)

### Data Konteks yang Ditampilkan pada Panel Kanan:
1. **Identitas Profil Pelanggan:**
   - Nama Lengkap (dapat diedit agen secara langsung).
   - Alamat Email (untuk pengiriman struk/faktur atau notifikasi balasan email).
   - Nomor Telepon / WhatsApp.
2. **Metadata Navigasi Website (*Live Browsing Context*):**
   - **Halaman Aktif Pengunjung (*Current Page URL*):** URL lengkap yang sedang dibuka pembeli (misal: `https://indracostore.com/checkout/step-2`).
   - **Judul Halaman (*Page Title*):** Judul halaman produk toko.
   - **Website Rujukan (*Referrer*):** Mengetahui dari mana pengunjung datang (Google Search, Instagram Ads, atau Direct Link).
3. **Metadata Teknis Perangkat:**
   - Sistem Operasi (Windows, macOS, iOS, Android).
   - Tipe Browser (Chrome, Safari, Firefox).
   - Alamat IP Publik pengunjung.
   - Waktu pertama kali berkunjung (*First Seen*).

---

## 4. Flowchart Alur Pengelolaan Konteks Pelanggan

```mermaid
flowchart TD
    Start([Agen Membuka Percakapan di Inbox]) --> LoadVisitorData[DashboardController Ambil Relasi visitor & conversation.metadata]
    LoadVisitorData --> RenderCRMCard[Render Panel Kanan: Kartu Profil & Kartu Konteks Teknis]
    
    RenderCRMCard --> ReviewNavigation[Agen Membaca Current Page URL: 'Pengunjung Sedang Membuka Halaman Promo Kopi']
    ReviewNavigation --> FormulateResponse[Agen Menyusun Penawaran Khusus Sesuai Halaman Tersebut]
    
    RenderCRMCard --> CustomerAction{Apakah Pelanggan Memberikan Identitas Kontak?}
    
    %% Alur Input Manual oleh Agen
    CustomerAction -- Pelanggan Menyebutkan Nama & No HP di Chat --> OpenEditForm[Agen Klik Tombol 'Edit Profil' di Panel Kanan]
    OpenEditForm --> FillCRMFields[Ketik Nama Lengkap, Email, dan No HP Pelanggan]
    FillCRMFields --> SubmitCRM[Kirim PUT Request ke /admin/inbox/{id}/customer]
    
    SubmitCRM --> ValidateCRMInput{Validasi Format Email & Nomor HP?}
    ValidateCRMInput -- Tidak Valid --> ShowCRMError[Peringatan: Format Email / Nomor HP Salah]
    ValidateCRMInput -- Valid --> UpdateVisitorDB[(Update Record pada Tabel visitors)]
    
    UpdateVisitorDB --> SyncContactHeader[Sistem Otomatis Perbarui Nama di Header Obrolan & Daftar Inbox]
    SyncContactHeader --> ShowSuccessToast[Notifikasi: Data Kontak Pelanggan Berhasil Disimpan]

    %% Alur Otomatis dari Widget
    CustomerAction -- Pelanggan Mengisi Form di Widget Toko --> AutoSyncEngine[API Client Otomatis Update Tabel visitors]
    AutoSyncEngine --> PollingRefresh[Polling Panel Kanan Langsung Menampilkan Nama Baru Secara Instan]

    ShowSuccessToast & PollingRefresh --> End([Selesai])
```

---

## 5. Rincian Antarmuka & Panduan CS

1. Buka salah satu obrolan yang sedang aktif.
2. **Melihat Konteks Belanja Pengunjung:**
   - Lihat kolom kanan pada bagian **Live Browsing Context**.
   - Jika pengunjung bertanya: *"Apakah barang ini ready stock?"* tanpa menyebutkan nama barang, perhatikan tautan **Current Page URL**. Anda langsung mengetahui persis produk apa yang sedang dimaksud pembeli tanpa harus bertanya balik!
3. **Menyimpan Kontak Pelanggan:**
   - Jika pengunjung menyebutkan: *"Nama saya Pak Rudi, tolong hubungi WA saya di 08123456789"*.
   - Segera klik **Edit Kontak** di panel kanan.
   - Masukkan nama `Pak Rudi` dan nomor WA `08123456789`, lalu klik **Simpan**.
   - Nama obrolan di daftar inbox Anda seketika berubah dari *Guest #8492* menjadi *Pak Rudi*.
