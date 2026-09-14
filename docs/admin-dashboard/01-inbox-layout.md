# Admin Dashboard: Inbox Layout & Realtime Operations

Admin Dashboard adalah pusat kendali bagi operator dan agen untuk melayani pelanggan. Desain mengadopsi estetika modern yang bersih, kompak, dan berdensitas informasi tinggi (mengambil inspirasi terbaik dari Linear, Intercom, dan Crisp).

---

## 1. Master Layout Architecture: Collapsible Sidebar + 3-Column Workspace

Dashboard admin menggunakan arsitektur antarmuka modular dengan **Collapsible Left Sidebar** (gaya modern SaaS OmniChat/Linear) yang dapat di-minimize ke mode *icon-only rail* untuk memaksimalkan area kerja percakapan:

```text
┌──────────────┬────────────────┬─────────────────────────────┬───────────────────────────────┐
│ SIDEBAR      │ KOLOM 1 (22%)  │ KOLOM 2 (48%)               │ KOLOM 3 (30%)                 │
│ (Collapsible)│ Conversation   │ Active Chat Thread          │ Customer Context Panel        │
│ 220px ➔ 68px │ List (320px)   │ (Flex Grow)                 │ Drawer (280px)                │
├──────────────┼────────────────┼─────────────────────────────┼───────────────────────────────┤
│ [Logo] [ ◨ ] │ Inbox   [+Web] │ Header: Jane Doe (Shopify)  │ CUSTOMER DETAILS              │
│              │ 🔍 Search conv. │ Status: Open • Assign: Sarah │ Name: Jane Doe                │
│ 📥 Inbox (3) │ [All 3][Unass] │ ├───────────────────────────┤ Email: jane@example.com       │
│ 🔌 Integr. 4 │ Sort: Latest ▾ │ │ Visitor:                  │ Visitor ID: #vis_c4b8b409     │
│ 👥 Team      │ ────────────── │ │ "Can I get a discount?"   │ First Seen: 12 mins ago       │
│ 📁 Archive   │ • Jane Doe     │ │                           │ Last Active: Just now         │
│ 🏷️ Tags      │   "Kopi.." 2m  │ │ Agent (Sarah):            │                               │
│ 📊 Analytics │ • Mr. Tan      │ │ "Here is a 10% coupon"    │ PAGE CONTEXT AUTO-DETECT      │
│              │   "Sumatra.."  │ ├───────────────────────────┤ Page: /products/sumatra-caps  │
│ ⚙️ Settings  │ • Budi Santoso │ │ [Composer: Tulis pesan..] │ Source: supresso.myshopify.com│
│ [JD] John D. │   "Promo.."    │ │ [ 📎 Foto ]      [ Kirim ]│ Device: Apple iPhone 15       │
└──────────────┴────────────────┴─────────────────────────────┴───────────────────────────────┘
```

### Spesifikasi Menu Samping (Collapsible Left Sidebar):
1. **Mode Expanded (Default: `220px`)**:
   - Header: Brand Icon + Title (`OmniChat` / `chat-me`) + Tombol Minimize (`#sidebar-toggle-btn`).
   - Navigasi:
     - `📥 Inbox`: Membuka inbox 3-kolom dengan badge percakapan aktif.
     - `🔌 Integrations`: Membuka **Integrations Hub** untuk kelola website, custom warna, dan copy snippet embed.
     - `👥 Team`: Penugasan agen CS.
     - `📁 Archive`: Percakapan yang ditutup/diarsipkan.
     - `🏷️ Tags`: Label prioritas dan kategori tiket.
     - `📊 Analytics`: Waktu respon agen dan statistik volume chat.
   - Footer: Menu `⚙️ Settings` & Kartu Profil Pengguna (`John Doe` / `Sarah` dengan status dot hijau *Online*).

2. **Mode Minimized (Collapsed: `68px`)**:
   - Transisi mulus: `transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1)`.
   - Teks label, brand title, badge angka, dan detail profil tersembunyi secara otomatis (`display: none; opacity: 0;`).
   - Ikon menu tetap berada di tengah (*centered*) dengan padding proporsional dan tooltip saat cursor diarahkan (*hover*).
   - Tombol toggle berotasi 180 derajat (`transform: rotate(180deg)`), siap untuk di-expand kembali dengan 1 klik.


---

## 2. Panel Breakdown

### Panel 1: Conversation List (Sidebar Kiri)
- **Filter Tabs**:
  - `Semua`: Seluruh percakapan di project.
  - `Open`: Percakapan yang belum terselesaikan.
  - `Tugas Saya`: Percakapan yang ditugaskan khusus ke agen yang sedang login.
  - `Selesai`: Percakapan yang sudah ditutup.
- **Format Kartu Percakapan (Standar CRM 4-Baris)**:
  - **Baris 1**: **Nama Customer** (bold) + Badge Unread count
  - **Baris 2**: **`● Web Chat · [Nama Toko / Website]`** (channel & sumber context)
  - **Baris 3**: **Preview Pesan Terakhir** (bold saat belum dibaca)
  - **Baris 4**: **`CUS-XXXX` • Waktu Relatif** (misal: `CUS-8F21 • 2 hours`)
- **Customer Identity & Unique Code**:
  - Pelanggan tidak lagi berlabel default `"Pengunjung Web"`.
  - Di awal sesi, widget menanyakan nama panggilan (*"Kami boleh memanggil Anda siapa?"*).
  - Setiap visitor memiliki kode unik sekunder berformat `CUS-XXXX` untuk membedakan pelanggan bernama sama. Fallback jika nama belum diisi adalah `Tamu · CUS-XXXX`.
- **Real-time Live Feed & Notifikasi Audio**:
  - Dashboard secara berkala mem-polling `GET /admin/inbox/feed/updates`. Percakapan baru langsung disisipkan di posisi teratas tanpa reload halaman.
  - Notifikasi suara menggunakan **Web Audio API** (nada harmonik ganda yang jernih) berbunyi otomatis saat ada chat pengunjung baru.
  - Dilengkapi floating toast alert di pojok kanan bawah dan kedipan judul tab browser saat tab berada di latar belakang.
- **Sorting Deterministik**:
  1. Percakapan dengan pesan unread berada di paling atas.
  2. Diurutkan berdasarkan `last_message_at DESC`.

### Panel 2: Active Chat Panel (Area Tengah)
- **Header Percakapan**:
  - Nama visitor & status online/offline
  - Dropdown pengalihan tugas agen (*Assign Agent*)
  - Tombol aksi cepat: *Close Conversation*, *Mark as Pending*
- **Message Feed**:
  - Pesan masuk dan keluar dengan bubble yang rapi dan penanda waktu
  - Indikator agen yang sedang mengetik (*typing indicator*)
  - Preview inline untuk gambar dan link download untuk PDF
- **Composer**:
  - Textarea responsif dengan shortcut `Enter` (kirim) dan `Shift + Enter` (baris baru)
  - Fitur Canned Responses (balasan cepat via shortcut garis miring `/`)
  - Unggah lampiran drag-and-drop

### Panel 3: Customer Context (Sidebar Kanan)
- Informasi teknis dan profil pengguna:
  - **Identitas**: Nama, Email, Telepon, ID internal
  - **Halaman yang Sedang Dilihat**: URL halaman aktif visitor, judul halaman, dan produk Shopify yang sedang dibuka
  - **Metadata Sistem**: Jenis device (Desktop/Mobile/Tablet), Browser, OS, Negara/IP
  - **Data Tidak Tersedia**: Jika data tidak tersedia, sistem secara eksplisit menampilkan `Not available` (dilarang memalsukan data placeholder).

---

## 3. Responsive Breakpoints

```text
Desktop (≥ 1280px)   : 3 Panel Sekaligus (List + Chat + Context)
Tablet (768px - 1279px): 2 Panel (List + Chat). Context dapat dibuka via Drawer Kanan.
Mobile (< 768px)       : 1 Panel Bertingkat:
                         [Daftar Chat] ──Tap──► [Tampilan Chat] ──Info Icon──► [Drawer Context]
```
*Tidak ada scrollbar horizontal pada layout utama.*

---

## 4. Realtime Tanpa Reload Halaman (No Full Page Reload)

Dashboard admin memanfaatkan polling polling periodik `GET /api/v1/admin/realtime/poll?after_event_id=X`:
1. **Pesan Baru Masuk**: Kartu percakapan di Panel 1 otomatis naik ke posisi teratas dan angka badge unread bertambah seketika.
2. **Chat Aktif Sedang Dibuka**: Jika pesan yang masuk adalah untuk percakapan yang sedang aktif di Panel 2, pesan langsung di-append ke daftar chat tanpa berkedip (*no flicker*).
3. **Optimistic UI**: Balasan yang diketik agen langsung muncul di feed chat dengan status "sending", lalu diperbarui menjadi "sent" dalam hitungan milidetik setelah API mengonfirmasi.

---

## 5. Fitur "Login As" (Impersonation untuk Admin / Superadmin)

Superadmin dapat bertindak sebagai staf CS / Agent tanpa memerlukan password mereka untuk tujuan pemantauan, verifikasi masalah, atau simulasi alur penanganan tiket:
- **Lokasi Akses**: Menu **Tim CS** (`/admin/team`) -> Tombol **Login As** pada baris staf yang dituju (`POST /admin/team/{id}/impersonate`).
- **Indikator Sticky Banner**: Saat mode impersonasi aktif, banner amber di bagian atas layar menginformasikan:
  `Mode Login As: Anda sedang bertindak sebagai [Nama Agen] (Role: agent).`
- **Satu Klik Kembali ke Akun Asli**: Tombol **Kembali ke Akun Asli** (`POST /admin/impersonate/leave`) memulihkan sesi Superadmin awal seketika tanpa perlu login ulang.
- **Audit Trail**: Seluruh aktivitas perpindahan akun dicatat ke tabel `activity_logs` (`user.impersonate` dan `user.impersonate_leave`).
