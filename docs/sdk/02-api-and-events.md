# SDK: Public API & Event System

Dokumen ini menjelaskan API publik JavaScript yang diekspos oleh `window.Chat`.

---

## 1. Initialization: `Chat.init(options)`

Memulai lifecycle SDK, memvalidasi kunci publik project, dan menghubungkan visitor ke server chat.

```javascript
Chat.init({
  // WAJIB:
  projectKey: "pk_live_839df4a18c",

  // OPSIONAL:
  apiUrl: "https://chat.mycompany.com/api", // Default otomatis ke domain script
  position: "bottom-right",                 // "bottom-right" | "bottom-left"
  defaultOpen: false,                       // Buka widget otomatis saat load
  greeting: "Hello there!",                 // Override greeting server jika diinginkan
  user: {                                   // Identifikasi instan jika user sudah login
    name: "Alex Smith",
    email: "alex@smith.com"
  }
});
```

---

## 2. Control Methods

### 2.1 Buka / Tutup Widget
```javascript
// Membuka modal chat
Chat.open();

// Menutup modal chat
Chat.close();

// Toggle antara buka dan tutup
Chat.toggle();

// Mengecek status terbuka (return boolean)
if (Chat.isOpen()) {
    console.log("Chat sedang aktif dilihat customer");
}
```

### 2.2 Mengirim Pesan Programatik
```javascript
Chat.sendMessage("Saya butuh bantuan untuk checkout pesanan #12345");
```

### 2.3 Identifikasi Customer (`Chat.identify`)
Dapat dipanggil kapan saja setelah inisialisasi (misal setelah customer login di website host):
```javascript
Chat.identify({
    name: "Alex Smith",
    email: "alex@smith.com",
    custom: {
        membership: "gold",
        orders_count: 14
    }
});
```

### 2.4 Hapus Sesi (`Chat.logout` / `Chat.reset`)
Digunakan saat user logout dari website induk untuk membersihkan visitor UUID dari localStorage:
```javascript
Chat.logout();
```

---

## 3. Event Listener System: `Chat.on(event, callback)`

SDK mengimplementasikan pub/sub typed emitter:

```javascript
// 1. Saat SDK selesai bootstrap dan siap digunakan
Chat.on("ready", (settings) => {
    console.log("Chat ready with settings:", settings);
});

// 2. Saat pesan baru diterima (baik dari visitor maupun agen)
Chat.on("message", (message) => {
    console.log("Pesan diterima:", message.sender_type, message.body);
});

// 3. Saat percakapan baru dibuat
Chat.on("conversation.created", (conversation) => {
    console.log("Percakapan aktif baru ID:", conversation.id);
});

// 4. Saat percakapan ditutup oleh agen
Chat.on("conversation.closed", (data) => {
    console.log("Percakapan telah diselesaikan oleh support");
});

// 5. Perubahan status koneksi ('connected' | 'reconnecting' | 'offline')
Chat.on("status.change", (status) => {
    console.log("Status koneksi chat:", status);
});

// 6. Tangani error jaringan atau autentikasi
Chat.on("error", (err) => {
    console.error("Chat Error:", err.code, err.message, "Retryable:", err.retryable);
});
```

---

## 4. Off Listener: `Chat.off(event, callback)`

Mencopot listener event:
```javascript
const handleMsg = (msg) => console.log(msg);
Chat.on("message", handleMsg);

// Copot listener:
Chat.off("message", handleMsg);
```
