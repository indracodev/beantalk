# Modul Kustomisasi & Studio Chat Widget (Superadmin)

## 1. Deskripsi Fitur
Modul Kustomisasi Chat Widget memungkinkan Superadmin untuk menyesuaikan identitas visual dan perilaku widget obrolan yang terpasang di setiap website toko. Sesuai prinsip rekayasa BeanTalk (*GEMINI.md Bagian 3.2: Fixed Layout & Shape*), bentuk kurva, tombol pemicu (*launcher*), struktur bubble, dan composer dibuat baku dan konsisten di seluruh platform untuk menjamin estetika kelas dunia, sementara kustomisasi difokuskan secara presisi pada:
- **Warna Aksen Inti Brand (*Primary Accent Color*):** Menggunakan kode HEX (cth: Emas *Supresso Dark Gold* `#C59B27`, Biru *Corporate Indigo* `#4F46E5`, Hijau *Emerald* `#059669`).
- **Mode Tema Widget (*Theme Mode*):** *Light*, *Dark*, atau *Auto (mengikuti preferensi sistem operasi pengunjung)*.
- **Posisi Widget di Layar:** *Pojok Kanan Bawah (Bottom-Right)* atau *Pojok Kiri Bawah (Bottom-Left)*.
- **Pesan Sambutan Otomatis (*Welcome Greeting*):** Pesan yang pertama kali muncul saat jendela obrolan dibuka.
- **Formulir Kontak Pengunjung (*Pre-chat Form*):** Menentukan apakah pengunjung wajib mengisi nama/email sebelum mulai mengobrol.

---

## 2. Hak Akses & Role Permission
- **Role:** `superadmin` (Superadmin)
- **Middleware:** `web`, `auth`
- **Fungsi:** Menjaga keselarasan desain (*brand alignment*) antara website toko dan widget obrolan BeanTalk.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php) (Metode `updateWidgetSettings()`)
- View: `resources/views/admin/integration-detail.blade.php`
- Route: `PUT /admin/integrations/{id}/settings` (`name('admin.integrations.settings')`)

### Skema Tabel `widget_settings`:
| Kolom | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `id` | BIGINT PRIMARY KEY | Identifier primer |
| `project_id` | BIGINT FK | Relasi ke tabel `projects.id` |
| `primary_color` | VARCHAR(7) | Kode HEX warna tema (cth: `#C59B27`) |
| `position` | ENUM | Posisi layar: `bottom-right`, `bottom-left` |
| `theme_mode` | ENUM | Mode visual: `light`, `dark`, `auto` |
| `title` | VARCHAR(100) | Judul header widget (cth: *Customer Support*) |
| `subtitle` | VARCHAR(255) | Keterangan status (cth: *Biasanya membalas dalam hitungan menit*) |
| `welcome_message` | TEXT | Kalimat sambutan awal yang menyapa pengunjung |
| `bot_enabled` | BOOLEAN | Saklar master bot penjawab otomatis |
| `require_email` | BOOLEAN | Mengharuskan pengunjung mengisi email |

---

## 4. Flowchart Alur Kustomisasi Pengaturan Chat Widget

```mermaid
flowchart TD
    Start([Superadmin Buka Tab Pengaturan Widget pada Detail Proyek]) --> LoadCurrentSettings[DashboardController Ambil Data dari Tabel widget_settings]
    LoadCurrentSettings --> RenderLiveStudio[Tampilkan Panel Konfigurasi & Pratinjau Widget Live]
    
    RenderLiveStudio --> AdjustSettings[Superadmin Melakukan Pengaturan Visual:]
    AdjustSettings --> SetColor[Pilih Warna Brand via Color Picker cth: #C59B27]
    AdjustSettings --> SetPosition[Pilih Posisi: Bottom-Right atau Bottom-Left]
    AdjustSettings --> SetTheme[Pilih Mode: Light / Dark / Auto Sync OS]
    AdjustSettings --> SetGreeting[Ketik Teks Salam Sambutan & Header Subtitle]
    AdjustSettings --> TogglePrechat[Tentukan Kebutuhan Wajib Nama / Email Pengunjung]

    SetColor & SetPosition & SetTheme & SetGreeting & TogglePrechat --> LivePreviewUpdate[JavaScript Shadow DOM Memperbarui Pratinjau Interaktif Secara Instan]
    
    LivePreviewUpdate --> SubmitSettings[Superadmin Klik Tombol 'Simpan Perubahan Widget']
    SubmitSettings --> SendPutRequest[Kirim Request PUT ke /admin/integrations/{id}/settings]
    
    SendPutRequest --> ValidateColorHex{Validasi Format Warna HEX?}
    ValidateColorHex -- Format Tidak Sesuai --> ShowColorError[Tampilkan Peringatan: Format Warna Tidak Valid]
    ValidateColorHex -- Valid --> UpdateSettingsDB[(Simpan Perubahan ke Tabel widget_settings)]
    
    UpdateSettingsDB --> LogSettingsActivity[AuditLogger Catat: widget.settings_updated]
    LogSettingsActivity --> InvalidateCache[Hapus Cache Handshake Widget /client/session/init]
    InvalidateCache --> NotifySuccess[Flash Pesan Sukses: Pengaturan Widget Berhasil Diperbarui]
    
    NotifySuccess --> End([Pengunjung Toko Langsung Melihat Tampilan Warna Baru])
```

---

## 5. Rincian Antarmuka & Langkah Operasional

1. Buka menu **Integrasi Multi-Site**, lalu klik proyek website yang ingin diubah (misal: *Indraco Store*).
2. Gulir ke bagian **Studio Pengaturan Tampilan Widget**.
3. **Mengubah Warna Brand:**
   - Klik kotak warna atau ketikkan kode HEX brand perusahaan Anda (misal: `#059669` untuk nuansa hijau segar).
   - Kotak pratinjau langsung memperlihatkan warna tombol launcher, header chat, dan bubble pesan tanpa perlu me-refresh halaman.
4. **Menentukan Posisi:**
   - Pilih *Bottom-Right* untuk mayoritas website standar, atau pilih *Bottom-Left* jika di pojok kanan bawah sudah terdapat tombol WhatsApp pihak ketiga atau widget keranjang belanja melayang.
5. **Mengatur Kalimat Sambutan:**
   - Tulis salam pembuka yang ramah, contoh: *"Halo! Selamat datang di Indraco Store. Ada yang bisa kami bantu seputar pesanan kopi Anda hari ini?"*.
6. Klik tombol **Simpan Konfigurasi Widget**.
7. Seluruh pengunjung yang membuka website toko Anda seketika akan melihat widget obrolan dengan tema warna dan salam sambutan yang baru!
