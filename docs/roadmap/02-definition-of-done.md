# Roadmap: Definition of Done (DoD) & Verification Checklist

Sebuah fitur atau fase dianggap selesai (**Done**) hanya jika memenuhi kriteria pengujian dan verifikasi di bawah ini.

---

## 1. Core End-to-End Acceptance Criteria

### Skenario 1: Integrasi Website Standar
- [ ] Pengguna menambahkan `<script src=".../widget.js" data-key="pk_..."></script>` ke file HTML.
- [ ] Tombol widget floating muncul di sudut kanan-bawah layar.
- [ ] Klik tombol membuka jendela chat dengan pesan sambutan (*greeting*).
- [ ] Pengunjung mengetik pesan dan menekan `Enter`.
- [ ] Pesan tersimpan di database MySQL server dengan ID sekuensial.
- [ ] Percakapan baru muncul seketika di Inbox Admin Dashboard tanpa reload halaman.
- [ ] Agen mengetik balasan di Dashboard Admin dan menekan kirim.
- [ ] Balasan agen diterima oleh pengunjung di website dalam waktu < 2 detik via Polling.

### Skenario 2: Integrasi Shopify
- [ ] Script disematkan pada tema Shopify (`theme.liquid`).
- [ ] Widget mendeteksi objek `window.Shopify` secara otomatis tanpa error di console.
- [ ] Ketika pengunjung mengirim chat dari halaman produk, Admin Dashboard menampilkan nama produk dan URL produk di panel *Customer Context*.
- [ ] Pesan balasan dari agen berhasil diterima kembali di toko Shopify.

### Skenario 3: Shared Hosting Mode
- [ ] Aplikasi berjalan 100% sempurna tanpa background service Node.js, Supervisor, Redis, atau WebSocket daemon.
- [ ] Koneksi internet terputus memicu status offline di widget tanpa melempar uncaught exception.
- [ ] Koneksi internet pulih langsung memicu instant poll dan menyinkronkan pesan tertunda.

---

## 2. Automated Test Checklist

### 2.1 Backend Tests (`php artisan test`)
- [ ] **Multi-Tenant Isolation**: Request dari tenant A tidak dapat membaca percakapan atau pesan milik tenant B.
- [ ] **Domain Whitelisting**: Request dengan header `Origin` yang tidak terdaftar di `project_domains` ditolak dengan status HTTP 403.
- [ ] **Message Idempotency**: Pengiriman ulang pesan dengan `client_message_id` yang sama menghasilkan pesan identik tanpa duplikasi record di MySQL.
- [ ] **Polling Query**: Endpoint `GET /api/v1/realtime/poll?after_id=X` hanya mengembalikan record dengan `id > X`.
- [ ] **Rate Limiting**: Request melebihi kuota per menit mengembalikan status HTTP 429.
- [ ] **Attachment Validation**: File executable (`.php`, `.sh`, `.exe`) ditolak sekalipun ekstensi diganti nama.

### 2.2 JavaScript SDK Tests
- [ ] Inisialisasi tanpa error saat `localStorage` dinonaktifkan atau dalam private browsing mode.
- [ ] Meng-generate `visitor_uuid` acak jika belum tersedia di storage.
- [ ] Event listener (`Chat.on('message')`) terpanggil tepat 1 kali per pesan baru.
- [ ] Ukuran bundle akhir `chat-widget.min.js` di bawah 25 KB gzipped.

### 2.3 UI & Accessibility Tests
- [ ] Tidak ada scrollbar horizontal pada desktop (1920px), laptop (1366px), tablet (768px), dan mobile (375px).
- [ ] Kontras warna teks memenuhi standar WCAG AA.
- [ ] Navigasi keyboard pada composer (`Tab`, `Enter`, `Shift+Enter`) berfungsi dengan benar.
- [ ] Style CSS website host tidak bocor ke dalam Shadow DOM widget.
