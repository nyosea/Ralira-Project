-- =====================================================
-- TABLE: landing_photos
-- Description: Khusus untuk landing page section "Tim Profesional Kami"
-- =====================================================

CREATE TABLE IF NOT EXISTS `landing_photos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `specialization` VARCHAR(100) NOT NULL,
  `experience` VARCHAR(100),
  `bio` TEXT,
  `photo` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `landing_photos` (`name`, `specialization`, `experience`, `bio`, `photo`) VALUES
('Dr. Ira Puspitawati', 'Psikolog Anak & Owner', '30+ Tahun Pengalaman', 'Spesialis dalam psikologi anak dan keluarga dengan pengalaman lebih dari 30 tahun.', 'psychologists/sample1.jpg'),
('Bu Nurul', 'Psikolog Industri', 'Spesialis Rekrutmen', 'Ahli dalam psikologi industri dan organisasi, fokus pada rekrutmen dan seleksi karyawan.', 'psychologists/sample2.jpg'),
('Bu Claudia', 'Psikolog Remaja', 'Konseling Online', 'Berpengalaman dalam konseling remaja dan masalah perkembangan usia muda.', 'psychologists/sample3.jpg'),
('Pak Refandi', 'Psikolog Dewasa', 'Masalah Karir', 'Spesialis dalam psikologi dewasa dengan fokus pada masalah karir dan pengembangan diri.', 'psychologists/sample4.jpg');
