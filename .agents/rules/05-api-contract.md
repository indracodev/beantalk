# 05 — Kontrak REST API v1 & Widget

## Envelope (wajib, semua endpoint JSON)
```json
{ "success": true,  "data": { ... } }
{ "success": false, "error": { "code": "UPPER_SNAKE", "message": "Bahasa Indonesia yang jelas" } }
```
Gunakan `$this->ok()` / `$this->fail()`. Kode yang sudah dipakai: `UNAUTHENTICATED`, `FORBIDDEN`, `VALIDATION_ERROR`,
`NOT_FOUND`, `CONVERSATION_NOT_FOUND`, `CONVERSATION_CLOSED`, `MISSING_PROJECT_KEY`, `INVALID_PROJECT_KEY`,
`UNAUTHORIZED_DOMAIN`, `TOO_MANY_REQUESTS`, `INVALID_CREDENTIALS`, `ALREADY_CLOSED`, `NO_EMAIL`.

## Grup route (`routes/api.php`)
| Prefix | Middleware | Pemanggil |
|---|---|---|
| `/api/v1/auth` | throttle login | staf |
| `/api/v1/client` | `project.key` + throttle per-route | widget pengunjung |
| `/api/v1/telegram/webhook` | throttle, verifikasi secret di controller | server Telegram |
| `/api/v1/admin` | `tenant.scope` (+ `role:superadmin` untuk mutasi) | staf |

## Aturan widget
- Kepemilikan percakapan selalu dicek dengan `visitor_uuid` (header `X-Visitor-Uuid` atau body) → bukan pemilik = **404**.
- Catatan internal (`is_internal_note`) tidak pernah dikirim ke widget/transkrip.
- Polling: `GET conversations/{id}/messages?after_id=` (range scan index), batas 100.
- Perubahan bentuk respons widget wajib kompatibel mundur (widget lama ter-cache di situs pelanggan); cabang transisi diberi penanda `ponytail:` dengan tenggat.

## Realtime
- Adaptive polling (2.5 dtk aktif / 15 dtk latar). WebSocket/daemon hanya bila ada pemicu jelas dan tetap opsional.
- Feed admin: handshake **tanpa** `since_message_id` = baseline; `since_message_id=0` tetap sah (tenant baru).

## Status baca (inbox staf)
- Per-user, disimpan di `conversation_participants.last_read_message_id`. Jangan lagi mengosongkan `unread_agent_count` saat chat dibuka.
- Membuka chat → `markReadBy($user)`; **superadmin tidak pernah tercatat membaca** (pemantauan tidak menghabiskan status "baru").
- Chat yang sudah di-assign/diambil CS lain tidak dihitung "belum dibaca" bagi CS lain.
- Balasan siapa pun (`appendMessage` non-visitor) → `unread_agent_count = 0` → terbaca untuk semua. Pesan pengunjung baru → "baru" lagi.
- Badge, feed polling, notifikasi bunyi, dan dashboard memakai scope `unreadFor($user)` yang sama.

