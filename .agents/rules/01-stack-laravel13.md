# 01 — Stack & Konvensi Laravel 13

## Versi terkunci
- PHP **^8.3** (`composer.json` → `config.platform.php = 8.3.0`, agar dependensi selalu cocok dengan server PHP 8.3+).
- Laravel **13.x**, PHPUnit **12**, Carbon **3**, MySQL 8 (produksi) / SQLite in-memory (test).
- Hanya paket first-party Laravel. Paket pihak ketiga baru **wajib** dicek dulu: mendukung L13 & PHP 8.3, aktif dirawat, `composer audit` bersih.
  Jika tidak mendukung → cari fitur core atau alternatif yang mendukung; jangan pin versi lama.

## Skeleton L13 (jangan kembalikan pola L7–L10)
| Kebutuhan | Tempat di L13 | ❌ Jangan |
|---|---|---|
| Routing, middleware, exception | `bootstrap/app.php` | `app/Http/Kernel.php`, `app/Exceptions/Handler.php` |
| Alias middleware | `$middleware->alias([...])` | `$routeMiddleware` |
| Provider | `bootstrap/providers.php` (hanya `AppServiceProvider`) | Auth/Event/Route/BroadcastServiceProvider |
| Gate | `Gate::define()` di `AppServiceProvider` | AuthServiceProvider |
| Command | `app/Console/Commands` (auto-discover) / `routes/console.php` | `Console\Kernel` |
| Config | hanya file yang dikustom; sisanya default framework | menyalin semua config |
| CORS / Trusted proxy | core (`config/cors.php` default framework) | fruitcake/cors, fideloper/proxy |

## Gaya kode
- Route pakai sintaks kelas: `[Controller::class, 'method']`, grup `Route::controller()`, `whereNumber('id')`.
- Model: `protected function casts(): array`, relasi bertipe (`: BelongsTo`), accessor `Attribute::get()`, scope `#[Scope]`.
- Password: cast `'hashed'` (jangan `Hash::make` manual saat create/update). Secret: cast `'encrypted'` + `$hidden`.
- Migrasi: anonymous class, `foreignId()->constrained()->cascadeOnDelete()`, index komposit bernama.
- Validasi: FormRequest untuk aturan yang dipakai ulang (`app/Http/Requests`), `Password::defaults()`.
- Promosi properti konstruktor + readonly; `match` ketimbang `switch`; nullsafe `?->`.

## Jebakan migrasi yang sudah terjadi (jangan ulangi)
- **Carbon 3**: `diffInDays/diffInSeconds` mengembalikan **float bertanda**. Selalu hitung dari tanggal lebih awal dan cast `(int)`/`round()`.
- `latestOfMany()` + `where()` di luar subquery bisa mengembalikan `null` → pakai `ofMany(['id' => 'max'], fn ($q) => ...)`.
- `firstOrCreate()` tidak memuat default kolom DB → `refresh()` bila nilai default akan dibaca.
- `@json([...])` multi-baris tidak didukung Blade → `{{ \Illuminate\Support\Js::from([...]) }}`.
- `report()` callback tidak terpanggil untuk `HttpException` → logging status (429/5xx) lewat `$exceptions->respond()`.
