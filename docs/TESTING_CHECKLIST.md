# Testing Checklist - Sistem Booking & Jadwal

## Prerequisites
- Database sudah migrate dengan schema terbaru
- Admin, Psikolog, Client user sudah ada di database
- Minimal 1 psikolog dengan spesialisasi di-set

---

## 1. Test Admin: Set Jadwal Psikolog

### Step 1.1: Login sebagai Admin
```
1. Go to /pages/auth/login.php
2. Login dengan admin account
3. Go to /pages/admin/manage_psychologist_schedule.php
```

### Step 1.2: Select Psikolog
```
1. Dropdown: Pilih psikolog
2. Expect: Form load dengan 7 hari kerja (checkbox)
```

### Step 1.3: Set Hari Kerja
```
1. Centang hari (misal: Mon, Tue, Wed, Thu, Fri)
2. Click "Simpan Jadwal Kerja"
3. Expect: Success message, checkbox tetap terpilih
4. Expect: Database: psychologist_schedule_slots update is_available=1 untuk hari-hari yg dipilih
```

### Step 1.4: Add Cuti
```
1. Input Dari: 25 Dec 2024
2. Input Sampai: 26 Dec 2024
3. Input Alasan: Liburan Natal
4. Click "Tambah"
5. Expect: Success message
6. Expect: Cuti muncul di list "Daftar Cuti"
7. Database: psychologist_off_days insert 1 record
```

### Step 1.5: Delete Cuti
```
1. Click "Hapus" pada cuti yg baru dibuat
2. Expect: Confirm dialog
3. Click OK
4. Expect: Success message, cuti dihapus dari list
5. Database: psychologist_off_days delete record
```

---

## 2. Test Client: Booking Konsultasi

### Step 2.1: Login sebagai Client
```
1. Go to /pages/auth/login.php
2. Login dengan client account
3. Go to /pages/client/booking.php
```

### Step 2.2: Isi Data Personal
```
1. Nama: [auto-fill or edit]
2. Telp: [auto-fill or edit]
3. Email: [auto-fill or edit]
4. Gender: Select Laki-laki / Perempuan
5. Expect: Form save & validation jalan
```

### Step 2.3: Isi Form Riwayat Hidup
```
1. Click "Isi Form Riwayat Hidup"
2. Expect: Modal buka
3. Isi semua field:
   - Keluhan/Masalah
   - Lama Masalah
   - Pernah Konsultasi (radio button)
   - Latar Belakang
   - Tahu dari (dropdown)
4. Click "Simpan"
5. Expect: Modal close, button ubah jadi "✓ Form RH Sudah Diisi"
6. Database: Hidden fields in main form store RH data
```

### Step 2.4: Pilih Layanan & Psikolog
```
1. Jenis Layanan: Select "Konseling Anak"
2. Expect: Dropdown psikolog filter (hanya yg spesialisasi "anak")
3. Psikolog: Select psikolog yg sudah set hari kerja di Step 1.3
```

### Step 2.5: Pilih Tanggal (Hari Kerja)
```
1. Tanggal Konsultasi: Pilih hari Senin (hari kerja dari Step 1.3)
2. Expect: Time slot dropdown populate dengan available times
3. Expect: Data dari API: /api/get_available_times.php?psychologist_id=X&date=YYYY-MM-DD
```

### Step 2.6: Pilih Tanggal (Libur/Cuti)
```
1. Tanggal Konsultasi: Pilih 25 Dec (cuti dari Step 1.4)
2. Expect: Time slot dropdown show "Psikolog sedang cuti pada tanggal ini"
3. Expect: API return empty available_times + on_leave: true
```

### Step 2.7: Pilih Jam
```
1. Jam Mulai: Select "09:00-11:00" (jam pertama)
2. Expect: Dropdown show available times (tidak termasuk booked slots)
3. Info text: "Sesi akan berlangsung selama 2 jam"
```

### Step 2.8: Upload Bukti Transfer
```
1. Click upload area
2. Select file JPG/PNG/PDF (max 5MB)
3. Expect: Filename show "File terpilih: [nama file]"
4. Try file > 5MB: Expect error dari browser validation
5. Try invalid format (.txt): Expect error dari backend validation
```

### Step 2.9: Submit Booking
```
1. Click "Konfirmasi Booking"
2. Expect: Form validate (semua field required)
3. Expect: Success message "Booking berhasil!"
4. Expect: Redirect ke /pages/client/history.php (after 2 sec)
5. Database checks:
   - consultation_bookings: INSERT with status='pending'
   - booking_riwayat_hidup: INSERT with RH data
   - payments: INSERT with bukti_transfer file path
```

### Step 2.10: Double Booking (Same Slot)
```
1. Buat booking ke psikolog yg sama, tanggal sama, jam sama
2. Expect: Error "Jam tersebut sudah terbooking"
3. Database: Cek ada 2 records di consultation_bookings (only 1st succeed)
```

---

## 3. Test API: Get Available Times

### Step 3.1: Manual API Test (Browser)
```
URL: http://localhost/ralira_project/api/get_available_times.php?psychologist_id=1&date=2024-12-27

Expected Response:
{
  "available_times": [
    {"time": "09:00", "display": "09:00-11:00"},
    {"time": "11:00", "display": "11:00-13:00"},
    {"time": "13:00", "display": "13:00-15:00"},
    {"time": "15:00", "display": "15:00-17:00"}
  ],
  "date": "2024-12-27",
  "day_name": "Friday",
  "total_slots": 4,
  "booked_count": 0
}
```

### Step 3.2: API - Day Not Working
```
URL: http://localhost/ralira_project/api/get_available_times.php?psychologist_id=1&date=2024-12-22 (Sunday)

Expected Response:
{
  "available_times": [],
  "day_working": false
}
```

### Step 3.3: API - On Leave
```
URL: http://localhost/ralira_project/api/get_available_times.php?psychologist_id=1&date=2024-12-25

Expected Response:
{
  "available_times": [],
  "on_leave": true
}
```

### Step 3.4: API - Some Booked
```
1. Buat 2 bookings: jam 09:00 dan 11:00
2. Call API: get_available_times.php?psychologist_id=1&date=[booking date]

Expected Response:
{
  "available_times": [
    {"time": "13:00", "display": "13:00-15:00"},
    {"time": "15:00", "display": "15:00-17:00"}
  ],
  "booked_count": 2
}
```

---

## 4. Test Psikolog: Manage Bookings

### Step 4.1: Login sebagai Psikolog
```
1. Go to /pages/auth/login.php
2. Login dengan psikolog account (dari Step 2.4)
3. Go to /pages/psychologist/bookings.php
```

### Step 4.2: View Pending Bookings
```
1. Expect: "Menunggu Konfirmasi" section show bookings dari Step 2.9
2. Expect: Display:
   - Client name, email, phone, gender
   - Tanggal & jam booking (card format)
   - Full RH data (keluhan, lama, pernah konsultasi, tahu dari)
   - Buttons: Terima, Tolak
```

### Step 4.3: Accept Booking
```
1. Click "Terima" button
2. Expect: Confirm dialog
3. Click OK
4. Expect: Success message "Booking diterima!"
5. Expect: Booking move ke "Booking Terkonfirmasi" section
6. Database: consultation_bookings.status_booking = 'confirmed'
```

### Step 4.4: View Confirmed Bookings
```
1. Expect: Booking yg di-accept (Step 4.3) ada di section ini
2. Expect: Card read-only (no buttons)
3. Expect: Tanggal & jam jelas terlihat
```

### Step 4.5: Reject Booking (from pending)
```
1. Buat booking baru dari client (repeat Step 2.1-2.9)
2. Login psikolog
3. Click "Tolak" pada pending booking terbaru
4. Expect: Confirm dialog
5. Click OK
6. Expect: Success message "Booking ditolak."
7. Expect: Booking move ke "Booking Ditolak/Dibatalkan" section (atau hilang dari pending)
8. Database: consultation_bookings.status_booking = 'canceled'
```

---

## 5. Integration Test: Full Flow

### Step 5.1: Complete Happy Path
```
1. [Admin] Set psikolog hari kerja: Mon-Fri
2. [Admin] Add cuti: 25 Dec
3. [Client] Login, isi semua data, book konsultasi:
   - Tanggal: Wed (hari kerja)
   - Jam: 09:00
   - Upload bukti bayar
4. [Psikolog] Login, lihat pending booking, accept
5. Expect: Database state consistent
   - consultation_bookings: 1 record, status=confirmed
   - booking_riwayat_hidup: 1 record
   - payments: 1 record, bukti_transfer save
   - psychologist_schedule_slots: hari Mon-Fri = 1, Sat-Sun = 0
   - psychologist_off_days: 1 record (25 Dec)
```

### Step 5.2: Error Case - Booking Closed Date
```
1. [Admin] Set psikolog hanya Senin kerja
2. [Client] Try book Selasa
3. Expect: Error "Psikolog tidak kerja pada hari ini"
```

### Step 5.3: Error Case - Payment Upload Missing
```
1. [Client] Try book tanpa upload bukti bayar
2. Expect: Form validation jalan (ada validasi?)
   - Note: Current code: Upload optional, tapi recommend required
3. Consider: Add required attribute to file input
```

---

## Database Validation

After all tests, run:

```sql
-- Check bookings
SELECT cb.booking_id, u.name, cb.tanggal_konsultasi, cb.jam_mulai, 
       cb.status_booking, p.status_pembayaran
FROM consultation_bookings cb
JOIN client_details cd ON cb.client_id = cd.client_id
JOIN users u ON cd.user_id = u.user_id
LEFT JOIN payments p ON cb.client_id = p.client_id
ORDER BY cb.created_at DESC;

-- Check schedule slots
SELECT pp.user_id, u.name, pss.hari, pss.is_available
FROM psychologist_schedule_slots pss
JOIN psychologist_profiles pp ON pss.psychologist_id = pp.psychologist_id
JOIN users u ON pp.user_id = u.user_id
ORDER BY u.name, FIELD(pss.hari, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

-- Check off days
SELECT pp.psychologist_id, u.name, pods.tanggal_mulai, pods.tanggal_selesai, pods.alasan
FROM psychologist_off_days pods
JOIN psychologist_profiles pp ON pods.psychologist_id = pp.psychologist_id
JOIN users u ON pp.user_id = u.user_id
ORDER BY pods.tanggal_mulai DESC;
```

---

## Notes

- ⚠️ Ensure uploads directory exists: `/uploads/payments/`
- ⚠️ File permissions allow write: `chmod 777 /uploads/payments/`
- ⚠️ Time zone: Ensure PHP & database time zone consistent
- ⚠️ Date format: All dates in YYYY-MM-DD, times in HH:MM:SS
- ⚠️ Session: Make sure session_start() called on all pages
- ⚠️ Database: Test dengan data yang clean (tidak ada orphan records)

