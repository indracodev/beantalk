# Architecture: Multi-Tenancy & Data Isolation

## 1. Multi-Tenant Model

Sistem mengadopsi model **Single Database, Column-Based Tenant Isolation** (`tenant_id` + `project_id`).

```text
Tenant (Perusahaan / Akun Pelanggan SaaS)
 ├── Users (Owner, Admin, Agent)
 ├── Contacts (Database pelanggan teridentifikasi)
 └── Projects (Situs / Channel terpisah)
      ├── Domains (Allowed domains whitelist)
      ├── API Keys (pk_live_xxxxx)
      ├── Widget Settings (Warna, teks, greeting)
      ├── Visitors (Anonymous browser cookies)
      └── Conversations
           ├── Messages
           └── Attachments
```

### Keuntungan untuk Shared Hosting:
- Satu koneksi database MySQL tunggal.
- Tidak ada overhead koneksi dinamis switching DB di setiap HTTP request.
- Migrasi database hanya dijalankan satu kali untuk seluruh server.

---

## 2. Global Scope & Data Isolation

Semua model yang dimiliki tenant (seperti `Project`, `Conversation`, `Message`, `Contact`, `Visitor`) mengimplementasikan Trait `BelongsToTenant`.

```php
namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->tenant_id && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

---

## 3. Public API Scoping (`Project` Resolution)

Pada endpoint public visitor (yang tidak memiliki sesi auth pengguna Laravel):
1. Client mengirimkan header `X-Project-Key: pk_live_xxxx`.
2. Middleware `ResolveProjectFromKey` melakukan:
   - Validasi keberadaan `api_keys.public_key`.
   - Mengambil relasi `project` dan `tenant`.
   - Melakukan cross-check origin domain dari header `Origin` / `Referer` dengan tabel `project_domains`.
   - Menginjeksikan instance `$project` dan `$tenant` ke dalam request pipeline.
3. Seluruh pembuatan entitas (`visitors`, `conversations`, `messages`) otomatis mengikat `tenant_id = $project->tenant_id` dan `project_id = $project->id`.

---

## 4. Authorization Matrix (RBAC)

| Resource / Aksi | Owner | Admin | Agent |
|---|:---:|:---:|:---:|
| Baca/Balas Chat Inbox | ✅ | ✅ | ✅ |
| Assign Conversation | ✅ | ✅ | ✅ |
| Tutup/Buka Conversation | ✅ | ✅ | ✅ |
| Kelola Widget Settings | ✅ | ✅ | ❌ |
| Kelola Domain & API Keys | ✅ | ✅ | ❌ |
| Tambah/Hapus Agent | ✅ | ✅ | ❌ |
| Billing & Tenant Settings | ✅ | ❌ | ❌ |

---

## 5. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Bukan Multi-Database (DB per Tenant)?**
>
> Multi-database per tenant memerlukan ratusan database schema di MySQL shared hosting, manajemen migration queue yang rumit, dan konsumsi memori tinggi di pool PDO PHP-FPM.
>
> *Keputusan: Single Database + Global Scope `tenant_id` + Indexed Composite.*  
> *Pencegahan Bocor: Validasi ketat di Eloquent Scope & Route Model Binding.*
