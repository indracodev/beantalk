# 06-audit-trail-and-activity-logs.md — Audit Trail & Activity Logs Architecture

Dokumentasi arsitektur pencatatan jejak aktivitas pengguna (*Audit Trail & Activity Logs*) untuk platform **chat-me** di seluruh tingkatan peran (**Owner**, **Admin**, **Agent**, dan **Visitor/System**).

---

## 1. Filosofi & Desain Arsitektur

Audit trail adalah komponen kepatuhan (*compliance*) dan transparansi yang menjamin setiap aksi penting yang terjadi di dalam akun bisnis dapat dilacak secara akurat:
- **Siapa** yang melakukannya (`user_id`, `user_name`, `user_role`).
- **Apa** tindakan yang diambil (`action`, `description`).
- **Objek** apa yang terdampak (`subject_type`, `subject_id`, `properties`).
- **Dari mana** aksi dilakukan (`ip_address`, `user_agent`).
- **Kapan** aksi dieksekusi (`created_at`).

### Prinsip Shared Hosting First & Zero Daemon
Sistem audit trail ini dirancang **tanpa memerlukan background queue daemon (Redis/Horizon/Elasticsearch)**:
- Setiap pemanggilan `ActivityLogger::log()` melakukan satu query `INSERT` ringan langsung ke tabel MySQL yang terindeks B-Tree.
- Waktu overhead penulisan log adalah **sub-1ms**, sehingga sama sekali tidak memperlambat respon API atau konsumsi RAM server shared hosting.

---

## 2. Taksonomi Aksi / Event Dictionary

Aktivitas dikelompokkan secara terstruktur berdasarkan peran pelaku:

| Kategori Peran | Kode Aksi (`action`) | Keterangan & Dampak |
|---|---|---|
| **👑 Owner** | `billing.upgrade` | Upgrade paket langganan atau kuota situs/staf |
| **👑 Owner** | `role.updated` | Mengubah hak akses staf (promosi / demosi) |
| **👑 Owner** | `team.removed` | Menghapus akun staf dari organisasi |
| **⚡ Admin** | `integration.created` | Mendaftarkan website baru & membuat public key |
| **⚡ Admin** | `integration.updated` | Mengubah tema warna core atau pesan greeting widget |
| **⚡ Admin** | `team.invited` | Mengundang staf CS baru ke dalam tim |
| **🎧 Agent** | `chat.assigned` | Mengambil alih atau ditugaskan pada tiket percakapan |
| **🎧 Agent** | `message.replied` | Mengirimkan pesan balasan ke pengunjung toko |
| **🎧 Agent** | `chat.closed` | Menandai percakapan pelanggan telah selesai |
| **🛍️ Visitor** | `session.init` | Pengunjung membuka website & widget chat tersinkronisasi |
| **🛍️ Visitor** | `attachment.uploaded`| Pengunjung mengirimkan foto produk terkompresi Canvas |

---

## 3. Skema Database (`activity_logs`)

```sql
CREATE TABLE `activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `user_name` VARCHAR(100) NULL,
    `user_role` ENUM('owner', 'admin', 'agent', 'visitor', 'system') NOT NULL DEFAULT 'system',
    `action` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `subject_type` VARCHAR(100) NULL,
    `subject_id` BIGINT UNSIGNED NULL,
    `properties` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- High performance composite indexes
    INDEX `idx_logs_tenant_created` (`tenant_id`, `created_at`),
    INDEX `idx_logs_tenant_user` (`tenant_id`, `user_id`),
    INDEX `idx_logs_tenant_role` (`tenant_id`, `user_role`),
    INDEX `idx_logs_tenant_action` (`tenant_id`, `action`),

    CONSTRAINT `fk_logs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 4. Helper Service (`ActivityLogger`)

Pencatatan log dilakukan melalui satu baris helper sederhana:

```php
use App\Services\ActivityLogger;

// Contoh 1: Catat pembuatan integrasi oleh Admin
ActivityLogger::log(
    'integration.created',
    "Membuat integrasi channel website baru: {$project->name}",
    $project,
    ['domain' => $domain, 'public_key' => $publicKey]
);

// Contoh 2: Catat pengubahan role oleh Owner
ActivityLogger::log(
    'role.updated',
    "Mengubah role {$targetUser->name} dari {$oldRole} menjadi {$targetUser->role}",
    $targetUser,
    ['old_role' => $oldRole, 'new_role' => $targetUser->role]
);
```

---

## 5. REST API v1 Endpoint

### `GET /api/v1/admin/activity-logs`
Mengambil riwayat log audit dengan filter per peran dan paginasi:

**Query Parameters:**
- `role`: Filter peran (`owner`, `admin`, `agent`, `visitor`, `system`)
- `user_id`: Filter ID pengguna tertentu
- `action`: Filter kata kunci event (misal: `message`)
- `per_page`: Jumlah log per halaman (default 25, maks 100)

**Contoh Respon Envelope (HTTP 200):**
```json
{
  "success": true,
  "data": {
    "logs": [
      {
        "id": 7,
        "tenant_id": 1,
        "user_id": 3,
        "user_name": "Budi (B2B Specialist)",
        "user_role": "agent",
        "action": "message.replied",
        "description": "Membalas inquiry ekspor kontainer green beans 20ft ke Mr. Tan (SG)",
        "properties": { "conversation_id": 3 },
        "ip_address": "182.253.120.44",
        "created_at": "2026-09-09T01:45:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 25,
      "total": 7,
      "last_page": 1
    }
  }
}
```
