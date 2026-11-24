-- ============================================================
-- SETUP PESANAN & NOTIFIKASI (MODUL INDRA)
-- Jalankan di MySQL/phpMyAdmin untuk menambahkan tabel pesanan dan notifikasi
-- ============================================================

-- 1. Tabel pesanan
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

-- 2. Tabel ulasan (akan digabung ke Modul Alfi)
CREATE TABLE IF NOT EXISTS ulasan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    donasi_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (donasi_id) REFERENCES donasi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Tabel notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT NOT NULL,
    link VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- QUERY CEPAT (Opsional - Hapus data sebelumnya jika ingin testing bersih)
-- ============================================================

-- Truncate untuk membersihkan data (PERHATIAN: Hapus semua pesanan)
-- DROP TABLE IF EXISTS ulasan;
-- DROP TABLE IF EXISTS notifikasi;
-- DROP TABLE IF EXISTS pesanan;

-- ============================================================
-- PENJELASAN TABEL
-- ============================================================

-- pesanan:
-- - kode_pesanan: Code API pengambilan (BP-XXXX unik)
-- - user_id: Penerima makanan
-- - donasi_id: Makanan yang dipesan
-- - jumlah_porsi: Kuota porsi
-- - status: Menunggu (belum diambil), Selesai (sudah diambil), Dibatalkan (batal), Kedaluwarsa (masa habis)
-- - waktu_pesan: Waktu pesanan dibuat
-- - waktu_ambil: Waktu pesanan diambil
-- - catatan: Catatan opsional dari penerima

-- ulasan:
-- - Satu pesanan hanya punya 1 ulasan
-- - Rating 1-5 bintang
-- - Terhubung ke pesanan_id untuk memastikan hanya yang selesai yang bisa review

-- notifikasi:
-- - Judul dan pesan sederhana
-- - Link tujuan saat diklik
-- - is_read: 0 = belum dibaca, 1 = sudah dibaca

-- ============================================================
-- TEMPLETED: Tabel persiapan Modul Indra
-- ============================================================