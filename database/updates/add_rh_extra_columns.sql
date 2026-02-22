-- Migration: add agama, suku, hobi, alamat to booking_riwayat_hidup

ALTER TABLE `booking_riwayat_hidup`
  ADD COLUMN `agama` ENUM('Islam','Kristen','Katholik','Buddha','Konghucu') NULL AFTER `tahu_dari`,
  ADD COLUMN `suku` VARCHAR(255) NULL AFTER `agama`,
  ADD COLUMN `hobi` TEXT NULL AFTER `suku`,
  ADD COLUMN `alamat` TEXT NULL AFTER `hobi`;

-- To apply: run this SQL in your MySQL (e.g., via phpMyAdmin or mysql CLI)