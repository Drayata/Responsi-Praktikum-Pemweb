# Panduan Deployment BagiPangan ke InfinityFree

Dokumen ini menjelaskan langkah-langkah lengkap untuk mendeploy aplikasi BagiPangan dari development lokal (XAMPP) ke **InfinityFree Free Hosting** menggunakan PHP Native, PDO, dan MySQL.

---

## 1. Persiapan Sebelum Deploy

### 1.1 File yang PERLU di-upload ke `htdocs` (public_html)

```
✅ Upload SEMUA file & folder BERIKUT:
├── admin/                    # Panel admin
├── auth/                     # Login, register, logout
├── config/
│   ├── database.php          # Konfigurasi DB (sudah support hosting)
│   └── config.example.php    # Template konfigurasi hosting
├── dampak/                   # Dashboard dampak SDGs
├── donasi/                   # CRUD donasi makanan
├── includes/                 # Header, footer, auth_check, notifikasi helper
├── notifikasi/               # Sistem notifikasi
├── pesanan/                  # Pesanan & verifikasi
├── profil/                   # Profil user
├── ulasan/                   # Ulasan & rating
├── assets/
│   ├── css/style.css
│   └── js/script.js
├── uploads/
│   ├── makanan/.gitkeep      # Folder upload foto donasi (harus writable)
│   └── avatars/.gitkeep      # Folder upload avatar (harus writable)
├── index.php                 # Landing page utama
├── database_hosting.sql      # Schema DB untuk hosting (TANPA CREATE DATABASE)
├── README.md
├── DEPLOYMENT.md
└── .gitignore
```

### 1.2 File yang TIDAK BOLEH di-upload (sudah dikecualikan di `.gitignore`)

```
❌ JANGAN upload:
├── config/config.php         # Kredensial database hosting (BERSIFAT RAHASIA!)
├── migrate.php               # Script migrasi hanya untuk lokal
├── database.sql              # Schema DB lokal (pakai CREATE DATABASE)
├── setup_pesanan.sql         # File SQL tambahan lokal
├── mockup.html               # Prototipe desain
├── PROGRESS-KELOMPOK.md      # Laporan progres tim
├── .git/                     # Folder Git
├── .gitignore
├── logs/                     # Folder log error (dibuat otomatis)
├── uploads/makanan/*         # File upload user (kecuali .gitkeep)
└── uploads/avatars/*         # File upload avatar (kecuali .gitkeep)
```

---

## 2. Setup Database di InfinityFree

### 2.1 Buat Database MySQL

1. Login ke **InfinityFree Client Area** → **Control Panel** → **MySQL Databases**
2. Klik **Create Database**
3. Catat informasi berikut:
   - **Database Name**: `if0_XXXXXXXX_bagipangan` (format: `if0_` + username + `_` + nama)
   - **Username**: `if0_XXXXXXXX` (sama dengan prefix database)
   - **Password**: **Buat password KUAT** (simpan dengan aman!)
   - **Hostname**: `sqlXXX.infinityfree.com` (contoh: `sql212.infinityfree.com`)
   - **Port**: `3306`

### 2.2 Import Schema Database

1. Buka **phpMyAdmin** dari Control Panel InfinityFree
2. Pilih database yang baru dibuat (`if0_XXXXXXXX_bagipangan`)
3. Klik tab **Import**
4. Pilih file **`database_hosting.sql`** dari komputer lokal
5. Klik **Go** / **Import**
6. Pastikan semua 7 tabel terbuat: `users`, `kategori`, `donasi`, `pesanan`, `ulasan`, `notifikasi`, plus index tambahan

> **Catatan**: `database_hosting.sql` **TIDAK** berisi `CREATE DATABASE`, `DROP DATABASE`, atau `USE`. File ini aman di-import ke database yang sudah dibuat di panel hosting.

---

## 3. Konfigurasi Kredensial Hosting

### 3.1 Buat File `config/config.php`

1. Di komputer lokal, salin `config/config.example.php` menjadi `config/config.php`
2. Isi kredensial database hosting yang sudah didapat dari langkah 2.1:

```php
<?php
// config/config.php - KREDENSIAL HOSTING (JANGAN COMMIT KE GITHUB!)
return [
    'db_host' => 'sqlXXX.infinityfree.com',     // Ganti dengan hostname dari panel
    'db_port' => '3306',
    'db_name' => 'if0_XXXXXXXX_bagipangan',     // Ganti dengan nama database hosting
    'db_user' => 'if0_XXXXXXXX',                // Ganti dengan username database hosting
    'db_pass' => 'password_hosting_anda_yang_kuat',  // Ganti dengan password database hosting
    
    // Base URL aplikasi (opsional, auto-detect biasanya sudah cukup)
    // 'base_url' => 'https://namadomain.infinityfreeapp.com/',
    
    'upload_max_size' => 3 * 1024 * 1024,       // 3 MB (batas InfinityFree)
    'avatar_max_size' => 2 * 1024 * 1024,       // 2 MB
    
    'app_env' => 'production',                  // WAJIB: 'production' di hosting
    'debug_mode' => false,                      // WAJIB: false di hosting
    
    'session_secure' => true,                   // WAJIB: true untuk HTTPS
    'session_samesite' => 'Lax',
];
```

3. **Upload `config/config.php` ke server** (via File Manager atau FTP)
   - Pastikan file ini **TIDAK** ter-commit ke GitHub (sudah ada di `.gitignore`)

### 3.2 Set Permission Folder Upload

Via File Manager atau FTP, set permission:
- `uploads/makanan/` → **755** (drwxr-xr-x)
- `uploads/avatars/` → **755** (drwxr-xr-x)
- `logs/` → **755** (drwxr-xr-x)

Folder ini harus **writable** oleh web server untuk upload foto.

---

## 4. Upload File Aplikasi

### 4.1 Via File Manager (Direkt di Panel InfinityFree)

1. Buka **File Manager** di Control Panel
2. Masuk ke folder `htdocs` (atau `public_html`)
3. Hapus file `index2.html` default InfinityFree jika ada
4. Upload **SEMUA file & folder** dari daftar **1.1** (kecuali yang di 1.2)
   - Bisa zip folder project lokal → upload zip → extract di File Manager
   - Atau drag-drop folder per folder

### 4.2 Via FTP (Direkomendasikan untuk upload massal)

**Koneksi FTP InfinityFree:**
- Host: `ftp.NAMADOMAIN.infinityfreeapp.com` (atau `ftp.clusterXXX.hosting.infinityfree.com`)
- Username: `if0_XXXXXXXX` (sama dengan username database)
- Password: Password hosting (bukan password database!)
- Port: `21` (Passive mode: ON)

Upload seluruh isi folder project ke `/htdocs/` (root web).

---

## 5. Verifikasi Deployment

### 5.1 Test Akses Website

1. Buka domain Anda: `https://namadomain.infinityfreeapp.com/`
2. Pastikan halaman utama (landing page) terbuka tanpa error
3. Cek console browser (F12) untuk error JavaScript/CSS

### 5.2 Test Fitur Utama

| Fitur | URL Test | Expected |
|-------|----------|----------|
| Landing Page | `/` | Tampil donasi, search, filter, stats |
| Register | `/auth/register.php` | Form pendaftaran role penyedia/penerima |
| Login | `/auth/login.php` | Login berhasil, redirect by role |
| Penyedia - Tambah Donasi | `/donasi/tambah.php` | Form + upload foto (max 3MB) |
| Penyedia - Kelola Donasi | `/donasi/riwayat.php` | List donasi, edit, hapus |
| Penerima - Pesan | `/pesanan/buat.php?id=X` | Form pesan, generate kode BP-XXXX |
| Penyedia - Verifikasi | `/pesanan/verifikasi.php` | Input kode, update status selesai |
| Ulasan | `/ulasan/tambah.php?pesanan_id=X` | Rating 1-5 bintang + komentar |
| Notifikasi | `/notifikasi/index.php` | List notifikasi, badge real-time |
| Profil | `/profil/index.php` | Lihat/edit profil, ganti password |
| Admin Panel | `/admin/index.php` | Dashboard stats, kelola user/donasi/kategori |
| Dampak SDGs | `/dampak/index.php` | Visualisasi statistik |

### 5.3 Test HTTPS & Session

- Pastikan semua halaman load via **HTTPS** (InfinityFree provide free SSL)
- Login → refresh halaman → session tetap aktif
- Logout → session dihancurkan, redirect ke beranda

---

## 6. Konfigurasi Tambahan (Opsional)

### 6.1 Custom Domain (Jika Punya Domain Sendiri)

1. Di InfinityFree: **Domains** → **Add Domain** → ikuti panduan pointing nameserver
2. Tunggu propagasi DNS (max 24 jam)
3. Update `config/config.php`:
   ```php
   'base_url' => 'https://domainanda.com/',
   ```

### 6.2 Error Logging

Error log tersimpan di: `logs/php_error.log`
- Cek file ini jika ada error 500 / blank page
- `debug_mode: false` memastikan error tidak ditampilkan ke user

---

## 7. Batasan & Limitasi InfinityFree

| Resource | Limit | Catatan |
|----------|-------|---------|
| **Disk Space** | 5 GB | Cukup untuk foto donasi & avatar |
| **File Upload Max** | **10 MB** (PHP `upload_max_filesize`) | Kode sudah batasi 3MB (donasi) & 2MB (avatar) |
| **POST Max Size** | 10 MB | `post_max_size` |
| **Memory Limit** | 256 MB | Cukup untuk PHP native |
| **Max Execution Time** | 60 detik | Cukup untuk operasi DB sederhana |
| **Concurrent Connections** | 30 | Shared hosting limit |
| **Inode Limit** | 30,000 | Monitor jumlah file upload |
| **CPU/RAM** | Fair use | Jangan jalankan cron heavy |

> **Tips**: Kompres foto sebelum upload (client-side atau server-side) untuk hemat quota.

---

## 8. Troubleshooting Umum

### 8.1 Error "Koneksi Database Gagal" / Blank Page

- Cek `config/config.php` → pastikan `db_host`, `db_name`, `db_user`, `db_pass` **BENAR**
- Pastikan hostname pakai `sqlXXX.infinityfree.com` (bukan `localhost`)
- Cek `logs/php_error.log` untuk detail error

### 8.2 Error 500 Internal Server Error

- Cek `logs/php_error.log`
- Pastikan file `.htaccess` tidak ada konflik (InfinityFree tidak support `.htaccess` custom penuh)
- Pastikan permission folder `uploads/` dan `logs/` = **755**

### 8.3 Upload Foto Gagal

- Cek ukuran file ≤ 3 MB (donasi) / 2 MB (avatar)
- Cek format: JPG, PNG, WEBP only
- Cek permission folder `uploads/makanan/` = **755**
- Cek `logs/php_error.log` untuk error `move_uploaded_file`

### 8.4 Session Tidak Tersimpan / Logout Otomatis

- Pastikan `session_secure: true` di `config.php` (karena HTTPS)
- Cek cookie `PHPSESSID` di browser → `Secure` & `SameSite=Lax`
- InfinityFree kadang reset session saat deploy baru → user perlu login ulang

### 8.5 CSS/JS Tidak Load (404)

- Pastikan `base_url()` mendeteksi path dengan benar
- Cek Network tab browser → URL CSS/JS apakah benar
- Jika di subfolder, set `base_url` manual di `config.php`

---

## 9. Keamanan Production Checklist

Sebelum go-live, pastikan:

- [ ] `config/config.php` **tidak** di-commit ke GitHub (sudah di `.gitignore`)
- [ ] `debug_mode: false` di `config.php`
- [ ] `app_env: 'production'` di `config.php`
- [ ] `session_secure: true` di `config.php`
- [ ] Password database hosting **KUAT** (min 16 karakter, campuran)
- [ ] **Tidak** menggunakan akun default `admin@bagipangan.com` / `password123`
- [ ] Buat akun admin baru via `/auth/register.php` lalu update role ke `admin` via phpMyAdmin
- [ ] Hapus/disable akun test development jika ada
- [ ] Folder `uploads/` permission **755** (bukan 777)
- [ ] Error logging aktif ke `logs/php_error.log`

---

## 10. Rollback & Backup

### 10.1 Backup Database
- Export via phpMyAdmin berkala (mingguan)
- Simpan file `.sql` di local/computer

### 10.2 Backup File Upload
- Download folder `uploads/makanan/` dan `uploads/avatars/` via FTP berkala

### 10.3 Rollback Code
- Simpan versi working di GitHub (branch `main` atau `production`)
- Jika deploy baru error, upload ulang versi lama via FTP

---

## 11. Catatan Khusus Praktikum

Proyek ini memenuhi cakupan materi **Praktikum Pemrograman Web I**:
- ✅ PHP Native (tanpa framework)
- ✅ PDO Prepared Statements (anti SQL Injection)
- ✅ Session & Role-based Access Control
- ✅ File Upload dengan Validasi
- ✅ CRUD Lengkap (Create, Read, Update, Delete)
- ✅ Relasi Database (Foreign Key, CASCADE)
- ✅ Password Hashing (BCRYPT)
- ✅ Flash Message & Redirect
- ✅ Responsive HTML5/CSS3/Vanilla JS
- ✅ SDGs Integration (Dashboard Dampak)

---

## 12. Support & Kontak

Jika mengalami masalah deployment:
1. Cek `logs/php_error.log` terlebih dahulu
2. Review checklist di bagian **9** dan **10**
3. Hubungi dosen/pengasuh praktikum dengan menyertakan:
   - Screenshot error
   - Isi `logs/php_error.log` (redact password)
   - Langkah yang sudah dicoba

---

**Selamat mencoba deployment! 🚀**
*BagiPangan - Responsi Praktikum Pemrograman Web - Informatika UNSOED 2026*