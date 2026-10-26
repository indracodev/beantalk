# Modul Unggah Media & Kompresi Canvas Sisi Klien (Customer / Visitor)

## 1. Deskripsi Fitur
Modul Unggah Media & Kompresi memungkinkan pengunjung website untuk mengirimkan foto langsung dari kamera ponsel atau galeri komputer mereka (contoh: foto kemasan produk yang rusak, bukti transfer pembayaran, atau label resi kurir). 

Sesuai standar rekayasa *GEMINI.md Bagian 3.2*, SDK BeanTalk dilengkapi dengan **Mesin Kompresi Gambar Berbasis HTML5 Canvas di Sisi Klien (*Client-Side Canvas Image Compression*)**. Foto berukuran besar dari kamera smartphone (yang kerap mencapai 8–15 MB) secara otomatis dikompresi di dalam memori browser ponsel pembeli hingga menjadi **di bawah 800 KB** sebelum dikirim ke server. Hal ini menghemat kuota internet pembeli hingga 90% dan menjamin proses pengunggahan selesai dalam hitungan detik.

---

## 2. Hak Akses & Role Permission
- **Role:** `customer` (Pelanggan / Pengunjung Website)
- **Otentikasi:** Header HTTP `X-Project-Key: pk_live_...`
- **Tipe File yang Diizinkan:** `image/jpeg`, `image/png`, `image/webp`.
- **Batas Ukuran Maksimal Server:** 10 MB per file (dengan kompresi klien yang selalu menghasilkan file < 1 MB).

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Modul SDK Klien: `packages/chat-sdk/src/compression.ts` (Fungsi `compressImage()`)
- Controller API: `App\Http\Controllers\Api\Client\UploadController` (Metode `upload()`)
- Route: `POST /api/v1/client/conversations/{id}/upload`
- Penyimpanan Fisik: Direktori `storage/app/public/attachments/{year}/{month}/`

### Algoritma Kompresi Sisi Klien (HTML5 Canvas):
1. Membaca file gambar menggunakan `FileReader` API.
2. Memuat gambar ke elemen `Image` virtual.
3. Menghitung dimensi baru dengan mempertahankan rasio aspek (*Max Dimension*: 1920px lebar/tinggi).
4. Menggambar ulang gambar ke elemen `<canvas>`.
5. Mengekspor kembali ke format `image/jpeg` dengan parameter kualitas `0.82`.

---

## 4. Flowchart Alur Unggah Foto & Kompresi Otomatis

```mermaid
flowchart TD
    Start([Pengunjung Memilih Foto dari Kamera / Galeri Ponsel]) --> InterceptFile[SDK BeanTalk Mengintersepsi File Sebelum Diunggah]
    InterceptFile --> CheckFileSize{Ukuran File Asli > 1.2 MB?}
    
    %% Alur Kompresi Klien
    CheckFileSize -- Ya (Foto Ponsel 5-15 MB) --> LoadToCanvas[Muat Foto ke Elemen Virtual HTML5 Canvas]
    LoadToCanvas --> ResizeDimension[Skalakan Dimensi Maksimal Menjadi 1920x1080 px]
    ResizeDimension --> ExportJpeg[Export Canvas ke Blob JPEG dengan Kualitas 82%]
    ExportJpeg --> CompressedBlob[Ukuran Berhasil Dikecilkan Menjadi < 800 KB]

    CheckFileSize -- Tidak (Foto Sudah Ringan) --> DirectBlob[Gunakan File Asli]
    
    CompressedBlob & DirectBlob --> BuildFormData[Bungkus Blob ke Objek FormData: file, client_message_id]
    BuildFormData --> ShowUploadProgress[Tampilkan Indikator Progress Bar di Bubble Obrolan]
    
    ShowUploadProgress --> SendUploadRequest[Kirim POST ke /api/v1/client/conversations/{id}/upload]
    SendUploadRequest --> ServerMimeValidation{Server Validasi MIME Type: jpeg/png/webp?}
    ServerMimeValidation -- Tidak Sah (cth: file exe/pdf) --> RejectUpload[Tolak Unggahan: Format File Tidak Didukung]
    
    ServerMimeValidation -- Sah --> StoreFileDisk[Simpan File ke Disk storage/public/attachments]
    StoreFileDisk --> SaveMessageRecord[(Simpan Pesan ke Tabel messages dengan Kolom attachments JSON)]
    
    SaveMessageRecord --> ReturnUploadSuccess[Kembalikan JSON URL Lampiran & Status Sukses]
    ReturnUploadSuccess --> RenderImagePreview[Widget Pengunjung & Inbox CS Seketika Menampilkan Foto yang Indah]
    RenderImagePreview --> End([Selesai])
```

---

## 5. Pengalaman Pengguna & Keamanan

1. **Pengunggahan Kilat di Segala Jaringan:**
   - Bahkan saat pembeli berada di jaringan seluler 3G/4G dengan sinyal 1 bar, proses upload bukti foto berjalan cepat tanpa kegagalan *connection timeout* karena ukuran file yang sangat ringan.
2. **Kualitas Gambar Tetap Jernih:**
   - Algoritma kompresi bicubic mempertahankan ketajaman teks pada foto dokumen (seperti nomor rekening atau alamat pengiriman) sehingga tetap mudah dibaca oleh agen CS.
3. **Penyimpanan Aman Terisolasi:**
   - Nama file diacak menggunakan hashing kriptografi (*SHA-256 random name*) untuk mencegah eksekusi skrip berbahaya pada server hosting.
