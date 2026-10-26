# Modul Obrolan Realtime & Polling Adaptif (CS Agent)

## 1. Deskripsi Fitur
Modul Obrolan Realtime & Polling Adaptif menggerakkan pertukaran pesan instan antara agen CS dan pengunjung toko. Dibangun dengan pendekatan cerdas **Smart Adaptive HTTP Polling**, fitur ini memberikan pengalaman obrolan secepat WebSocket tanpa mewajibkan server latar belakang eksternal (*Zero-Daemon Architecture*), sehingga sangat stabil dijalankan pada server *Shared Hosting* sekalipun.

Sistem secara dinamis menyesuaikan frekuensi penarikan data (*polling frequency*) berdasarkan status keaktifan tab browser agen, menjamin efisiensi penggunaan memori browser dan beban server database MySQL.

---

## 2. Hak Akses & Role Permission
- **Role:** `agent` (Customer Support Agent)
- **Middleware:** `web`, `auth`
- **Prinsip Operasional:** Pesan diurutkan berdasarkan `messages.id` sekuensial (bukan timestamp) untuk menjamin kronologi obrolan yang 100% akurat tanpa risiko tumpang tindih waktu (*time drift*).

---

## 3. Komponen Teknis & Logika Polling Adaptif

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php)
  - `pollUpdates()`: Endpoint `/admin/inbox/feed/updates` yang mengembalikan ringkasan status unread dan update pesan terbaru.
  - `messages()`: Endpoint `/admin/inbox/{id}/messages` dengan parameter `after_id` untuk mengambil hanya pesan yang lebih baru dari ID pesan terakhir yang dimiliki browser.
- Route:
  - `GET /admin/inbox/feed/updates` (`name('admin.inbox.updates')`)
  - `GET /admin/inbox/{id}/messages` (`name('admin.inbox.messages')`)

### Strategi Frekuensi Polling Adaptif:
| Kondisi Jendela Browser Agen | Frekuensi Polling | Alasan Teknis |
| :--- | :--- | :--- |
| **Tab Aktif & Sedang Fokus** | Setiap **2.5 – 3 detik** | Menjamin respons instan layaknya chat aplikasi pesan instan |
| **Tab Latar Belakang (*Inactive/Blur*)** | Setiap **15 – 30 detik** | Menghemat daya CPU, memori RAM, dan kuota request server |
| **User Kembali Membuka Tab (*Focus*)** | **Langsung Eksekusi Instan (0 detik)** | Sinkronisasi seketika saat agen kembali ke layar kerja |

### Formula Kueri Range Scan B-Tree:
```sql
SELECT * FROM messages 
WHERE conversation_id = ? AND id > ? 
ORDER BY id ASC;
```
Kueri ini memanfaatkan indeks gabungan `idx_messages_conversation_id` sehingga proses pencarian berjalan ultra-cepat (< 2 milidetik).

---

## 4. Flowchart Alur Kerja Polling Adaptif Realtime

```mermaid
flowchart TD
    Start([Agen CS Buka Layar Obrolan Aktif]) --> InitLastId[Catat ID Pesan Terakhir yang Tampil: last_rendered_id]
    InitLastId --> CheckTabVisibility{Apakah Tab Browser Sedang Aktif / Terbuka?}
    
    CheckTabVisibility -- Tab Aktif & Fokus --> SetFastInterval[Set Timer Polling = 3 Detik]
    CheckTabVisibility -- Tab di Latar Belakang (Blur) --> SetSlowInterval[Set Timer Polling Throttled = 20 Detik]

    SetFastInterval & SetSlowInterval --> WaitInterval[Tunggu Hingga Timer Interval Tercapai]
    WaitInterval --> SendPollRequest[Kirim GET Request ke /admin/inbox/{id}/messages?after_id=last_rendered_id]
    
    SendPollRequest --> QueryDB[MySQL Eksekusi Range Scan Menggunakan Indeks B-Tree: id > after_id]
    QueryDB --> CheckReturnedData{Apakah Ada Pesan Baru Masuk?}
    
    CheckReturnedData -- Tidak Ada Pesan Baru (Empty Array) --> CheckTabVisibility
    
    CheckReturnedData -- Ada Pesan Baru --> IterateMessages[Loop Pesan Baru yang Diterima]
    IterateMessages --> AppendDOM[Render Bubble Chat Baru ke Layar Tanpa Reload Halaman]
    AppendDOM --> PlaySoundNotification[Bunyikan Suara Ting untuk Alert Agen]
    AppendDOM --> UpdateLastId[Perbarui Nilai last_rendered_id ke ID Terbesar]
    UpdateLastId --> AutoScrollBottom[Gulir Otomatis Layar Obrolan ke Baris Terbawah]
    AutoScrollBottom --> CheckTabVisibility
```

---

## 5. Rincian Antarmuka & Panduan CS

1. **Memastikan Obrolan Tetap Realtime:**
   - Tidak perlu menekan tombol refresh browser (*F5*). Sistem secara otomatis menarik pesan baru setiap 3 detik.
2. **Saat Menjalankan Tugas Lain di Komputer:**
   - Jika Anda sedang membuka tab lain (misal: sistem ERP atau marketplace), BeanTalk tetap berjalan di latar belakang.
   - Begitu pelanggan mengirim pesan, tab BeanTalk akan membunyikan nada notifikasi dan judul tab browser akan berkedip (*"Pesan Baru Masuk..."*).
3. **Mengakses Kembali Obrolan:**
   - Cukup klik tab BeanTalk; sistem seketika menyinkronkan seluruh pesan baru tanpa jeda.
