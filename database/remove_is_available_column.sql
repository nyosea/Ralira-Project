-- Remove is_available column (use hard delete instead)
-- This will change from soft delete to hard delete approach

-- Drop the index first
DROP INDEX IF EXISTS idx_availability ON psychologist_schedule_dates;

-- Drop the column
ALTER TABLE psychologist_schedule_dates 
DROP COLUMN IF EXISTS is_available;

-- Show table structure after removal
DESCRIBE psychologist_schedule_dates;

-- Show remaining indexes
SHOW INDEX FROM psychologist_schedule_dates;
