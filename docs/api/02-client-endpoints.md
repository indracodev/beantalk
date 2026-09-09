# API: Public Client Endpoints

Endpoint publik yang digunakan oleh Universal JavaScript SDK dan Chat Widget.

---

## 1. POST /api/v1/identify

Mengidentifikasi visitor anonim atau yang sudah login, mengambil konfigurasi widget, serta memeriksa apakah ada percakapan aktif yang sedang berjalan.

- **Headers**:
  - `X-Project-Key`: `pk_live_xxxxx`
  - `Content-Type`: `application/json`
- **Rate Limit**: 60 req/min per IP

### Request Body
```json
{
  "visitor_uuid": "e5b8a1c9-72f1-4db5-9e63-47029517fa91",
  "page_url": "https://mystore.com/products/leather-bag",
  "page_title": "Premium Leather Bag - Store",
  "source": "shopify",
  "browser": "Chrome 128.0",
  "os": "Windows 11",
  "email": "customer@example.com",     // Opsional
  "name": "Jane Doe"                   // Opsional
}
```

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "visitor": {
      "id": 8102,
      "visitor_uuid": "e5b8a1c9-72f1-4db5-9e63-47029517fa91",
      "name": "Jane Doe"
    },
    "active_conversation": {
      "id": 402,
      "status": "open",
      "last_message_id": 1820
    },
    "widget_settings": {
      "title": "Customer Support",
      "greeting": "Hi Jane! How can we assist you today?",
      "primary_color": "#0F172A",
      "text_color": "#FFFFFF",
      "position": "bottom-right",
      "show_branding": true,
      "offline_message": "Our agents are away. Please leave a message!",
      "poll_interval_active_ms": 2000,
      "poll_interval_idle_ms": 15000
    }
  }
}
```

---

## 2. POST /api/v1/conversations

Membuat thread percakapan baru untuk visitor.

- **Headers**:
  - `X-Project-Key`: `pk_live_xxxxx`
- **Rate Limit**: 10 req/min per visitor

### Request Body
```json
{
  "visitor_id": 8102,
  "initial_message": {
    "client_message_id": "bf7a3055-b778-450f-90e6-58c0c1ea3ad0",
    "body": "Hi, does this product ship to Indonesia?",
    "type": "text"
  }
}
```

### Response (`201 Created`)
```json
{
  "success": true,
  "data": {
    "id": 403,
    "status": "open",
    "created_at": "2026-09-08T07:35:00Z",
    "first_message": {
      "id": 1821,
      "body": "Hi, does this product ship to Indonesia?",
      "status": "sent"
    }
  }
}
```

---

## 3. GET /api/v1/conversations/{id}

Mengambil status dan metadata percakapan.

- **Parameters**: `id` (Conversation ID)
- **Query Params**: `visitor_id=8102` (Wajib untuk verifikasi kepemilikan)
- **Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "id": 403,
    "status": "open",
    "assigned_agent": {
      "name": "Sarah Connor",
      "avatar_url": "https://chat.domain.com/avatars/sarah.png"
    },
    "created_at": "2026-09-08T07:35:00Z"
  }
}
```

---

## 4. POST /api/v1/conversations/{id}/messages

Mengirim pesan dari visitor ke agen.

- **Rate Limit**: 30 req/min per visitor

### Request Body
```json
{
  "visitor_id": 8102,
  "client_message_id": "c16dae92-3a87-43cf-bc04-8b6bceb75f0a",
  "body": "Here is the screenshot of the error.",
  "type": "image",
  "attachment_ids": [108]
}
```

### Response (`201 Created` atau `200 OK` jika idempotency hit)
```json
{
  "success": true,
  "data": {
    "id": 1822,
    "conversation_id": 403,
    "sender_type": "visitor",
    "body": "Here is the screenshot of the error.",
    "type": "image",
    "attachments": [
      {
        "id": 108,
        "url": "https://chat.domain.com/storage/attachments/2026/09/error.png",
        "mime_type": "image/png"
      }
    ],
    "status": "sent",
    "created_at": "2026-09-08T07:35:40Z"
  }
}
```

---

## 5. GET /api/v1/realtime/poll

Endpoint inti untuk polling sinkronisasi pesan baru dan event.

- **Query Params**:
  - `conversation_id`: `403`
  - `after_id`: `1821` (ID pesan terakhir yang ada di klien)
  - `visitor_id`: `8102`
- **Rate Limit**: 60 req/min per visitor

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "messages": [
      {
        "id": 1823,
        "conversation_id": 403,
        "sender_type": "agent",
        "sender_name": "Sarah Connor",
        "body": "Yes, we provide international express shipping!",
        "type": "text",
        "attachments": [],
        "created_at": "2026-09-08T07:35:55Z"
      }
    ],
    "events": [
      { "type": "agent.typing", "value": false }
    ],
    "latest_id": 1823
  }
}
```

---

## 6. POST /api/v1/attachments

Mengunggah lampiran gambar atau dokumen sebelum pesan dikirim.

- **Content-Type**: `multipart/form-data`
- **Form Fields**:
  - `file`: binary file (Maksimal 5MB, format: jpg, jpeg, png, webp, pdf)
  - `visitor_id`: `8102`
- **Rate Limit**: 10 req/min per IP

### Response (`201 Created`)
```json
{
  "success": true,
  "data": {
    "id": 108,
    "original_name": "receipt.pdf",
    "mime_type": "application/pdf",
    "file_size": 348120,
    "url": "https://chat.domain.com/storage/attachments/2026/09/hash_name.pdf"
  }
}
```
