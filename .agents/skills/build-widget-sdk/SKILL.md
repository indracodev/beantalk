---
name: build-widget-sdk
description: Mengubah dan membangun Universal Chat SDK / widget Shadow DOM BeanTalk (packages/chat-sdk, TypeScript tanpa dependensi runtime, output public/chat-widget.js). Pakai saat menyentuh perilaku/tampilan widget embed.
---

# Build Widget SDK

1. Sumber: `packages/chat-sdk/src` (TypeScript, Web API native, Shadow DOM). Tanpa dependensi runtime.
2. Kontrak API widget di `.agents/rules/05-api-contract.md`; perubahan respons harus kompatibel mundur.
3. Build (Node hanya di mesin dev):
   ```bash
   cd packages/chat-sdk && npm install && npm run typecheck
   php artisan build        # = node packages/chat-sdk/build.js → public/chat-widget.js (+ salinan)
   ```
4. Anggaran ukuran: < 25 KB gzip. `pointer-events` hanya aktif di launcher & window terbuka.
5. Uji: `public/demo-store.html` dengan key dev, plus test API (`tests/Feature/ChatApiTest.php`).
6. Commit hasil build di `public/` (server tidak menjalankan Node).
