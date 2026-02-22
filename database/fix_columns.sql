-- Cek struktur tabel psychologist_profiles
DESCRIBE psychologist_profiles;

-- Cek apakah column specialization ada
SHOW COLUMNS FROM psychologist_profiles LIKE 'specialization';

-- Tambah column jika belum ada
ALTER TABLE psychologist_profiles 
ADD COLUMN IF NOT EXISTS specialization VARCHAR(100) DEFAULT NULL;

ALTER TABLE psychologist_profiles 
ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL;

ALTER TABLE psychologist_profiles 
ADD COLUMN IF NOT EXISTS sipp VARCHAR(50) DEFAULT NULL;

-- Verifikasi lagi
DESCRIBE psychologist_profiles;
