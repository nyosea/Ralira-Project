-- =====================================================
-- Database: ralira_db
-- Deskripsi: Database Biro Psikologi Rali Ra
-- =====================================================

CREATE DATABASE IF NOT EXISTS `ralira_db`;
USE `ralira_db`;

-- =====================================================
-- TABLE 1: users
-- Deskripsi: Tabel pengguna (admin, psikolog, klien)
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255),
  `phone` VARCHAR(15),
  `role` ENUM('admin', 'psychologist', 'client') NOT NULL DEFAULT 'client',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 2: client_details
-- Deskripsi: Detail data klien
-- =====================================================
CREATE TABLE IF NOT EXISTS `client_details` (
  `client_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nik` VARCHAR(20),
  `tempat_lahir` VARCHAR(100),
  `tanggal_lahir` DATE,
  `alamat` TEXT,
  `riwayat_hidup_file` VARCHAR(255),
  `status_pendaftaran` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_user (user_id),
  INDEX idx_status (status_pendaftaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 3: psychologist_profiles
-- Deskripsi: Profil psikolog
-- =====================================================
CREATE TABLE IF NOT EXISTS `psychologist_profiles` (
  `psychologist_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `spesialisasi` VARCHAR(100),
  `nomor_sipp` VARCHAR(50) UNIQUE,
  `bio` LONGTEXT,
  `foto_profil` VARCHAR(255),
  `pengalaman_tahun` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 4: schedules
-- Deskripsi: Jadwal praktik psikolog
-- =====================================================
CREATE TABLE IF NOT EXISTS `schedules` (
  `schedule_id` INT AUTO_INCREMENT PRIMARY KEY,
  `psychologist_id` INT NOT NULL,
  `hari_praktik` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
  INDEX idx_psychologist (psychologist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 5: consultation_bookings
-- Deskripsi: Booking konsultasi
-- =====================================================
CREATE TABLE IF NOT EXISTS `consultation_bookings` (
  `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `psychologist_id` INT NOT NULL,
  `schedule_id` INT NOT NULL,
  `tanggal_konsultasi` DATE NOT NULL,
  `status_booking` ENUM('pending', 'confirmed', 'canceled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES client_details(client_id) ON DELETE CASCADE,
  FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
  FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id) ON DELETE CASCADE,
  INDEX idx_client (client_id),
  INDEX idx_psychologist (psychologist_id),
  INDEX idx_status (status_booking)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 6: payments
-- Deskripsi: Pembayaran konsultasi
-- =====================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `tanggal_transfer` DATE,
  `bukti_transfer` VARCHAR(255),
  `status_pembayaran` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES client_details(client_id) ON DELETE CASCADE,
  INDEX idx_client (client_id),
  INDEX idx_status (status_pembayaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 7: test_results
-- Deskripsi: Hasil tes psikologi
-- =====================================================
CREATE TABLE IF NOT EXISTS `test_results` (
  `result_id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `psychologist_id` INT NOT NULL,
  `jenis_tes` VARCHAR(100),
  `tanggal_pelaksanaan` DATE,
  `file_hasil_tes` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES client_details(client_id) ON DELETE CASCADE,
  FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
  INDEX idx_client (client_id),
  INDEX idx_psychologist (psychologist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 8: education_contents
-- Deskripsi: Konten edukasi / artikel
-- =====================================================
CREATE TABLE IF NOT EXISTS `education_contents` (
  `content_id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(255) NOT NULL,
  `kategori` VARCHAR(100),
  `isi_konten` LONGTEXT,
  `thumbnail` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 9: psychologist_schedule_slots
-- Deskripsi: Slot jam kerja psikolog per hari
-- =====================================================
CREATE TABLE IF NOT EXISTS `psychologist_schedule_slots` (
  `slot_id` INT AUTO_INCREMENT PRIMARY KEY,
  `psychologist_id` INT NOT NULL,
  `hari` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `jam` TIME NOT NULL,
  `is_available` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
  UNIQUE KEY unique_slot (psychologist_id, hari, jam),
  INDEX idx_psychologist (psychologist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 10: psychologist_off_days
-- Deskripsi: Tanggal cuti psikolog
-- =====================================================
CREATE TABLE IF NOT EXISTS `psychologist_off_days` (
  `off_id` INT AUTO_INCREMENT PRIMARY KEY,
  `psychologist_id` INT NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `alasan` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
  INDEX idx_psychologist (psychologist_id),
  INDEX idx_tanggal (tanggal_mulai, tanggal_selesai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 11: psychologist_schedule_dates
-- Deskripsi: Jadwal psikolog berdasarkan tanggal tertentu
-- =====================================================
CREATE TABLE IF NOT EXISTS `psychologist_schedule_dates` (
  `schedule_date_id` INT AUTO_INCREMENT PRIMARY KEY,
  `psychologist_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `is_available` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
  UNIQUE KEY unique_date_time (psychologist_id, tanggal, jam_mulai),
  INDEX idx_psychologist (psychologist_id),
  INDEX idx_tanggal (tanggal),
  INDEX idx_availability (is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 12: booking_riwayat_hidup
-- Deskripsi: Riwayat hidup untuk setiap booking
-- =====================================================
CREATE TABLE IF NOT EXISTS `booking_riwayat_hidup` (
  `rh_id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `keluhan_masalah` TEXT,
  `lama_masalah` ENUM('Baru', '1-3 bulan', '3-6 bulan', 'Lebih dari 6 bulan') NULL,
  `pernah_konsultasi` ENUM('Ya', 'Tidak') NULL,
  `latar_belakang` TEXT,
  `tahu_dari` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES consultation_bookings(booking_id) ON DELETE CASCADE,
  INDEX idx_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ALTER TABLE: Menambahkan kolom ke tabel yang ada
-- =====================================================
-- Tambah field gender ke client_details (jika belum ada)
ALTER TABLE `client_details` 
ADD COLUMN `gender` ENUM('Laki-laki', 'Perempuan') NULL AFTER `alamat`;

-- Tambah kolom jam_konsultasi ke consultation_bookings (jika belum ada)
ALTER TABLE `consultation_bookings` 
ADD COLUMN `jam_konsultasi` TIME AFTER `tanggal_konsultasi`;

-- Tambah index untuk query yang lebih cepat
ALTER TABLE `consultation_bookings`
ADD INDEX idx_date_psychologist (tanggal_konsultasi, psychologist_id);

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Sample Admin User
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`) VALUES
('Admin Rali Ra', 'admin@ralira.com', '$2y$10$8C6yl/h1Jm9gN5qFl7x0KugB6z5hE0lD2yM3pQ4rS5tU6vW7xY8zZ', '081234567890', 'admin');

-- Sample Psikolog User
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`) VALUES
('Dr. Ira Puspitawati', 'ira@ralira.com', '$2y$10$8C6yl/h1Jm9gN5qFl7x0KugB6z5hE0lD2yM3pQ4rS5tU6vW7xY8zZ', '081234567891', 'psychologist');

-- Sample Psychologist Profile
INSERT INTO `psychologist_profiles` (`user_id`, `spesialisasi`, `nomor_sipp`, `bio`, `pengalaman_tahun`) VALUES
(2, 'Psikolog Anak', 'SIPP-12345-2024', 'Psikolog berpengalaman lebih dari 30 tahun', 30);

-- Sample Klien User
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`) VALUES
('Budi Santoso', 'budi@email.com', '$2y$10$8C6yl/h1Jm9gN5qFl7x0KugB6z5hE0lD2yM3pQ4rS5tU6vW7xY8zZ', '081234567892', 'client');

-- Sample Client Details
INSERT INTO `client_details` (`user_id`, `nik`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `status_pendaftaran`) VALUES
(3, '3201050515900001', 'Jakarta', '1990-05-15', 'Jl. Merdeka No. 123, Jakarta', 'verified');

-- Sample Schedule for Psychologist
INSERT INTO `schedules` (`psychologist_id`, `hari_praktik`, `jam_mulai`, `jam_selesai`) VALUES
(1, 'Monday', '09:00:00', '17:00:00'),
(1, 'Wednesday', '09:00:00', '17:00:00'),
(1, 'Friday', '09:00:00', '17:00:00');

-- Sample Education Content
INSERT INTO `education_contents` (`judul`, `kategori`, `isi_konten`) VALUES
('Pentingnya Kesehatan Mental', 'Edukasi', 'Kesehatan mental adalah bagian penting dari kesejahteraan umum...'),
('Cara Mengatasi Stres', 'Tips', 'Berikut adalah beberapa cara efektif untuk mengatasi stres...');
