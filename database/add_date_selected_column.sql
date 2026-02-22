-- Add column for date selection tracking
ALTER TABLE psychologist_schedule_dates 
ADD COLUMN is_date_selected TINYINT(1) DEFAULT 0 COMMENT '1 = tanggal dipilih, 0 = tanggal tidak dipilih';

-- Add index for better performance
CREATE INDEX idx_psychologist_date_selected ON psychologist_schedule_dates(psychologist_id, tanggal, is_date_selected);
