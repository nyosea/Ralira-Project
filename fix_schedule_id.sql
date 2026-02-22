-- Fix consultation_bookings schedule_id constraint
-- Option: Modify to allow NULL values (safer than dropping foreign key)

ALTER TABLE consultation_bookings MODIFY schedule_id INT NULL;
