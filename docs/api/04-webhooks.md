# API: Webhook System & Event Subscriptions

Sistem webhook memungkinkan tenant mengintegrasikan event percakapan chat secara realtime ke sistem eksternal mereka (CRM, bot WhatsApp, ERP, atau Zapier/Make).

---

## 1. Supported Event Types

| Event Name | Kapan Terpicu |
|---|---|
| `message.created` | Pesan baru terkirim oleh visitor, agent, atau sistem |
| `conversation.created` | Percakapan baru berhasil dibuat oleh visitor |
| `conversation.assigned` | Percakapan ditugaskan ke agen |
| `conversation.closed` | Percakapan diselesaikan dan ditutup |
| `contact.identified` | Visitor memberikan email/identitas |

---

## 2. Webhook Delivery Payload

Setiap request HTTP `POST` webhook dikirimkan dengan format JSON seragam:

```json
{
  "event": "message.created",
  "id": "evt_9a4f21b7e",
  "timestamp": "2026-09-08T07:40:00Z",
  "tenant_id": 12,
  "project_id": 4,
  "data": {
    "conversation_id": 403,
    "message": {
      "id": 1826,
      "sender_type": "visitor",
      "body": "Can I get a discount code?",
      "type": "text",
      "created_at": "2026-09-08T07:40:00Z"
    },
    "customer": {
      "visitor_uuid": "e5b8a1c9-72f1-4db5-9e63-47029517fa91",
      "name": "Jane Doe",
      "email": "jane@example.com"
    }
  }
}
```

---

## 3. HMAC SHA-256 Signature Verification

Untuk memverifikasi keaslian pengirim, setiap payload webhook disertai header:
- `X-Chat-Signature`: Hash HMAC-SHA256 dari raw request body menggunakan secret webhook yang diberikan ke tenant.

### Contoh Verifikasi (Node.js / PHP)
```php
$signature = $request->header('X-Chat-Signature');
$computedSignature = hash_hmac('sha256', $request->getContent(), $webhookSecret);

if (!hash_equals($signature, $computedSignature)) {
    abort(401, 'Invalid Webhook Signature');
}
```

---

## 4. Retry Policy & Delivery Logs

1. **Timeout**: Request HTTP webhook memiliki timeout ketat 5 detik.
2. **Retry Mechanism**: Jika server tenant mengembalikan status `5xx` atau timeout:
   - Attempt 1: Langsung
   - Attempt 2: 5 menit kemudian
   - Attempt 3: 30 menit kemudian
   - Attempt 4: 2 jam kemudian
3. **Circuit Breaker**: Jika webhook gagal 50 kali berturut-turut, status webhook diubah menjadi `is_active = FALSE` untuk melindungi performa server shared hosting.

---

## 5. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Synchronous Fallback untuk Dispatch Webhook?**
>
> Di shared hosting tanpa background queue worker, dispatch webhook tetap dapat dieksekusi dengan aman menggunakan `dispatchAfterResponse()` Laravel (FastCGI finish request). Response HTTP dikembalikan seketika ke browser, lalu PHP mengirim HTTP request webhook sebelum worker terminate.
>
> *Skipped: Redis queue daemon, Kafka broker.*  
> *Upgrade when: Tenant membutuhkan jaminan antrean jutaan event per detik.*
