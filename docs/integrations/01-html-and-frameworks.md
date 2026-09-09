# Integrations: HTML, CMS & Modern Frameworks

SDK Universal Customer Chat dirancang agar mudah dipasang pada berbagai jenis platform web modern.

---

## 1. HTML Statis & PHP Native

Cukup tempelkan baris script ini sebelum tag penutup `</body>`:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Toko Online Saya</title>
</head>
<body>
    <h1>Selamat Datang</h1>

    <!-- Universal Customer Chat Widget -->
    <script
        src="https://chat.example.com/widget.js"
        data-key="pk_live_839df4a18c"
        async>
    </script>
</body>
</html>
```

---

## 2. Laravel (Blade Template)

Dapat diintegrasikan langsung ke dalam layout Blade `resources/views/layouts/app.blade.php`:

```blade
    {{-- Di akhir sebelum </body> --}}
    <script
        src="{{ config('services.chat.url', 'https://chat.example.com') }}/widget.js"
        data-key="{{ config('services.chat.key') }}"
        async>
    </script>
</body>
</html>
```

Atau menggunakan Blade Component / Directive kustom jika diinginkan:
```blade
<x-chat-widget :key="config('services.chat.key')" />
```

---

## 3. WordPress (Theme or Plugin)

Ada dua metode instalasi di WordPress:

### Metode A: Melalui `functions.php` Tema Anak (Child Theme)
```php
function enqueue_universal_chat_widget() {
    wp_enqueue_script(
        'universal-chat',
        'https://chat.example.com/widget.js',
        array(),
        '1.0.0',
        array('in_footer' => true, 'strategy' => 'async')
    );

    // Tambahkan data-key attribute ke script tag
    add_filter('script_loader_tag', function($tag, $handle) {
        if ('universal-chat' !== $handle) return $tag;
        return str_replace(' src=', ' data-key="pk_live_839df4a18c" src=', $tag);
    }, 10, 2);
}
add_action('wp_enqueue_scripts', 'enqueue_universal_chat_widget');
```

### Metode B: Melalui Plugin "Insert Headers and Footers"
Cukup copy-paste kode snippet HTML ke bagian **Footer Scripts**.

---

## 4. React / Next.js (App Router & Pages Router)

### 4.1 Next.js (App Router)
Gunakan komponen `next/script` di dalam `app/layout.tsx`:

```tsx
import Script from "next/script";

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>
        {children}
        <Script
          src="https://chat.example.com/widget.js"
          data-key="pk_live_839df4a18c"
          strategy="lazyOnload"
        />
      </body>
    </html>
  );
}
```

### 4.2 React SPA (Vite / CRA)
Pasang di `index.html` atau panggil secara dinamis di `App.tsx`:

```tsx
import { useEffect } from "react";

export function useChatWidget(projectKey: string) {
  useEffect(() => {
    const script = document.createElement("script");
    script.src = "https://chat.example.com/widget.js";
    script.setAttribute("data-key", projectKey);
    script.async = true;
    document.body.appendChild(script);

    return () => {
      document.body.removeChild(script);
    };
  }, [projectKey]);
}
```

---

## 5. Vue.js / Nuxt 3

Di dalam `nuxt.config.ts`:

```typescript
export default defineNuxtConfig({
  app: {
    head: {
      script: [
        {
          src: 'https://chat.example.com/widget.js',
          'data-key': 'pk_live_839df4a18c',
          async: true
        }
      ]
    }
  }
});
```
