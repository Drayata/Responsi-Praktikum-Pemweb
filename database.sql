-- database.sql
-- Skema database BagiPangan untuk Development Lokal (XAMPP)
-- Termasuk data seed untuk testing
-- Responsi Praktikum Pemrograman Web - BagiPangan

CREATE DATABASE IF NOT EXISTS `bagipangan_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bagipangan_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 1. Tabel users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `no_hp` VARCHAR(20) NOT NULL,
    `alamat` TEXT NOT NULL,
    `role` ENUM('penyedia', 'penerima', 'admin') NOT NULL DEFAULT 'penerima',
    `foto` VARCHAR(255) DEFAULT 'default-avatar.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel kategori
CREATE TABLE IF NOT EXISTS `kategori` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_kategori` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `deskripsi` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Data Awal Kategori
INSERT IGNORE INTO `kategori` (`id`, `nama_kategori`, `slug`, `deskripsi`) VALUES 
(1, 'Makanan berat', 'makanan-berat', 'Nasi kotak, lauk pauk, hidangan pokok berkalori'),
(2, 'Roti & kue', 'roti-kue', 'Roti segar, bolu, kue basah, dan pastry'),
(3, 'Buah & sayur', 'buah-sayur', 'Buah segar, sayuran mentah, dan hasil kebun');

-- 4. Tabel donasi
CREATE TABLE IF NOT EXISTS `donasi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `kategori_id` INT NOT NULL,
    `nama_makanan` VARCHAR(150) NOT NULL,
    `deskripsi` TEXT NULL,
    `porsi_awal` INT NOT NULL,
    `porsi_tersisa` INT NOT NULL,
    `batas_waktu` DATETIME NOT NULL,
    `alamat_ambil` TEXT NOT NULL,
    `foto` VARCHAR(255) NULL,
    `status` ENUM('tersedia', 'habis', 'kedaluwarsa', 'dibatalkan') DEFAULT 'tersedia',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`kategori_id`) REFERENCES `kategori`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel pesanan
CREATE TABLE IF NOT EXISTS `pesanan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_pesanan` VARCHAR(20) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `donasi_id` INT NOT NULL,
    `jumlah_porsi` INT NOT NULL DEFAULT 1,
    `status` ENUM('menunggu', 'selesai', 'dibatalkan', 'kedaluwarsa') DEFAULT 'menunggu',
    `catatan` TEXT NULL,
    `waktu_pesan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `waktu_ambil` DATETIME NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`donasi_id`) REFERENCES `donasi`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel ulasan
CREATE TABLE IF NOT EXISTS `ulasan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pesanan_id` INT NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `donasi_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `komentar` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`donasi_id`) REFERENCES `donasi`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel notifikasi
CREATE TABLE IF NOT EXISTS `notifikasi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `judul` VARCHAR(100) NOT NULL,
    `pesan` TEXT NOT NULL,
    `link` VARCHAR(255) NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Index tambahan untuk performa
CREATE INDEX `idx_donasi_status_waktu` ON `donasi` (`status`, `batas_waktu`);
CREATE INDEX `idx_pesanan_user_status` ON `pesanan` (`user_id`, `status`);
CREATE INDEX `idx_pesanan_donasi_status` ON `pesanan` (`donasi_id`, `status`);
CREATE INDEX `idx_notifikasi_user_read` ON `notifikasi` (`user_id`, `is_read`);
CREATE INDEX `idx_ulasan_donasi` ON `ulasan` (`donasi_id`);

-- 9. Data Seed HANYA UNTUK DEVELOPMENT LOKAL
-- PERINGATAN: Password default 'password123' TIDAK AMAN untuk production!
-- Gunakan database_hosting.sql untuk deployment hosting (tanpa user seed).
-- Buat akun admin manual di hosting dengan password KUAT.

-- Password hash untuk 'password123' (BCRYPT cost 12)
-- $2y$12$MajKx4hOlQaWsdxXiF5dgeQ08BAxinMhiut9HOCRWnP7d6.2glnNG

INSERT IGNORE INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`, `foto`) VALUES
(1, 'Katering Sari Boga', 'katering@bagipangan.com', '$2y$12$MajKx4hOlQaWsdxXiF5dgeQ08BAxinMhiut9HOCRWnP7d6.2glnNG', '081234567890', 'Jl. Kampus No. 12, Purwokerto', 'penyedia', 'default-avatar.png'),
(2, 'Budi Santoso', 'budi@bagipangan.com', '$2y$12$MajKx4hOlQaWsdxXiF5dgeQ08BAxinMhiut9HOCRWnP7d6.2glnNG', '089876543210', 'Asrama Mahasiswa Unsoed, Purwokerto', 'penerima', 'default-avatar.png'),
(3, 'Admin BagiPangan', 'admin@bagipangan.com', '$2y$12$MajKx4hOlQaWsdxXiF5dgeQ08BAxinMhiut9HOCRWnP7d6.2glnNG', '081122334455', 'Kantor Komunitas BagiPangan, Purwokerto', 'admin', 'default-avatar.png');

-- Contoh donasi awal yang sinkron dengan tampilan index.html
INSERT IGNORE INTO `donasi` (`id`, `user_id`, `kategori_id`, `nama_makanan`, `deskripsi`, `porsi_awal`, `porsi_tersisa`, `batas_waktu`, `alamat_ambil`, `foto`, `status`) VALUES
(1, 1, 1, 'Nasi kotak ayam bakar', 'Nasi kotak surplus acara makan siang, lengkap sambal & lalapan, higienis dan hangat.', 15, 12, DATE_ADD(NOW(), INTERVAL 5 HOUR), 'Jl. Kampus No. 12, Grendeng, Purwokerto', NULL, 'tersedia'),
(2, 1, 2, 'Roti manis campur', 'Roti manis aneka rasa (coklat, keju, srikaya) baru selesai display hari ini.', 25, 20, DATE_ADD(NOW(), INTERVAL 6 HOUR), 'Toko Roti Kenanga, Jl. Dr. Soeparno No. 45', NULL, 'tersedia'),
(3, 1, 3, 'Paket buah segar', 'Buah jeruk dan apel manis dalam wadah mika siap santap.', 10, 8, DATE_ADD(NOW(), INTERVAL 4 HOUR), 'Lapak Buah Pak Darmo, Pasar Wage Kios 14', NULL, 'tersedia');