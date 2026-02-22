-- Create triggers to automatically update has_booking column
-- When booking is created/updated/deleted

-- Trigger: When consultation_bookings is inserted or updated
DELIMITER //
CREATE TRIGGER update_has_booking_on_booking_insert
AFTER INSERT ON consultation_bookings
FOR EACH ROW
BEGIN
    IF NEW.status_booking != 'canceled' THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 1 
        WHERE schedule_date_id = NEW.schedule_id;
    END IF;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER update_has_booking_on_booking_update
AFTER UPDATE ON consultation_bookings
FOR EACH ROW
BEGIN
    -- If booking is canceled, check if there are other active bookings
    IF NEW.status_booking = 'canceled' THEN
        -- Check if there are other active bookings for this schedule
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
        -- Booking is now active
        UPDATE psychologist_schedule_dates 
        SET has_booking = 1 
        WHERE schedule_date_id = NEW.schedule_id;
    END IF;
END//
DELIMITER ;

-- Trigger: When consultation_bookings is deleted
DELIMITER //
CREATE TRIGGER update_has_booking_on_booking_delete
AFTER DELETE ON consultation_bookings
FOR EACH ROW
BEGIN
    -- Check if there are other active bookings for this schedule
    IF NOT EXISTS (
        SELECT 1 FROM consultation_bookings 
        WHERE schedule_id = OLD.schedule_id 
        AND status_booking != 'canceled'
    ) THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 0 
        WHERE schedule_date_id = OLD.schedule_id;
    END IF;
END//
DELIMITER ;

-- Show existing triggers
SHOW TRIGGERS WHERE Table = 'consultation_bookings';
