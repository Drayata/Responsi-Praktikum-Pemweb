# BagiPangan

> Platform web untuk menyalurkan makanan surplus dari penyedia ke penerima sebelum terbuang.

## 1. Nama dan Deskripsi Proyek

**Nama Proyek:** BagiPangan  
**Deskripsi:** BagiPangan adalah aplikasi web yang mempertemukan pihak yang memiliki makanan berlebih (restoran, kafe, toko roti, katering, atau rumah tangga) dengan pihak yang membutuhkan (warga kurang mampu, mahasiswa, panti asuhan, atau komunitas sosial).

Penyedia mengunggah makanan yang masih layak konsumsi beserta jumlah, lokasi, dan batas waktu pengambilan. Penerima menelusuri daftar tersebut, memesan, lalu mengambil makanan sesuai jadwal. Setiap transaksi tercatat sehingga dampaknya dapat diukur, misalnya jumlah porsi yang terselamatkan.

## 2. Latar Belakang dan Tujuan Proyek

### Latar Belakang

Sebagian besar makanan yang diproduksi tidak pernah dikonsumsi. Di sisi lain, masih banyak orang yang kesulitan memenuhi kebutuhan pangan harian. Ironi ini terjadi bukan karena makanan kurang, tetapi karena tidak ada penghubung yang cepat antara yang berlebih dan yang membutuhkan.

Permasalahan yang diangkat:

- Makanan layak konsumsi dari usaha kuliner sering dibuang di akhir hari karena tidak terjual.
- Penyedia ingin berdonasi, tetapi tidak tahu harus menyalurkan ke siapa dan bagaimana caranya.
- Penerima tidak punya informasi kapan dan di mana makanan tersedia.
- Makanan yang terbuang di TPA menghasilkan emisi gas metana yang memperburuk perubahan iklim.

### Tujuan

1. Mengurangi makanan layak konsumsi yang terbuang.
2. Menyediakan akses pangan bagi kelompok yang membutuhkan.
3. Menyediakan sistem yang sederhana dan transparan untuk donasi makanan.
4. Mengukur dampak penyelamatan makanan melalui data dan statistik.

### Manfaat

- **Penyedia:** mengurangi pemborosan, ikut berkontribusi sosial, dan mendapat catatan donasi.
- **Penerima:** memperoleh makanan secara gratis atau dengan harga sangat terjangkau.
- **Lingkungan:** menekan sampah makanan dan emisi yang menyertainya.
- **Masyarakat:** membangun budaya berbagi di tingkat komunitas.

## 3. Daftar Fitur atau Modul yang Diimplementasikan

### Modul Autentikasi & Profil (`auth/`, `profil/`)
- **Registrasi & Login:** Mendukung 3 peran — `penyedia`, `penerima`, `admin` — dengan hash password BCRYPT.
- **Profil Pengguna:** Lihat detail akun, edit nama/nomor HP/alamat, ganti password.
- **Proteksi Rute:** Middleware `auth_check.php` dengan fungsi `require_login()` dan `require_role()`.

### Modul Donasi Makanan (`donasi/`)
- **Tambah Donasi (`tambah.php`):** Penyedia membuat listing makanan surplus dengan nama, kategori, jumlah porsi, batas waktu pengambilan, alamat penjemputan, deskripsi, dan upload foto (JPG/PNG/WEBP, max 3 MB).
- **Riwayat Donasi (`riwayat.php`):** Penyedia melihat daftar donasi mereka, status (tersedia/habis/kedaluwarsa/dibatalkan), porsi tersisa.
- **Edit & Hapus Donasi (`edit.php`, `hapus.php`):** Manajemen donasi milik sendiri.
- **Validasi:** Batas waktu harus di masa depan, porsi minimal 1.

### Modul Pesanan (`pesanan/`)
- **Buat Pesanan (`buat.php`):** Penerima memesan porsi dari donasi yang tersedia, sistem otomatis kurangi `porsi_tersisa`, generate kode pesanan unik (`BP-XXXX`).
- **Verifikasi (`verifikasi.php`):** Penyedia menandai pesanan sebagai `selesai` saat penerima mengambil makanan.
- **Riwayat Pesanan (`riwayat.php`):** Penerima melihat riwayat pesanan, status, kode pengambilan, waktu ambil.

### Modul Ulasan & Rating (`ulasan/`)
- **Tambah Ulasan (`tambah.php`):** Penerima memberi rating 1–5 bintang + komentar setelah pesanan `selesai`. Satu pesanan hanya bisa diulas sekali.
- **Notifikasi ke Penyedia:** Penyedia mendapat notifikasi saat ada ulasan baru.

### Modul Notifikasi (`notifikasi/`)
- **Pusat Notifikasi (`index.php`):** Daftar notifikasi real-time (pesanan baru, verifikasi, ulasan, dll), auto-mark as read saat dibuka.
- **Auto-refresh Badge (`refresh.php`):** Polling AJAX tiap 60 detik untuk badge jumlah notifikasi belum dibaca di navbar.

### Modul Admin (`admin/`)
- **Dashboard Admin (`index.php`):** Statistik total pengguna, penyedia, penerima, donasi, pesanan, ulasan.
- **Kelola Pengguna (`users.php`):** Lihat, filter, nonaktifkan, hapus akun.
- **Moderasi Donasi (`donasi.php`):** Hapus donasi yang tidak higienis/melanggar aturan.
- **Kelola Kategori (`kategori.php`):** CRUD master data kategori makanan.

### Modul Dampak (`dampak/`)
- **Halaman Dampak (`index.php`):** Visualisasi statistik porsi terselamatkan, jumlah penyedia, penerima terlayani.

### Fitur Umum
- **Pencarian & Filter:** Search makanan/penyedia/deskripsi + filter kategori (Makanan berat, Roti & kue, Buah & sayur).
- **Responsive UI:** Mobile-first, navigasi hamburger, grid kartu makanan adaptif.
- **Flash Message:** Notifikasi sukses/error lintas halaman via session.

## 4. Teknologi yang Digunakan

| Kategori | Teknologi |
|----------|-----------|
| **Backend** | PHP 8.x (native, tanpa framework) |
| **Database** | MySQL / MariaDB (XAMPP) |
| **Koneksi DB** | PDO (PHP Data Objects) dengan prepared statements |
| **Frontend** | HTML5, CSS3 (Custom Properties, Flexbox, Grid), Vanilla ES6 JavaScript |
| **Font** | Google Fonts: Inter, Anton |
| **Ikon** | Inline SVG / Unicode emoji |
| **Web Server** | Apache (via XAMPP) |
| **Password Hashing** | `password_hash()` / `password_verify()` (BCRYPT) |
| **Session** | PHP `$_SESSION` untuk auth & flash message |
| **File Upload** | `$_FILES` dengan validasi ekstensi & ukuran, penyimpanan di `uploads/makanan/` |

## 5. Panduan Instalasi dan Konfigurasi

### Prasyarat
- **XAMPP** (Apache + MySQL) versi 8.x atau lebih baru
- **Browser** modern (Chrome, Firefox, Edge, Safari)
- **Git** (opsional, untuk clone repo)

### Langkah Instalasi

1. **Clone / Salin Project ke htdocs**
   ```bash
   cd /opt/lampp/htdocs/
   git clone <repository-url> Responsi-Praktikum-Pemweb
   # atau copy folder project manual ke htdocs
   ```

2. **Aktifkan Apache & MySQL di XAMPP Control Panel**
   - Start **Apache** dan **MySQL** hingga status *Running*.

3. **Buat Database & Import Schema**
   - Buka **phpMyAdmin**: `http://localhost/phpmyadmin`
   - Buat database baru: `bagipangan_db` (Collation: `utf8mb4_unicode_ci`)
   - Import file `database.sql` dari root project:
     - Klik tab **Import** → Choose File → pilih `database.sql` → **Go**
   - Atau gunakan CLI:
     ```bash
     mysql -u root -p bagipangan_db < /opt/lampp/htdocs/Responsi-Praktikum-Pemweb/database.sql
     ```

4. **Konfigurasi Database (Opsional)**
   - File konfigurasi: `config/database.php`
   - Default sudah disesuaikan untuk XAMPP:
     ```php
     $db_host = 'localhost';
     $db_port = '3306';
     $db_name = 'bagipangan_db';
     $db_user = 'root';
     $db_pass = ''; // default XAMPP tanpa password
     ```
   - Jika MySQL Anda pakai password/port berbeda, edit nilai di atas.

5. **Folder Upload**
   - Pastikan folder `uploads/makanan/` writable oleh web server (sudah di-handle otomatis di `tambah.php` via `mkdir(..., 0777, true)`).

### Akun Default (Seed Data)
Setelah import `database.sql`, tersedia 3 akun uji coba (password: `password123`):

| Peran | Email | Nama |
|-------|-------|------|
| Penyedia | `katering@bagipangan.com` | Katering Sari Boga |
| Penerima | `budi@bagipangan.com` | Budi Santoso |
| Admin | `admin@bagipangan.com` | Admin BagiPangan |

## 6. Panduan Menjalankan Aplikasi

1. **Akses Aplikasi**
   - Buka browser: `http://localhost/Responsi-Praktikum-Pemweb/`
   - Atau jika pakai virtual host / port custom: `http://localhost:8080/Responsi-Praktikum-Pemweb/`

2. **Alur Penggunaan Cepat**

   **Sebagai Penyedia:**
   - Login dengan `katering@bagipangan.com` / `password123`
   - Klik **+ Tambah Donasi** di navbar atau hero
   - Isi form: nama makanan, kategori, porsi, batas waktu, alamat ambil, deskripsi, foto (opsional)
   - Klik **Publikasikan Donasi**
   - Kelola donasi di **Kelola Donasi** → lihat status, edit/hapus
   - Verifikasi pesanan masuk di **Pesanan → Verifikasi**

   **Sebagai Penerima:**
   - Login dengan `budi@bagipangan.com` / `password123`
   - Di beranda, cari & filter makanan tersedia
   - Klik **Pesan 1 porsi** → isi jumlah porsi & catatan → **Buat Pesanan**
   - Simpan **Kode Pengambilan** (format `BP-XXXX`)
   - Ambil makanan ke alamat penyedia sebelum batas waktu
   - Setelah mengambil, beri **Ulasan & Rating** di riwayat pesanan

   **Sebagai Admin:**
   - Login dengan `admin@bagipangan.com` / `password123`
   - Akses **Panel Admin** via navbar (hanya tampil untuk admin)
   - Pantau statistik, kelola pengguna, moderasi donasi, kelola kategori

3. **Fitur Tambahan**
   - **Notifikasi:** Ikon lonceng di navbar → klik untuk lihat riwayat notifikasi (pesanan baru, verifikasi, ulasan, dll).
   - **Profil:** Klik nama di navbar → edit profil, ganti password.
   - **Dampak:** Scroll ke section **Dampak** di beranda untuk lihat statistik real-time.

---

## Keterkaitan dengan SDGs

| SDG | Keterkaitan |
|-----|-------------|
| **SDG 12: Konsumsi dan Produksi yang Bertanggung Jawab** (utama) | Mendukung target pengurangan limbah makanan dengan menyalurkan surplus sebelum terbuang. |
| **SDG 2: Tanpa Kelaparan** (utama) | Menyalurkan makanan kepada orang yang membutuhkan. |
| **SDG 13: Penanganan Perubahan Iklim** (pendukung) | Makanan yang terselamatkan berarti emisi dari limbah makanan berkurang. |

---

## Struktur Direktori Utama

```
Responsi-Praktikum-Pemweb/
├── admin/           # Panel admin (dashboard, users, donasi, kategori)
├── auth/            # Login, register, logout
├── config/          # database.php (PDO config + base_url helper)
├── dampak/          # Halaman statistik dampak
├── donasi/          # CRUD donasi makanan (tambah, edit, hapus, riwayat)
├── includes/        # header, footer, auth_check, notifikasi helper
├── notifikasi/      # Daftar notifikasi & auto-refresh badge
├── pesanan/         # Buat pesanan, verifikasi, riwayat
├── profil/          # Profil user, edit, ganti password
├── ulasan/          # Tambah ulasan & rating
├── assets/
│   ├── css/style.css
│   └── js/script.js
├── uploads/makanan/ # Foto donasi (auto-created)
├── database.sql     # Schema + seed data
├── index.php        # Landing page dinamis (search, filter, stats)
└── README.md
```

---

## Lisensi & Kontak

Proyek ini dibuat untuk **Responsi Praktikum Pemrograman Web** — Informatika, Universitas Jenderal Soedirman, 2026.

Tim: Willy, Indra, Alfi