-- Tabel untuk tracking status konsultasi (terpisah dari booking)
CREATE TABLE IF NOT EXISTS consultation_status (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    psychologist_id INT NOT NULL,
    client_id INT NOT NULL,
    konsultasi_status ENUM('belum_ditangani', 'sedang_ditangani', 'sudah_ditangani') DEFAULT 'belum_ditangani',
    updated_by_user_id INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES consultation_bookings(booking_id),
    FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id),
    FOREIGN KEY (client_id) REFERENCES client_details(client_id),
    FOREIGN KEY (updated_by_user_id) REFERENCES users(user_id),
    
    INDEX idx_booking_id (booking_id),
    INDEX idx_psychologist_id (psychologist_id),
    INDEX idx_client_id (client_id),
    INDEX idx_status (konsultasi_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger untuk otomatis buat record consultation_status saat booking menjadi 'completed'
DELIMITER //
CREATE TRIGGER after_booking_completed
AFTER UPDATE ON consultation_bookings
FOR EACH ROW
BEGIN
    IF NEW.status_booking = 'completed' AND OLD.status_booking != 'completed' THEN
        INSERT INTO consultation_status (booking_id, psychologist_id, client_id)
        VALUES (NEW.booking_id, NEW.psychologist_id, NEW.client_id);
    END IF;
END//
DELIMITER ;
