-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 07 Feb 2026 pada 04.32
-- Versi server: 11.7.2-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ralira_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_riwayat_hidup`
--

CREATE TABLE `booking_riwayat_hidup` (
  `rh_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `keluhan_masalah` text DEFAULT NULL,
  `lama_masalah` enum('Baru','1-3 bulan','3-6 bulan','Lebih dari 6 bulan') DEFAULT NULL,
  `pernah_konsultasi` enum('Ya','Tidak') DEFAULT NULL,
  `latar_belakang` text DEFAULT NULL,
  `tahu_dari` varchar(100) DEFAULT NULL,
  `agama` varchar(15) NOT NULL,
  `suku` varchar(25) NOT NULL,
  `hobi` varchar(100) NOT NULL,
  `alamat_rh` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `booking_riwayat_hidup`
--

INSERT INTO `booking_riwayat_hidup` (`rh_id`, `booking_id`, `keluhan_masalah`, `lama_masalah`, `pernah_konsultasi`, `latar_belakang`, `tahu_dari`, `agama`, `suku`, `hobi`, `alamat_rh`, `created_at`, `updated_at`) VALUES
(7, 9, 'Sakit', 'Baru', 'Ya', 'Sakit', 'Instagram', '', '', '', '', '2026-01-05 19:57:38', '2026-01-05 19:57:38'),
(8, 10, 'CAD', 'Baru', 'Ya', 'ADA', 'Instagram', '', '', '', '', '2026-01-05 20:53:04', '2026-01-05 20:53:04'),
(9, 11, 'asdasd', '1-3 bulan', 'Ya', 'asdasd', 'Google', '', '', '', '', '2026-01-06 08:19:19', '2026-01-06 08:19:19'),
(10, 12, 'Gini', 'Baru', 'Ya', 'Pra', 'Instagram', '', '', '', '', '2026-01-12 01:20:26', '2026-01-12 01:20:26'),
(11, 13, 'Stress', '3-6 bulan', 'Tidak', 'Lorem ipsum', 'Instagram', '', '', '', '', '2026-01-12 04:00:11', '2026-01-12 04:00:11'),
(12, 14, 'Stress', '3-6 bulan', 'Ya', 'Stress', 'Google', '', '', '', '', '2026-01-12 04:09:25', '2026-01-12 04:09:25'),
(13, 15, 'Lorem', '1-3 bulan', 'Ya', 'lorem', 'Google', '', '', '', '', '2026-01-12 04:19:29', '2026-01-12 04:19:29'),
(14, 17, 'tes', 'Baru', 'Tidak', 'apa aja deh', 'Instagram', 'Katholik', 'Jawa', 'Main main', 'Adadeh Pokok nya', '2026-01-14 16:06:53', '2026-01-14 16:06:53'),
(15, 18, 'Gua keren', 'Baru', 'Ya', 'keren', 'Google', 'Kristen', 'Batak', 'Menulis', 'Jalanan', '2026-01-18 07:55:13', '2026-01-18 07:55:13'),
(16, 19, 'Test', '1-3 bulan', 'Ya', 'Test', 'Google', 'Kristen', 'Batak', 'Test', 'Test', '2026-01-18 12:28:10', '2026-01-18 12:28:10'),
(17, 20, 'Test', 'Baru', 'Ya', 'Test', 'Google', 'Islam', 'Test', 'Test', 'Test', '2026-01-18 14:02:27', '2026-01-18 14:02:27'),
(18, 21, 'test', 'Baru', 'Ya', 'test', 'Google', 'Islam', 'test', 'test', 'test', '2026-01-18 15:34:29', '2026-01-18 15:34:29'),
(19, 22, 'test', 'Baru', 'Ya', 'test', 'Google', 'Kristen', 'test', 'test', 'test', '2026-01-20 13:23:52', '2026-01-20 13:23:52'),
(20, 23, 'test', 'Baru', 'Ya', 'test', 'Google', 'Kristen', 'test', 'testv', 'test', '2026-01-20 18:09:33', '2026-01-20 18:09:33'),
(21, 24, 'test', 'Baru', 'Ya', 'test', 'Google', 'Kristen', 'test', 'test', 'test', '2026-01-20 18:19:43', '2026-01-20 18:19:43'),
(22, 25, 'test', 'Baru', 'Ya', 'test', 'Google', 'Kristen', 'test', 'test', 'test', '2026-01-20 18:29:29', '2026-01-20 18:29:29'),
(23, 26, 'test', 'Baru', 'Ya', 'test', 'Instagram', 'Kristen', 'test', 'test', 'test', '2026-01-21 02:14:08', '2026-01-21 02:14:08'),
(24, 27, 'Test', 'Baru', 'Ya', 'Test', 'Instagram', 'Kristen', 'Batak', 'Menulis', 'Kalimalang', '2026-01-21 04:08:12', '2026-01-21 04:08:12'),
(25, 28, 'Test', 'Baru', 'Ya', 'Test', 'Google', 'Kristen', 'Test', 'Test', 'Test', '2026-01-26 03:27:30', '2026-01-26 03:27:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `client_details`
--

CREATE TABLE `client_details` (
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `gender` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `riwayat_hidup_file` varchar(255) DEFAULT NULL,
  `status_pendaftaran` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `client_details`
--

INSERT INTO `client_details` (`client_id`, `user_id`, `nik`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `gender`, `riwayat_hidup_file`, `status_pendaftaran`, `created_at`, `updated_at`) VALUES
(5, 22, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-12-05 03:55:27', '2025-12-05 03:55:27'),
(6, 28, NULL, NULL, NULL, NULL, 'Laki-laki', NULL, 'pending', '2025-12-06 16:57:03', '2026-01-02 16:35:26'),
(7, 29, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-12-09 04:20:55', '2025-12-09 04:20:55'),
(8, 30, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-12-09 04:23:13', '2025-12-09 04:23:13'),
(9, 31, NULL, NULL, NULL, NULL, 'Laki-laki', NULL, 'pending', '2025-12-17 02:20:43', '2025-12-22 17:18:11'),
(10, 32, NULL, NULL, NULL, NULL, 'Laki-laki', NULL, 'pending', '2025-12-18 01:02:32', '2025-12-19 09:33:27'),
(11, 39, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-12-31 05:04:22', '2025-12-31 05:04:22'),
(12, 44, NULL, NULL, NULL, NULL, 'Laki-laki', NULL, 'pending', '2026-01-13 14:06:42', '2026-01-14 07:37:35'),
(13, 46, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2026-01-20 17:39:58', '2026-01-20 17:39:58'),
(14, 47, NULL, NULL, NULL, NULL, 'Laki-laki', NULL, 'pending', '2026-01-20 17:52:31', '2026-01-20 18:09:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `consultation_bookings`
--

CREATE TABLE `consultation_bookings` (
  `booking_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `psychologist_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `tanggal_konsultasi` date NOT NULL,
  `jam_konsultasi` time DEFAULT NULL,
  `status_booking` enum('pending','confirmed','canceled','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `consultation_bookings`
--

INSERT INTO `consultation_bookings` (`booking_id`, `client_id`, `psychologist_id`, `schedule_id`, `tanggal_konsultasi`, `jam_konsultasi`, `status_booking`, `created_at`, `updated_at`) VALUES
(9, 6, 6, 377, '2026-01-07', '13:00:00', 'confirmed', '2026-01-05 19:57:38', '2026-01-06 02:11:47'),
(10, 6, 6, 369, '2026-01-07', '09:00:00', 'confirmed', '2026-01-05 20:53:04', '2026-01-06 02:10:52'),
(11, 6, 6, 393, '2026-01-08', '11:00:00', 'confirmed', '2026-01-06 08:19:19', '2026-01-06 08:22:32'),
(12, 6, 5, 385, '2026-01-28', '11:00:00', 'confirmed', '2026-01-12 01:20:26', '2026-01-12 01:22:50'),
(13, 6, 11, 416, '2026-01-12', '15:00:00', 'confirmed', '2026-01-12 04:00:11', '2026-01-12 04:01:28'),
(14, 9, 5, 386, '2026-01-28', '13:00:00', 'confirmed', '2026-01-12 04:09:25', '2026-01-12 04:10:28'),
(15, 9, 11, 417, '2026-01-19', '09:00:00', 'confirmed', '2026-01-12 04:19:29', '2026-01-12 04:19:48'),
(16, 12, 11, 445, '2026-02-02', '09:00:00', 'confirmed', '2026-01-14 07:37:35', '2026-01-18 12:31:32'),
(17, 12, 11, 448, '2026-02-03', '09:00:00', 'confirmed', '2026-01-14 16:06:53', '2026-01-18 12:31:17'),
(18, 6, 5, 453, '2026-01-18', '15:00:00', 'confirmed', '2026-01-18 07:55:13', '2026-01-18 07:55:41'),
(19, 6, 5, 454, '2026-01-19', '09:00:00', 'confirmed', '2026-01-18 12:28:10', '2026-01-18 12:29:24'),
(20, 6, 5, 457, '2026-01-19', '15:00:00', 'confirmed', '2026-01-18 14:02:27', '2026-01-18 14:38:38'),
(21, 6, 5, 456, '2026-01-19', '13:00:00', 'confirmed', '2026-01-18 15:34:29', '2026-01-18 15:37:06'),
(22, 6, 5, 462, '2026-01-21', '09:00:00', 'confirmed', '2026-01-20 13:23:52', '2026-01-20 18:12:39'),
(23, 14, 5, 472, '2026-01-22', '13:00:00', 'rejected', '2026-01-20 18:09:33', '2026-01-20 18:18:17'),
(24, 14, 5, 473, '2026-01-22', '15:00:00', 'confirmed', '2026-01-20 18:19:43', '2026-01-20 18:28:32'),
(25, 14, 5, 476, '2026-01-23', '15:00:00', 'confirmed', '2026-01-20 18:29:29', '2026-01-20 18:29:58'),
(26, 6, 5, 471, '2026-01-22', '11:00:00', 'confirmed', '2026-01-21 02:14:08', '2026-01-21 02:16:16'),
(27, 6, 5, 470, '2026-01-22', '09:00:00', 'confirmed', '2026-01-21 04:08:12', '2026-01-21 04:09:24'),
(28, 6, 5, 494, '2026-01-27', '09:00:00', 'confirmed', '2026-01-26 03:27:30', '2026-01-26 03:28:28');

--
-- Trigger `consultation_bookings`
--
DELIMITER $$
CREATE TRIGGER `update_has_booking_on_booking_delete` AFTER DELETE ON `consultation_bookings` FOR EACH ROW BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM consultation_bookings 
        WHERE schedule_id = OLD.schedule_id 
        AND status_booking != 'canceled'
    ) THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 0 
        WHERE schedule_date_id = OLD.schedule_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_has_booking_on_booking_insert` AFTER INSERT ON `consultation_bookings` FOR EACH ROW BEGIN
    IF NEW.status_booking != 'canceled' THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 1 
        WHERE schedule_date_id = NEW.schedule_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_has_booking_on_booking_update` AFTER UPDATE ON `consultation_bookings` FOR EACH ROW BEGIN
    IF NEW.status_booking = 'canceled' THEN
        IF NOT EXISTS (
            SELECT 1 FROM consultation_bookings 
            WHERE schedule_id = NEW.schedule_id 
            AND status_booking != 'canceled' 
            AND booking_id != NEW.booking_id
        ) THEN
            UPDATE psychologist_schedule_dates 
            SET has_booking = 0 
            WHERE schedule_date_id = NEW.schedule_id;
        END IF;
    ELSEIF NEW.status_booking != 'canceled' THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 1 
        WHERE schedule_date_id = NEW.schedule_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `consultation_status`
--

CREATE TABLE `consultation_status` (
  `status_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `psychologist_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `konsultasi_status` enum('belum_ditangani','sedang_ditangani','sudah_ditangani') DEFAULT 'belum_ditangani',
  `updated_by_user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `consultation_status`
--

INSERT INTO `consultation_status` (`status_id`, `booking_id`, `psychologist_id`, `client_id`, `konsultasi_status`, `updated_by_user_id`, `updated_at`, `created_at`) VALUES
(1, 18, 5, 6, 'sudah_ditangani', 33, '2026-01-18 12:36:40', '2026-01-18 08:06:10'),
(2, 19, 5, 6, 'sudah_ditangani', NULL, '2026-01-18 12:36:00', '2026-01-18 12:36:00'),
(3, 14, 5, 9, 'sudah_ditangani', NULL, '2026-01-18 12:36:10', '2026-01-18 12:36:10'),
(4, 17, 11, 12, 'sudah_ditangani', NULL, '2026-01-18 13:08:53', '2026-01-18 13:03:08'),
(5, 13, 11, 6, 'sudah_ditangani', NULL, '2026-01-18 13:03:13', '2026-01-18 13:03:13'),
(6, 15, 11, 9, 'sudah_ditangani', NULL, '2026-01-18 13:03:16', '2026-01-18 13:03:16'),
(7, 16, 11, 12, 'sudah_ditangani', NULL, '2026-01-18 13:03:19', '2026-01-18 13:03:19'),
(8, 12, 5, 6, 'sudah_ditangani', NULL, '2026-01-18 14:10:38', '2026-01-18 14:09:51'),
(9, 20, 5, 6, 'sudah_ditangani', NULL, '2026-01-18 15:21:24', '2026-01-18 14:39:56'),
(10, 21, 5, 6, 'sudah_ditangani', NULL, '2026-01-18 15:44:58', '2026-01-18 15:39:56'),
(11, 24, 5, 14, 'sudah_ditangani', NULL, '2026-01-20 18:31:02', '2026-01-20 18:30:56'),
(12, 22, 5, 6, 'sudah_ditangani', NULL, '2026-01-21 02:17:25', '2026-01-21 02:17:20'),
(13, 26, 5, 6, 'sudah_ditangani', NULL, '2026-01-21 04:16:58', '2026-01-21 04:16:37'),
(14, 28, 5, 6, 'sudah_ditangani', NULL, '2026-01-26 03:37:43', '2026-01-26 03:37:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `education_contents`
--

CREATE TABLE `education_contents` (
  `content_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `isi_konten` longtext DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `education_contents`
--

INSERT INTO `education_contents` (`content_id`, `judul`, `kategori`, `isi_konten`, `thumbnail`, `created_at`, `updated_at`) VALUES
(1, 'Pentingnya Kesehatan Mental', 'Edukasi', 'Kesehatan mental adalah bagian penting dari kesejahteraan umum...', NULL, '2025-12-03 08:42:12', '2025-12-03 08:42:12'),
(2, 'Cara Mengatasi Stres', 'Tips', 'Berikut adalah beberapa cara efektif untuk mengatasi stres...', NULL, '2025-12-03 08:42:12', '2025-12-03 08:42:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `psychologist_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_price` decimal(10,2) NOT NULL,
  `total_payment` decimal(10,2) NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `invoice_date` date NOT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `psychologist_id`, `service_name`, `service_price`, `total_payment`, `payment_method`, `invoice_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(3, 'INV-20260119-8969', 28, 34, 'Konsultasi Keren', 50000000.00, 50000000.00, 'Transfer Bank', '2026-01-19', 'pending', 'Keren banget bang', '2026-01-19 06:34:48', '2026-01-19 06:34:48'),
(4, 'TEST-20260119-001', 22, 33, 'Konsultasi Psikologi Individu', 750000.00, 750000.00, 'Transfer Bank', '2026-01-19', 'pending', 'Test invoice untuk demonstrasi', '2026-01-19 06:43:20', '2026-01-19 06:43:20'),
(5, 'INV-20260119-7874', 28, 33, 'Konsultasi Keren', 300000.00, 300000.00, 'Tunai', '2026-01-19', 'pending', 'Test', '2026-01-19 06:46:25', '2026-01-19 06:46:25'),
(6, 'INV-20260119-2207', 28, 33, 'Konsultasi Keren', 300000.00, 300000.00, 'Tunai', '2026-01-19', 'paid', 'Test', '2026-01-19 07:07:32', '2026-01-21 02:20:09'),
(7, 'INV-20260121-7575', 28, 33, 'Kosultasi Percintaan', 100000.00, 100000.00, 'Tunai', '2026-01-21', 'pending', 'Testt Test', '2026-01-21 02:21:37', '2026-01-21 02:21:37'),
(8, 'INV-20260121-3601', 28, 33, 'Konsultasi ', 400000.00, 400000.00, 'Tunai', '2026-01-21', 'paid', 'Test', '2026-01-21 04:20:39', '2026-01-21 04:21:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `landing_photos`
--

CREATE TABLE `landing_photos` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `landing_photos`
--

INSERT INTO `landing_photos` (`id`, `name`, `specialization`, `experience`, `bio`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Dr. Ira Puspitawati', 'Psikolog Anak & Owner', '30+ Tahun Pengalaman', '', 'psychologists/sample1.jpg', 'active', '2026-01-04 12:26:45', '2026-01-04 15:38:10'),
(2, 'Bu Nurul', 'Psikolog Industri', 'Spesialis Rekrutmen', '', 'psychologists/1767611019_landing_Screenshot 2025-03-22 232758.png', 'active', '2026-01-04 12:26:45', '2026-01-05 11:03:39'),
(3, 'Bu Claudia', 'Psikolog Remaja', 'Konseling Online', '', 'psychologists/1767542128_landing_Screenshot 2025-03-22 232758.png', 'active', '2026-01-04 12:26:45', '2026-01-04 15:55:28'),
(4, 'Pak Refandi', 'Psikolog Dewasa', 'Masalah Karir', 'Spesialis dalam psikologi dewasa dengan fokus pada masalah karir dan pengembangan diri.', 'psychologists/sample4.jpg', 'active', '2026-01-04 12:26:45', '2026-01-04 12:26:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `tanggal_transfer` date DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `status_pembayaran` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`payment_id`, `client_id`, `tanggal_transfer`, `bukti_transfer`, `status_pembayaran`, `created_at`, `updated_at`) VALUES
(1, 10, '2025-12-19', 'uploads/payments/payment-1-1766136807.png', 'pending', '2025-12-19 09:33:27', '2025-12-19 09:33:27'),
(2, 10, '2025-12-19', 'uploads/payments/payment-2-1766138548.png', 'pending', '2025-12-19 10:02:28', '2025-12-19 10:02:28'),
(3, 9, '2025-12-22', 'uploads/payments/payment-3-1766423891.png', 'pending', '2025-12-22 17:18:11', '2025-12-22 17:18:11'),
(4, 9, '2026-01-02', 'uploads/payments/payment-6-1767336157.png', 'pending', '2026-01-02 06:42:37', '2026-01-02 06:42:37'),
(5, 6, '2026-01-02', 'uploads/payments/payment-7-1767371726.png', 'pending', '2026-01-02 16:35:26', '2026-01-02 16:35:26'),
(6, 6, '2026-01-05', 'uploads/payments/payment-8-1767601255.png', 'pending', '2026-01-05 08:20:55', '2026-01-05 08:20:55'),
(7, 6, '2026-01-05', 'uploads/payments/payment-9-1767643058.png', 'pending', '2026-01-05 19:57:38', '2026-01-05 19:57:38'),
(8, 6, '2026-01-05', 'uploads/payments/payment-10-1767646384.png', 'pending', '2026-01-05 20:53:04', '2026-01-05 20:53:04'),
(9, 6, '2026-01-06', 'uploads/payments/payment-11-1767687559.png', 'pending', '2026-01-06 08:19:19', '2026-01-06 08:19:19'),
(10, 6, '2026-01-12', 'uploads/payments/payment-12-1768180826.png', 'pending', '2026-01-12 01:20:26', '2026-01-12 01:20:26'),
(11, 6, '2026-01-12', 'uploads/payments/payment-13-1768190411.png', 'pending', '2026-01-12 04:00:11', '2026-01-12 04:00:11'),
(12, 9, '2026-01-12', 'uploads/payments/payment-14-1768190965.png', 'pending', '2026-01-12 04:09:25', '2026-01-12 04:09:25'),
(13, 9, '2026-01-12', 'uploads/payments/payment-15-1768191569.png', 'pending', '2026-01-12 04:19:29', '2026-01-12 04:19:29'),
(14, 12, '2026-01-14', 'uploads/payments/payment-16-1768376255.jpg', 'pending', '2026-01-14 07:37:36', '2026-01-14 07:37:36'),
(15, 12, '2026-01-14', 'uploads/payments/payment-17-1768406814.jpg', 'pending', '2026-01-14 16:06:54', '2026-01-14 16:06:54'),
(16, 6, '2026-01-18', 'uploads/payments/payment-18-1768722913.png', 'pending', '2026-01-18 07:55:13', '2026-01-18 07:55:13'),
(17, 6, '2026-01-18', 'uploads/payments/payment-19-1768739290.png', 'pending', '2026-01-18 12:28:10', '2026-01-18 12:28:10'),
(18, 6, '2026-01-18', 'uploads/payments/payment-20-1768744947.png', 'pending', '2026-01-18 14:02:27', '2026-01-18 14:02:27'),
(19, 6, '2026-01-18', 'uploads/payments/payment-21-1768750469.png', 'pending', '2026-01-18 15:34:29', '2026-01-18 15:34:29'),
(20, 6, '2026-01-20', 'uploads/payments/payment-22-1768915432.png', 'pending', '2026-01-20 13:23:52', '2026-01-20 13:23:52'),
(21, 14, '2026-01-20', 'uploads/payments/payment-23-1768932573.png', 'pending', '2026-01-20 18:09:33', '2026-01-20 18:09:33'),
(22, 14, '2026-01-20', 'uploads/payments/payment-24-1768933183.png', 'pending', '2026-01-20 18:19:43', '2026-01-20 18:19:43'),
(23, 14, '2026-01-20', 'uploads/payments/payment-25-1768933769.png', 'pending', '2026-01-20 18:29:29', '2026-01-20 18:29:29'),
(24, 6, '2026-01-21', 'uploads/payments/payment-26-1768961648.pdf', 'pending', '2026-01-21 02:14:08', '2026-01-21 02:14:08'),
(25, 6, '2026-01-21', 'uploads/payments/payment-27-1768968492.pdf', 'pending', '2026-01-21 04:08:12', '2026-01-21 04:08:12'),
(26, 6, '2026-01-26', 'uploads/payments/payment-28-1769398050.png', 'pending', '2026-01-26 03:27:30', '2026-01-26 03:27:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `psychologist_off_days`
--

CREATE TABLE `psychologist_off_days` (
  `off_id` int(11) NOT NULL,
  `psychologist_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `psychologist_profiles`
--

CREATE TABLE `psychologist_profiles` (
  `psychologist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spesialisasi` varchar(100) DEFAULT NULL,
  `nomor_sipp` varchar(50) DEFAULT NULL,
  `bio` longtext DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `pengalaman_tahun` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `photo` varchar(255) DEFAULT 'psikolog1.jpg',
  `show_on_landing` tinyint(1) DEFAULT 0 COMMENT 'Tampilkan di landing page (1=ya, 0=tidak)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `psychologist_profiles`
--

INSERT INTO `psychologist_profiles` (`psychologist_id`, `user_id`, `spesialisasi`, `nomor_sipp`, `bio`, `foto_profil`, `pengalaman_tahun`, `created_at`, `updated_at`, `photo`, `show_on_landing`) VALUES
(5, 33, 'Anak, Keluarga, Couple Therapy', '19980035-2023-02-1831', 'Psikolog\r\nCertified AJT Cognitive-Test\r\nDiagnosis perilaku anak, remaja, dan keluarga di era digital\r\nCouple Therapy & Scientific Hypnotherapy\r\nKonsultasi offline & online', 'psychologists/1769439744_Screenshot 2026-01-26 220046.png', 7, '2025-12-19 04:32:23', '2026-01-26 15:02:24', 'psikolog1.jpg', 1),
(6, 34, 'Psikolog Klinis', '20220933-2022-01-0163', 'Menangani gangguan kepribadian, kecemasan, depresi, serta masalah karier dan bakat.\r\nMelayani konsultasi offline & online dan Home Visit Khusus daerah Tangerang dan sekitar nya', 'psychologists/1769439690_Screenshot 2026-01-26 220046.png', 5, '2025-12-19 04:36:19', '2026-01-26 15:01:30', 'psychologist-34-1767587921.png', 1),
(7, 35, 'Industrial & Organisasi', '20150464-2023-01-1595', 'Psikolog\r\nPsikologi Organisasi dan Industri\r\nKonseling berbasis psikologi positif & psikologi Islam\r\nMelayani konsultasi offline, online, dan home visit\r\n(khusus Jakarta Timur & Bekasi Barat)', 'psychologists/1769439770_Screenshot 2026-01-26 220046.png', 5, '2025-12-19 04:37:55', '2026-01-26 15:02:50', 'psikolog1.jpg', 1),
(9, 37, 'Dewasa (non-pernikahan)', '20240954-2024-01-5480', 'Berdedikasi dalam membantu individu mengatasi tantangan kesehatan mental dan menemukan potensi terbaik mereka. Spesialis dalam menangani Depresi, Anxiety, dan Stres Pasca Trauma (PTSD). Selain fokus pada pemulihan, saya juga melayani konsultasi Minat dan Bakat untuk perencanaan masa depan yang lebih terarah.\r\n\r\n📍 Melayani Konsultasi: Online, Offline, & Home Visit (Khusus wilayah Jabodetabek).', 'psychologists/1769439824_Screenshot 2026-01-26 220046.png', 0, '2025-12-19 04:39:44', '2026-01-26 15:03:49', 'psikolog1.jpg', 1),
(10, 41, 'Psikolog Anak dan Remaja', '20251016-2025-01-1387', 'Psikolog Klinis Anak & Remaja\r\nMendampingi tumbuh kembang anak dan remaja dengan empati dan pendekatan ilmiah.\r\nMelayani konseling perkembangan, parenting, dan regulasi emosi.\r\n📍 Offline & Online', 'psychologists/1769439800_Screenshot 2026-01-26 220046.png', 0, '2026-01-06 20:08:46', '2026-01-26 15:03:20', 'psikolog1.jpg', 1),
(11, 42, 'Pengembangan Diri', '2819-22-2-2', 'Psikolog\r\nFokus pengembangan diri, anxiety, dan depresi\r\nMelayani konsultasi offline, online, dan home visit\r\n(khusus Jakarta Timur & Bekasi Barat)', 'psychologists/1769439710_Screenshot 2026-01-26 220046.png', 7, '2026-01-09 08:51:45', '2026-01-26 15:01:50', 'psikolog1.jpg', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `psychologist_schedule_dates`
--

CREATE TABLE `psychologist_schedule_dates` (
  `schedule_date_id` int(11) NOT NULL,
  `psychologist_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `has_booking` tinyint(1) DEFAULT 0 COMMENT '1 = has active booking, 0 = no booking'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `psychologist_schedule_dates`
--

INSERT INTO `psychologist_schedule_dates` (`schedule_date_id`, `psychologist_id`, `tanggal`, `jam_mulai`, `jam_selesai`, `created_at`, `updated_at`, `has_booking`) VALUES
(380, 6, '2026-01-07', '09:00:00', '11:00:00', '2026-01-06 06:08:14', '2026-01-06 06:08:14', 0),
(381, 6, '2026-01-07', '11:00:00', '13:00:00', '2026-01-06 06:08:14', '2026-01-06 06:08:14', 0),
(382, 6, '2026-01-07', '13:00:00', '15:00:00', '2026-01-06 06:08:14', '2026-01-06 06:08:14', 0),
(383, 6, '2026-01-07', '15:00:00', '17:00:00', '2026-01-06 06:08:14', '2026-01-06 06:08:14', 0),
(384, 5, '2026-01-28', '09:00:00', '11:00:00', '2026-01-06 06:59:47', '2026-01-06 06:59:47', 0),
(385, 5, '2026-01-28', '11:00:00', '13:00:00', '2026-01-06 06:59:47', '2026-01-12 01:20:26', 1),
(386, 5, '2026-01-28', '13:00:00', '15:00:00', '2026-01-06 06:59:47', '2026-01-12 04:09:25', 1),
(387, 5, '2026-01-28', '15:00:00', '17:00:00', '2026-01-06 06:59:47', '2026-01-06 06:59:47', 0),
(388, 5, '2026-01-07', '09:00:00', '11:00:00', '2026-01-06 07:18:41', '2026-01-06 07:18:41', 0),
(389, 5, '2026-01-07', '11:00:00', '13:00:00', '2026-01-06 07:18:41', '2026-01-06 07:18:41', 0),
(392, 6, '2026-01-08', '09:00:00', '11:00:00', '2026-01-06 07:48:37', '2026-01-06 07:48:37', 0),
(393, 6, '2026-01-08', '11:00:00', '13:00:00', '2026-01-06 07:48:37', '2026-01-06 08:19:19', 1),
(396, 6, '2026-01-09', '09:00:00', '11:00:00', '2026-01-06 07:48:37', '2026-01-06 07:48:37', 0),
(397, 6, '2026-01-09', '11:00:00', '13:00:00', '2026-01-06 07:48:37', '2026-01-06 07:48:37', 0),
(398, 6, '2026-01-09', '13:00:00', '15:00:00', '2026-01-06 07:48:37', '2026-01-06 07:48:37', 0),
(399, 6, '2026-01-09', '15:00:00', '17:00:00', '2026-01-06 07:48:37', '2026-01-06 07:48:37', 0),
(400, 6, '2026-01-06', '15:00:00', '17:00:00', '2026-01-06 08:05:45', '2026-01-06 08:05:45', 0),
(403, 7, '2026-01-11', '09:00:00', '11:00:00', '2026-01-10 09:28:12', '2026-01-10 09:28:12', 0),
(404, 7, '2026-01-11', '11:00:00', '13:00:00', '2026-01-10 09:28:12', '2026-01-10 09:28:12', 0),
(407, 7, '2026-01-12', '09:00:00', '11:00:00', '2026-01-10 09:28:12', '2026-01-10 09:28:12', 0),
(408, 7, '2026-01-12', '11:00:00', '13:00:00', '2026-01-10 09:28:12', '2026-01-10 09:28:12', 0),
(409, 7, '2026-01-12', '13:00:00', '15:00:00', '2026-01-10 09:28:12', '2026-01-10 09:28:12', 0),
(410, 7, '2026-01-12', '15:00:00', '17:00:00', '2026-01-10 09:28:12', '2026-01-10 09:28:12', 0),
(411, 6, '2026-01-12', '09:00:00', '11:00:00', '2026-01-11 08:02:02', '2026-01-11 08:02:02', 0),
(412, 6, '2026-01-12', '11:00:00', '13:00:00', '2026-01-11 08:02:03', '2026-01-11 08:02:03', 0),
(413, 6, '2026-01-12', '13:00:00', '15:00:00', '2026-01-11 08:02:03', '2026-01-11 08:02:03', 0),
(414, 6, '2026-01-12', '15:00:00', '17:00:00', '2026-01-11 08:02:03', '2026-01-11 08:02:03', 0),
(416, 11, '2026-01-12', '15:00:00', '17:00:00', '2026-01-12 03:58:39', '2026-01-12 04:00:11', 1),
(417, 11, '2026-01-19', '09:00:00', '11:00:00', '2026-01-12 04:18:42', '2026-01-12 04:19:29', 1),
(445, 11, '2026-02-02', '09:00:00', '11:00:00', '2026-01-13 16:57:27', '2026-01-14 07:37:35', 1),
(448, 11, '2026-02-03', '09:00:00', '11:00:00', '2026-01-14 16:04:52', '2026-01-14 16:06:53', 1),
(449, 9, '2026-01-18', '09:00:00', '11:00:00', '2026-01-17 06:23:20', '2026-01-17 06:23:20', 0),
(450, 9, '2026-01-18', '11:00:00', '13:00:00', '2026-01-17 06:23:20', '2026-01-17 06:23:20', 0),
(451, 9, '2026-01-18', '13:00:00', '15:00:00', '2026-01-17 06:23:20', '2026-01-17 06:23:20', 0),
(452, 9, '2026-01-18', '15:00:00', '17:00:00', '2026-01-17 06:23:20', '2026-01-17 06:23:20', 0),
(453, 5, '2026-01-18', '15:00:00', '17:00:00', '2026-01-18 07:53:50', '2026-01-18 07:55:13', 1),
(454, 5, '2026-01-19', '09:00:00', '11:00:00', '2026-01-18 12:26:04', '2026-01-18 12:28:10', 1),
(455, 5, '2026-01-19', '11:00:00', '13:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(456, 5, '2026-01-19', '13:00:00', '15:00:00', '2026-01-18 12:26:04', '2026-01-18 15:34:29', 1),
(457, 5, '2026-01-19', '15:00:00', '17:00:00', '2026-01-18 12:26:04', '2026-01-18 14:02:27', 1),
(458, 5, '2026-01-20', '09:00:00', '11:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(459, 5, '2026-01-20', '11:00:00', '13:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(460, 5, '2026-01-20', '13:00:00', '15:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(461, 5, '2026-01-20', '15:00:00', '17:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(462, 5, '2026-01-21', '09:00:00', '11:00:00', '2026-01-18 12:26:04', '2026-01-20 13:23:52', 1),
(463, 5, '2026-01-21', '11:00:00', '13:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(464, 5, '2026-01-21', '13:00:00', '15:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(465, 5, '2026-01-21', '15:00:00', '17:00:00', '2026-01-18 12:26:04', '2026-01-18 12:26:04', 0),
(466, 11, '2026-01-21', '09:00:00', '11:00:00', '2026-01-20 13:22:02', '2026-01-20 13:22:02', 0),
(467, 11, '2026-01-21', '11:00:00', '13:00:00', '2026-01-20 13:22:02', '2026-01-20 13:22:02', 0),
(468, 11, '2026-01-21', '13:00:00', '15:00:00', '2026-01-20 13:22:02', '2026-01-20 13:22:02', 0),
(469, 11, '2026-01-21', '15:00:00', '17:00:00', '2026-01-20 13:22:02', '2026-01-20 13:22:02', 0),
(470, 5, '2026-01-22', '09:00:00', '11:00:00', '2026-01-20 17:11:44', '2026-01-21 04:08:12', 1),
(471, 5, '2026-01-22', '11:00:00', '13:00:00', '2026-01-20 17:11:44', '2026-01-21 02:14:08', 1),
(472, 5, '2026-01-22', '13:00:00', '15:00:00', '2026-01-20 17:11:44', '2026-01-20 18:09:33', 1),
(473, 5, '2026-01-22', '15:00:00', '17:00:00', '2026-01-20 17:11:44', '2026-01-20 18:19:43', 1),
(474, 5, '2026-01-23', '09:00:00', '11:00:00', '2026-01-20 17:18:06', '2026-01-20 17:18:06', 0),
(475, 5, '2026-01-23', '11:00:00', '13:00:00', '2026-01-20 17:18:06', '2026-01-20 17:18:06', 0),
(476, 5, '2026-01-23', '15:00:00', '17:00:00', '2026-01-20 17:18:06', '2026-01-20 18:29:29', 1),
(481, 11, '2026-01-23', '09:00:00', '11:00:00', '2026-01-21 02:36:09', '2026-01-21 02:36:09', 0),
(482, 11, '2026-01-23', '11:00:00', '13:00:00', '2026-01-21 02:36:09', '2026-01-21 02:36:09', 0),
(483, 11, '2026-01-23', '13:00:00', '15:00:00', '2026-01-21 02:36:09', '2026-01-21 02:36:09', 0),
(484, 11, '2026-01-23', '15:00:00', '17:00:00', '2026-01-21 02:36:09', '2026-01-21 02:36:09', 0),
(486, 5, '2026-01-25', '09:00:00', '11:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(487, 5, '2026-01-25', '11:00:00', '13:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(488, 5, '2026-01-25', '13:00:00', '15:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(489, 5, '2026-01-25', '15:00:00', '17:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(490, 5, '2026-01-26', '09:00:00', '11:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(491, 5, '2026-01-26', '11:00:00', '13:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(492, 5, '2026-01-26', '13:00:00', '15:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(493, 5, '2026-01-26', '15:00:00', '17:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(494, 5, '2026-01-27', '09:00:00', '11:00:00', '2026-01-21 04:13:08', '2026-01-26 03:27:30', 1),
(495, 5, '2026-01-27', '11:00:00', '13:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(496, 5, '2026-01-27', '13:00:00', '15:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0),
(497, 5, '2026-01-27', '15:00:00', '17:00:00', '2026-01-21 04:13:08', '2026-01-21 04:13:08', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `psychologist_id` int(11) NOT NULL,
  `hari_praktik` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `test_results`
--

CREATE TABLE `test_results` (
  `result_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `psychologist_id` int(11) NOT NULL,
  `jenis_tes` varchar(100) DEFAULT NULL,
  `tanggal_pelaksanaan` date DEFAULT NULL,
  `file_hasil_tes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `test_results`
--

INSERT INTO `test_results` (`result_id`, `client_id`, `psychologist_id`, `jenis_tes`, `tanggal_pelaksanaan`, `file_hasil_tes`, `created_at`, `updated_at`) VALUES
(1, 6, 5, 'konseling', '2026-01-19', 'result_6_1768789664.pdf', '2026-01-19 02:27:44', '2026-01-19 02:27:44'),
(2, 9, 5, 'konseling', '2026-01-19', 'result_9_1768790079.pdf', '2026-01-19 02:34:39', '2026-01-19 02:34:39'),
(3, 6, 5, 'iq', '2026-01-19', 'result_6_1768790107.pdf', '2026-01-19 02:35:07', '2026-01-19 02:35:07'),
(4, 6, 5, 'iq', '2026-01-19', 'result_6_1768790199.pdf', '2026-01-19 02:36:39', '2026-01-19 02:36:39'),
(5, 9, 5, 'konseling', '2026-01-19', 'result_9_1768793042.pdf', '2026-01-19 03:24:02', '2026-01-19 03:24:02'),
(6, 6, 5, 'iq', '2026-01-19', 'result_6_1768793091.pdf', '2026-01-19 03:24:51', '2026-01-19 03:24:51'),
(7, 6, 5, 'konseling', '2026-01-21', 'result_6_1768928694.pdf', '2026-01-20 17:04:54', '2026-01-20 17:04:54'),
(8, 6, 5, 'konseling', '2026-01-21', 'result_6_1768961895.pdf', '2026-01-21 02:18:15', '2026-01-21 02:18:15'),
(9, 6, 5, 'konseling', '2026-01-22', 'result_6_1768968909.pdf', '2026-01-21 04:15:09', '2026-01-21 04:15:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('admin','psychologist','client') NOT NULL DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `google_id` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `role`, `created_at`, `updated_at`, `google_id`, `profile_picture`) VALUES
(22, 'Jonathan Lucas', 'jocas030804@gmail.com', '$2y$10$.pK.pjjm.NAihj6CJZsRpOgarNidpCQ9cGuyLKjY9qGQ7rjknd3TW', NULL, 'client', '2025-12-05 03:55:27', '2025-12-05 03:55:27', '104360868525684142405', 'https://lh3.googleusercontent.com/a/ACg8ocLRl81cCEsEesDPYEqKVIAk94rmm7PP3dmtn2cXgBUiUNC6DUk=s96-c'),
(23, 'Admin', 'admin@ralira.com', '$2y$12$U53gkqWHaGp7mbLFzBrNYePJFFsuIwFc79Hh4uyiSSPkidh7wc1cW', '081234567890', 'admin', '2025-12-05 04:00:19', '2026-01-06 20:05:12', NULL, NULL),
(28, 'Joshua', 'thomasselaluberuntung@gmail.com', '$2y$10$VEEW18C69ZNM7PuJgdeoTOVPIdjvOfTCUoFQi9u4nqu8e5pVn1hp2', '081558003083', 'client', '2025-12-06 16:57:03', '2026-01-26 05:00:11', '100489409172165723373', NULL),
(29, 'Wahyoo Sudiwan', 'Wahyo@yahoo.com', '$2y$10$6stC.fhMN0DAguQvYdMC.eEusYRo.mNzYqb287EfZiqate42GplQy', '081209877890', 'client', '2025-12-09 04:20:55', '2025-12-09 04:20:55', NULL, NULL),
(30, 'Testsatu', 'Tetsuu@yahoo.com', '$2y$10$.H/vLSgrTyRdewxu3YDBX.vycK45DLuGdVBEpPnDIXJHFJrHk5BSq', '08120095678', 'client', '2025-12-09 04:23:13', '2025-12-09 04:23:13', NULL, NULL),
(31, 'Thomas Sharon Hizkia', 'lookbehindme384@gmail.com', '$2y$10$w9nts7/M6YOExGgl3CYWIOUoQ73qUzYWFMNrnR05cUjEYzFt2QXm6', '081267896789', 'client', '2025-12-17 02:20:40', '2025-12-22 17:18:11', '108795926262066060779', 'https://lh3.googleusercontent.com/a/ACg8ocIeqQU4Dz_AX3y_QlVjCFGqf2kQ7vy519A9H0_AOrscfCgG_l8=s96-c'),
(32, 'Lutfi Saoqi', 'kazenosanemi@gmail.com', '$2y$10$TuHcqDkeUOKQTaUVNfcOjO6RPtqMoJBn3s2IplvjzNDlnu0.1sdQG', '087112341234', 'client', '2025-12-18 01:02:32', '2025-12-19 10:10:45', '111859459494875623353', 'uploads/profile_pics/pp-32-1766139045.png'),
(33, 'Ira Puspitawati', 'ira@gmail.com', '$2y$10$Y/V9FDkuTJzc1gOIvzVZ7uUKnHPIkw.kK9hjy3N0POQuBxFnPoSqq', '081278904567', 'psychologist', '2025-12-19 04:32:23', '2025-12-19 04:32:23', NULL, NULL),
(34, 'Claudia Morin', 'Claudia@gmail.com', '$2y$10$ZMOrHd3H3d53Tk1RKDvugOC3S649tZvN7SMofr0Ey71SojlclIXMK', '081909877890', 'psychologist', '2025-12-19 04:36:19', '2026-01-13 15:01:52', NULL, 'uploads/profile_pics/pp-34-1767589397.png'),
(35, 'Nurul Qomariyah', 'Nurul@gmail.com', '$2y$10$iiJAS3LsMCpqfeM9ugiLoeu..Pv4eowki6JBXElzZZdiTRZUwPWmO', '085612341234', 'psychologist', '2025-12-19 04:37:55', '2026-01-13 15:09:23', NULL, NULL),
(37, 'Refandi Irfan Faisal', 'refandi@gmail.com', '$2y$10$LadO0TEQIzt48Q6jYjjMY.ro2ISiSQWJTgz79ZKnyfT1hqaOeU9qG', '081234567891', 'psychologist', '2025-12-19 04:39:44', '2026-01-13 15:15:59', NULL, NULL),
(39, 'Frost EXE', 'thomasshpakpahan384@gmail.com', '$2y$10$.qbQt5pzPJ3SKfE4eqzhNez5Y.SsbZh8O.YSkP4FZp56oFujkg14.', NULL, 'client', '2025-12-31 05:04:22', '2025-12-31 05:04:22', '111286010736007137608', 'https://lh3.googleusercontent.com/a/ACg8ocJWlQdbJIoBV0uKkD7JBzJXkTHWJc7H9P-oydmz5wl2ufsXzQ=s96-c'),
(41, 'Ratriana Naila Syafira', 'ratriana@psychologist.com', '$2y$12$ck8jd5GMMj4J4OjD0e9NuO2OpaepIv58BTMDVoIP8JoY/yPpavo/S', '08123456789', 'psychologist', '2026-01-06 20:08:46', '2026-01-13 14:51:35', NULL, NULL),
(42, 'Adisti Natali', 'lagiruk@gmail.com', '$2y$10$90aC0AlPe4vFguO0L7iJU.Zu4NkwWDbf9yTBH0lZIeEoottOwL5CS', '081558003082', 'psychologist', '2026-01-09 08:51:45', '2026-01-13 15:11:22', NULL, NULL),
(44, 'Damario Immanuel', 'damarioimmanuel76@gmail.com', '$2y$10$R4DtTGDCSR1RaT7A53xC3OIh7p1Ayab02wTAQcbz/FJYapwSW1WvK', '081558003082', 'client', '2026-01-13 14:06:42', '2026-01-14 07:37:35', '117071135818262775337', 'https://lh3.googleusercontent.com/a/ACg8ocIt9qfpwhq8HmXhbR-f-wPKc9ONVu_z_KjcZUKzDN6B7p2XunV_=s96-c'),
(46, 'Saiko', 'Saiko@gmail.com', '$2y$10$zPdR6qpWD5zsdPhd3oSw7.9M3NJKQ2/1qPlV0k4rf2CUZ5Ef79SuO', '081689028902', 'client', '2026-01-20 17:39:58', '2026-01-20 17:39:58', NULL, NULL),
(47, 'simon', 'simonsimanjuntak270904@gmail.com', '$2y$10$.r2xvRx.KEisGG9jAKKhTu1.QzOJaE/M2OGlUS3erjM6710t973gC', '081234567890', 'client', '2026-01-20 17:52:31', '2026-01-20 17:52:31', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking_riwayat_hidup`
--
ALTER TABLE `booking_riwayat_hidup`
  ADD PRIMARY KEY (`rh_id`),
  ADD KEY `idx_booking` (`booking_id`);

--
-- Indeks untuk tabel `client_details`
--
ALTER TABLE `client_details`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD KEY `idx_status` (`status_pendaftaran`);

--
-- Indeks untuk tabel `consultation_bookings`
--
ALTER TABLE `consultation_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `idx_client` (`client_id`),
  ADD KEY `idx_psychologist` (`psychologist_id`),
  ADD KEY `idx_status` (`status_booking`),
  ADD KEY `idx_date_psychologist` (`tanggal_konsultasi`,`psychologist_id`);

--
-- Indeks untuk tabel `consultation_status`
--
ALTER TABLE `consultation_status`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_psychologist_id` (`psychologist_id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_status` (`konsultasi_status`);

--
-- Indeks untuk tabel `education_contents`
--
ALTER TABLE `education_contents`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `idx_kategori` (`kategori`);

--
-- Indeks untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_invoice_number` (`invoice_number`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_psychologist_id` (`psychologist_id`);

--
-- Indeks untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indeks untuk tabel `landing_photos`
--
ALTER TABLE `landing_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_client` (`client_id`),
  ADD KEY `idx_status` (`status_pembayaran`);

--
-- Indeks untuk tabel `psychologist_off_days`
--
ALTER TABLE `psychologist_off_days`
  ADD PRIMARY KEY (`off_id`),
  ADD KEY `idx_psychologist` (`psychologist_id`),
  ADD KEY `idx_tanggal` (`tanggal_mulai`,`tanggal_selesai`);

--
-- Indeks untuk tabel `psychologist_profiles`
--
ALTER TABLE `psychologist_profiles`
  ADD PRIMARY KEY (`psychologist_id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD UNIQUE KEY `nomor_sipp` (`nomor_sipp`);

--
-- Indeks untuk tabel `psychologist_schedule_dates`
--
ALTER TABLE `psychologist_schedule_dates`
  ADD PRIMARY KEY (`schedule_date_id`),
  ADD UNIQUE KEY `unique_date_time` (`psychologist_id`,`tanggal`,`jam_mulai`),
  ADD KEY `idx_psychologist` (`psychologist_id`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_has_booking` (`psychologist_id`,`tanggal`,`has_booking`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_psychologist` (`psychologist_id`);

--
-- Indeks untuk tabel `test_results`
--
ALTER TABLE `test_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `idx_client` (`client_id`),
  ADD KEY `idx_psychologist` (`psychologist_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking_riwayat_hidup`
--
ALTER TABLE `booking_riwayat_hidup`
  MODIFY `rh_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `client_details`
--
ALTER TABLE `client_details`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `consultation_bookings`
--
ALTER TABLE `consultation_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `education_contents`
--
ALTER TABLE `education_contents`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `landing_photos`
--
ALTER TABLE `landing_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `psychologist_off_days`
--
ALTER TABLE `psychologist_off_days`
  MODIFY `off_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `psychologist_profiles`
--
ALTER TABLE `psychologist_profiles`
  MODIFY `psychologist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `psychologist_schedule_dates`
--
ALTER TABLE `psychologist_schedule_dates`
  MODIFY `schedule_date_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=498;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `test_results`
--
ALTER TABLE `test_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `booking_riwayat_hidup`
--
ALTER TABLE `booking_riwayat_hidup`
  ADD CONSTRAINT `booking_riwayat_hidup_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `consultation_bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `client_details`
--
ALTER TABLE `client_details`
  ADD CONSTRAINT `client_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `consultation_bookings`
--
ALTER TABLE `consultation_bookings`
  ADD CONSTRAINT `consultation_bookings_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client_details` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultation_bookings_ibfk_2` FOREIGN KEY (`psychologist_id`) REFERENCES `psychologist_profiles` (`psychologist_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`psychologist_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client_details` (`client_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `psychologist_off_days`
--
ALTER TABLE `psychologist_off_days`
  ADD CONSTRAINT `psychologist_off_days_ibfk_1` FOREIGN KEY (`psychologist_id`) REFERENCES `psychologist_profiles` (`psychologist_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `psychologist_profiles`
--
ALTER TABLE `psychologist_profiles`
  ADD CONSTRAINT `psychologist_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `psychologist_schedule_dates`
--
ALTER TABLE `psychologist_schedule_dates`
  ADD CONSTRAINT `psychologist_schedule_dates_ibfk_1` FOREIGN KEY (`psychologist_id`) REFERENCES `psychologist_profiles` (`psychologist_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`psychologist_id`) REFERENCES `psychologist_profiles` (`psychologist_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `test_results`
--
ALTER TABLE `test_results`
  ADD CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client_details` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_results_ibfk_2` FOREIGN KEY (`psychologist_id`) REFERENCES `psychologist_profiles` (`psychologist_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
