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

## Palet admin "Roasted Bean" (BeanTalk = biji kopi; tenang untuk CS berjam-jam)
- Warna hanya lewat token `apple-*` (tailwind.config.js) / `var(--apple-*)` (public/css/admin/01-base.css) — keduanya harus sama.
  Jangan menulis hex/hitam mentah (`bg-black`, `#1D1D1F`, `bg-[#1C1C1E]`) di view.
- Token warna **wajib hex solid**, bukan `rgba()`: di Tailwind v3 modifier `/80` mengganti alpha, sehingga
  `rgba(0,0,0,.08)` + `/80` ter-render 80% hitam (bug "terlalu kontras" sebelumnya).
- Aksen utama `apple.blue` = **`#7A5A44` biji kopi sangrai** (hover `#694B37`); skala `blue-*` Tailwind di-override jadi skala kopi
  (50–950) sehingga semua kelas `blue-*` ikut tema. `sky-*` = warna resmi Telegram, `indigo/purple` = sekunder (peran, fitur bot).
- Netral hangat: kanvas `#F5F4F2`, sidebar oat `#EBE7E2`, garis `#E5E1DC`; teks `#322E2A` / `#645D56` / `#847C74` (jangan lebih pucat).
- Grafik SVG & favicon membaca `var(--apple-blue)` — jangan tulis hex aksen di view/JS.
- Sidebar oat `#EBE7E2` (`--apple-sidebar`/`apple.sidebar`); item aktif putih.
- Area konten: latar **ambient** `.ambient-canvas` di `<main>` (cahaya radial mocha/sage/karamel tipis, statis; jangan pakai SVG filter — berat saat render). Wrapper halaman
  dibiarkan transparan (jangan beri `bg-apple-canvas`) agar ambient terlihat; panel kerja (inbox) tetap putih polos.
- Warna brand website tampil sebagai **pita 3 px** (daftar chat & header thread) + tint `14/30`, bukan blok penuh.
- Blok kode/payload: terang (`bg-apple-canvas` + garis), tombol gelap `bg-apple-textPrimary hover:bg-apple-inkHover`,
  overlay modal `bg-apple-textPrimary/25`.
- Swatch pilihan warna widget (tab Tampilan) memakai hex aslinya — itu data, bukan tema admin.

## Widget (Shadow DOM)
- Sumber di `packages/chat-sdk` (TypeScript, tanpa dependensi runtime), build → `public/chat-widget.js` (lihat skill `build-widget-sdk`).
- Bentuk/radius/tata letak tetap; kustomisasi hanya warna utama, teks, kanal, maskot.
