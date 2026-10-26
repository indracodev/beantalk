# Modul Eskalasi Bot & Auto-Yielding (CS Agent)

## 1. Deskripsi Fitur
Modul Eskalasi Bot & Auto-Yielding mengatur kolaborasi harmonis antara kecerdasan buatan (*Smart Bot*) dan staf Customer Support manusia (*Human Agent*). Fitur ini memastikan bahwa:
1. Pelanggan yang baru datang langsung dilayani oleh bot dalam hitungan milidetik (*Zero Wait Time*).
2. Begitu agen manusia masuk ke percakapan dan mengirimkan balasan, bot secara otomatis mengalah (*Auto-Yielding*) dan berhenti membalas agar tidak mengacaukan dialog.
3. Agen memiliki kendali penuh untuk menyalakan kembali bot (*Re-enable Bot*) jika pelanggan hanya menanyakan informasi umum atau saat agen harus meninggalkan meja kerja sementara waktu.

---

## 2. Hak Akses & Role Permission
- **Role:** `agent` (Customer Support Agent)
- **Middleware:** `web`, `auth`
- **Tujuan Operasional:** Mencegah terjadinya benturan balasan ganda antara bot dan manusia (*race condition / duplicate answers*).

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php) (Metode `toggleBot()`, `reply()`)
- Service Inti: `App\Services\BotService`
- Route:
  - `POST /admin/inbox/{id}/toggle-bot` (`name('admin.inbox.toggle-bot')`)
  - `POST /admin/inbox/{id}/reply` (`name('admin.inbox.reply')`)

### Logika Eksekusi Auto-Yielding pada Balasan Agen:
Di dalam metode `reply()` di `DashboardController`:
```php
// Saat agen mengirim balasan manual, matikan bot secara otomatis
if ($conversation->is_bot_active) {
    $conversation->update([
        'is_bot_active'  => false,
        'bot_handoff_at' => now(),
    ]);
    
    // Kirim event status bot terupdate ke UI
}
```

---

## 4. Flowchart Kolaborasi Staf CS dan Smart Bot

```mermaid
flowchart TD
    Start([Percakapan Dimulai oleh Pengunjung]) --> BotAnswers[Smart Bot Menjawab Pertanyaan Awal FAQ: is_bot_active = true]
    BotAnswers --> UIStateBotActive[Di Layar Inbox Agen: Badge Biru 'Bot Active' Menyala]
    
    UIStateBotActive --> DecisionPoint{Apakah Pertanyaan Membutuhkan Penanganan Manusia?}
    
    %% Cabang 1: Pengunjung Minta Bantuan Manusia
    DecisionPoint -- Pengunjung Ketik 'Bicara dengan CS' --> SmartHandoffTrigger[Sistem Deteksi Keyword Eskalasi Otomatis]
    SmartHandoffTrigger --> BotYieldsAutomatically[Bot Menyerahkan Tiket: is_bot_active = false]
    BotYieldsAutomatically --> AlertAgent[Munculkan Peringatan Khusus di Inbox: 'Customer Meminta Bantuan CS!']

    %% Cabang 2: Agen Langsung Menjawab Tanpa Disuruh
    DecisionPoint -- Agen Mengambil Alih Manual --> AgentTypesReply[Agen Mengetik Jawaban Solutif pada Kotak Komposer]
    AgentTypesReply --> SubmitAgentReply[Agen Klik Kirim / Enter]
    SubmitAgentReply --> ExecuteAutoYield[Sistem Jalankan Auto-Yielding:]
    ExecuteAutoYield --> TurnOffBot[Set conversations.is_bot_active = false]
    TurnOffBot --> RecordHandoffTimestamp[Set bot_handoff_at = now]
    RecordHandoffTimestamp --> UIStateBotPaused[Badge Berubah Menjadi Abu-abu: 'Bot Paused']

    %% Cabang 3: Menghidupkan Kembali Bot
    UIStateBotPaused --> AgentNeedsBotAgain{Apakah Masalah Selesai & Ingin Bot Aktif Kembali?}
    AgentNeedsBotAgain -- Ya --> ClickToggleBotBtn[Agen Klik Tombol 'Resume Bot' di Header Chat]
    ClickToggleBotBtn --> CallToggleEndpoint[POST ke /admin/inbox/{id}/toggle-bot]
    CallToggleEndpoint --> ReenableBot[Set conversations.is_bot_active = true]
    ReenableBot --> UIStateBotActive

    AgentNeedsBotAgain -- Tidak / Obrolan Selesai --> End([Selesai])
```

---

## 5. Rincian Antarmuka & Langkah Kerja CS

1. Buka ruang obrolan di halaman Inbox.
2. **Melihat Status Bot:**
   - Perhatikan header di atas ruang obrolan.
   - Jika terdapat lencana biru dengan ikon robot bertuliskan **Bot Active**, berarti obrolan tersebut saat ini masih otomatis dijawab oleh sistem bot FAQ.
3. **Mengambil Alih Obrolan (*Takeover*):**
   - Anda tidak perlu mematikan bot secara manual terlebih dahulu.
   - Cukup ketik balasan Anda dan klik **Kirim**.
   - Sistem secara otomatis menghentikan respons bot dan mengalihkan status menjadi **Bot Paused**.
4. **Mengaktifkan Kembali Bot (*Resume Bot*):**
   - Jika pelanggan menanyakan hal lain di luar jam kerja Anda, atau Anda ingin bot kembali melayani FAQ pelanggan tersebut, klik tombol **Resume Bot** di header obrolan.
   - Lencana akan kembali berwarna biru menandakan bot siap membalas pesan pembeli.
