# Deployment: Security Hardening & Boundaries

Keamanan platform adalah prioritas sejak awal. Dokumen ini mendefinisikan batasan keamanan, proteksi public API, validasi file, dan kepatuhan isolasi data.

---

## 1. Public API Key vs Private Key

1. **Public Key (`pk_live_xxxxx`)**:
   - Didesain untuk diekspos di HTML browser visitor.
   - **Bukan rahasia (not a secret)**.
   - Hak aksesnya dibatasi secara ketat hanya untuk: registrasi visitor (`identify`), mengirim pesan ke thread milik visitor bersangkutan, dan polling pesan miliknya sendiri.
   - **Dilarang keras**: Public key tidak boleh dapat mengakses data tenant lain, tidak boleh dapat membaca riwayat percakapan visitor lain, dan tidak boleh mengakses endpoint admin.

2. **Domain Whitelisting (CORS & Origin Boundary)**:
   - Server memvalidasi header `Origin` atau `Referer` pada setiap request yang menggunakan public key terhadap daftar `project_domains`.
   - Mencegah pihak ketiga mencuri script tag Anda untuk dipasang di website spam/pishing mereka.

---

## 2. File Upload Validation & Sanitization

File attachment berpotensi menjadi celah eksekusi Remote Code Execution (RCE) jika tidak divalidasi dengan benar.

### Aturan Ketat Upload Controller:
```php
public function storeAttachment(UploadAttachmentRequest $request)
{
    $file = $request->file('file');

    // 1. Validasi Ukuran (Max 5MB)
    // 2. Validasi Ekstensi & MIME Sejati (Bukan dari nama file)
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf'
    ];

    $realMime = $file->getMimeType();
    if (!array_key_exists($realMime, $allowedMimes)) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INVALID_FILE_TYPE',
                'message' => 'Hanya format JPG, PNG, WEBP, dan PDF yang diizinkan.'
            ]
        ], 422);
    }

    // 3. Simpan dengan nama acak hash (dilarang menggunakan original filename di disk)
    $safeExtension = $allowedMimes[$realMime];
    $filename = Str::random(40) . '.' . $safeExtension;
    $path = $file->storeAs("attachments/" . date('Y/m'), $filename, 'public');

    // 4. Catat ke database
    // ...
}
```

---

## 3. SQL Injection & XSS Protection

1. **SQL Injection**:
   - 100% menggunakan Eloquent ORM atau PDO Prepared Statements dengan parameter binding (`where('id', $id)`).
   - Dilarang keras melakukan konkatenasi raw SQL string dengan input user (`DB::raw("... $input")`).

2. **XSS (Cross-Site Scripting)**:
   - Semua pesan yang diketik visitor di-escape sebelum dirender ke DOM.
   - Di sisi widget: Menggunakan `element.textContent = message.body` daripada `innerHTML`.
   - Di sisi Admin Dashboard Blade: Menggunakan tag kurung kurawal ganda `{{ $message->body }}` yang otomatis di-escape oleh engine Blade.

---

## 4. Rate Limiting Protection

| Endpoint Target | Batasan Default | Scope |
|---|:---:|:---:|
| `POST /api/v1/identify` | 60 req / menit | IP Address |
| `POST /api/v1/conversations/*/messages` | 30 req / menit | Visitor ID / IP |
| `GET /api/v1/realtime/poll` | 60 req / menit | Visitor ID / IP |
| `POST /api/v1/attachments` | 10 req / menit | IP Address |
| `POST /login` (Admin) | 5 attempts / menit | Email + IP (Lockout) |

---

## 5. Audit Logging Policy

Setiap aksi administratif penting dicatat di tabel `audit_logs`:
- Login / logout agen
- Pengalihan tugas percakapan (*reassignment*)
- Penutupan percakapan
- Perubahan setting widget atau domain whitelist
- Pembuatan atau pencabutan API Key

**Pencegahan Bocor Informasi Rahasia**:
- Dilarang keras mencatat password, hash password, atau raw session cookies ke dalam kolom metadata `audit_logs`.

---

## 6. Anti-Bot & Abuse Prevention (Proteksi Bot & Spam API)

Untuk mencegah bot scraper, spammer, dan serangan brute-force / flood pada endpoint publik, sistem menerapkan 5 lapisan pertahanan (*Defense-in-Depth*):

```text
Request dari Internet / Bot
            │
            ▼
┌───────────────────────────────────────┐
│ Layer 1: Domain Whitelist (Origin)    │ ──► Bukan dari domain terdaftar? (403 FORBIDDEN)
└──────────────────┬────────────────────┘
                   ▼
┌───────────────────────────────────────┐
│ Layer 2: Multi-Tier Rate Limiter      │ ──► Melebihi kuota IP/Visitor? (429 TOO MANY REQUESTS)
└──────────────────┬────────────────────┘
                   ▼
┌───────────────────────────────────────┐
│ Layer 3: Invisible Honeypot Trap      │ ──► Field jebakan terisi oleh bot? (Silent Drop)
└──────────────────┬────────────────────┘
                   ▼
┌───────────────────────────────────────┐
│ Layer 4: Typing Velocity & Time-Delta │ ──► Kirim pesan dalam < 1 detik sejak load? (Flag Bot)
└──────────────────┬────────────────────┘
                   ▼
┌───────────────────────────────────────┐
│ Layer 5: Cloudflare Turnstile (Opt.)  │ ──► Verifikasi CAPTCHA tak terlihat jika IP berisiko
└──────────────────┬────────────────────┘
                   ▼
       Pesan Lolos ke Database MySQL
```

### 6.1 Layer 1: Whitelist Domain Asli (Origin Check)
- Setiap request yang masuk ke API publik wajib menyertakan header `Origin` atau `Referer` yang diverifikasi ke tabel `project_domains`.
- Jika bot mencoba melakukan request langsung via curl/Postman tanpa domain yang sah atau dari domain asing, request ditolak langsung dengan status `403 FORBIDDEN`.

### 6.2 Layer 2: Multi-Tier Rate Limiting
- **Level IP Address**: Maksimal 60 request identify per menit per IP.
- **Level Visitor**: Maksimal 10 pesan per menit per `visitor_id` (manusia normal mengetik 1-3 pesan per menit).
- **Auto-Ban Sementara**: Jika sebuah IP memicu error 429 lebih dari 5 kali berturut-turut, sistem otomatis memblokir IP tersebut selama 30 menit.

### 6.3 Layer 3: Invisible Honeypot Field (Jebakan Bot)
- Di dalam form composer widget, disisipkan input tersembunyi yang tidak terlihat oleh mata manusia:
  ```html
  <input type="text" name="_hp_website_trap" style="display:none !important;" tabindex="-1" autocomplete="off">
  ```
- **Prinsip Kerja**: Bot otomatis (crawler/spambot) akan memindai semua input form dan mengisinya dengan link spam. Manusia normal tidak akan pernah melihat atau mengisi input ini.
- **Tindakan**: Jika field `_hp_website_trap` berisi teks apapun, server **secara diam-diam membuang pesan (silent drop)** atau mengembalikan respon sukses palsu agar bot tidak mencoba cara lain.

### 6.4 Layer 4: Time-Velocity Check (Anti-Instant Flood)
- Bot biasanya mengirim pesan hanya dalam waktu milidetik (0.1 detik) setelah script dimuat.
- Manusia normal membutuhkan waktu setidaknya **2 hingga 5 detik** untuk membaca greeting dan mengetik kalimat.
- Server memeriksa selisih waktu (`timestamp_message - visitor_first_seen`). Jika pesan pertama dikirim dalam `< 1.5 detik`, pesan ditolak karena indikasi bot otomatis.

### 6.5 Layer 5: Cloudflare Turnstile (Opsional Tanpa Puzzle)
- Jika suatu IP terindikasi mencurigakan (skor fraud tinggi), sistem dapat mengaktifkan **Cloudflare Turnstile** (gratis, tanpa puzzle gambar lampu merah, 100% transparan di latar belakang).
