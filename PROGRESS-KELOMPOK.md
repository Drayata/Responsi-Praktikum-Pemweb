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
| **Indra** | • Pencarian dan Filter<br>• Pemesanan Makanan<br>• Status & Verifikasi Pengambilan | `pesanan` | **Siap Dikerjakan (Next)** | `modul-pesanan-indra` |
| **Alfi** | • Ulasan dan Rating<br>• Dashboard Dampak SDGs<br>• Panel Admin & Moderasi<br>• Notifikasi dan Riwayat | `ulasan`<br>`notifikasi` | **Siap Dikerjakan (Next)** | `modul-ulasan-notifikasi` |

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
2. **Jalankan Web Server via Terminal**:
   Buka terminal di root proyek lalu jalankan:
   ```bash
   php -S localhost:8000
   ```
3. **Buka di Browser**: Kunjungi **`http://localhost:8000`**.
4. **Akun Uji Coba Cepat (Password semua: `password123`)**:
   - **Penyedia Makanan:** `katering@bagipangan.com`
   - **Penerima Makanan:** `budi@bagipangan.com`
   - **Admin Sistem:** `admin@bagipangan.com`

---

## 4. Rincian Modul Lanjutan: Indra
> **Fokus:** Pencarian & Filter, Alur Pemesanan Makanan, Status & Verifikasi Pengambilan  
> **Branch Git Rekomendasi:** `modul-pesanan-indra`  
> **Tabel yang Dibuat:** `pesanan`

### A. Skema SQL Tabel `pesanan`
Indra cukup menambahkan query ini ke MySQL XAMPP (atau file `database.sql`):

```sql
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(20) NOT NULL UNIQUE, -- Contoh format: BP-8492
    user_id INT NOT NULL,                     -- Penerima makanan (FK ke users)
    donasi_id INT NOT NULL,                   -- Makanan yang dipesan (FK ke donasi)
    jumlah_porsi INT NOT NULL DEFAULT 1,
    status ENUM('menunggu', 'selesai', 'dibatalkan', 'kedaluwarsa') DEFAULT 'menunggu',
    catatan TEXT NULL,
    waktu_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    waktu_ambil DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (donasi_id) REFERENCES donasi(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B. Daftar Fitur & Berkas yang Harus Dibuat Indra:
1. **Pencarian & Filter Makanan Dinamis (`index.php` / `makanan/cari.php`)**:
   - Tambahkan input teks pencarian kata kunci pada katalog makanan di `index.php`.
   - Hubungkan form pencarian dan tombol chips kategori dengan query SQL:
     ```php
     $query = "SELECT d.*, k.nama_kategori, u.nama AS nama_penyedia 
               FROM donasi d 
               JOIN kategori k ON d.kategori_id = k.id 
               JOIN users u ON d.user_id = u.id 
               WHERE d.status = 'tersedia' AND d.batas_waktu > NOW()";
     // Tambahkan filter jika ada input search atau kategori
     ```
2. **Logika Pemesanan Makanan (`pesanan/buat.php`)**:
   - Menghubungkan tombol "Pesan 1 Porsi" di kartu makanan ke proses penyimpanan pesanan:
     - Pastikan pemesan wajib login sebagai `penerima` (`require_role('penerima')`).
     - Validasi apakah sisa porsi di tabel `donasi` masih mencukupi (`porsi_tersisa >= 1`).
     - Generate kode unik pengambilan acak, misalnya: `BP-` + angka acak 4 digit (`BP-4892`).
     - Simpan data baru ke tabel `pesanan` dengan status default `menunggu`.
     - **Penting:** Kurangi kuota porsi pada tabel donasi:
       ```sql
       UPDATE donasi SET porsi_tersisa = porsi_tersisa - 1 WHERE id = ?
       ```
       *(Jika `porsi_tersisa` menjadi 0, update status donasi menjadi `habis`)*.
3. **Halaman Tiket & Riwayat Pesanan Penerima (`pesanan/riwayat.php`)**:
   - Menampilkan daftar pesanan milik penerima yang sedang login.
   - Menampilkan kartu tiket dengan **Kode Pengambilan**, nama makanan, lokasi penjemputan, batas jam ambil, dan status.
   - Tombol batalkan pesanan (jika dibatalkan, kembalikan kuota porsi `porsi_tersisa + 1` ke tabel donasi).
4. **Halaman Verifikasi Pengambilan untuk Penyedia (`pesanan/verifikasi.php`)**:
   - Halaman khusus role `penyedia` (`require_role(['penyedia', 'admin'])`).
   - Penyedia memasukkan kode `BP-XXXX` yang ditunjukkan penerima di lokasi serah terima.
   - Jika kode cocok, ubah status pesanan menjadi `selesai` dan set `waktu_ambil = NOW()`.

---

## 5. Rincian Modul Lanjutan: Alfi
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

### B. Daftar Fitur & Berkas yang Harus Dibuat Alfi:
1. **Fitur Ulasan dan Rating Bintang (`ulasan/tambah.php`)**:
   - Form input rating bintang (skala 1 sampai 5) dan komentar ulasan.
   - Hanya bisa diisi jika pesanan sudah berstatus `selesai`.
   - Hitung rating rata-rata untuk setiap donasi/penyedia (`SELECT AVG(rating), COUNT(*) FROM ulasan WHERE donasi_id = ?`).
   - Tampilkan testimoni riil pada kartu cerita di seksi `#cerita` beranda.
2. **Dashboard Dampak Penyelamatan Pangan (`dampak/index.php` atau seksi `#dampak`)**:
   - Menghubungkan angka statistik beranda ke data riil dari database:
     - **Porsi Terselamatkan:** `SELECT SUM(jumlah_porsi) FROM pesanan WHERE status = 'selesai'`.
     - **Kilogram Sampah Pangan Dicegah:** Asumsi 1 porsi = 0,35 kg ($Porsi \times 0.35$).
     - **Emisi CO2 Dicegah (kg CO2e):** Asumsi 1 kg sampah makanan = 2,5 kg CO2e ($Kg\_Sampah \times 2.5$).
     - **Penyedia Terlibat:** `SELECT COUNT(DISTINCT user_id) FROM donasi`.
     - **Penerima Terbantu:** `SELECT COUNT(DISTINCT user_id) FROM pesanan WHERE status = 'selesai'`.
3. **Panel Admin & Moderasi (`admin/`)**:
   - Dibatasi khusus role `admin` (`require_role('admin')`).
   - **`admin/users.php`**: Tabel daftar semua pengguna, filter role, dan tombol nonaktifkan/hapus akun bermasalah.
   - **`admin/donasi.php`**: Moderasi daftar donasi makanan untuk menghapus makanan yang tidak higienis atau melanggar aturan.
   - **`admin/kategori.php`**: Tambah, edit, dan hapus kategori makanan pada tabel `kategori`.
4. **Sistem Notifikasi (`includes/notifikasi.php` & `notifikasi/index.php`)**:
   - Fungsi helper `kirim_notifikasi($user_id, $judul, $pesan, $link)`.
   - Otomatis membuat notifikasi saat:
     - Ada pesanan baru masuk -> kirim ke **Penyedia**.
     - Pesanan selesai diverifikasi -> kirim ke **Penerima** untuk ajakan mengulas.
   - Ikon lonceng notifikasi di navbar dengan indikator badge belum dibaca.

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
