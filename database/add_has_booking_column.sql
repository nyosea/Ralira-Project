-- Add has_booking column to psychologist_schedule_dates table
-- This column will indicate if a schedule has active booking (1 = has booking, 0 = no booking)

ALTER TABLE psychologist_schedule_dates 
ADD COLUMN has_booking TINYINT(1) DEFAULT 0 COMMENT '1 = has active booking, 0 = no booking';

-- Add index for better performance
CREATE INDEX idx_has_booking ON psychologist_schedule_dates(psychologist_id, tanggal, has_booking);

-- Update existing data: Set has_booking = 1 for schedules with active bookings
UPDATE psychologist_schedule_dates psd 
SET has_booking = 1 
WHERE EXISTS (
    SELECT 1 
    FROM consultation_bookings cb 
    WHERE cb.schedule_id = psd.schedule_date_id 
    AND cb.status_booking != 'canceled'
);

-- Show table structure after update
DESCRIBE psychologist_schedule_dates;
