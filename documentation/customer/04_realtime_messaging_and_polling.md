# Modul Pengiriman Pesan & Polling Realtime Widget (Customer / Visitor)

## 1. Deskripsi Fitur
Modul Pengiriman Pesan & Polling Realtime adalah jantung komunikasi interaktif pada widget BeanTalk di sisi pembeli. Fitur ini menangani pengetikan pesan, pengiriman teks instan, pencegahan pengiriman pesan ganda akibat koneksi internet yang tidak stabil (*Idempotency via Client Message ID*), serta penarikan balasan agen secara realtime menggunakan teknik *Smart Adaptive HTTP Polling*.

---

## 2. Hak Akses & Role Permission
- **Role:** `customer` (Pelanggan / Pengunjung Website)
- **Otentikasi:** Header HTTP `X-Project-Key: pk_live_...`
- **Rate Limiting:**
  - Pengiriman pesan: Dibatasi maksimal 120 pesan/menit per IP.
  - Penarikan pesan (*polling*): Dibatasi maksimal 300 request/menit per IP.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller API: `App\Http\Controllers\Api\Client\MessageController`
  - `store()`: Menyimpan pesan yang diketik pengunjung.
  - `index()`: Mengambil daftar pesan baru (`id > after_id`).
- Route:
  - `GET /api/v1/client/conversations/{id}/messages`
  - `POST /api/v1/client/conversations/{id}/messages`

### Prinsip Idempotensi (*Idempotency Protection*):
Setiap pesan yang dikirim oleh widget menyertakan identifier acak unik pada sisi klien (`client_message_id`, cth: `uuid_v4` atau timestamp hash).
```php
$existingMessage = Message::where('conversation_id', $conversationId)
                          ->where('client_message_id', $request->client_message_id)
                          ->first();

if ($existingMessage) {
    // Kembalikan pesan yang sudah ada tanpa menduplikasi data di database
    return response()->json(['success' => true, 'data' => $existingMessage]);
}
```
Hal ini menjamin bahwa jika pembeli mengklik tombol kirim berkali-kali saat sinyal internet melemah, sistem hanya akan menyimpan dan menampilkan 1 pesan saja.

---

## 4. Flowchart Alur Pengiriman Pesan & Polling Realtime Widget

```mermaid
flowchart TD
    Start([Pengunjung Mengetik Pesan di Kotak Chat Widget]) --> PressSend[Pengunjung Menekan Enter / Tombol Kirim]
    
    PressSend --> OptimisticUI[SDK Langsung Render Pesan di Layar dengan Status: 'Sending...']
    OptimisticUI --> GenerateUUID[Generate client_message_id Unik untuk Idempotensi]
    
    GenerateUUID --> SendPostRequest[Kirim POST Request ke /api/v1/client/conversations/{id}/messages]
    SendPostRequest --> CheckServerAuth{Validasi Header X-Project-Key?}
    CheckServerAuth -- Tidak Sah --> MarkMessageFailed[Ubah Status Bubble: 'Gagal Terkirim, Coba Lagi']
    
    CheckServerAuth -- Sah --> CheckIdempotency{Apakah client_message_id Sudah Pernah Disimpan?}
    CheckIdempotency -- Duplikat --> ReturnExisting[Kembalikan Record Lama Tanpa Insert Duplikat]
    CheckIdempotency -- Pesan Baru --> SaveMessageDB[(Insert ke Tabel messages dengan sender_type = 'visitor')]
    
    SaveMessageDB --> CheckBotTrigger[Evaluasi BotService: Apakah Bot Harus Menjawab?]
    SaveMessageDB --> MarkMessageSent[Kembalikan Sukses: Ubah Status Bubble Menjadi 'Sent' (Centang Satu)]
    
    %% Alur Polling Balasan
    subgraph AdaptivePollingLoop [Siklus Polling Adaptif di Sisi Klien]
        StartPoll[SDK Memulai Polling Setiap 3 Detik saat Tab Aktif] --> SendGetPoll[GET ke /api/v1/client/conversations/{id}/messages?after_id=last_id]
        SendGetPoll --> QueryNewMessages[MySQL Cari Pesan dengan id > after_id]
        QueryNewMessages --> CheckAnyAgentReply{Apakah Ada Balasan Baru dari Agen / Bot?}
        
        CheckAnyAgentReply -- Ada Balasan --> AppendReplyBubble[Render Bubble Balasan Agen / Bot di Widget Pengunjung]
        AppendReplyBubble --> PlayAudioBeep[Bunyikan Nada Chat Lembut & Update last_id]
        CheckAnyAgentReply -- Belum Ada Balasan --> SleepInterval[Tunggu 3 Detik Lalu Ulangi Polling]
    end

    MarkMessageSent --> StartPoll
    PlayAudioBeep --> SleepInterval
```

---

## 5. Pengalaman Pengguna & Keunggulan Komunikasi

1. **Pengiriman Instan (*Optimistic UI*):**
   - Begitu pembeli menekan tombol kirim, pesan langsung muncul di layar pembeli seketika tanpa jeda putar *loading*, memberikan sensasi obrolan yang sangat gesit.
2. **Indikator Status Pengiriman:**
   - Ikon centang abu-abu menandakan pesan telah sukses diterima server toko.
   - Jika sinyal ponsel pembeli terputus, muncul tombol *Coba Lagi* berwarna merah.
3. **Pengurutan Kronologis Presisi:**
   - Menggunakan ID sekuensial bawaan database MySQL memastikan jawaban dari agen CS tidak pernah tertukar posisinya dengan pertanyaan dari pembeli.
