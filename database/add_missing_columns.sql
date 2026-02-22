-- =====================================================
-- ADD MISSING COLUMNS to psychologist_profiles
-- =====================================================

-- Tambah column specialization
ALTER TABLE psychologist_profiles 
ADD COLUMN specialization VARCHAR(100) DEFAULT NULL;

-- Tambah column bio
ALTER TABLE psychologist_profiles 
ADD COLUMN bio TEXT DEFAULT NULL;

-- Tambah column sipp
ALTER TABLE psychologist_profiles 
ADD COLUMN sipp VARCHAR(50) DEFAULT NULL;

-- Verifikasi semua column
DESCRIBE psychologist_profiles;
