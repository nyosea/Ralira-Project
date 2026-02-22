# Sistem Booking & Jadwal Psikolog - Dokumentasi

## Overview
Sistem booking terdiri dari 3 komponen utama:
1. **Admin** - Mengatur hari kerja psikolog (tetap)
2. **Client** - Booking jadwal konsultasi dengan pembayaran
3. **Psikolog** - Menerima permintaan booking dan lihat client RH

---

## 1. Admin: Manajemen Jadwal Psikolog
**URL**: `/pages/admin/manage_psychologist_schedule.php`

### Alur:
- Admin login → Pilih psikolog
- **Atur HARI KERJA**: Centang hari mana psikolog tersedia (Mon-Sun)
- **JAM TETAP**: 09:00-11:00 | 11:00-13:00 | 13:00-15:00 | 15:00-17:00 (tidak berubah)
- **Tambah Cuti**: Tentukan tanggal mulai-selesai + alasan cuti

### Database:
- **`psychologist_schedule_slots`** - Simpan hari kerja
  - `psychologist_id`, `hari` (Monday-Sunday), `jam` (09:00), `is_available` (0/1)
  
- **`psychologist_off_days`** - Simpan cuti psikolog
  - `psychologist_id`, `tanggal_mulai`, `tanggal_selesai`, `alasan`

---

## 2. Client: Booking Jadwal Konsultasi
**URL**: `/pages/client/booking.php`

### Alur:
1. **Isi Data**: Nama, email, telepon, gender
2. **Isi Form RH**: Keluhan, lama masalah, riwayat konsultasi, background, sumber tahu
3. **Pilih Layanan**: Jenis konsultasi (anak, remaja, dewasa, keluarga, rekrutmen)
4. **Pilih Psikolog**: Dropdown filter by spesialisasi
5. **Pilih Tanggal**: Input date (1-21 hari ke depan)
6. **Load Jam Tersedia**: Via API `/api/get_available_times.php`
   - Cek hari kerja psikolog
   - Cek cuti psikolog
   - Cek booking sudah ada atau belum
   - Tampilkan jam kosong
7. **Pilih Jam**: Dropdown jam tersedia
8. **Upload Bukti Transfer**: Rp 50.000 ke BCA (JPG/PNG/PDF, max 5MB)
9. **Submit Booking**:
   - Status: `pending`
   - Simpan ke `consultation_bookings` + `booking_riwayat_hidup`
   - Simpan bukti bayar ke `payments` (status: pending)
   - Redirect ke history

### Database:
- **`consultation_bookings`** - Simpan booking
  - `booking_id`, `client_id`, `psychologist_id`, `tanggal_konsultasi`, `jam_mulai`, `status_booking` (pending/confirmed)

- **`booking_riwayat_hidup`** - Simpan RH client
  - `booking_id`, `keluhan_masalah`, `lama_masalah`, `pernah_konsultasi`, `latar_belakang`, `tahu_dari`

- **`payments`** - Simpan bukti transfer
  - `client_id`, `bukti_transfer`, `status_pembayaran` (pending/verified)

---

## 3. API: Get Available Times
**Endpoint**: `GET /api/get_available_times.php?psychologist_id=X&date=YYYY-MM-DD`

### Logic:
```
1. Cek hari kerja psikolog (psychologist_schedule_slots.is_available)
2. Cek cuti psikolog (psychologist_off_days)
3. Cek booking yang sudah ada (consultation_bookings)
4. Return array jam kosong
```

### Response:
```json
{
  "available_times": [
    {"time": "09:00", "display": "09:00-11:00"},
    {"time": "11:00", "display": "11:00-13:00"}
  ],
  "date": "2024-12-27",
  "day_name": "Friday",
  "total_slots": 4,
  "booked_count": 2
}
```

---

## 4. Psikolog: Lihat & Accept/Reject Booking
**URL**: `/pages/psychologist/bookings.php`

### Fitur:
- **Pending Bookings**: List booking yang menunggu konfirmasi
  - Tampil: Tanggal, jam, nama client, email, telp, gender
  - Tampil: Riwayat Hidup (RH) lengkap
  - Action: Accept (status → confirmed) / Reject (status → canceled)

- **Confirmed Bookings**: List booking yang sudah diterima
  - Read-only, untuk referensi jadwal
  
- **Canceled Bookings**: Riwayat booking yang ditolak/dibatalkan (max 10)

### Database Query:
- JOIN `consultation_bookings` + `client_details` + `users` + `booking_riwayat_hidup`
- Filter by `psychologist_id` dan `status_booking`
- Order by tanggal ASC

---

## Validasi Key Points

### 1. Admin Schedule
- ✓ Hanya atur HARI kerja (Mon-Sun)
- ✓ Jam tetap: 09:00, 11:00, 13:00, 15:00
- ✓ Dapat add/remove cuti per tanggal range

### 2. Client Booking
- ✓ Tanggal harus 1-21 hari ke depan
- ✓ Tanggal harus hari kerja psikolog
- ✓ Tanggal tidak boleh cuti psikolog
- ✓ Jam tidak boleh sudah terbooking
- ✓ Form RH harus lengkap
- ✓ Bukti bayar harus upload

### 3. API Get Times
- ✓ Return kosong jika psikolog tidak kerja hari itu
- ✓ Return kosong jika semua jam sudah terbooking
- ✓ Return kosong jika psikolog sedang cuti

---

## Testing Checklist

- [ ] Admin: Set hari kerja psikolog (misal: Sen-Jum)
- [ ] Admin: Tambah cuti (misal: 25-26 Dec)
- [ ] Client: Pilih tanggal hari kerja → jam muncul
- [ ] Client: Pilih tanggal libur → jam tidak muncul
- [ ] Client: Pilih tanggal cuti → error message
- [ ] Client: Booking same slot → error conflict
- [ ] Client: Verify payment upload works
- [ ] Check database records save correctly

---

## File Changes Summary

### Created/Modified:
1. **`pages/admin/manage_psychologist_schedule.php`** - UPDATED
   - Simplify: Checkbox per HARI (Mon-Sun), bukan per tanggal
   - Hapus: Grid kompleks 21x4 (tanggal x jam)
   - Tambah: Simple day selection UI
   - Keep: Cuti/off days management
   
2. **`pages/client/booking.php`** - UPDATED
   - Improve: Validation logic (check hari kerja, off_days)
   - Improve: Remove jam_mulai format issue
   - Update: API call parameter (date, bukan tanggal)
   - Add: Better error messages
   
3. **`api/get_available_times.php`** - UPDATED
   - Rewrite: Logic untuk check hari kerja (bukan jam per tanggal)
   - Add: Off days validation
   - Improve: Error handling & JSON response
   - Keep: Jam tetap (09:00, 11:00, 13:00, 15:00)

4. **`pages/psychologist/bookings.php`** - CREATED
   - New: Dashboard untuk psikolog lihat incoming bookings
   - Feature: Grouping by status (pending, confirmed, canceled)
   - Feature: Accept/reject booking dengan form POST
   - Feature: Display RH client untuk context
   - Style: Card-based UI dengan status badges

### Database Tables (No Changes):
- `consultation_bookings` - Already OK
- `booking_riwayat_hidup` - Already OK
- `psychologist_schedule_slots` - Already OK, hanya perubahan data (per hari, bukan per jam)
- `psychologist_off_days` - Already OK
- `payments` - Already OK

---

## Catatan
- JAM TETAP: Jangan ubah 09:00, 11:00, 13:00, 15:00
- Admin punya kontrol HARI, Client pilih TANGGAL (dari hari kerja)
- API handle semua logic filtering jam tersedia
- Future: Psikolog bisa override jadwal per minggu/custom times
