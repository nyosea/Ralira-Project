-- =====================================================
-- ALTER TABLE: Add photo column to psychologist_profiles
-- =====================================================

-- Tambah column photo ke table psychologist_profiles
ALTER TABLE psychologist_profiles 
ADD COLUMN photo VARCHAR(255) DEFAULT NULL;

-- Update existing records menjadi NULL (kosong)
UPDATE psychologist_profiles 
SET photo = NULL 
WHERE photo IS NULL OR photo = '';

-- Verifikasi column sudah ditambahkan
DESCRIBE psychologist_profiles;
