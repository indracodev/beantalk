# Modul Status Tiket Percakapan & Penugasan (CS Agent)

## 1. Deskripsi Fitur
Modul Status Tiket & Penugasan mengatur siklus hidup (*lifecycle*) tiket obrolan pelanggan dari saat pertama kali masuk hingga dinyatakan tuntas diselesaikan. Agen CS dapat memperbarui status tiket secara berkala untuk menjaga keteraturan antrean kerja (*Inbox Zero*) dan mendelegasikan tiket ke rekan tim lain yang lebih kompeten (misal: eskalasi teknis atau kendala akuntansi pengembalian dana).

---

## 2. Hak Akses & Role Permission
- **Role:** `agent` (Customer Support Agent)
- **Middleware:** `web`, `auth`
- **Tanggung Jawab:** Menjaga agar tiket berstatus *Open* tidak menumpuk dan memastikan seluruh pertanyaan pelanggan ditindaklanjuti secara bertanggung jawab.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php)
  - `updateStatus()`: Endpoint mengubah status obrolan.
  - `assign()`: Endpoint menugaskan obrolan ke agen staf.
- Route:
  - `PUT /admin/inbox/{id}/status` (`name('admin.inbox.status')`)
  - `PUT /admin/inbox/{id}/assign` (`name('admin.inbox.assign')`)

### Tiga Status Siklus Hidup Tiket:
```
[ Open / Aktif ] ──(CS Membalas & Menunggu Pembeli)──► [ Pending / Menunggu ]
       │                                                         │
       │                                                (Pembeli Mengonfirmasi)
       │                                                         │
       └──────────────(Masalah Selesai Ditangani)───────────────►▼
                                                         [ Resolved / Selesai ]
```

---

## 4. Flowchart Alur Pembaruan Status & Penugasan Tiket

```mermaid
flowchart TD
    Start([Agen Membuka Percakapan di Inbox]) --> ReviewTicket[Tinjau Perkembangan Penanganan Masalah]
    
    ReviewTicket --> ActionType{Tentukan Tindakan Manajemen:}
    
    %% Alur 1: Penugasan Tiket
    ActionType -- 1. Ambil Alih / Alihkan Tiket --> ClickAssignDropdown[Buka Dropdown 'Assigned Agent' di Panel Kanan]
    ClickAssignDropdown --> SelectAgentChoice{Pilih Penugasan:}
    SelectAgentChoice -- Tugaskan ke Diri Sendiri --> AssignSelf[Pilih Nama Anda Sendiri: Ambil Alih Tanggung Jawab]
    SelectAgentChoice -- Alihkan ke Rekan CS Lain --> AssignPeer[Pilih Rekan Staf CS Lain: Eskalasi Kasus Khusus]
    
    AssignSelf & AssignPeer --> SubmitAssignAPI[Kirim PUT Request ke /admin/inbox/{id}/assign]
    SubmitAssignAPI --> UpdateAssignedDB[(Update assigned_user_id di Tabel conversations)]
    UpdateAssignedDB --> ShowAssignSuccess[Notifikasi: Tiket Berhasil Ditugaskan]

    %% Alur 2: Pembaruan Status Tiket
    ActionType -- 2. Ubah Status Tiket --> ClickStatusDropdown[Pilih Status Baru pada Dropdown Header Obrolan]
    ClickStatusDropdown --> SelectStatusOption{Pilih Status:}
    
    SelectStatusOption -- Tiket Sedang Ditangani --> ChooseOpen[Status: Open (Muncul di Tab Obrolan Aktif)]
    SelectStatusOption -- Menunggu Respon Pembeli --> ChoosePending[Status: Pending (Masuk ke Antrean Follow-up)]
    SelectStatusOption -- Masalah Sudah Selesai Tuntas --> ChooseResolved[Status: Resolved (Diarsipkan dari Antrean Aktif)]
    
    ChooseOpen & ChoosePending & ChooseResolved --> SubmitStatusAPI[Kirim PUT Request ke /admin/inbox/{id}/status]
    SubmitStatusAPI --> UpdateStatusDB[(Update Kolom status di Tabel conversations)]
    
    UpdateStatusDB --> MoveInboxTab[Sistem Secara Instan Memindahkan Kartu Chat ke Tab Terkait]
    MoveInboxTab --> End([Selesai])
```

---

## 5. Rincian Antarmuka & Panduan Praktis CS

1. **Mengambil Alih Tiket Baru (*Self-Assign*):**
   - Saat melihat obrolan baru yang belum bertuan (*Unassigned*), klik kartu **Assigned Agent** di panel kanan.
   - Pilih nama Anda sendiri. Tiket tersebut kini resmi berada di bawah tanggung jawab Anda.
2. **Mengalihkan Kasus Rumit (*Re-Assign to Specialist*):**
   - Jika pelanggan menanyakan hal kompleks seputar pengiriman kargo ekspor atau kendala mesin espresso pabrik, pilih nama rekan tim yang menguasai bidang tersebut.
   - Tiket akan langsung muncul pada filter inbox rekan Anda.
3. **Menandai Selesai (*Resolve*):**
   - Begitu pembeli mengucapkan terima kasih dan mengonfirmasi pesanannya sudah selesai, klik tombol **Resolve / Tandai Selesai**.
   - Kartu obrolan akan hilang dari tab *Open* dan berpindah ke tab *Resolved*, memberikan ruang kerja yang rapi dan terorganisir.
