-- FIX SCHEDULE_ID CONSTRAINT - BEST SOLUTION
-- Modify consultation_bookings table to allow NULL in schedule_id
-- This is the SAFEST option - keeps foreign key but allows NULL values

ALTER TABLE consultation_bookings MODIFY schedule_id INT NULL;

-- After running this, the booking system will work perfectly
-- No data loss, no constraint removal, just allows NULL values
