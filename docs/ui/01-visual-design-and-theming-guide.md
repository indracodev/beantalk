# Panduan Visual & Desain UI: Anti-AI Slop Standards

Dokumen ini mendefinisikan filosofi visual, palet warna, tipografi, dan aturan estetika untuk **Admin Dashboard** dan **Chat Widget**. Sistem ini secara tegas menolak tampilan "generik buatan AI" (*AI-generated slop*) demi menghadirkan antarmuka kelas dunia yang setara dengan **Linear, Vercel, Stripe, dan Intercom**.

---

## 1. Menolak "AI Slop" (Apa yang DILARANG Keras)

Banyak antarmuka yang dibuat oleh AI terlihat murahan dan norak karena menggunakan pola klise berikut. Sistem ini **MELARANG KERAS**:

| Elemen Klise AI Slop | Mengapa Ditolak | Standar Pengganti Kita |
|---|---|---|
| **Gradien Neon Ungu/Cyan** | Terlihat seperti template kripto/web3 murah | Palet Netral Premium (Zinc/Slate) + 1 Solid Brand Accent |
| **Glassmorphism Berlebihan** | Teks sulit dibaca, membebani GPU render browser | Permukaan Solid Bersih (*Crisp Surface*) + Hairline 1px Border |
| **Sudut Puffy (Border Radius 30px+)** | Memboroskan ruang layar, terlihat seperti mainan | Radius Presisi (6px untuk tombol, 14px untuk bubble, 16px untuk window) |
| **Card Overload & Wasted Space** | Densitas informasi rendah, operator harus banyak scroll | High Information Density (Layout kompak 3-kolom seperti Linear) |
| **Animasi Bouncing Berlebihan** | Memperlambat alur kerja agen CS | Transisi Mikro Halus (150ms - 200ms `cubic-bezier(0.16, 1, 0.3, 1)`) |

---

## 2. Tema Admin Dashboard: "Modern High-Density SaaS"

Admin Dashboard mengadopsi bahasa desain **Linear & Cloudflare Dashboard**:
- **Tujuan**: Membantu agen CS bekerja secepat kilat dengan densitas informasi tinggi, tipografi tajam, dan kontras yang nyaman untuk mata selama 8 jam kerja sehari.

```text
┌────────────────────────────────────────────────────────────────────────┐
│ Palet Warna Admin Dashboard (Zinc Neutral System)                      │
├──────────────────┬─────────────────┬───────────────────────────────────┤
│ Token            │ Hex Code        │ Peran Penggunaan                  │
├──────────────────┼─────────────────┼───────────────────────────────────┤
│ --admin-bg       │ #FAFAFA         │ Latar belakang canvas utama       │
│ --admin-surface  │ #FFFFFF         │ Permukaan panel & kartu           │
│ --admin-sidebar  │ #F4F4F5         │ Sidebar panel 1 (Conversation)    │
│ --admin-border   │ #E4E4E7         │ Garis pembatas tipis 1px hairline │
│ --admin-text-1   │ #09090B         │ Teks judul & nama (High Contrast) │
│ --admin-text-2   │ #52525B         │ Teks preview pesan (Muted Slate)  │
│ --admin-text-3   │ #A1A1AA         │ Timestamp & label sekunder        │
│ --admin-accent   │ #0F172A         │ Tombol aksi utama & state aktif   │
│ --admin-badge-bg │ #F1F5F9         │ Latar badge proyek                │
└──────────────────┴─────────────────┴───────────────────────────────────┘
```

### Karakteristik Visual Admin:
1. **Hairline 1px Borders**: Tidak menggunakan bayangan tebal; pemisahan antar kolom 1, 2, dan 3 menggunakan border 1px solid `#E4E4E7`.
2. **Tabular Numbers (`tnum`)**: Waktu (misal: `14:32`, `2m`) dan angka unread menggunakan font tabular angka monospaced agar tidak bergeser saat data ter-update.
3. **Keyboard Shortcut Badges**: Tombol aksi menampilkan hint keyboard yang rapi (misal: `⌘ + Enter` atau `Enter ↵`).
4. **Collapsible Left Sidebar (OmniChat Style)**:
   - **Expanded (220px)**: Menampilkan brand icon gradient, judul aplikasi, navigasi lengkap (`Inbox`, `Integrations`, `Team`, `Archive`, `Tags`, `Analytics`), menu `Settings`, serta profil agen CS (`John Doe` - Online).
   - **Minimized (68px)**: Menyusut mulus via `transition: width 0.22s` menjadi icon-only rail terpusat dengan tooltip hover, memaksimalkan area kerja percakapan.
   - **Active State**: Menggunakan rounded pill (`border-radius: 8px`) dengan subtle tint `#EEF2FF` dan warna aksen ikon/teks `#4F46E5`.


---

## 3. Tema Chat Widget: "Refined Crisp & Intercom Style"

Widget yang disematkan ke website host (Shopify, WordPress, toko online) menggunakan estetika **minimalis elegan**:
- Tidak mencuri perhatian berlebihan di website toko, namun terlihat sangat mewah dan menyatu ketika diklik oleh pembeli.

```text
┌────────────────────────────────────────────────────────────────────────┐
│ Struktur Visual Widget (Shadow DOM)                                    │
├──────────────────┬─────────────────┬───────────────────────────────────┤
│ Komponen         │ Visual Specs    │ Detail Penerapan                  │
├──────────────────┼─────────────────┼───────────────────────────────────┤
│ Floating Trigger │ 56px × 56px     │ Bulat sempurna, bayangan lembut   │
│                  │                 │ `box-shadow: 0 4px 14px rgba(0,0,0,0.14)`│
│ Jendela Chat     │ 380px × 580px   │ Radius 16px, background putih bersih│
│ Header Chat      │ 64px height     │ Latar putih, border bawah 1px,    │
│                  │                 │ avatar 36px, nama + status dot 🟢 │
│ Visitor Bubble   │ Solid Brand Col │ Rata kanan, teks kontras (putih), │
│                  │                 │ radius 14px (sudut bawah kanan 4px)│
│ Agent Bubble     │ #F4F4F5 (Zinc)  │ Rata kiri, teks gelap #09090B,    │
│                  │                 │ radius 14px (sudut bawah kiri 4px)│
│ Composer Bar     │ Subdued Gray    │ Textarea rounded dengan tombol ▲  │
│                  │ #F9FAFB         │ yang berubah aktif saat ada teks  │
└──────────────────┴─────────────────┴───────────────────────────────────┘
```

---

## 4. Tipografi: System Native Typography

Untuk menjamin waktu loading **0.00 detik tanpa jeda download font**, kita tidak mengunduh font eksternal Google Fonts (seperti Roboto atau Poppins berbobot 100KB) ke dalam widget.

Kita menggunakan **Native System Font Stack**:
```css
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
```
- Di perangkat Apple (iPhone/Mac): Tampil dengan font **SF Pro** yang sangat presisi.
- Di perangkat Windows: Tampil dengan font **Segoe UI**.
- Di perangkat Android: Tampil dengan font **Roboto**.
- **Hasil**: Widget terasa seperti aplikasi natif bawaan sistem operasi smartphone/laptop pengguna.

---

## 5. Micro-Interactions & Rasa Halus (Feel & Feedback)

Antarmuka yang premium dibedakan oleh **respon sentuhan yang responsif**, bukan animasi heboh:

1. **Tombol Launcher Buka/Tutup**:
   - Ikon chat bertransformasi halus menjadi tanda silang `✕` SVG (`transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1)`).
2. **Pesan Masuk**:
   - Slide naik halus setinggi 4px dengan opacity dari 0 ke 1 dalam waktu 150ms.
3. **Indikator Mengetik (Typing Indicator)**:
   - 3 titik abu-abu halus yang berdenyut pelan bergantian (bukan kedipan kasar).
4. **Active State Ring**:
   - Saat textarea fokus, border berubah tegas dengan subtle ring 2px tanpa merusak posisi layout.

---

## 6. Standar Ikonografi: Pure Vector SVG (DILARANG Emoji Sistem)

Sesuai standar Staff Engineer dan instruksi langsung, **seluruh antarmuka (Admin Dashboard dan Chat Widget) DILARANG menggunakan Emoji Sistem OS (seperti 📥, 🔌, 👥, 📁, 🏷️, 📊, ⚙️, 💬, 🔍, 📎, 🛒, 📸)**:

| Masalah Emoji Sistem | Standar Pengganti: Pure Vector SVG |
|---|---|
| Tampilan berbeda-beda di Windows, macOS, iOS, dan Android (inkonsisten). | Tampilan 100% identik dan tajam (*pixel-perfect*) di semua browser dan resolusi layar. |
| Terkesan amatir dan tidak memenuhi standar estetika enterprise B2B/D2C. | Estetika profesional setara Linear/Lucide/Heroicons line-art. |
| Ukuran font dan baseline alignment sering melompat antar browser. | Geometri terkontrol via `viewBox="0 0 24 24"` dan `stroke="currentColor"`. |

### Spesifikasi SVG Icon:
- **Ukuran Standar**: `16px × 16px` (kompak/header), `18px × 18px` (navigasi sidebar), `24px × 24px` (launcher/hero).
- **Stroke**: `1.8px` hingga `2.2px`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Warna**: Mewarisi `currentColor` untuk mengikuti tema teks atau CSS variable token.

---

## 7. Arsitektur Navigasi Widget 2-Tahap: Halaman Awal ➔ Chat Utama

Untuk menciptakan *first impression* yang hangat dan terpercaya bagi pengunjung toko, Chat Widget menerapkan **2-Stage Navigation Flow**:

```text
┌────────────────────────────────────────────────────────┐
│ STAGE 1: HALAMAN AWAL (Welcome Hub)                   │
├────────────────────────────────────────────────────────┤
│ [Top Gradient Header]                                  │
│   "Hallo!"                                             │
│   "Apakah ada yang bisa kami bantu? Tanyakan..."  [✕]  │
│                                                        │
│ [Card 1: Your Conversation]                            │
│   ● Live Chat Available                                │
│   [Avatar] INDRACO Store / Supresso Support       [ > ]│
│   "Halo! Terima kasih telah menghubungi..."            │
│   (Klik kartu ➔ Masuk ke Stage 2: Chat Utama)          │
│                                                        │
│ [Card 2: Reach Us Anywhere Else (Social Fallback)]     │
│   (WA) WhatsApp  •  (FB) Messenger  •  (IG) Instagram │
│                                                        │
│ [Footer] [Logo] Powered by chat-me                     │
└──────────────────────────┬─────────────────────────────┘
                           │ Klik Card 1
                           ▼
┌────────────────────────────────────────────────────────┐
│ STAGE 2: CHAT UTAMA (Active Conversation Thread)       │
├────────────────────────────────────────────────────────┤
│ [Header] [← Kembali ke Stage 1] [Avatar] Support   [✕] │
│ [Page Context Card] Supresso Sumatra Mandheling        │
│ [Message Feed] Bubble Agen & Visitor                   │
│ [Composer] [📎 Attach] [Input Pesan...] [▲ Kirim]     │
└────────────────────────────────────────────────────────┘
```

1. **Stage 1 (Halaman Awal / Welcome Hub)**:
   - **Brand Hero Header**: Gradien halus dengan sapaan ramah dan tombol tutup `✕`.
   - **Card 1 (Your Conversation)**: Menampilkan badge denyut hijau (`Live Chat Available`), nama brand/toko aktif, preview potongan pesan terakhir, dan ikon chevron panah `>` yang mengarahkan pembeli ke ruang obrolan utama saat diklik.
   - **Card 2 (Reach Us Anywhere Else)**: Fallback direct channel (WhatsApp resmi `#25D366`, Facebook Messenger `#1877F2`, dan Instagram DM) untuk pelanggan yang ingin melanjutkan percakapan di aplikasi pesan favorit mereka jika meninggalkan website.
   - **Subtle Branding**: Footer netral `Powered by chat-me` dengan ikon SVG terpusat.
2. **Stage 2 (Chat Utama / Conversation Thread)**:
   - **Header Navigasi**: Dilengkapi tombol Back (`←` SVG) di kiri atas untuk kembali ke Halaman Awal secara instan tanpa memutus koneksi obrolan atau menghapus pesan.
   - **Konteks Halaman**: Auto-deteksi produk yang sedang dilihat pembeli ditampilkan di bagian atas thread.
   - **Composer**: Dilengkapi upload kompresi foto lokal canvas dan tombol kirim SVG paperplane.
