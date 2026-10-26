# Modul Manajemen Unified Inbox Terpusat (Superadmin)

## 1. Deskripsi Fitur
Modul Unified Inbox adalah pusat kendali komunikasi percakapan langsung (*Live Chat Control Hub*) di BeanTalk. Antarmuka mengadopsi tata letak modern **3-Kolom bergaya Linear/Intercom**:
1. **Kolom Kiri (Daftar Percakapan):** Menampilkan daftar antrean obrolan dengan filter status (*All, Open, Pending, Resolved*), filter berdasarkan proyek toko tertentu, indikator bot aktif, serta penanda pesan belum terbaca (*unread count*).
2. **Kolom Tengah (Ruang Obrolan Aktif):** Thread percakapan interaktif dengan bubble chat bergradien, preview lampiran gambar, kontrol pengiriman pesan agen, dan tombol pintas toggle bot (*Bot On / Paused*).
3. **Kolom Kanan (Konteks Pelanggan & CRM):** Panel informasi teknis pengunjung (Alamat IP, Sistem Operasi, Browser, URL halaman web yang sedang dibuka pengunjung, waktu kunjungan awal) dan formulir pengeditan profil kontak pelanggan (Nama, Email, Nomor Telepon).

Superadmin memiliki wewenang penuh untuk memantau semua obrolan dari seluruh website, membalas pesan, mengalihkan penugasan (*assign*) tiket ke agen CS tertentu, atau mengambil alih percakapan dari bot otomatis kapan saja.

---

## 2. Hak Akses & Role Permission
- **Role:** `superadmin`
- **Middleware:** `web`, `auth`
- **Wewenang Khusus Superadmin:** Dapat melihat dan mengintervensi percakapan di seluruh proyek website tanpa batasan penugasan agen tertentu.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php)
  - `inbox()`: Menampilkan antarmuka 3-kolom dan memuat percakapan aktif.
  - `reply()`: Mengirim balasan agen CS ke pengunjung.
  - `toggleBot()`: Menghidupkan atau menjeda status bot per percakapan.
  - `updateStatus()`: Mengubah status tiket (`open`, `pending`, `closed`).
  - `assign()`: Menugaskan tiket percakapan ke agen staf CS.
  - `updateCustomerName()`: Memperbarui nama dan kontak pengunjung.
  - `pollUpdates()`: Endpoint polling periodik untuk sinkronisasi pembaruan tanpa reload layar.
- View: `resources/views/admin/inbox.blade.php`
- Route:
  - `GET /admin/inbox/{id?}` (`name('admin.inbox')`)
  - `POST /admin/inbox/{id}/reply` (`name('admin.inbox.reply')`)
  - `POST /admin/inbox/{id}/toggle-bot` (`name('admin.inbox.toggle-bot')`)
  - `PUT /admin/inbox/{id}/status` (`name('admin.inbox.status')`)
  - `PUT /admin/inbox/{id}/assign` (`name('admin.inbox.assign')`)
  - `PUT /admin/inbox/{id}/customer` (`name('admin.inbox.customer')`)
  - `GET /admin/inbox/feed/updates` (`name('admin.inbox.updates')`)

### Aturan Bisnis Smart Bot Auto-Yielding:
Saat Superadmin (atau Agen) mengirim balasan manual melalui formulir inbox, sistem secara otomatis mengeksekusi logika *Smart CS Auto-Yielding*:
```php
$conversation->update([
    'is_bot_active' => false,
    'bot_handoff_at' => now(),
]);
```
Hal ini memastikan bot kecerdasan buatan/FAQ tidak akan menginterupsi atau membalas secara tumpang tindih ketika manusia sudah mengambil alih penanganan keluhan pelanggan.

---

## 4. Flowchart Alur Kerja Manajemen Unified Inbox

```mermaid
flowchart TD
    Start([Superadmin Buka Menu Inbox /admin/inbox]) --> LoadInboxView[DashboardController::inbox Memuat Daftar Percakapan & Detail Aktif]
    LoadInboxView --> Render3Column[Render Antarmuka 3-Kolom: Daftar Chat, Thread Aktif, Profil Pengunjung]
    
    Render3Column --> StartAdaptivePoll[Jalankan Background Polling /admin/inbox/feed/updates Setiap 3-5 Detik]
    StartAdaptivePoll --> CheckNewMessages{Apakah Ada Pesan Baru Masuk?}
    CheckNewMessages -- Ya --> UpdateChatList[Perbarui Preview Pesan di Kolom Kiri & Bunyikan Notifikasi Audio]
    CheckNewMessages -- Tidak --> WaitNextPoll[Tunggu Interval Polling Berikutnya]

    Render3Column --> AdminAction{Superadmin Memilih Aksi:}
    
    %% Aksi 1: Membalas Pesan
    AdminAction -- 1. Kirim Balasan Chat --> TypeMessage[Ketik Pesan pada Composer & Tekan Enter / Send]
    TypeMessage --> SubmitReply[POST ke /admin/inbox/{id}/reply]
    SubmitReply --> SaveAgentMessage[(Simpan Pesan ke Tabel messages dengan sender_type = 'agent')]
    SaveAgentMessage --> TriggerAutoYielding[Nonaktifkan Bot: is_bot_active = false & bot_handoff_at = now]
    TriggerAutoYielding --> AppendToUI[Tambahkan Bubble Pesan ke Layar Seketika Tanpa Reload]

    %% Aksi 2: Toggle Status Bot
    AdminAction -- 2. Toggle Bot Manual --> ClickBotToggle[Klik Tombol Bot Status di Header Chat]
    ClickBotToggle --> PostToggleBot[POST ke /admin/inbox/{id}/toggle-bot]
    PostToggleBot --> SwitchBotState[Balik Nilai is_bot_active: True <-> False]
    SwitchBotState --> UpdateBadge[Perbarui Status Visual Badge Bot Aktif / Dijeda]

    %% Aksi 3: Ubah Status Tiket
    AdminAction -- 3. Ubah Status Obrolan --> SelectStatusDropdown[Pilih Status: Open / Pending / Resolved]
    SelectStatusDropdown --> PutStatus[PUT ke /admin/inbox/{id}/status]
    PutStatus --> UpdateConversationStatus[(Update Kolom status di Tabel conversations)]
    UpdateConversationStatus --> MoveTabUI[Pindahkan Percakapan ke Tab Status yang Sesuai]

    %% Aksi 4: Alihkan Penugasan Agen
    AdminAction -- 4. Alihkan Penugasan CS --> SelectAgentDropdown[Pilih Nama Agen CS dari Dropdown]
    SelectAgentDropdown --> PutAssign[PUT ke /admin/inbox/{id}/assign]
    PutAssign --> UpdateAssignedUser[(Update assigned_user_id di Tabel conversations)]

    %% Aksi 5: Edit Profil Kontak
    AdminAction -- 5. Edit Profil Pengunjung --> EditContactForm[Ubah Nama, Email, atau No. Telepon di Panel Kanan]
    EditContactForm --> PutCustomer[PUT ke /admin/inbox/{id}/customer]
    PutCustomer --> UpdateVisitorRecord[(Update Kolom name/email/phone di Tabel visitors)]

    AppendToUI & UpdateBadge & MoveTabUI & UpdateAssignedUser & UpdateVisitorRecord --> End([Selesai])
```

---

## 5. Rincian Antarmuka & Langkah Operasional

1. **Memilih Proyek Website:**
   - Gunakan filter dropdown di atas kolom percakapan untuk mengisolasi percakapan dari satu toko tertentu (contoh: *Supresso Indonesia*) atau memilih *All Projects* untuk melihat seluruh pesan lintas website perusahaan.
2. **Membalas Pesan Pengunjung:**
   - Klik salah satu percakapan di kolom kiri.
   - Ketikkan respons pada kotak teks obrolan di bagian bawah kolom tengah.
   - Tekan tombol **Kirim** (atau shortcut keyboard `Ctrl + Enter`).
   - Sistem seketika mengunci bot agar nonaktif dan menampilkan bubble obrolan agen berwarna hijau/emas.
3. **Mengalihkan Penugasan Tiket (*Reassign*):**
   - Pada panel konteks kanan, temukan kartu **Assigned Agent**.
   - Pilih nama staf CS (contoh: *Sarah - CS Shift Pagi*) dari dropdown. Tiket obrolan akan langsung masuk ke filter kerja agen tersebut.
4. **Menyelesaikan Percakapan (*Resolve*):**
   - Setelah kebutuhan pelanggan terpenuhi, klik tombol **Resolve / Selesai** di header obrolan. Percakapan akan diarsipkan ke tab *Resolved* untuk menjaga antrean obrolan tetap bersih (*Inbox Zero*).
