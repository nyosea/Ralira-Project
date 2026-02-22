-- Cek struktur lengkap psychologist_profiles
DESCRIBE psychologist_profiles;

-- Cek semua column yang ada
SHOW COLUMNS FROM psychologist_profiles;

-- Cek apakah ada column 'spesialisasi'
SHOW COLUMNS FROM psychologist_profiles LIKE 'spesialisasi';

-- Cek apakah ada column 'specialization'
SHOW COLUMNS FROM psychologist_profiles LIKE 'specialization';
