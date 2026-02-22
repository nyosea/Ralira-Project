# Implementasi Manajemen Jadwal Psikolog - User Guide

## 📋 Ringkasan Fitur

Sistem manajemen jadwal baru untuk admin dan psikolog dengan interface yang intuitif:

### 🎯 Alur Kerja
1. **Pilih Jam Kerja** - Psikolog/Admin mencentang jam yang tersedia (09:00-11:00, 11:00-13:00, dst)
2. **Pilih Tanggal** - Menggunakan kalender interaktif untuk memilih 1 atau lebih tanggal
3. **Simpan Jadwal** - Data langsung tersimpan ke database
4. **Kelola Jadwal** - Dapat menghapus jadwal yang sudah dibuat
5. **Client Booking** - Klien melihat waktu yang tersedia saat memilih psikolog & tanggal

---

## 🔧 Perubahan Teknis

### Database (New Table)
```sql
CREATE TABLE psychologist_schedule_dates (
  schedule_date_id INT PRIMARY KEY,
  psychologist_id INT,
  tanggal DATE,
  jam_mulai TIME,
  jam_selesai TIME,
  is_available TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**File SQL**: `database/update_schedule_dates.sql`

### File yang Dibuat/Diubah

#### 1. CSS - Styling Terpusat
- **File**: `assets/css/schedule_management.css` ✨ BARU
- Berisi semua styling untuk:
  - Time slot selector
  - Calendar picker
  - Notifications
  - Responsive design

#### 2. Admin Schedule Management
- **File**: `pages/admin/manage_psychologist_schedule.php` 📝 DIPERBARUI
- **Fitur**:
  - Pilih psikolog dari dropdown
  - Grid time slot interaktif (checkbox)
  - Kalender bulan penuh
  - List jadwal tersimpan dengan opsi hapus
  - Notifikasi real-time
  - AJAX-based save (tidak perlu page reload)

#### 3. Psychologist Schedule Management
- **File**: `pages/psychologist/schedule.php` 📝 DIPERBARUI
- **Fitur**: Sama seperti admin, tapi untuk psikolog sendiri
- Auto-detect user_id untuk security

#### 4. API untuk Client Booking
- **File 1**: `api/get_available_dates.php` ✨ BARU
  - Endpoint: `?psychologist_id=X&start_date=YYYY-MM-DD&end_date=YYYY-MM-DD`
  - Response: JSON array of available dates
  
- **File 2**: `api/get_available_times_by_date.php` ✨ BARU
  - Endpoint: `?psychologist_id=X&tanggal=YYYY-MM-DD`
  - Response: JSON array of available time slots

---

## 📱 UI/UX Details

### Admin Page Layout (2 Column)

#### Kolom Kiri: Time Slot Selector
```
┌─────────────────────────────┐
│ Pilih Jam Kerja            │
├─────────────────────────────┤
│ [09:00-11:00] [11:00-13:00] │
│ [13:00-15:00] [15:00-17:00] │
│                             │
│ [Terapkan] [Bersihkan]      │
└─────────────────────────────┘
```

#### Kolom Kanan: Calendar Selector
```
┌────────────────────────────────┐
│ Pilih Tanggal                 │
├────────────────────────────────┤
│ < Januari 2025 >              │
├────────────────────────────────┤
│ Min Sen Sel ... (7 hari)      │
│ [1]  [2]  [3] ...             │
│ [8]  [9]  [10] ...            │
│ ...                           │
├────────────────────────────────┤
│ Tanggal Terpilih:             │
│ - Senin, 20 Januari 2025      │
│   (09:00-11:00, 11:00-13:00) │
│ - Selasa, 21 Januari 2025 [×] │
│                             │
│ [Simpan] [Reset]              │
└────────────────────────────────┘
```

#### Jadwal Tersimpan
```
┌────────────────────────────────┐
│ Jadwal Tersimpan              │
├────────────────────────────────┤
│ Senin, 20 Januari 2025       │
│ 09:00 - 11:00     [Hapus]    │
│                             │
│ Senin, 20 Januari 2025       │
│ 11:00 - 13:00     [Hapus]    │
└────────────────────────────────┘
```

---

## 🚀 Implementasi Steps

### 1. Database Migration
```bash
# Jalankan SQL migration
mysql -u root ralira_db < database/update_schedule_dates.sql
```

### 2. Test Admin Interface
```
URL: http://localhost/ralira_project/pages/admin/manage_psychologist_schedule.php
- Pilih psikolog
- Select jam-jam
- Click tanggal di kalender
- Save dan verifikasi di database
```

### 3. Test Psychologist Interface
```
URL: http://localhost/ralira_project/pages/psychologist/schedule.php
- Auto-detect user profile
- Sama workflow seperti admin
```

### 4. Test API Endpoints
```
GET /api/get_available_dates.php?psychologist_id=1&start_date=2025-01-01&end_date=2025-01-31
GET /api/get_available_times_by_date.php?psychologist_id=1&tanggal=2025-01-20
```

### 5. Integrasikan dengan Client Booking
Di `pages/client/booking.php`, tambahkan:
```javascript
// Saat psychologist dipilih
const psychologistId = selectedValue;
fetch(`/api/get_available_dates.php?psychologist_id=${psychologistId}`)
  .then(r => r.json())
  .then(data => {
    // Highlight available dates di date picker
  });

// Saat tanggal dipilih
fetch(`/api/get_available_times_by_date.php?psychologist_id=${psychologistId}&tanggal=${selectedDate}`)
  .then(r => r.json())
  .then(data => {
    // Populate time dropdown dengan available slots
  });
```

---

## 🎨 Styling Features

### Color Scheme (dari variables.css)
- **Primary**: `var(--color-primary)` (Kuning)
- **Text**: `var(--color-text)` (Gelap)
- **Border**: `var(--color-border)` (Abu-abu)

### Responsive Design
- Desktop (2 kolom) → 1200px
- Tablet (1 kolom) → 1024px
- Mobile (fullwidth) → <768px

### Interactive Elements
- **Time Slot**: Highlight kuning saat checked
- **Calendar Days**: Highlight hijau untuk hari dengan jadwal
- **Notifications**: Auto-dismiss setelah 4 detik
- **Smooth Transitions**: 0.2-0.3s ease

---

## ❌ Fitur yang Dihapus

### Cuti/Off Days
- Tidak ada lagi "tambah cuti" di interface baru
- Sistem date-based lebih fleksibel (cuti tinggal jangan select tanggalnya)
- Tabel `psychologist_off_days` tetap di database untuk backward compatibility

---

## 📊 Database Structure

### psychologist_schedule_dates
| Column | Type | Notes |
|--------|------|-------|
| schedule_date_id | INT | Primary Key |
| psychologist_id | INT | FK to psychologist_profiles |
| tanggal | DATE | Tanggal jadwal |
| jam_mulai | TIME | Jam mulai (e.g., 09:00) |
| jam_selesai | TIME | Jam selesai (e.g., 11:00) |
| is_available | TINYINT | 1=aktif, 0=dihapus |
| created_at | TIMESTAMP | Auto |
| updated_at | TIMESTAMP | Auto |

### consultation_bookings (Updated)
```sql
ALTER TABLE consultation_bookings 
ADD COLUMN jam_konsultasi TIME AFTER tanggal_konsultasi;
```

---

## 🔐 Security Considerations

1. **Authentication**: User role check pada setiap page
   - Admin: Bisa atur jadwal psikolog lain
   - Psychologist: Hanya bisa atur jadwal sendiri

2. **Authorization**: AJAX requests validate:
   - `psychologist_id` ownership
   - Prevent direct DB manipulation

3. **Input Validation**:
   - Date format validation (YYYY-MM-DD)
   - Time format validation
   - Prepared statements (prevent SQL injection)

---

## 📝 Notes untuk Developer

1. **CSS Loading Order** di head:
   ```html
   <link rel="stylesheet" href="variables.css">
   <link rel="stylesheet" href="glass.css">
   <link rel="stylesheet" href="style.css">
   <link rel="stylesheet" href="admin.css">
   <link rel="stylesheet" href="responsive.css">
   <link rel="stylesheet" href="schedule_management.css"> <!-- LAST -->
   ```

2. **JavaScript Dependencies**: Vanilla JS (no jQuery required)

3. **Browser Support**: Modern browsers (ES6+)

4. **Performance**:
   - Calendar rendering: < 100ms
   - API calls: < 500ms
   - Minimal DOM manipulation

---

## 🐛 Troubleshooting

### Issue: Jadwal tidak muncul di kalender
- **Solusi**: Check `psychologist_schedule_dates` table punya data
- **Debug**: `SELECT * FROM psychologist_schedule_dates WHERE psychologist_id = X`

### Issue: API returns empty array
- **Solusi**: Verify date format (YYYY-MM-DD)
- **Solusi 2**: Check `is_available` column = 1

### Issue: CSS tidak loading
- **Solusi**: Verify path relatif ke `schedule_management.css`
- **Solusi 2**: Check browser console untuk 404 errors

---

## 📞 Support

File yang butuh bantuan atau pertanyaan:
- Layout: Lihat `schedule_management.css`
- Database: Lihat `update_schedule_dates.sql`
- Logic: Lihat comments di `.php` files

Generated: 2025-01-26
