# Modul Komposer Pesan, Balasan Cepat & Lampiran (CS Agent)

## 1. Deskripsi Fitur
Modul Komposer Pesan memfasilitasi agen CS dalam mengetik balasan secara efisien, mengunggah dan meninjau foto lampiran (*media attachments* seperti bukti transfer atau foto produk cacat), serta memanfaatkan pintasan keyboard (*keyboard shortcuts*) untuk mempercepat penanganan volume obrolan yang tinggi.

---

## 2. Hak Akses & Role Permission
- **Role:** `agent` (Customer Support Agent)
- **Middleware:** `web`, `auth`
- **Fokus Fitur:** Memberikan kemudahan komunikasi yang fleksibel, responsif, dan kaya media (*rich media messaging*).

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php) (Metode `reply()`)
- Model: [Message.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Models/Message.php)
- Skema Kolom Pesan:
  - `id`: Identifier berurutan.
  - `conversation_id`: ID tiket obrolan.
  - `sender_type`: Ditandai sebagai `agent`.
  - `sender_id`: ID akun staf CS pelaksana.
  - `body`: Teks konten balasan.
  - `attachments`: JSON array yang memuat URL gambar, nama file, ukuran, dan tipe MIME:
    ```json
    [
      {
        "url": "/storage/attachments/2026/10/img_89a7f.jpg",
        "name": "bukti-pembayaran.jpg",
        "size": 245100,
        "type": "image/jpeg"
      }
    ]
    ```

### Pintasan Keyboard (*Keyboard Shortcuts*):
- `Enter`: Mengirim pesan langsung (dapat diubah konfigurasinya).
- `Shift + Enter`: Membuat baris baru (*line break*) di dalam komposer tanpa mengirim pesan.
- `Ctrl + Enter`: Pintasan alternatif untuk mengirim pesan cepat.

---

## 4. Flowchart Alur Penanganan Pesan & Lampiran oleh Agen

```mermaid
flowchart TD
    Start([Agen Membuka Kotak Komposer Pesan]) --> SelectAction{Pilih Bentuk Komunikasi:}
    
    %% Alur Pesan Teks
    SelectAction -- Ketik Pesan Teks Standar --> TypeText[Agen Mengetik Pesan pada Textarea Komposer]
    TypeText --> CheckShortcuts{Apakah Agen Menekan Pintasan Keyboard?}
    CheckShortcuts -- Shift + Enter --> InsertNewLine[Buat Baris Baru / Paragraf Baru]
    CheckShortcuts -- Enter / Ctrl + Enter --> TriggerSend[Kirim Pesan ke Server]

    %% Alur Tinjau Lampiran dari Pengunjung
    SelectAction -- Menerima Lampiran Gambar dari Pengunjung --> DisplayThumbnail[Layar Menampilkan Thumbnail Gambar di Bubble Chat]
    DisplayThumbnail --> ClickImage[Agen Klik Thumbnail Gambar]
    ClickImage --> OpenModalLightbox[Buka Modal Lightbox Pratinjau Resolusi Penuh]
    OpenModalLightbox --> LightboxAction{Pilih Tindakan Agen:}
    LightboxAction -- Zoom & Teliti Gambar --> InspectDetail[Periksa Detail Bukti Transfer / Kerusakan Produk]
    LightboxAction -- Unduh File Asli --> DownloadOriginal[Klik Tombol Unduh untuk Simpan ke Komputer]

    %% Eksekusi Pengiriman Balasan
    TriggerSend --> PostReplyEndpoint[Kirim POST ke /admin/inbox/{id}/reply]
    PostReplyAPI --> ValidatePayload{Validasi Isi Pesan Tidak Kosong?}
    ValidatePayload -- Kosong --> ShowWarning[Peringatan: Pesan Tidak Boleh Kosong]
    ValidatePayload -- Valid --> SaveToDB[(Simpan Record Pesan ke Tabel messages)]
    
    SaveToDB --> AppendAgentBubble[Tampilkan Bubble Emas/Hijau di Sisi Kanan Obrolan]
    AppendAgentBubble --> ClearComposer[Kosongkan Kotak Teks Komposer & Kembalikan Fokus Kursor]
    ClearComposer --> End([Pesan Terkirim ke Pengunjung])
```

---

## 5. Rincian Antarmuka & Langkah Kerja CS

1. **Mengetik Balasan Berformat Rapi:**
   - Gunakan `Shift + Enter` untuk membuat poin-poin penjelasan produk (misal: rincian harga, nomor rekening, atau petunjuk penyeduhan kopi).
2. **Memeriksa Lampiran Gambar Pembeli:**
   - Jika pengunjung mengirimkan tangkapan layar (*screenshot*) bukti pembayaran, klik gambar tersebut.
   - Jendela modal *lightbox* akan membesar untuk memudahkan agen mencocokkan nomor referensi bank dengan mutasi rekening.
3. **Mengunduh Bukti Lampiran:**
   - Pada pojok atas jendela pratinjau gambar, klik ikon **Unduh** untuk mengarsipkan file ke komputer jika diperlukan untuk laporan divisi keuangan.
