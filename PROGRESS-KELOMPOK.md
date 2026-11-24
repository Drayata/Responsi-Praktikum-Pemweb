# Laporan Progres Keseluruhan Proyek Kelompok BagiPangan
## Responsi Praktikum Pemrograman Web (Fullstack — 3 Anggota)

> **Platform:** BagiPangan — Sistem Penyaluran Makanan Surplus  
> **Teknologi:** PHP Native (murni tanpa framework), HTML5, CSS3, JavaScript Vanilla, MySQL (XAMPP).  
> **Repository:** `crud-manejemenstok/Responsi-Praktikum-Pemweb`

---

## 1. Matriks Pembagian Tugas & Status Pengerjaan Tim

| Anggota | Modul Tugas | Tabel Database | Status | Branch Git |
|---|---|---|---|---|
| **Willy** | • Autentikasi dan Role<br>• Manajemen Profil<br>• Manajemen Donasi Makanan | `users`<br>`kategori`<br>`donasi` | **SELESAI (100%)** | `auth-willy` |
| **Indra** | • Pencarian dan Filter<br>• Pemesanan Makanan<br>• Status & Verifikasi Pengambilan | `pesanan` | **SELESAI (100%)** | `modul-pesanan-indra` |
| **Alfi** | • Ulasan dan Rating<br>• Dashboard Dampak SDGs<br>• Panel Admin & Moderasi<br>• Notifikasi dan Riwayat | `ulasan`<br>`notifikasi` | **SELESAI (100%)** | `modul-ulasan-notifikasi` |

---

## 2. Modul yang Telah Selesai Dikerjakan (Modul Willy)

Seluruh modul pondasi utama sistem telah selesai dibangun, terintegrasi dengan database MySQL XAMPP, dan telah lolos uji coba sintaks maupun fungsionalitas:

### A. Fondasi Database & Utilitas (`database.sql` & `migrate.php`)
- **Tabel `users`**: Mendukung multi-role (`penyedia`, `penerima`, `admin`), kontak WhatsApp, alamat titik penjemputan, foto avatar, dan keamanan sandi terenkripsi `BCRYPT`.
- **Tabel `kategori`**: Kategori jenis makanan surplus (*Makanan berat*, *Roti & kue*, *Buah & sayur*).
- **Tabel `donasi`**: Mencatat makanan berlebih, kuota porsi awal & tersisa, batas waktu penjemputan (`batas_waktu`), alamat ambil, foto makanan, dan status ketersediaan.
- **Script Otomatis `migrate.php`**: Inisialisasi otomatis seluruh tabel dan data default cukup dengan menjalankan `php migrate.php` di terminal.
- **Koneksi PDO Aman (`config/database.php`)**: Menggunakan *PDO Prepared Statements* yang kebal terhadap serangan SQL Injection.

### B. Modul Autentikasi & Multi-Role (`auth/`)
- **`auth/register.php`**: Pendaftaran akun dengan opsi role `penerima` atau `penyedia`. Validasi lengkap (email unik, konfirmasi password, nomor HP).
- **`auth/login.php`**: Login aman dengan pencocokan hash password `password_verify()`. Pengalihan otomatis: `penyedia` langsung ke halaman kelola donasi, sedangkan `penerima` ke beranda.
- **`auth/logout.php`**: Pembersihan sesi pengguna secara native.
- **`includes/auth_check.php`**: Helper otorisasi session (`require_login()`, `require_role()`, `current_user()`).

### C. Modul Manajemen Profil (`profil/`)
- **`profil/index.php`**: Menampilkan ringkasan profil, lencana role, nomor WhatsApp, alamat domisili/penjemputan, dan widget statistik donasi bagi penyedia.
- **`profil/edit.php`**: Form perubahan profil dan upload foto avatar (validasi JPG/PNG/WEBP maks 2 MB dengan preview JS).
- **`profil/ganti-password.php`**: Form ganti kata sandi dengan verifikasi kata sandi saat ini.

### D. Modul Manajemen Donasi Makanan (`donasi/`)
- **`donasi/tambah.php`**: Form penambahan makanan surplus untuk role `penyedia` dan `admin` (nama makanan, kategori, porsi, batas jam ambil, alamat ambil, dan upload foto).
- **`donasi/riwayat.php`**: Panel kelola donasi milik penyedia bersangkutan lengkap dengan informasi sisa porsi dan status badge (`tersedia`, `habis`, `kedaluwarsa`, `dibatalkan`).
- **`donasi/edit.php`**: Form update informasi porsi, batas waktu, foto, dan status donasi.
- **`donasi/hapus.php`**: Hapus donasi beserta pembersihan file foto dari folder `uploads/makanan/`.

### E. Integrasi UI/UX & Beranda Dinamis (`index.php`)
- Desain antarmuka 100% konsisten dengan prototipe `index.html` (font *Anton* dan *Inter*, palet warna `#F1F0E8` dan `#FF6B3D`, card rounded, dan tombol kapsul).
- Menampilkan data donasi riil langsung dari database MySQL.
- Filter chips interaktif berdasarkan kategori makanan.

---

## 3. Tutorial Menjalankan Proyek Saat Ini

1. **Nyalakan MySQL di XAMPP**: Pastikan status MySQL pada XAMPP Control Panel berwarna hijau (**Running**).
2. **Jalankan Migrasi Database** (Otomatis buat tabel `pesanan`, `ulasan`, `notifikasi`):
   Buka terminal di root proyek lalu jalankan:
   ```bash
   php migrate.php
   ```
   *Atau manual: Import `database.sql` via phpMyAdmin (http://localhost/phpmyadmin)*
3. **Jalankan Web Server via Terminal**:
   ```bash
   php -S localhost:8000
   ```
4. **Buka di Browser**: Kunjungi **`http://localhost:8000`**.
5. **Akun Uji Coba Cepat (Password semua: `password123`)**:
   - **Penyedia Makanan:** `katering@bagipangan.com`
   - **Penerima Makanan:** `budi@bagipangan.com`
   - **Admin Sistem:** `admin@bagipangan.com`

**Alur Uji Coba Lengkap:**
- **Penerima**: Login → Cari/Filter makanan di beranda → Klik "Pesan 1 porsi" → Isi jumlah & catatan → Lihat riwayat di `/pesanan/riwayat.php` → Salin kode `BP-XXXX`.
- **Penyedia**: Login → `/pesanan/verifikasi.php` → Masukkan kode `BP-XXXX` → Verifikasi → Notifikasi ke penerima.
- **Penerima (ulasan)**: Login → `/ulasan/tambah.php?pesanan_id=X` (dari notifikasi) → Beri rating 1-5 + komentar.
- **Admin**: Login → `/admin/` → Kelola users/donasi/kategori.
- **Dampak SDGs**: Buka `/dampak/` untuk dashboard visual statistik.

---

## 4. Rincian Modul Lanjutan: Indra ✅ SELESAI
> **Fokus:** Pencarian & Filter, Alur Pemesanan Makanan, Status & Verifikasi Pengambilan  
> **Branch Git Rekomendasi:** `modul-pesanan-indra`  
> **Tabel yang Dibuat:** `pesanan`

### A. Skema SQL Tabel `pesanan`
Tabel sudah ditambahkan ke `database.sql` dan dibuat via `migrate.php`:

```sql
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    donasi_id INT NOT NULL,
    jumlah_porsi INT NOT NULL DEFAULT 1,
    status ENUM('menunggu', 'selesai', 'dibatalkan', 'kedaluwarsa') DEFAULT 'menunggu',
    catatan TEXT NULL,
    waktu_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    waktu_ambil DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (donasi_id) REFERENCES donasi(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B. Fitur & Berkas yang Telah Dibuat Indra:

| Fitur | Berkas | Status |
|-------|--------|--------|
| **Pencarian & Filter Makanan** | `index.php` (updated) | ✅ Selesai |
| **Logika Pemesanan Makanan** | `pesanan/buat.php` | ✅ Selesai |
| **Riwayat Pesanan Penerima** | `pesanan/riwayat.php` | ✅ Selesai |
| **Verifikasi Pengambilan Penyedia** | `pesanan/verifikasi.php` | ✅ Selesai |

**Detail Implementasi:**
1. **Pencarian & Filter** (`index.php`): Input teks search + chips kategori filter dengan query SQL dinamis.
2. **Pemesanan** (`pesanan/buat.php`): Validasi role penerima, cek stok porsi, generate kode `BP-XXXX`, kurangi stok otomatis, notifikasi ke penyedia.
3. **Riwayat Pesanan** (`pesanan/riwayat.php`): Daftar pesanan dengan kode pengambilan, status badge, tombol batalkan (kembalikan stok).
4. **Verifikasi** (`pesanan/verifikasi.php`): Input kode `BP-XXXX`, validasi kepemilikan donasi, update status `selesai`, notifikasi ke penerima untuk ulasan.

---

## 5. Rincian Modul Lanjutan: Alfi ✅ SELESAI
> **Fokus:** Ulasan & Rating, Dashboard Dampak SDGs, Panel Admin & Moderasi, Notifikasi  
> **Branch Git Rekomendasi:** `modul-ulasan-notifikasi`  
> **Tabel yang Dibuat:** `ulasan`, `notifikasi`

### A. Skema SQL Tabel `ulasan` & `notifikasi`
Alfi cukup menambahkan query ini ke MySQL XAMPP:

```sql
-- 1. Tabel Ulasan & Rating
CREATE TABLE IF NOT EXISTS ulasan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL UNIQUE,  -- 1 pesanan hanya boleh diberi 1 ulasan
    user_id INT NOT NULL,            -- Penerima yang mengulas (FK ke users)
    donasi_id INT NOT NULL,          -- Makanan yang diulas (FK ke donasi)
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (donasi_id) REFERENCES donasi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Tabel Notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,            -- Penerima notifikasi (FK ke users)
    judul VARCHAR(100) NOT NULL,
    pesan TEXT NOT NULL,
    link VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B. Fitur & Berkas yang Telah Dibuat Alfi:

| Fitur | Berkas | Status |
|-------|--------|--------|
| **Ulasan & Rating Bintang** | `ulasan/tambah.php` | ✅ Selesai |
| **Dashboard Dampak SDGs** | `dampak/index.php` | ✅ Selesai |
| **Panel Admin - Users** | `admin/users.php` | ✅ Selesai |
| **Panel Admin - Donasi** | `admin/donasi.php` | ✅ Selesai |
| **Panel Admin - Kategori** | `admin/kategori.php` | ✅ Selesai |
| **Notifikasi System** | `includes/notifikasi.php` | ✅ Selesai |
| **Notifikasi Inbox** | `notifikasi/index.php` | ✅ Selesai |

**Detail Implementasi:**
1. **Ulasan & Rating** (`ulasan/tambah.php`): Form 1-5 bintang + komentar, validasi pesanan `selesai`, notifikasi ke penyedia.
2. **Dashboard Dampak** (`dampak/index.php`): Porsi terselamatkan, kg sampah (0.35 kg/porsi), CO₂e (2.5 kg/kg), penyedia/penerima count, chart 6 bulan, SDGs mapping (SDG 2, 12, 13, 17).
3. **Admin Panel** (`admin/`): Dashboard ringkasan, kelola users (role toggle/delete), moderasi donasi (hapus/status), kelola kategori (CRUD).
4. **Notifikasi** (`includes/notifikasi.php`): Helper `kirim_notifikasi()`, bell icon di header dengan badge, inbox di `notifikasi/index.php`, auto-refresh badge.

---

## 6. Panduan Kolaborasi Tim & Alur Kerja Git

Agar tidak terjadi bentrokan kode (*merge conflict*), seluruh anggota tim wajib mengikuti alur kerja berikut:

### 1. Memanfaatkan Pondasi Kode Willy yang Sudah Siap
Indra dan Alfi **tidak perlu membuat koneksi database atau template header/footer dari nol**. Cukup sertakan:
```php
// Panggil koneksi database PDO
require_once __DIR__ . '/../config/database.php'; // Variabel $pdo siap digunakan

// Panggil pengecekan login & role
require_once __DIR__ . '/../includes/auth_check.php';

// Panggil template header & footer seragam
$page_title = 'Judul Halaman';
require_once __DIR__ . '/../includes/header.php';
// ... isi konten halaman ...
require_once __DIR__ . '/../includes/footer.php';
```

### 2. Alur Git Branching:
1. **Willy** melakukan commit dan push dari branch `auth-willy` ke remote repository:
   ```bash
   git add .
   git commit -m "feat: selesaikan modul autentikasi, profil, donasi makanan dan progress kelompok"
   git push origin auth-willy
   ```
2. **Indra** dan **Alfi** mengambil update terbaru di komputer masing-masing:
   ```bash
   git checkout main
   git pull origin main # (atau pull dari origin auth-willy)
   ```
3. **Mengerjakan Modul di Branch Terpisah**:
   - Indra: `git checkout -b modul-pesanan-indra`
   - Alfi: `git checkout modul-ulasan-notifikasi`
4. Lakukan commit secara berkala pada branch masing-masing sebelum akhirnya digabungkan (*merge*) ke branch `main`.
