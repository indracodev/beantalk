# Widget: Shadow DOM & CSS Encapsulation

Chat widget disematkan ke berbagai jenis website (Shopify, WordPress, custom SPA) yang memiliki framework CSS masing-masing (Bootstrap, Tailwind, styling CSS native). Untuk menjamin widget tidak rusak oleh CSS website host dan tidak merusak layout website host, widget menggunakan **Web Components Shadow DOM**.

---

## 1. Shadow Root Architecture

```text
Document Body (Website Host)
  │
  └── <div id="universal-chat-root">
        │
        └── #shadow-root (open)
              ├── <style> (100% Terisolasi)
              ├── <div class="chat-trigger-btn">
              └── <div class="chat-window">
                    ├── <header class="chat-header">
                    ├── <div class="chat-messages">
                    └── <div class="chat-composer">
```

### Keuntungan Mutlak:
1. **Host CSS Reset**: Selektor CSS website host seperti `* { box-sizing: content-box; font-family: serif; }` tidak akan menembus ke dalam Shadow Root.
2. **Zero Global Bleed**: Style widget seperti `.btn`, `.header`, `.modal` tidak akan bentrok dengan class Bootstrap atau Tailwind milik website host.
3. **Penyusupan Aman**: Widget dapat disematkan ke halaman mana pun cukup dengan `document.body.appendChild(container)`.

---

## 2. Inisialisasi Shadow DOM di TypeScript

```typescript
export function mountChatWidget(containerId = "universal-chat-root"): ShadowRoot {
  let hostEl = document.getElementById(containerId);
  if (!hostEl) {
    hostEl = document.createElement("div");
    hostEl.id = containerId;
    hostEl.style.position = "fixed";
    hostEl.style.zIndex = "2147483647"; // Nilai z-index maksimal browser
    hostEl.style.bottom = "0";
    hostEl.style.right = "0";
    document.body.appendChild(hostEl);
  }

  // Buat Shadow Root mode 'open'
  const shadowRoot = hostEl.attachShadow({ mode: "open" });

  // Injeksi CSS reset dan token styling
  const styleEl = document.createElement("style");
  styleEl.textContent = CHAT_WIDGET_CSS;
  shadowRoot.appendChild(styleEl);

  return shadowRoot;
}
```

---

## 3. Strict CSS Reset di Dalam Shadow DOM

Setiap elemen di dalam Shadow Root diawali dengan reset standar agar tampil konsisten di semua browser:

```css
:host {
  all: initial;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  font-size: 14px;
  line-height: 1.5;
  color: #0f172a;
  box-sizing: border-box;
}

*, *::before, *::after {
  box-sizing: inherit;
  margin: 0;
  padding: 0;
  outline: none;
  -webkit-tap-highlight-color: transparent;
}
```

---

## 4. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Shadow DOM Mengalahkan IFrame?**
>
> Menggunakan IFrame untuk seluruh widget menimbulkan masalah kompleks:
> 1. Kesulitan resize responsif saat floating launcher dibuka menjadi jendela chat penuh di mobile.
> 2. Keterbatasan akses focus input keyboard di beberapa browser mobile.
> 3. Overhead memori browser karena setiap iframe memuat instance document terpisah.
>
> *Keputusan: Shadow DOM. Ringan, responsif instan, CSS tetap terisolasi 100%.*
