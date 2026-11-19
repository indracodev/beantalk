# 06 — Frontend: Blade, JS, CSS

## Blade
- Halaman = kerangka tipis (`admin/<halaman>.blade.php`) + partial per seksi di `admin/partials/<halaman>/`.
- Markup yang muncul di ≥2 halaman → komponen anonim `resources/views/components/admin/*` (`<x-admin.pagination>`, `<x-admin.list-toolbar>`).
- Variabel yang dipakai beberapa partial didefinisikan di **induk** sebelum `@include` (variabel di partial tidak bocor ke saudaranya).
- Jangan memotong rantai `@if/@elseif/@endif` atau `@foreach` di antara dua file.
- Tidak ada logika query di view; data disiapkan controller/service.

## JavaScript
- Tidak ada JS panjang inline. Kode di `public/js/<area>/NN-nama.js`, dimuat berurutan dengan `<script src="@asset('...')">`.
- Data server → satu objek config inline: `window.BT = {{ \Illuminate\Support\Js::from([...]) }};` lalu file JS membaca `BT.x`.
  Dilarang `{{ }}` / `@json` di dalam file JS atau string JS.
- Inline hanya untuk skrip anti-flicker kecil sebelum render (restore sidebar/loader).
- Script klasik berbagi scope global → nama fungsi unik per area; urutan file = urutan dependensi.
- Vanilla JS + Web API. Tanpa framework/bundler untuk admin. Cek sintaks: `node --check file.js`.

## CSS & aset
- **Tanpa CDN / layanan pihak ketiga.** Semua aset lokal di `public/` (dijaga test `test_pages_load_no_third_party_assets`).
- Tailwind v3 **dikompilasi** → `public/css/tailwind.css` (config `tailwind.config.js`, sumber `resources/css/tailwind.css`).
  Setelah menambah class Tailwind baru di Blade/JS/PHP: `npm run build:css` lalu commit hasilnya. Dev UI: `npm run watch:css`.
  Jangan merakit nama class sepotong (`'bg-' + warna`) — tulis nama lengkap agar terdeteksi scanner.
- Font self-hosted (`public/fonts`, `public/css/fonts.css`) dari `@fontsource` via `npm run build:fonts`.
- Avatar = inisial (CSS), bukan layanan gambar eksternal.
- CSS admin di `public/css/admin/NN-*.css` (urutan = cascade); `tailwind.css` dimuat terakhir.
- Selalu `@asset()` untuk cache-busting (`?v=filemtime`), jangan `?v=time()`.
- Ikon: SVG inline `viewBox="0 0 24 24"`, `currentColor`. Tanpa emoji sistem.
- Aset publik terkompilasi (widget, css, js) **di-commit** (server tanpa Node).

## Widget (Shadow DOM)
- Sumber di `packages/chat-sdk` (TypeScript, tanpa dependensi runtime), build → `public/chat-widget.js` (lihat skill `build-widget-sdk`).
- Bentuk/radius/tata letak tetap; kustomisasi hanya warna utama, teks, kanal, maskot.
