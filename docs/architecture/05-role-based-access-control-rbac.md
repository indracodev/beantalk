# 05-role-based-access-control-rbac.md — Role-Based Access Control (RBAC) Architecture

Dokumentasi arsitektur hak akses hierarkis (*Role-Based Access Control* / RBAC) untuk Dashboard Admin dan Universal Customer Chat platform **chat-me**.

---

## 1. Filosofi & Desain Hierarki

Platform dirancang untuk melayani ribuan bisnis e-commerce dan enterprise dengan model **Multi-Tenant Single Database**. Setiap pengguna (*user*) terikat secara permanen pada sebuah `tenant_id` dan memiliki satu peran (*role*) spesifik.

Sistem menerapkan hierarki 3-tingkat:
```text
┌─────────────────────────────────────────────────────────────┐
│ 1. OWNER (Super Admin Tenant)                                │
│    • Kepemilikan akun, penagihan / billing, kuota kupon     │
│    • Mengundang, mengubah role, dan menghapus staf          │
│    • Membuat & menghapus integrasi web & API keys           │
│    • Mengakses seluruh percakapan inbox & analitik          │
├─────────────────────────────────────────────────────────────┤
│ 2. ADMIN (Operational Manager)                              │
│    • Membuat & mengonfigurasi integrasi web baru            │
│    • Kustomisasi warna core UI & widget settings            │
│    • Mengundang staf agen customer service baru             │
│    • DILARANG mengubah role staf atau menghapus Owner (403) │
├─────────────────────────────────────────────────────────────┤
│ 3. AGENT (Customer Support Staff)                           │
│    • Membalas chat, mengganti status pesan (open/closed)    │
│    • Melihat daftar integrasi sebagai referensi kerja       │
│    • DILARANG membuat integrasi web atau API key (403)      │
│    • DILARANG mengundang atau memodifikasi anggota tim (403)│
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Matriks Otorisasi Kapabilitas (Capability Matrix)

| Tindakan / Resource | Owner | Admin | Agent | Endpoint REST API v1 | Middleware Guard |
|---|:---:|:---:|:---:|---|---|
| **Balas Chat & Kirim Media** | ✓ | ✓ | ✓ | `POST /api/v1/admin/conversations/{id}/reply` | `tenant.scope` |
| **Lihat Daftar Percakapan** | ✓ | ✓ | ✓ | `GET /api/v1/admin/conversations` | `tenant.scope` |
| **Lihat Daftar Integrasi Web** | ✓ | ✓ | ✓ | `GET /api/v1/admin/integrations` | `tenant.scope` |
| **Buat Integrasi Web & API Key** | ✓ | ✓ | ✕ | `POST /api/v1/admin/integrations` | `role:owner,admin` |
| **Lihat Anggota Tim Tenant** | ✓ | ✓ | ✓ | `GET /api/v1/admin/team` | `tenant.scope` |
| **Undang / Tambah Staf Baru** | ✓ | ✓* | ✕ | `POST /api/v1/admin/team` | `role:owner,admin` |
| **Ubah Peran (Promosi / Demosi)**| ✓ | ✕ | ✕ | `PUT /api/v1/admin/team/{id}/role` | `role:owner` |
| **Hapus Akun Staf** | ✓ | ✕ | ✕ | `DELETE /api/v1/admin/team/{id}` | `role:owner` |

*\*Catatan: Admin hanya diizinkan mengundang role `agent`. Hanya Owner yang dapat menunjuk atau mengundang `admin` baru.*

---

## 3. Implementasi Backend (Laravel 7/10)

### 3.1 Skema Database (`users` table)
```sql
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('owner', 'admin', 'agent') NOT NULL DEFAULT 'agent',
    `status` ENUM('online', 'offline', 'busy', 'inactive') NOT NULL DEFAULT 'offline',
    `avatar_url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    INDEX `idx_users_tenant_role` (`tenant_id`, `role`),
    CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Model Helpers (`App\Models\User`)
```php
public function isOwner(): bool { return $this->role === 'owner'; }
public function isAdmin(): bool { return $this->role === 'admin'; }
public function isAgent(): bool { return $this->role === 'agent'; }

public function hasRole($roles): bool
{
    if (is_string($roles)) {
        $roles = array_map('trim', explode(',', $roles));
    }
    return in_array($this->role, (array) $roles);
}
```

### 3.3 Middleware Enforcer (`App\Http\Middleware\CheckRole`)
Middleware memeriksa pengguna terotentikasi dan menolak permintaan jika peran tidak memadai dengan format JSON envelope standar:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Akses ditolak. Tindakan ini memerlukan hak akses: [owner, admin]. Peran Anda saat ini: 'agent'."
  }
}
```

---

## 4. UI/UX & Dynamic Permissions

1. **Role Switcher Prototype Sandbox**:
   Toolbar atas preview memungkinkan pergantian peran instan (`Owner`, `Admin`, `Agent`) untuk memverifikasi perilaku UI secara langsung.
2. **Visual Cues & Tombol Bersyarat**:
   - Jika staf berstatus `Agent`, tombol `+ Tambah Integrasi Web Baru` diredupkan (*opacity 0.55*) dengan tooltip peringatan batas akses.
   - Klik yang tidak sah memunculkan *toast banner* `HTTP 403 Forbidden` informatif tanpa merusak alur aplikasi (*zero crash*).
3. **Pemberian Identitas Visual**:
   - Badge warna emas (`#FEF3C7` / `#92400E`) untuk `Owner`.
   - Badge warna biru indigo (`#EEF2FF` / `#4338CA`) untuk `Admin`.
   - Badge warna zamrud (`#ECFDF5` / `#065F46`) untuk `Agent`.
