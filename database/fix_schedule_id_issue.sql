-- Script untuk memperbaiki masalah schedule_id = 0 di consultation_bookings

-- 1. Hapus booking dengan schedule_id = 0 karena data tidak valid
DELETE FROM consultation_bookings WHERE schedule_id = 0;

-- 2. Verifikasi struktur tabel consultation_bookings
DESCRIBE consultation_bookings;

-- 3. Tampilkan booking terbaru untuk verifikasi
SELECT 
    booking_id,
    client_id,
    psychologist_id,
    schedule_id,
    tanggal_konsultasi,
    jam_konsultasi,
    status_booking,
    created_at
FROM consultation_bookings
ORDER BY created_at DESC
LIMIT 10;

-- 4. Tampilkan struktur psychologist_schedule_dates untuk referensi
DESCRIBE psychologist_schedule_dates;
