# Modul Ruang Kerja Inbox & Manajemen Obrolan (CS Agent)

## 1. Deskripsi Fitur
Modul Ruang Kerja Inbox adalah antarmuka kerja utama bagi staf Customer Support (CS Agent) untuk melayani pertanyaan, keluhan, dan konsultasi pelanggan secara real-time. Tata letak dirancang ergonomis dengan arsitektur **3-Kolom Linear-style**:
- **Kolom Kiri (Antrean Obrolan):** Menampilkan daftar seluruh chat yang masuk, dipilah berdasarkan tab status (*All, Open, Pending, Resolved*), filter berdasarkan proyek website toko, waktu pesan terakhir, dan indikator lencana belum dibaca (*unread count badge*).
- **Kolom Tengah (Ruang Obrolan Interaktif):** Thread percakapan aktif yang memuat riwayat pesan, bubble obrolan agen vs pelanggan, pratinjau lampiran foto, indikator pengetikan (*typing*), dan kotak komposer pesan.
- **Kolom Kanan (Konteks Pelanggan & CRM):** Menampilkan identitas pelanggan, catatan kontak, serta metadata halaman web toko yang sedang dijelajahi pengunjung.

---

## 2. Hak Akses & Role Permission
- **Role:** `agent` (Customer Support Agent)
- **Middleware:** `web`, `auth`
- **Fokus Kerja:** Menjawab pertanyaan pelanggan secepat mungkin (*Low First Response Time*), memberikan informasi akurat, dan menyelesaikan kendala belanja pembeli.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php) (Metode `inbox()`, `reply()`, `pollUpdates()`)
- View: `resources/views/admin/inbox.blade.php`
- Route:
  - `GET /admin/inbox/{id?}` (`name('admin.inbox')`)
  - `POST /admin/inbox/{id}/reply` (`name('admin.inbox.reply')`)
  - `GET /admin/inbox/feed/updates` (`name('admin.inbox.updates')`)

### Tiga Tab Status Percakapan:
1. **Open (Aktif):** Obrolan baru masuk atau obrolan yang sedang aktif membutuhkan tanggapan dari staf CS.
2. **Pending (Menunggu):** Obrolan yang telah dibalas oleh agen, namun masih menunggu respons konfirmasi lanjutan dari pihak pembeli.
3. **Resolved (Selesai):** Obrolan yang telah tuntas diselesaikan oleh agen dan diarsipkan.

---

## 4. Flowchart Alur Kerja Penanganan Obrolan oleh CS Agent

```mermaid
flowchart TD
    Start([Agen CS Membuka Inbox /admin/inbox]) --> LoadInbox[Sistem Memuat Daftar Percakapan Terkini]
    LoadInbox --> StartBackgroundPolling[Polling Latar Belakang /admin/inbox/feed/updates Aktif]
    
    StartBackgroundPolling --> IncomingMessageAlert{Apakah Ada Pesan Baru Masuk?}
    IncomingMessageAlert -- Ya --> PlayChime[Bunyikan Suara Notifikasi Audio & Munculkan Titik Merah Unread]
    IncomingMessageAlert -- Tidak --> StandbyWait[Standby Menunggu Obrolan]

    PlayChime --> SelectConversation[Agen Memilih Percakapan dari Kolom Kiri]
    SelectConversation --> LoadChatHistory[Ambil Riwayat Obrolan & Konteks Pelanggan di Kolom Kanan]
    LoadChatHistory --> ReviewContext[Agen Membaca Pertanyaan Pengunjung & URL Halaman yang Dilihat Pengunjung]
    
    ReviewContext --> ComposeAnswer[Ketik Jawaban Solutif pada Kotak Balasan]
    ComposeAnswer --> SendReply[Klik Tombol Kirim / Tekan Ctrl+Enter]
    
    SendReply --> PostReplyAPI[POST ke /admin/inbox/{id}/reply]
    PostReplyAPI --> SaveMessageDB[(Simpan Pesan ke Tabel messages dengan sender_type = 'agent')]
    SaveMessageDB --> AutoYieldBot[Sistem Otomatis Menjeda Bot: is_bot_active = false]
    AutoYieldBot --> DeliverToCustomer[Pesan Langsung Diterima Pengunjung di Widget Toko]

    DeliverToCustomer --> EvaluateNextStep{Evaluasi Kondisi Tiket Obrolan:}
    EvaluateNextStep -- Masih Perlu Jawaban Pembeli --> SetPendingStatus[Ubah Status Menjadi: Pending]
    EvaluateNextStep -- Masalah Sudah Tuntas --> SetResolvedStatus[Ubah Status Menjadi: Resolved]
    EvaluateNextStep -- Lanjut Percakapan --> KeepOpenStatus[Pertahankan Status: Open]

    SetPendingStatus & SetResolvedStatus & KeepOpenStatus --> End([Selesai / Lanjut ke Antrean Berikutnya])
```

---

## 5. Rincian Antarmuka & Langkah Operasional Harian

1. Masuk ke aplikasi BeanTalk menggunakan akun agen Anda (bisa menggunakan email atau username, misal: `sarah`).
2. Klik menu **Inbox** di bilah navigasi samping.
3. **Memantau Pesan Masuk:**
   - Saat ada pelanggan yang mengirim pesan di website, sistem akan membunyikan nada dering dan menambahkan lencana angka merah pada daftar chat.
4. **Membuka Obrolan:**
   - Klik kartu chat tersebut. Thread obrolan di kolom tengah akan termuat.
   - Perhatikan panel kanan: Anda dapat melihat halaman produk apa yang sedang dilihat oleh pengunjung tersebut (misal: *https://supresso.com/products/kopi-sumatra-mandheling*), sehingga Anda dapat langsung memberikan rekomendasi yang relevan!
5. **Mengirim Balasan:**
   - Ketikkan pesan jawaban dan tekan tombol kirim.
   - Begitu Anda membalas, bot penjawab otomatis akan mengalah (*auto-yield*) sehingga Anda memiliki kontrol penuh atas percakapan tersebut.
