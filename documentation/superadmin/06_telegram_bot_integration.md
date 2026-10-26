# Modul Integrasi Bot & Webhook Telegram (Superadmin)

## 1. Deskripsi Fitur
Modul Integrasi Telegram memungkinkan Superadmin untuk menghubungkan sistem obrolan BeanTalk ke aplikasi perpesanan Telegram. Fitur ini dirancang untuk memastikan staf CS tidak pernah melewatkan pesan darurat pelanggan, meskipun sedang tidak membuka dasbor komputer.

Sistem mendukung:
1. **Notifikasi Instan Masuk (*Telegram Push Alerts*):** Meneruskan obrolan baru atau permintaan bantuan manusia ke grup/channel Telegram staf CS lengkap dengan nama pengunjung, asal website toko, dan ringkasan pesan.
2. **Uji Coba Koneksi Langsung (*Test Telegram Alert*):** Mengirim pesan tes satu sentuhan untuk memverifikasi validitas bot token dan chat ID.
3. **Pendaftaran Otomatis Webhook Telegram (*Set Telegram Webhook*):** Mendaftarkan URL endpoint publik BeanTalk ke server resmi Telegram secara otomatis.
4. **Balasan Dua Arah (*Two-Way Messaging*):** Agen dapat membalas pesan langsung dari dalam aplikasi Telegram (menggunakan fitur *Reply to message*), dan jawaban tersebut otomatis masuk ke widget obrolan web pengunjung.

---

## 2. Hak Akses & Role Permission
- **Role:** `superadmin`
- **Middleware:** `web`, `auth`
- **Keamanan:** Kunci token bot Telegram (`telegram_bot_token`) dan identifier grup (`telegram_chat_id`) disimpan terenkripsi pada basis data dan hanya dapat dimodifikasi oleh Superadmin.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Service Inti: `App\Services\TelegramService`
- Controller Admin: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php)
  - `testTelegramAlert()`: Menguji pengiriman notifikasi ke Telegram.
  - `setTelegramWebhook()`: Mendaftarkan URL webhook ke API Telegram.
- Controller Webhook Publik: `App\Http\Controllers\Api\Client\TelegramWebhookController` (Metode `handle()`)
- Route:
  - `POST /admin/integrations/{id}/test-telegram` (`name('admin.integrations.test-telegram')`)
  - `POST /admin/integrations/{id}/set-telegram-webhook` (`name('admin.integrations.set-telegram-webhook')`)
  - `POST /api/v1/telegram/webhook/{token_hash?}` (`name('api.v1.telegram.webhook')`)

---

## 4. Flowchart Alur Integrasi & Komunikasi Dua Arah Telegram

```mermaid
flowchart TD
    subgraph SetupPhase [Tahap 1: Konfigurasi Webhook Telegram oleh Superadmin]
        StartSetup([Superadmin Buka Tab Integrasi Telegram]) --> InputCredentials[Input Bot Token dari BotFather & Chat ID Grup CS]
        InputCredentials --> ClickSetWebhook[Klik Tombol 'Set Telegram Webhook']
        ClickSetWebhook --> CallTelegramAPI[TelegramService Memanggil API Telegram: setWebhook]
        CallTelegramAPI --> VerifyWebhookResponse{Respon Telegram = OK (True)?}
        VerifyWebhookResponse -- Gagal --> ShowWebhookError[Tampilkan Pesan Error: Token / Domain Salah]
        VerifyWebhookResponse -- Sukses --> WebhookRegistered[Webhook Aktif: Telegram Mengarah ke /api/v1/telegram/webhook]
        WebhookRegistered --> ClickTestAlert[Klik Tombol 'Test Alert Telegram']
        ClickTestAlert --> SendTestMsg[Kirim Pesan Tes: 'Koneksi BeanTalk Berhasil!']
    end

    subgraph LiveAlertPhase [Tahap 2: Notifikasi Masuk saat Pengunjung Chat]
        VisitorChats[Pengunjung Mengirim Pesan di Widget Toko] --> SaveWebMsg[Pesan Disimpan ke Database BeanTalk]
        SaveWebMsg --> CheckTelegramActive{Apakah Integrasi Telegram Aktif pada Proyek Ini?}
        CheckTelegramActive -- Ya --> FormatTelegramCard[Format Pesan HTML: Judul Website, Pengunjung, Pesan, Link Chat]
        FormatTelegramCard --> PushToTelegram[Kirim Pesan ke Grup CS via Telegram Bot API]
    end

    subgraph TwoWayReplyPhase [Tahap 3: Balasan Dua Arah dari Telegram ke Web]
        PushToTelegram --> AgentRepliesInTelegram[Agen CS Membuka Telegram & Membalas: 'Reply to Message']
        AgentRepliesInTelegram --> TelegramServer[Server Telegram Meneruskan Update JSON ke Webhook BeanTalk]
        TelegramServer --> TelegramWebhookController[TelegramWebhookController Menerima Request POST Webhook]
        
        TelegramWebhookController --> ParseReply[Ekstrak ID Percakapan Asal & Teks Jawaban Agen]
        ParseReply --> SaveReplyToDB[(Simpan ke Tabel messages dengan sender_type = 'agent')]
        SaveReplyToDB --> DeliverToWebWidget[Widget Web Pengunjung Menerima Jawaban via Polling Seketika]
        DeliverToWebWidget --> End([Pengunjung Membaca Jawaban di Website])
    end
```

---

## 5. Rincian Antarmuka & Langkah Konfigurasi

### A. Mendapatkan Bot Token & Chat ID:
1. Buka aplikasi Telegram, cari akun `@BotFather` dan ketik `/newbot` untuk membuat bot baru.
2. Salin token API yang diberikan (format: `123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ`).
3. Buat grup di Telegram untuk tim CS Anda, masukkan bot tersebut ke dalam grup, dan dapatkan Chat ID grup tersebut.

### B. Mengatur Integrasi di BeanTalk:
1. Masuk ke menu **Integrasi Multi-Site** > pilih proyek website Anda.
2. Pada panel **Integrasi Telegram Notifikasi**, masukkan:
   - **Telegram Bot Token:** Tempelkan token dari BotFather.
   - **Telegram Chat ID:** Masukkan Chat ID grup CS (biasanya diawali tanda minus `-` untuk grup, misal: `-100123456789`).
3. Klik tombol **Set Webhook Otomatis**. Sistem akan menghubungi server Telegram dan mengonfirmasi bahwa webhook telah aktif.
4. Klik tombol **Kirim Pesan Tes** untuk memastikan pesan berhasil mendarat di grup Telegram tim Anda.
