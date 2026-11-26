-- database_hosting.sql
-- Skema database BagiPangan untuk InfinityFree / Shared Hosting
-- TANPA perintah CREATE DATABASE, DROP DATABASE, atau USE
-- Import file ini SETELAH membuat database di panel hosting
-- Responsi Praktikum Pemrograman Web - BagiPangan

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

-- CATATAN PENTING:
-- JANGAN insert user default dengan password lemah di hosting production!
-- Buat akun admin manual melalui register.php atau phpMyAdmin dengan password KUAT.
-- Password default 'password123' HANYA untuk development lokal.