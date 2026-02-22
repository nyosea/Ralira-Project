-- Remove is_date_selected column (not used in current system)
-- This column was only used in the new system that is not being used

-- Drop the index first
DROP INDEX IF EXISTS idx_psychologist_date_selected ON psychologist_schedule_dates;

-- Drop the column
ALTER TABLE psychologist_schedule_dates 
DROP COLUMN IF EXISTS is_date_selected;

-- Show table structure after removal
DESCRIBE psychologist_schedule_dates;

-- Show remaining indexes
SHOW INDEX FROM psychologist_schedule_dates;
