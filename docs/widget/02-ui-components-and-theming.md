# Widget: UI Components, Composer & Theming

Dokumen ini mendefinisikan komponen visual, struktur UI, interaksi keyboard, dan variabel token tema untuk Embeddable Chat Widget.

---

## 1. Widget Wireframe & 2-Stage Navigation Hierarchy

Widget menggunakan sistem navigasi **2-Stage Flow** (Halaman Awal ➔ Chat Utama) untuk memberikan pengalaman penyambutan yang profesional:

```text
┌────────────────────────────────────────────────────────┐
│ STAGE 1: HALAMAN AWAL (Welcome Hub)                   │
├────────────────────────────────────────────────────────┤
│ [Top Curved Gradient Header]                           │
│   "Hallo!"                                             │
│   "Apakah ada yang bisa kami bantu? Tanyakan..."  [✕]  │
│                                                        │
│ [Card 1: Your Conversation] (Floating Card)            │
│   ● Live Chat Available                                │
│   [Avatar] Store Support                          [ > ]│
│   "Halo! Ada yang bisa kami bantu seputar..."          │
│   (Klik kartu ➔ Navigasi ke Stage 2: Chat Utama)       │
│                                                        │
│ [Card 2: Customer Identity] (Kenalan Dulu Yuk!)        │
│   "Kami boleh memanggil Anda siapa?"                   │
│   [Input nama panggilan...] [Simpan]                   │
│                                                        │
│ [Footer] [Logo] Powered by BeanTalk                    │
└──────────────────────────┬─────────────────────────────┘
                           │ Klik Card 1
                           ▼
┌────────────────────────────────────────────────────────┐
│ STAGE 2: CHAT UTAMA (Active Conversation Thread)       │
├────────────────────────────────────────────────────────┤
│ [Header] [← Back] [Avatar] Support • Online        [✕] │
├────────────────────────────────────────────────────────┤
│ [Customer Identity Pill] Halo, Budi! [Ganti]           │
│ [Product Context Card] Auto-detected page context      │
│                                                        │
│  Agent (Sarah)                                         │
│  ┌──────────────────────────────────────────────┐      │
│  │ Halo! Ada yang bisa kami bantu hari ini?     │      │
│  └──────────────────────────────────────────────┘      │
│                                                        │
│                                  Visitor (Budi)        │
│                   ┌─────────────────────────────┐      │
│                   │ Apakah stok produk ini ada? │      │
│                   └─────────────────────────────┘      │
├────────────────────────────────────────────────────────┤
│ [Attach SVG] [ Tulis pesan ke CS...      ] [Kirim SVG] │
└────────────────────────────────────────────────────────┘
```

---

## 2. Core UI Components

### 2.1 Floating Trigger Button
- **Ukuran**: Lingkaran 56px × 56px dengan bayangan lembut (`box-shadow: 0 4px 14px rgba(0,0,0,0.16)`).
- **Posisi**: Default 24px dari sudut bawah-kanan layar (`bottom: 24px; right: 24px`).
- **Badge Unread**: Lingkaran merah berdiameter 19px di sudut kanan atas tombol pemicu jika ada pesan baru yang belum dibaca.
- **Ikon**: Menggunakan pure SVG inline (vektor gelembung chat saat tertutup, tanda silang `✕` saat jendela terbuka). DILARANG menggunakan emoji.

### 2.2 Stage 1: Halaman Awal (Welcome Hub)
- **Curved Hero Header**: Gradien dinamis (`linear-gradient(180deg, var(--chat-primary) 0%, #1F2937 100%)`) dengan radius lengkung bawah 20px, teks sapaan besar "Hallo!", dan deskripsi ramah.
- **Card 1 (Your Conversation)**:
  - Header kartu: Judul "Your Conversation" dengan indikator denyut hijau animasi (`@keyframes livePulse`) bertuliskan "Live Chat Available".
  - Baris percakapan: Avatar brand/tim, nama toko aktif, cuplikan pesan terbaru, dan tombol panah `>` SVG.
  - Interaksi: Mengklik kartu ini akan memicu transisi mulus ke Stage 2 (Chat Utama).
- **Card 2 (Customer Identity — Kenalan Dulu Yuk!)**:
  - Menyediakan form input nama panggilan: *"Kami boleh memanggil Anda siapa?"*.
  - Customer dapat mengisi nama mereka (misal: "Budi") yang akan langsung tersimpan di `localStorage` dan tersinkron ke backend server via `POST /api/v1/client/session/profile`.
  - Jika belum diisi, sistem menggunakan fallback informatif `Tamu · CUS-XXXX` (bebas dari label generik "Pengunjung Web").
- **Branding Footer**: Teks netral `Powered by BeanTalk` dengan logo SVG subtle di bagian bawah.

### 2.3 Stage 2: Chat Header & Navigation
- **Tombol Back (`←` SVG)**: Terletak di kiri header chat untuk kembali ke Halaman Awal tanpa memutuskan koneksi WebSocket/Polling atau mereset state percakapan.
- **Avatar & Status**: Avatar toko 34px dengan indikator status dot hijau `Online`.
- **Tombol Tutup (`✕` SVG)**: Di kanan header untuk menyembunyikan jendela widget.

### 2.4 Message History Area
- **Auto-scroll**: Otomatis menggulir ke bawah saat pesan baru diterima atau dikirim jika scrollbar berada di dekat dasar.
- **Dua Style Gelembung**:
  - **Visitor Bubble**: Rata kanan, berlatar warna `var(--chat-primary)`, teks putih, radius 14px dengan sudut bawah-kanan 3px.
  - **Agent Bubble**: Rata kiri, berlatar warna netral `#F4F4F5`, teks gelap `#09090B`, radius 14px dengan sudut bawah-kiri 3px.
- **Product Context Card**: Otomatis menyematkan link, judul, dan thumbnail produk yang sedang dibuka oleh pembeli.

### 2.5 Composer (Message Input)
- **Attachment Trigger**: Tombol SVG klip kertas (`paperclip`) untuk memilih foto yang otomatis dikompresi di sisi klien via Canvas API (menghemat bandwidth hingga 95%).
- **Textarea Input**: Input teks responsif dengan placeholder profesional.
- **Tombol Kirim**: Tombol bulat dengan ikon SVG pesawat kertas / panah kirim.

---

## 3. Theming: Bentuk Tetap (Fixed Shape), Kustomisasi Hanya Warna Core

Sesuai prinsip desain sistem yang konsisten dan anti-bug:
- **Bentuk, Layout & Dimensi Bersifat Baku (Fixed)**: Seluruh chat widget memiliki bentuk kurva, layout header, bubble pesan, dan composer yang identik di semua website. Tidak ada custom layout template yang memicu layout breaking.
- **Kustomisasi Hanya pada Warna Core (Accent Color)**: Pengaturan tema dari server (`widget_settings.primary_color`) hanya mengubah warna aksen utama yang diturunkan ke elemen kunci.

### Elemen yang Terpengaruh oleh Warna Core:
1. Tombol pemicu mengambang (*Floating Launcher Button*).
2. Latar belakang gelembung pesan pengunjung (*Visitor Message Bubble*).
3. Tombol kirim aktif & indikator interaksi (*Send button & active ring*).
4. Badge notifikasi dan aksen header chat.

```css
:host {
  /* =========================================================
   * 1. DINAMIS: Hanya variabel ini yang diatur per-chat/project
   * ========================================================= */
  --chat-primary: #0F172A;           /* Dari widget_settings.primary_color */
  --chat-primary-text: #FFFFFF;      /* Kontras otomatis (putih/gelap) */

  /* =========================================================
   * 2. STATIS & IDENTIK: Bentuk, sudut, padding & layout sama
   * ========================================================= */
  --chat-bg: #FFFFFF;
  --chat-surface: #F8FAFC;
  --chat-border: #E2E8F0;
  --chat-text-main: #0F172A;
  --chat-text-muted: #64748B;
  --chat-bubble-agent: #F1F5F9;

  /* Geometri Baku (Identik untuk Semua) */
  --chat-radius-window: 16px;
  --chat-radius-bubble: 14px;
  --chat-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
  --chat-width: 380px;
  --chat-height: 580px;
}
```

---

## 4. Responsive Mobile Behavior

Ketika lebar layar perangkat < 640px (perangkat mobile):
1. Jendela chat membuka penuh satu layar (*full screen drawer*: `width: 100vw; height: 100vh; bottom: 0; right: 0; border-radius: 0;`).
2. Header menambahkan tombol panah kembali (`←`) yang ramah sentuhan jari (*touch target* minimal 44px × 44px).
3. Penyesuaian keyboard virtual menggunakan viewport meta agar composer tidak tertutup keyboard HP.
