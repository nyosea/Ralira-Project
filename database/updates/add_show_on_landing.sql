-- Tambah kolom show_on_landing di tabel psychologist_profiles
-- Untuk mengatur apakah psikolog ditampilkan di landing page

ALTER TABLE psychologist_profiles 
ADD COLUMN show_on_landing TINYINT(1) DEFAULT 0 
COMMENT 'Tampilkan di landing page (1=ya, 0=tidak)';
