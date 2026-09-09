# Architecture: Realtime Transport & Fallback Abstraction

## 1. Transport Abstraction Interface

Untuk menjamin kompatibilitas shared hosting tanpa menutup jalan integrasi WebSocket di masa depan, layer transport diabstraksikan di sisi client dan server.

### Client Transport Contract (TypeScript)
```typescript
export interface TransportMessage {
  id: number;
  conversation_id: number;
  sender_type: 'visitor' | 'agent' | 'system';
  body: string;
  created_at: string;
}

export interface RealtimeTransport {
  connect(): void;
  disconnect(): void;
  onMessage(callback: (msg: TransportMessage) => void): void;
  onEvent(callback: (eventType: string, payload: any) => void): void;
  onStatusChange(callback: (status: 'connected' | 'reconnecting' | 'offline') => void): void;
}
```

---

## 2. Transport Implementations

```text
               RealtimeTransport (Interface)
                             │
       ┌─────────────────────┼─────────────────────┐
       ▼                     ▼                     ▼
PollingTransport        SSETransport       WebSocketTransport
(Default MVP)           (Optional)         (Future VPS Driver)
Shared Hosting 100%     Streaming HTTP     Persistent Socket
```

### 2.1 PollingTransport (Default)
- **Mekanisme**: HTTP GET `GET /api/v1/realtime/poll?conversation_id=X&after_id=Y`
- **Keunggulan**: Berjalan di shared hosting $2/bulan, zero background process, tidak terpengaruh batas timeout proxy server.
- **Strategi Optimasi**: Query MySQL menggunakan klausa indexed `WHERE conversation_id = ? AND id > ? ORDER BY id ASC LIMIT 50`.

### 2.2 SSETransport (Server-Sent Events)
- **Mekanisme**: HTTP keep-alive streaming `GET /api/v1/realtime/sse`
- **Fallback**: Jika koneksi terputus dan gagal reconnect sebanyak 3 kali berturut-turut, otomatis beralih ke `PollingTransport`.

### 2.3 WebSocketTransport (Future VPS Scale)
- **Mekanisme**: Persistent bi-directional TCP socket (Laravel Reverb / Pusher-compatible protocol).
- SDK beralih ke driver ini hanya jika konfigurasi server mengirimkan `realtime_driver: 'websocket'`.

---

## 3. Fallback Flowchart

```text
                     SDK Init
                        │
       Config 'auto' / driver ditentukan?
                        │
             ┌──────────┴──────────┐
             ▼                     ▼
    Driver = 'websocket'     Driver = 'polling' (Default)
             │                     │
      Coba koneksi WS              │
             │                     │
      Sukses?                      │
      ├── YES ──► Gunakan WS       │
      └── NO                       │
           │                       │
      Coba SSE                     │
           │                       │
      Sukses?                      │
      ├── YES ──► Gunakan SSE      │
      └── NO ──────────────────────┴──► Gunakan Polling (Guaranteed)
```

---

## 4. Delivery Guarantee: Database First

Realtime layer **bukan tempat penyimpanan pesan**. Realtime hanyalah akselerator notifikasi.

```text
Visitor sends message
        │
        ▼
Laravel Controller: Database Transaction (INSERT into messages)
        │
        ├────────────────────────────────────┐
        ▼ (Sukses Tersimpan di MySQL)        ▼ (Background Notify)
HTTP Response 201 ke Visitor           Publish Event ke Realtime Layer
                                             │
                                     Berhasil terkirim?
                                     ├── YES ──► Instan masuk ke Admin/Visitor
                                     └── NO  ──► Polling berikutnya (2s)
                                                 tetap mengambil pesan ini!
```
*Zero Message Loss*: Sekalipun koneksi realtime terputus, tidak ada pesan yang hilang karena database telah menyimpan pesan sebelum event dikirim.

---

## 5. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Polling Default MVP Bukan Anti-Pattern?**
>
> Banyak engineer berasumsi chat = wajib WebSocket. Faktanya:
> 1. Polling interval 2 detik menggunakan indexed query `WHERE id > :after_id` memakan waktu CPU < 2 milidetik di MySQL.
> 2. Tab yang tidak aktif otomatis diturunkan intervalnya ke 15–30 detik (adaptive polling).
> 3. Hasilnya: nol server crash, nol zombie connection, nol kerumitan konfigurasi SSL port 6001.
