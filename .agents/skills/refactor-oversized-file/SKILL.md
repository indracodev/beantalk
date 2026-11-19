---
name: refactor-oversized-file
description: Memecah file BeanTalk yang melewati batas ukuran (PHP >300, Blade >300, JS/CSS >300, test >300 baris) tanpa mengubah perilaku. Pakai saat menemukan atau akan membuat file gemuk.
---

# Memecah File Gemuk

Cari kandidat:
```bash
find app resources/views public/js public/css tests -type f \( -name '*.php' -o -name '*.js' -o -name '*.css' \) \
  -not -name 'chat-widget.js' | xargs wc -l | sort -rn | awk '$1>300'
```

| Jenis | Cara pecah aman |
|---|---|
| Controller | satu controller per domain; logika → `Services/`; query bersama → scope `#[Scope]` |
| Service | per tanggung jawab (contoh `Dashboard/DashboardMetrics` → `VolumeSeries`, `ProjectMetrics`) |
| Model | scope/filter panjang → trait `Models/Concerns/` (contoh `FiltersVisitors`) |
| Blade | potong per elemen utuh (tag seimbang) ke `admin/partials/<halaman>/`; variabel bersama di induk; jangan potong `@if/@foreach` |
| Markup duplikat | komponen `components/admin/*` dengan `@props` |
| JS | potong di batas seksi tingkat atas (bukan di dalam IIFE/closure), muat berurutan via `@asset` |
| CSS | potong di batas seksi, urutan `<link>` = urutan cascade |
| Test | base class abstrak berisi `setUp()` + kelas per fitur |

Verifikasi wajib: `php artisan test --compact`, `php artisan view:cache`, `node --check` tiap JS, smoke halaman di MySQL.
Pengecualian yang diterima: bundle hasil build, data JSON, satu modul IIFE kohesif (mis. `03-audio-engine.js`) — beri catatan.
