# API: Overview, Authentication & Standards

Semua endpoint API beralamat di prefix `/api/v1/` dengan header wajib `Accept: application/json`.

---

## 1. Response Envelope Format

### 1.1 Standar Sukses (`200 OK` / `201 Created`)
```json
{
  "success": true,
  "data": { ... }
}
```

### 1.2 Standar Error (`4xx` / `5xx`)
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE_NAME",
    "message": "Human readable explanation of the error",
    "details": null
  }
}
```

### 1.3 Daftar Error Code
| Code | HTTP Status | Keterangan |
|---|:---:|---|
| `VALIDATION_ERROR` | 422 | Payload request tidak lolos validasi input |
| `UNAUTHORIZED` | 401 | Kunci publik/sesi login tidak valid atau kadaluarsa |
| `FORBIDDEN` | 403 | Domain tidak terdaftar atau hak akses peran tidak cukup |
| `NOT_FOUND` | 404 | Resource percakapan/pesan/visitor tidak ditemukan |
| `DOMAIN_NOT_ALLOWED` | 403 | Origin request tidak sesuai dengan whitelist domain project |
| `RATE_LIMIT_EXCEEDED` | 429 | Request melebihi batas rate limiting |
| `SERVER_ERROR` | 500 | Terjadi kesalahan internal (stack trace disembunyikan) |

---

## 2. Authentication Layers

### 2.1 Public Client Auth (Widget / Visitor)
Widget dan visitor tidak menggunakan email/password ataupun private key rahasia. Autentikasi dilakukan via:
- Header: `X-Project-Key: pk_live_xxxxx`
- Header: `Origin` atau `Referer` (Browser-managed, tidak dapat dipalsukan oleh JavaScript klien)

**Langkah Validasi Middleware:**
```text
Client Request
      │
      ▼
Middleware: ResolveProjectKey
      │
      ├── Cek public_key di tabel api_keys
      │   └── Tidak ditemukan? ──► Error 401 UNAUTHORIZED
      │
      ├── Cek Origin/Referer di tabel project_domains
      │   └── Tidak cocok?    ──► Error 403 DOMAIN_NOT_ALLOWED
      │
      └── Bind $project dan $tenant ke request pipeline ──► Lanjut Controller
```

### 2.2 Admin Dashboard Auth (Agent / Admin)
Admin dashboard menggunakan mekanisme autentikasi bawaan Laravel:
- **Web Session Guard** (Cookie-based session dengan CSRF protection) untuk browser admin.
- Opsional: **Laravel Sanctum Bearer Token** jika admin menggunakan native app atau API eksternal.

---

## 3. Rate Limiting Configuration

Dikonfigurasi di `app/Providers/RouteServiceProvider.php` atau `routes/api.php` menggunakan rate limiter bawaan Laravel:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('chat-identify', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

RateLimiter::for('chat-messages', function (Request $request) {
    return Limit::perMinute(30)->by($request->input('visitor_id') ?: $request->ip());
});

RateLimiter::for('chat-poll', function (Request $request) {
    return Limit::perMinute(60)->by($request->input('visitor_id') ?: $request->ip());
});

RateLimiter::for('chat-upload', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});
```

---

## 4. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Tidak Menggunakan JWT untuk Visitor Anonim?**
>
> Menggunakan JWT untuk ratusan ribu visitor anonim membutuhkan key management, refresh token cycle, token expiration handler, dan parsing overhead di setiap polling call.
>
> *Keputusan: `visitor_uuid` acak di browser `localStorage` + validasi `X-Project-Key` & `Origin`. Sederhana, aman, tanpa library JWT.*
