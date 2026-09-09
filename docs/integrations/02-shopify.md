# Integrations: Shopify Compatibility

Shopify **bukanlah backend chat terpisah**. Shopify hanyalah salah satu platform host tempat Universal JavaScript SDK dijalankan.

---

## 1. Core Integration Flow

```text
Shopify Online Store (Liquid Theme / App Extension)
               │
               ▼
Universal Chat Library (chat-widget.js)
               │
               ▼
Deteksi Browser & Objek JavaScript Shopify (Window.Shopify)
               │
               ▼ POST /api/v1/identify (dengan konteks produk & toko)
Laravel Core Engine (MySQL)
               │
               ▼
Admin Dashboard (Konteks Shopify muncul di Panel Customer Context)
```

Tidak ada backend khusus Shopify yang perlu dibuat. SDK yang digunakan sama persis 100% dengan SDK untuk WordPress atau website biasa.

---

## 2. Cara Pemasangan di Shopify

### Metode 1: Edit Theme Liquid (`theme.liquid`)
1. Buka Shopify Admin -> **Online Store** -> **Themes**.
2. Klik **Actions** -> **Edit code**.
3. Buka file `layout/theme.liquid`.
4. Masukkan kode berikut tepat sebelum tag `</body>`:

```liquid
<!-- Universal Customer Chat for Shopify -->
<script
  src="https://chat.example.com/widget.js"
  data-key="pk_live_839df4a18c"
  async>
</script>
```

---

## 3. Deteksi Konteks Shopify Tanpa Private API (Client-Side Detection)

Untuk versi MVP, **tidak diperlukan Shopify Private App Token atau OAuth App yang rumit**. SDK JavaScript mendeteksi konteks toko secara langsung di browser pengunjung melalui variabel global bawaan Shopify:

```javascript
// Di dalam core SDK client-side context scanner:
function extractShopifyContext() {
  const isShopify = Boolean(window.Shopify || window.ShopifyAnalytics);
  if (!isShopify) return null;

  const context = {
    source: "shopify",
    shop_domain: window.Shopify?.shop || window.location.hostname,
    currency: window.Shopify?.currency?.active || null,
    page_type: null,
    product_title: null,
    product_id: null
  };

  // Deteksi Halaman Produk
  if (window.location.pathname.includes("/products/")) {
    context.page_type = "product";
    // Baca meta tag atau data JSON Shopify bawaan
    const titleEl = document.querySelector('meta[property="og:title"]');
    context.product_title = titleEl ? titleEl.getAttribute("content") : document.title;
  } else if (window.location.pathname.includes("/collections/")) {
    context.page_type = "collection";
  } else if (window.location.pathname === "/cart") {
    context.page_type = "cart";
  }

  return context;
}
```

Ketika visitor mengirim pesan dari halaman produk Shopify (misal: `/products/nike-air-jordan`), informasi ini otomatis terkirim pada payload `POST /api/v1/identify`. Agen di Laravel Dashboard dapat langsung melihat:
- **Source**: Shopify
- **Current Page**: Nike Air Jordan 1 High
- **URL**: `https://mystore.com/products/nike-air-jordan`

---

## 4. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Menghindari Shopify Private API & OAuth di MVP?**
>
> Membangun Shopify App Bridge, OAuth 2.0 exchange, dan webhook sync order untuk MVP membutuhkan server webhook berkapasitas tinggi, partner account review, dan waktu berminggu-minggu.
>
> *Keputusan: Client-side detection via `window.Shopify` & standard DOM meta properties.*  
> *Hasil: 100% kompatibel dengan seluruh tema Shopify sejak hari pertama tanpa otorisasi API tambahan.*
