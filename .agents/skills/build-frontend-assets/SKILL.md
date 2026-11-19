---
name: build-frontend-assets
description: Membangun ulang aset frontend lokal BeanTalk (Tailwind CSS terkompilasi, font self-hosted) tanpa CDN. Pakai setiap menambah/mengubah class Tailwind di Blade/JS/PHP atau mengubah font.
---

# Build Aset Frontend (tanpa CDN)

Node hanya di mesin dev; hasil build **di-commit** (server shared hosting tidak menjalankan Node).

```bash
npm install                 # sekali: tailwindcss@3, @fontsource/*
npm run build:css           # Blade/JS/PHP -> public/css/tailwind.css (minified)
npm run watch:css           # saat mengembangkan UI
npm run build:fonts         # hanya bila daftar font/weight di scripts/build-fonts.mjs berubah
npm run build               # build:css + build:sdk (widget)
```

Checklist:
1. Class Tailwind ditulis lengkap (scanner tidak memahami `'bg-' + x`). Kelas dari PHP (`app/**`) juga dipindai.
2. Setelah build: buka halaman terkait di browser, pastikan styling sama, konsol bersih.
3. `php artisan test --filter=test_pages_load_no_third_party_assets` — tidak boleh ada `<script>/<link>` ke host luar.
4. Commit `public/css/tailwind.css` / `public/fonts` bersama perubahan view.
