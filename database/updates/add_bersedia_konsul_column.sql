-- Add bersedia_konsul column to psychologist_profiles table
-- Created: 2026-01-12
-- Purpose: Add consultation availability status for psychologists

ALTER TABLE psychologist_profiles 
ADD COLUMN bersedia_konsul VARCHAR(3) NOT NULL DEFAULT 'yes' 
AFTER pengalaman_tahun;

-- Add index for better performance
CREATE INDEX idx_bersedia_konsul ON psychologist_profiles(bersedia_konsul);

-- Update existing records to have default value
UPDATE psychologist_profiles 
SET bersedia_konsul = 'yes' 
WHERE bersedia_konsul IS NULL;
