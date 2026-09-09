# PRD: User Stories & Acceptance Scenarios

Dokumen ini memetakan cerita pengguna (User Stories) berdasarkan persona dan kriteria penerimaan formal (*Given - When - Then*).

---

## Epic 1: Onboarding & Script Installation

### Story 1.1: Pemilik Situs Mendapatkan Snippet Integrasi
- **Sebagai**: Pemilik Toko / Merchant
- **Saya ingin**: Menyalin kode snippet JavaScript satu baris dari dashboard
- **Sehingga**: Saya dapat langsung memasang chat widget di website saya tanpa instalasi npm

```gherkin
Scenario: Menghasilkan Snippet Instalasi
  Given Merchant membuka halaman "Installation" untuk Project "Shopify Store"
  When Halaman selesai dimuat
  Then Muncul blok kode HTML yang memuat tag <script> dengan public key project yang benar
  And Terdapat tombol "Copy Code" yang otomatis menyalin teks ke clipboard pengguna
```

### Story 1.2: Verifikasi Koneksi Otomatis
- **Sebagai**: Pemilik Toko / Merchant
- **Saya ingin**: Memverifikasi apakah script yang saya pasang di Shopify/WordPress sudah terhubung
- **Sehingga**: Saya yakin pengunjung situs sudah dapat melihat widget chat

```gherkin
Scenario: Verifikasi Sukses
  Given Merchant telah menempelkan script di situs "mystore.com"
  And Pengunjung (atau merchant) membuka situs tersebut
  When Merchant menekan tombol "Verify Connection" di dashboard
  Then Sistem memeriksa request identify terbaru dari origin "mystore.com"
  And Menampilkan centang hijau "✓ Connection Verified! Widget is ready."
```

---

## Epic 2: Pengalaman Pengunjung (Visitor Experience)

### Story 2.1: Mengirim Pesan Pertama Kali
- **Sebagai**: Pengunjung Toko Online
- **Saya ingin**: Bertanya mengenai ketersediaan ukuran sepatu secara instan
- **Sehingga**: Saya mendapatkan kejelasan sebelum memutuskan membeli

```gherkin
Scenario: Pengunjung Anonim Mengirim Pesan
  Given Pengunjung membuka halaman produk di toko online
  When Pengunjung mengklik tombol mengambang widget chat
  Then Jendela chat terbuka menampilkan pesan sambutan (greeting)
  When Pengunjung mengetik "Apakah ukuran 42 tersedia?" dan menekan Enter
  Then Pesan langsung muncul di feed chat dengan status "sending" lalu "sent"
  And ID pengunjung unik (visitor_uuid) tersimpan di browser localStorage
```

### Story 2.2: Mengunggah Screenshot Bukti
- **Sebagai**: Pengunjung Toko
- **Saya ingin**: Mengunggah foto bukti error checkout
- **Sehingga**: Tim CS dapat memahami kendala saya dengan lebih cepat

```gherkin
Scenario: Upload Gambar Berhasil
  Given Jendela chat terbuka
  When Pengunjung mengklik ikon lampiran (📎) dan memilih file "error.png" (1.2 MB)
  Then Muncul indikator progress upload
  And Gambar otomatis terkirim dan tampil di thread percakapan
```

---

## Epic 3: Operasional Agen (Admin Inbox Operations)

### Story 3.1: Membalas Pesan Secara Realtime
- **Sebagai**: Agen Customer Support
- **Saya ingin**: Melihat pesan baru masuk ke inbox secara otomatis tanpa refresh halaman
- **Sehingga**: Saya dapat langsung membalas pelanggan dengan cepat

```gherkin
Scenario: Agen Menerima dan Membalas Pesan
  Given Agen sedang membuka halaman Inbox Admin di tab browser
  When Pengunjung mengirimkan pesan baru
  Then Kartu percakapan pengunjung otomatis naik ke posisi teratas daftar inbox
  And Badge angka unread berwarna merah bertambah
  When Agen memilih percakapan tersebut dan mengetik balasan "Ukuran 42 ready kak!"
  And Agen menekan Enter
  Then Balasan terkirim ke backend dan tersinkronisasi ke browser pengunjung dalam < 2 detik
```

### Story 3.2: Melihat Konteks Halaman Pengunjung
- **Sebagai**: Agen Customer Support
- **Saya ingin**: Melihat produk apa yang sedang dilihat pengunjung saat mereka bertanya
- **Sehingga**: Saya tidak perlu menanyakan kembali link produk yang dimaksud

```gherkin
Scenario: Panel Customer Context Terisi Otomatis
  Given Pengunjung mengirim chat dari URL "/products/leather-jacket" di Shopify
  When Agen membuka percakapan pengunjung tersebut di panel chat tengah
  Then Panel konteks kanan menampilkan:
    | Field         | Value                        |
    | Source        | Shopify                      |
    | Current Page  | /products/leather-jacket     |
    | Product Name  | Classic Leather Jacket Brown |
    | Device        | Mobile (iPhone / Safari)     |
```

---

## Epic 4: Ketahanan Jaringan & Offline Recovery

### Story 4.1: Pemulihan Saat Koneksi Terputus
- **Sebagai**: Pengunjung yang menggunakan koneksi seluler tidak stabil
- **Saya ingin**: Pesan saya tidak hilang saat internet sempat terputus
- **Sehingga**: Percakapan tetap berlanjut tanpa perlu mengulang dari awal

```gherkin
Scenario: Reconnect Otomatis
  Given Pengunjung sedang dalam percakapan aktif
  When Koneksi internet HP terputus (status offline)
  Then Widget menampilkan indikator status "Offline. Reconnecting..."
  And Polling loop dihentikan sementara untuk menghemat resource
  When Sinyal internet pulih kembali (status online)
  Then SDK otomatis memicu satu kali "Immediate Poll"
  And Status berubah kembali menjadi "Connected"
  And Semua pesan baru yang tertunda langsung disinkronkan ke layar
```
