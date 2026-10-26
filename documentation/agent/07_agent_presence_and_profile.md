# Modul Kehadiran Staf & Profil Akun (CS Agent)

## 1. Deskripsi Fitur
Modul Kehadiran Staf & Profil Akun memberikan kendali kepada agen CS untuk mengelola status ketersediaan kerja mereka (*Agent Presence Status*: Online, Away, Offline), memperbarui kata sandi akun secara berkala, serta mengonfigurasi username Telegram pribadi jika ingin menerima notifikasi chat langsung ke akun personal mereka.

---

## 2. Hak Akses & Role Permission
- **Role:** `agent` (Customer Support Agent)
- **Middleware:** `web`, `auth`
- **Fungsi Utama:** Memberikan visibilitas kepada rekan tim dan Superadmin mengenai siapa saja staf CS yang sedang aktif berjaga di shift kerja saat ini.

---

## 3. Komponen Teknis & Skema Database

### File Terkait:
- Controller: [DashboardController.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Http/Controllers/Admin/DashboardController.php)
- Model: [User.php](file:///C:/laragon/www/%23Project2026/beantalk/app/Models/User.php)

### Tiga Status Kehadiran Agen (*Presence States*):
1. **Online (Hijau):** Agen aktif berada di depan layar komputer, siap membalas obrolan baru dalam hitungan detik.
2. **Away / Istirahat (Kuning):** Agen sedang istirahat makan siang, sholat, atau menghadiri briefing singkat. Sistem bot dapat mengambil alih obrolan otomatis saat agen berada pada status ini.
3. **Offline (Abu-abu):** Jam shift kerja agen telah berakhir dan agen telah keluar (*logout*) dari sistem.

---

## 4. Flowchart Alur Pengaturan Status Kehadiran & Profil Agen

```mermaid
flowchart TD
    Start([Agen CS Klik Avatar / Status di Sudut Kanan Atas]) --> DisplayMenu[Buka Menu Dropdown Profil & Status Kehadiran]
    
    DisplayMenu --> PresenceChoice{Pilih Status Kehadiran Saat Ini:}
    PresenceChoice -- Mulai Shift Kerja --> SetOnline[Pilih Status: Online (Titik Hijau)]
    PresenceChoice -- Istirahat / Jam Makan --> SetAway[Pilih Status: Away (Titik Kuning)]
    PresenceChoice -- Akhir Shift Kerja --> SetOffline[Pilih Status: Offline / Logout]

    SetOnline & SetAway & SetOffline --> UpdatePresenceDB[(Update Kolom status di Tabel users)]
    UpdatePresenceDB --> BroadcastPresence[Sistem Memperbarui Indikator Kehadiran di Dasbor Tim]

    DisplayMenu -- Ganti Password Akun --> OpenPasswordForm[Buka Form Ganti Kata Sandi]
    OpenPasswordForm --> InputPasswordData[Masukkan Kata Sandi Lama & Kata Sandi Baru Minimal 8 Karakter]
    InputPasswordData --> SubmitPassword[Kirim Request Update Password]
    
    SubmitPassword --> VerifyOldPassword{Apakah Kata Sandi Lama Sesuai?}
    VerifyOldPassword -- Salah --> ShowPasswordError[Peringatan: Kata Sandi Lama Tidak Cocok]
    VerifyOldPassword -- Benar --> HashNewPassword[Hash Bcrypt Kata Sandi Baru & Simpan ke DB]
    HashNewPassword --> ShowSuccessPassword[Pesan Sukses: Kata Sandi Anda Berhasil Diperbarui]
    
    BroadcastPresence & ShowSuccessPassword --> End([Selesai])
```

---

## 5. Rincian Antarmuka & Langkah Kerja CS

1. **Mengubah Status Kehadiran:**
   - Di sudut kanan atas layar (dekat nama akun Anda), klik lencana status bulat.
   - Pilih:
     - **Online:** Saat Anda siap menerima dan membalas chat.
     - **Away:** Saat Anda meninggalkan meja kerja (misal: istirahat makan siang).
   - Superadmin dan rekan tim lainnya dapat melihat status Anda di halaman tim secara langsung.
2. **Memperbarui Kata Sandi:**
   - Klik nama profil Anda > pilih **Pengaturan Akun**.
   - Masukkan kata sandi lama, ketikkan kata sandi baru yang kuat, lalu simpan.
   - Disarankan untuk mengganti kata sandi setiap 60–90 hari demi menjaga keamanan data pelanggan perusahaan.
