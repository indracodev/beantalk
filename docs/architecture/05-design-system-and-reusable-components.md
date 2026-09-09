# Desain Sistem & Komponen Reusable (Anti-Hardcode)

Dokumen ini mendefinisikan arsitektur **Design System** dan daftar **Reusable Components** di sisi Frontend (Admin Dashboard Blade & Shadow DOM Widget) maupun Backend (Laravel Services, Enums, dan Traits) untuk memastikan **nol kode hardcode (zero hardcoding)** dan kepatuhan standar *Staff Engineer*.

---

## 1. Filosofi Design System: Token-Driven

Seluruh visual sistem (warna, tipografi, radius sudut, bayangan, dan jarak spasi) dikendalikan oleh **Design Tokens**. Pengembang dilarang keras menulis nilai warna (*hex code*) atau padding secara acak (ad-hoc) di dalam kode template.

### 1.1 Global Design Tokens (CSS Variables)
```css
:root {
  /* Skala Warna Netral (Surface & Text) */
  --color-surface-base: #FFFFFF;
  --color-surface-subtle: #F8FAFC;
  --color-surface-muted: #F1F5F9;
  --color-border-subtle: #E2E8F0;
  --color-border-strong: #CBD5E1;
  --color-text-main: #0F172A;
  --color-text-muted: #64748B;

  /* Warna Semantik / Status */
  --color-success: #10B981;
  --color-warning: #F59E0B;
  --color-danger: #EF4444;
  --color-info: #3B82F6;

  /* Geometri & Radius */
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 16px;
  --radius-full: 9999px;

  /* Bayangan Halus (Restrained Elevation) */
  --shadow-card: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
  --shadow-floating: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
}
```

---

## 2. Reusable UI Components: Admin Dashboard (Laravel Blade)

Semua elemen visual di Admin Dashboard dipecah menjadi komponen Blade murni (`resources/views/components/`) sehingga tampilan 100% konsisten dan bebas dari duplikasi kode HTML.

```text
resources/views/components/
├── ui/
│   ├── button.blade.php             # Tombol seragam (Primary, Secondary, Ghost, Danger)
│   ├── badge.blade.php              # Label status & Project Tag ([supresso], [indracostore])
│   ├── avatar.blade.php             # Avatar inisial / gambar dengan status online dot
│   ├── input.blade.php              # Textfield & Textarea konsisten
│   └── empty-state.blade.php        # Tampilan kosong saat belum ada chat
└── chat/
    ├── conversation-card.blade.php  # Kartu item percakapan di daftar inbox
    ├── message-bubble.blade.php     # Gelembung pesan (Visitor vs Agent)
    ├── customer-context-item.blade.php # Baris data konteks (otomatis "Not available" jika kosong)
    └── composer.blade.php           # Input balasan pesan dengan tombol aksi
```

### 2.1 Contoh Penggunaan Komponen Reusable Blade:

#### Komponen Badge Proyek & Status:
```blade
{{-- Bebas hardcode: Cukup panggil komponen badge dengan varian --}}
<x-ui.badge variant="project" :label="$conversation->project->name" />
<x-ui.badge :variant="$conversation->status" :label="strtoupper($conversation->status)" />
```

#### Komponen Baris Konteks Pengunjung (`customer-context-item`):
Mencegah data palsu dan string kosong yang berantakan:
```blade
{{-- Jika data null, komponen otomatis menampilkan "Not available" dengan warna muted --}}
<x-chat.customer-context-item label="Device" :value="$conversation->visitor->device" />
<x-chat.customer-context-item label="Current Product" :value="$conversation->visitor->current_page_title" />
<x-chat.customer-context-item label="Referrer" :value="$conversation->visitor->referrer" />
```

---

## 3. Reusable UI Components: Chat Widget (Shadow DOM)

Di dalam `packages/chat-sdk/src/ui/`, antarmuka widget dipecah menjadi kelas modular dengan antarmuka seragam (`render()`, `update()`, `destroy()`):

```text
packages/chat-sdk/src/ui/
├── components/
│   ├── launcher.ts        # Tombol bulat mengambang + badge unread counter
│   ├── header.ts          # Bar judul, status kehadiran (dot hijau), dan tombol close
│   ├── message-list.ts    # Container scroll feed pesan dengan auto-scroll bottom
│   ├── message-item.ts    # Renderer gelembung pesan individual + status tick
│   └── composer.ts        # Textarea responsif + tombol kirim + tombol lampiran
└── widget-view.ts         # Orkestrator yang menggabungkan seluruh komponen di atas
```

**Anti-Hardcode di Widget**:
- Label teks seperti *"Type a message..."*, *"We are offline"*, dan nama agen diambil secara dinamis dari API server (`widget_settings`).
- Warna tombol dan bubble diambil dari token CSS variabel `--chat-primary` yang diinjeksikan dinamis.

---

## 4. Backend Architecture: Anti-Hardcode (Zero Magic Strings)

Kode backend Laravel dilarang keras menggunakan *magic strings* atau logika bisnis yang tercecer di Controller.

### 4.1 PHP Enums / Constants untuk Status & Tipe
Semua status dikunci di dalam class Enum resmi:

```php
namespace App\Enums;

enum ConversationStatus: string
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case CLOSED = 'closed';
}

enum MessageSenderType: string
{
    case VISITOR = 'visitor';
    case AGENT = 'agent';
    case SYSTEM = 'system';
}

enum MessageType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case FILE = 'file';
    case SYSTEM = 'system';
}
```
*Manfaat*: Jika ada salah ketik status, compiler/linter PHP langsung mendeteksi error sebelum kode dijalankan.

---

### 4.2 Standard API Response Trait (`ApiResponse`)
Semua Controller menggunakan trait tunggal untuk membentuk response JSON envelope:

```php
namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function respondSuccess($data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
        ], $status);
    }

    public function respondError(string $code, string $message, $details = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }
}
```
*Manfaat*: Tidak ada controller yang membuat array JSON manual `{ 'status' => 'ok' }` sendiri-sendiri. Seluruh 20+ endpoint API menghasilkan struktur yang 100% identik.

---

### 4.3 Reusable Multi-Tenant Trait (`BelongsToTenant`)
Isolasi tenant dikendalikan oleh 1 file Trait tunggal:
- Otomatis menambahkan query `WHERE tenant_id = ?` ke seluruh query Eloquent (`Model::all()`, `Model::find()`).
- Otomatis mengisi kolom `tenant_id` saat `Model::create()` dipanggil.
- **Nol kemungkinan lupa menambahkan `tenant_id` di controller**.

---

## 5. Matriks Ringkasan Komponen Reusable

| Lapisan Sistem | Masalah Jika Di-Hardcode | Solusi Komponen Reusable Kita | Lokasi Kode |
|---|---|---|---|
| **Styling Widget** | Warna tombol di-hardcode warna biru | CSS Custom Property `--chat-primary` dari database | `packages/chat-sdk/src/ui/styles.css` |
| **Widget UI** | HTML ditulis panjang ratusan baris | Komponen modular `Launcher`, `Header`, `Composer` | `packages/chat-sdk/src/ui/components/` |
| **Admin UI** | Tag HTML kartu chat di-copy-paste | Blade Component `<x-chat.conversation-card>` | `resources/views/components/chat/` |
| **Konteks Data** | Tulisan "Tidak ada data" berbeda-beda | Blade Component `<x-chat.customer-context-item>` | `resources/views/components/chat/` |
| **Status Percakapan**| Teks string acak `'selesai'`, `'closed'` | Native Enum `ConversationStatus` | `app/Enums/ConversationStatus.php` |
| **API Envelope** | Format JSON sukses/error beda-beda | Trait `ApiResponse` (`respondSuccess`, `respondError`) | `app/Http/Controllers/Concerns/` |
| **Tenant Scoping** | Query manual `where('tenant_id', ...)` | Global Scope Trait `BelongsToTenant` | `app/Models/Concerns/BelongsToTenant.php`|
