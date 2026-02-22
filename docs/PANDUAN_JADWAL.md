# 🎯 PANDUAN PENGGUNAAN - Manajemen Jadwal Psikolog

## Untuk Psikolog/Admin

### 📍 Akses Halaman

**Admin**: `pages/admin/manage_psychologist_schedule.php`
- Pilih psikolog dari dropdown
- Manage jadwal psikolog manapun

**Psikolog**: `pages/psychologist/schedule.php`
- Langsung manage jadwal sendiri
- Auto-detect dari user login

---

## 🎬 Langkah-Langkah Penggunaan

### Step 1: Buka Halaman
- Admin: Masuk dashboard admin → Menu Manajemen Jadwal Psikolog
- Psikolog: Masuk dashboard → Menu Jadwal Praktik

### Step 2: Pilih Jam Kerja (Kotak Kiri)
```
[09:00-11:00]  [11:00-13:00]
[13:00-15:00]  [15:00-17:00]
```
- Klik jam yang ingin dijadwalkan
- Jam yang dipilih akan highlight kuning
- Bisa pilih lebih dari satu jam

### Step 3: Pilih Tanggal (Kotak Kanan)
```
< [Januari 2025] >

Min Sen Sel Rab Kam Jum Sab
 1   2   3   4   5   6   7
 8   9  10  11  12  13  14
15  16  17  18  19  20  21
22  23  24  25  26  27  28
29  30  31
```
- Klik tanggal yang ingin di-set jadwal
- Tanggal yang dipilih highlight hijau
- Bisa pilih multiple dates (klik satu-satu)
- Tanggal yang sudah lewat tidak bisa dipilih

### Step 4: Lihat Tanggal Terpilih
```
Tanggal Terpilih:
• Senin, 20 Januari 2025 (09:00-11:00, 11:00-13:00) [×]
• Selasa, 21 Januari 2025 (11:00-13:00) [×]
```
- Menunjukkan tanggal + jam yang akan di-save
- Klik [×] untuk batalkan tanggal tertentu

### Step 5: Simpan Jadwal
- Klik tombol **[✓ Simpan Jadwal]**
- Tunggu notifikasi "Jadwal berhasil disimpan!"
- Page akan auto-refresh

### Step 6: Kelola Jadwal Tersimpan
```
Jadwal Tersimpan:
┌─────────────────────────────────┐
│ Senin, 20 Januari 2025          │
│ 09:00 - 11:00       [🗑 Hapus] │
├─────────────────────────────────┤
│ Senin, 20 Januari 2025          │
│ 11:00 - 13:00       [🗑 Hapus] │
└─────────────────────────────────┘
```
- Lihat semua jadwal yang sudah diset
- Klik [Hapus] untuk menghapus jadwal tertentu
- Konfirmasi saat hapus

---

## 💡 Tips & Trik

### Bulk Select
```
Ingin set jadwal setiap hari dalam seminggu?
1. Centang semua jam yang tersedia
2. Click tanggal Senin
3. Shift+Click tanggal Jumat
4. Semua tanggal Senin-Jumat akan ter-select
5. Simpan
```

### Reset/Mulai Ulang
```
Salah pilih jam atau tanggal?
- Klik [Bersihkan] untuk reset jam
- Klik [Reset] untuk reset tanggal
- Mulai lagi dari awal
```

### Navigasi Bulan
```
Ingin lihat bulan depan?
- Klik [>] untuk bulan berikutnya
- Klik [<] untuk bulan sebelumnya
- Hari ini ditandai dengan border tebal
```

---

## 🎨 Penjelasan Warna

| Warna | Arti |
|-------|------|
| 🟡 Kuning | Jam yang dipilih / Tombol aksi |
| 🟢 Hijau | Tanggal yang dipilih |
| ⚪ Abu-abu | Tanggal yang sudah lewat (tidak bisa dipilih) |
| 🟦 Biru | Hari ini (garis border) |

---

## ⚠️ Penting Diperhatikan

### DON'T
- ❌ Jangan close browser saat sedang simpan (tunggu notifikasi)
- ❌ Jangan select tanggal yang sama dengan jam yang berbeda di saat yang bersamaan (bisa duplicate)
- ❌ Jangan set jadwal di tanggal yang sudah lewat

### DO
- ✅ Select SEMUA jam dulu sebelum pick tanggal
- ✅ Pilih tanggal yang akan datang
- ✅ Verifikasi di "Jadwal Tersimpan" bahwa data benar
- ✅ Set jadwal 1-2 minggu sebelumnya untuk optimal

---

## 🔄 Workflow Contoh

### Skenario: Admin set jadwal Dr. Ira untuk minggu depan

```
1. Buka: pages/admin/manage_psychologist_schedule.php
2. Dropdown: Pilih "Dr. Ira Puspitawati"
3. Jam: Centang [09:00-11:00] [11:00-13:00] [15:00-17:00]
4. Kalender: Klik 20, 21, 22, 23, 24 (Mon-Fri)
5. Review: Verify 15 jadwal akan dibuat (3 jam × 5 hari)
6. Simpan: Click [✓ Simpan Jadwal]
7. Notif: "Jadwal berhasil disimpan!"
8. Check: Lihat di "Jadwal Tersimpan" list
```

---

## 🐛 Troubleshooting

### Problem: Tidak bisa klik tanggal di kalender
**Solusi**: Tanggal tersebut sudah lewat. Navigasi ke bulan depan.

### Problem: Notifikasi tidak muncul
**Solusi**: Cek browser console (F12). Mungkin ada JavaScript error.

### Problem: Jadwal tidak muncul setelah save
**Solusi**: 
- Refresh page (F5)
- Cek database: `SELECT * FROM psychologist_schedule_dates WHERE psychologist_id = X`

### Problem: Tidak bisa simpan
**Solusi**:
- Verify minimal 1 jam & 1 tanggal sudah dipilih
- Cek koneksi internet
- Cek browser console untuk error message

---

## 📱 Mobile Version

Layout otomatis menyesuaikan di mobile:
```
Mobile (< 768px)
├── Jam (full width, 2 kolom)
└── Kalender (full width, stacked)

Tablet (768px - 1024px)
├── Jam (full width, 2 kolom)
└── Kalender (full width, stacked)

Desktop (> 1024px)
├── Jam (kiri, 50% width)
└── Kalender (kanan, 50% width)
```

---

## 🔐 Keamanan

- Hanya user yang login yang bisa akses
- Psikolog hanya bisa edit jadwal sendiri (tidak bisa lihat psikolog lain)
- Admin bisa manage semua psikolog
- Semua input divalidasi di server-side

---

## 📊 Data yang Tersimpan di Database

Setiap jadwal yang disimpan menyimpan:
- Psikolog ID
- Tanggal (YYYY-MM-DD)
- Jam mulai (HH:MM)
- Jam selesai (HH:MM)
- Status aktif/dihapus
- Timestamp created & updated

---

## 🤝 Integrasi dengan Booking Client

Ketika klien membuat booking:

1. Klien pilih psikolog → API ambil available dates
2. Klien pilih tanggal → API ambil available times
3. Klien pilih jam → Booking dibuat

**API yang digunakan**:
- `api/get_available_dates.php` → list tanggal
- `api/get_available_times_by_date.php` → list jam

---

## 💬 FAQ

**Q: Bisa set jadwal untuk tanggal yang sama dengan jam berbeda?**
A: Ya! Misal 20 Januari ada jam 09:00-11:00 DAN 11:00-13:00. Buat 2x entry.

**Q: Kalau jadinya tidak bisa praktik di tanggal tertentu?**
A: Hapus dari "Jadwal Tersimpan" list. Tidak perlu diset ulang.

**Q: Bisa set jadwal untuk 1 bulan ke depan sekaligus?**
A: Ya! Pilih semua hari dalam bulan tersebut. Bisa bulk select.

**Q: Kalau jam praktik berubah tengah minggu?**
A: Hapus yang lama dari "Jadwal Tersimpan", set yang baru.

**Q: Data jadwal disimpan kemana?**
A: Database tabel `psychologist_schedule_dates`. Admin bisa check di PHPMyAdmin.

---

## 📞 Support

Jika ada pertanyaan atau issue:
- Lihat file `JADWAL_IMPLEMENTATION.md` untuk technical details
- Atau tanya ke developer

---

**Last Updated**: December 26, 2025
**Version**: 1.0 Final
