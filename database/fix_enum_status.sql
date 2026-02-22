-- Fix status_booking ENUM to include 'rejected'
ALTER TABLE consultation_bookings 
MODIFY status_booking ENUM('pending','confirmed','canceled','rejected') DEFAULT 'pending';
