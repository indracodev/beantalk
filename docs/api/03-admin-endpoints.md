# API: Admin & Agent Endpoints

Endpoint internal yang digunakan oleh Laravel Admin Dashboard. Seluruh endpoint dilindungi oleh session auth middleware atau API token Sanctum dengan scoping `tenant_id`.

---

## 1. GET /api/v1/admin/conversations

Mengambil daftar percakapan untuk panel percakapan Inbox.

- **Query Params**:
  - `status`: `all` | `open` | `pending` | `closed` | `mine` (Default: `open`)
  - `project_id`: opsional filter per project
  - `search`: cari nama visitor, email, atau teks pesan
  - `page`: nomor halaman (15 record per halaman)

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 403,
        "project_name": "Main Shopify Store",
        "visitor_name": "Jane Doe",
        "visitor_email": "jane@example.com",
        "status": "open",
        "unread_admin_count": 1,
        "assigned_agent": {
          "id": 3,
          "name": "Sarah Connor"
        },
        "source": "shopify",
        "last_message": {
          "body": "Hi, does this product ship to Indonesia?",
          "sender_type": "visitor",
          "created_at": "2026-09-08T07:35:00Z"
        },
        "last_message_at": "2026-09-08T07:35:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 4,
      "total": 52
    }
  }
}
```

---

## 2. GET /api/v1/admin/conversations/{id}

Mengambil seluruh thread riwayat pesan dan data customer context untuk panel tengah dan panel kanan.

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "conversation": {
      "id": 403,
      "status": "open",
      "source": "shopify",
      "created_at": "2026-09-08T07:35:00Z"
    },
    "customer_context": {
      "visitor_id": 8102,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "current_page": "https://mystore.com/products/leather-bag",
      "source": "shopify",
      "device": "Desktop",
      "browser": "Chrome 128.0",
      "os": "Windows 11",
      "ip": "103.24.56.78",
      "first_seen_at": "2026-09-08T07:30:12Z",
      "last_seen_at": "2026-09-08T07:36:00Z"
    },
    "messages": [
      {
        "id": 1821,
        "sender_type": "visitor",
        "body": "Hi, does this product ship to Indonesia?",
        "created_at": "2026-09-08T07:35:00Z"
      },
      {
        "id": 1823,
        "sender_type": "agent",
        "sender_name": "Sarah Connor",
        "body": "Yes, we provide international express shipping!",
        "created_at": "2026-09-08T07:35:55Z"
      }
    ]
  }
}
```

---

## 3. POST /api/v1/admin/conversations/{id}/reply

Agen membalas pesan ke visitor.

### Request Body
```json
{
  "body": "Our estimated delivery time is 3 to 5 business days.",
  "type": "text",
  "client_message_id": "c0e9b981-d1f8-45ee-9e0c-f38b0244917f",
  "attachment_ids": []
}
```

### Response (`201 Created`)
```json
{
  "success": true,
  "data": {
    "id": 1824,
    "conversation_id": 403,
    "sender_type": "agent",
    "sender_name": "Sarah Connor",
    "body": "Our estimated delivery time is 3 to 5 business days.",
    "created_at": "2026-09-08T07:37:10Z"
  }
}
```

---

## 4. POST /api/v1/admin/conversations/{id}/assign

Menugaskan percakapan ke agen lain.

### Request Body
```json
{
  "agent_user_id": 5
}
```

---

## 5. POST /api/v1/admin/conversations/{id}/status

Mengubah status percakapan (`open`, `pending`, `closed`).

### Request Body
```json
{
  "status": "closed"
}
```

---

## 5.1 POST /admin/inbox/{id}/toggle-bot (atau /api/v1/admin/conversations/{id}/toggle-bot)

Mengaktifkan atau menjeda status Smart Bot untuk percakapan spesifik.

### Request Body
```json
{
  "is_bot_active": false
}
```

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "conversation_id": 403,
    "is_bot_active": false,
    "bot_handoff_at": "2026-09-15T03:30:00Z"
  }
}
```

---

## 6. GET /api/v1/admin/realtime/poll

Endpoint polling realtime dashboard admin untuk memperbarui daftar inbox dan badge notifikasi tanpa reload halaman.

- **Query Params**:
  - `after_event_id`: ID event terakhir yang diterima dashboard admin
  - `active_conversation_id`: percakapan yang sedang dibuka di layar admin saat ini
- **Interval**: 2.500 ms (2,5 detik) saat window browser fokus.

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "unread_total": 4,
    "conversation_updates": [
      {
        "id": 403,
        "last_message": "Thank you!",
        "last_message_at": "2026-09-08T07:38:00Z",
        "unread_admin_count": 1
      }
    ],
    "new_messages": [
      {
        "id": 1825,
        "conversation_id": 403,
        "sender_type": "visitor",
        "body": "Thank you!",
        "created_at": "2026-09-08T07:38:00Z"
      }
    ],
    "latest_event_id": 920
  }
}
```

---

## 7. GET /admin/inbox/feed/updates

Polling real-time daftar percakapan di Inbox admin. Mendukung deteksi percakapan baru, pesan visitor yang baru masuk, dan update badge notifikasi total unread.

- **Query Params**:
  - `since_message_id`: ID pesan tertinggi di tenant yang sudah diterima admin
  - `project_id`: opsional filter website/toko
  - `status`: `all` | `open` | `mine` | `closed`
  - `search`: filter pencarian kata kunci / customer code
- **Interval**: 3.000 ms saat tab aktif, 12.000 ms saat tab idle / di latar belakang.

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "conversations": [
      {
        "id": 403,
        "customer_name": "Budi Santoso",
        "customer_code": "CUS-8F21",
        "initials": "BU",
        "channel_label": "Web Chat",
        "project_name": "Supresso Coffee",
        "last_message_preview": "Halo Pak, apakah promo masih berlaku?",
        "last_message_time": "2 minutes ago",
        "unread_agent_count": 1,
        "is_unread": true,
        "status": "open",
        "assigned_user_id": 2
      }
    ],
    "unread_total": 3,
    "max_message_id": 1826,
    "has_new_incoming": true,
    "new_incoming_count": 1,
    "latest_incoming": {
      "conversation_id": 403,
      "sender_name": "Budi Santoso",
      "content": "Halo Pak, apakah promo masih berlaku?"
    }
  }
}
```

---

## 8. PUT /admin/inbox/{id}/customer

Memperbarui nama display customer dari panel konteks drawer atau header thread CS.

### Request Body
```json
{
  "name": "Budi Sudarsono"
}
```

### Response (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 8102,
    "name": "Budi Sudarsono",
    "customer_code": "CUS-8F21",
    "display_name": "Budi Sudarsono"
  }
}
```

