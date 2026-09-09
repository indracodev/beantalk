# SDK: Architecture & Bundling Strategy

Package Universal JavaScript SDK berlokasi di `packages/chat-sdk/`. SDK dirancang dengan prinsip **Zero External Dependencies**, modular, dan dapat menghasilkan bundle terkompilasi tunggal yang sangat ringan.

---

## 1. Directory Layout

```text
packages/chat-sdk/
├── package.json
├── tsconfig.json
├── vite.config.ts              # Konfigurasi build Rollup / Vite
├── src/
│   ├── index.ts                # Entrypoint utama (mengaitkan window.Chat)
│   ├── core/
│   │   ├── config.ts           # Parser konfigurasi & validasi opsi
│   │   ├── state.ts            # State store (active conv, messages, visitor)
│   │   ├── storage.ts          # Abstraksi localStorage visitor
│   │   └── emitter.ts          # Pub/sub event emitter ringan (<30 baris)
│   ├── transport/
│   │   ├── transport.interface.ts
│   │   ├── polling-transport.ts # Adaptive HTTP polling engine
│   │   └── sse-transport.ts     # Optional EventSource transport
│   ├── ui/
│   │   ├── shadow-widget.ts    # Shadow DOM root & cycle container
│   │   ├── html-template.ts    # String HTML template widget
│   │   └── styles.css          # Scoped CSS di-embed ke Shadow DOM
│   └── utils/
│       ├── uuid.ts             # crypto.randomUUID generator
│       └── net.ts              # Fetch HTTP helper dengan auto-timeout
└── dist/
    ├── chat-sdk.js             # Headless core SDK (~8 KB minified)
    ├── chat-widget.js          # Full bundle: SDK + Shadow DOM Widget UI (~19 KB min)
    └── chat-widget.min.js      # Gzip target < 12 KB
```

---

## 2. Bundling Specifications

- **Format Distribusi**: IIFE (Immediately Invoked Function Expression) & UMD untuk kompatibilitas tag `<script>` langsung di browser lama maupun modern.
- **Target Browser**: ES2020+ (Mendukung 98%+ browser aktif: Chrome, Safari, Edge, Firefox, iOS Webkit).
- **Zero Polyfill Bloat**: Menggunakan native browser APIs:
  - `window.fetch()` untuk HTTP
  - `window.crypto.randomUUID()` untuk ID generation
  - `Element.attachShadow({ mode: 'open' })` untuk CSS encapsulation
  - `document.visibilityState` untuk adaptive polling
  - `window.localStorage` untuk persistensi sesi visitor

---

## 3. Build & CDN Distribution Output

Script build menghasilkan file di `dist/` yang otomatis di-copy ke folder Laravel `public/vendor/chat/`:

```bash
# Di dalam packages/chat-sdk
npm run build
```

Hasil build dapat langsung diakses publik:
- `https://chat.domain.com/widget.js` (alias ke versi stabil terbaru)
- `https://chat.domain.com/v1/widget.js` (versi terpinjang)

---

## 4. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Menolak UI Framework (React/Svelte/Preact) di SDK?**
>
> Memasukkan runtime UI framework ke dalam script embed pihak ketiga meningkatkan ukuran file script hingga 3x–10x lipat dan berisiko konflik globals.
>
> *Keputusan: Pure TypeScript + Shadow DOM template string.*  
> *Hasil: Bundle total di bawah 20 KB, waktu load di browser < 30ms, dan 0 dependensi npm yang bisa kadaluarsa.*
