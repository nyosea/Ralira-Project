# 📋 INDEX - Sistem Booking & Jadwal Psikolog

## 📖 Dokumentasi

### Ringkas (START HERE)
- **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** ⭐
  - Alur sistem (diagram)
  - File yang berubah (summary)
  - Database (unchanged)
  - Testing quick start
  - Important notes

### Detail
- **[SISTEM_BOOKING.md](SISTEM_BOOKING.md)**
  - Overview lengkap
  - Alur per-role (Admin, Client, Psikolog, API)
  - Database schema (detail)
  - Validasi key points
  - File changes (technical)

### Testing
- **[TESTING_CHECKLIST.md](TESTING_CHECKLIST.md)**
  - Prerequisites
  - Step-by-step testing per role
  - Integration tests
  - Database validation queries
  - Error case testing

---

## 🔧 File yang Berubah/Dibuat

### 1. Admin - Manage Psychologist Schedule
**File**: `pages/admin/manage_psychologist_schedule.php`
**Type**: UPDATED ✏️
**Perubahan**:
- UI: Dari grid 21x4 (tanggal x jam) → 7 checkboxes (hari saja)
- Logic: Simpan per HARI (Monday-Sunday), bukan per tanggal
- Keep: Cuti/off days management tetap

**New UI**:
```
[✓] Senin    [✓] Selasa  [✓] Rabu  [✓] Kamis  [✓] Jumat  [✓] Sabtu  [ ] Minggu
+ Tombol: Centang Semua / Hapus Semua
+ Cuti section tetap sama
```

---

### 2. Client - Booking Konsultasi
**File**: `pages/client/booking.php`
**Type**: UPDATED ✏️
**Perubahan**:
- Add: Validation check hari kerja psikolog
- Add: Validation check off_days psikolog
- Add: API call ke `/api/get_available_times.php`
- Improve: Better error messages

**New Logic**:
```
Pilih psikolog → Pilih tanggal → [AJAX] Load jam tersedia
- Cek: Hari kerja? → Cek: Cuti? → Cek: Booked? → Display: Jam kosong
```

---

### 3. API - Get Available Times
**File**: `api/get_available_times.php`
**Type**: UPDATED ✏️
**Endpoint**: `GET /api/get_available_times.php?psychologist_id=X&date=YYYY-MM-DD`

**Logic**:
1. Validate: psychologist_id, date (format & range)
2. Check: Hari kerja psikolog? (from `psychologist_schedule_slots`)
3. Check: Cuti psikolog? (from `psychologist_off_days`)
4. Check: Jam sudah booking? (from `consultation_bookings`)
5. Return: Array jam kosong (JSON)

**Response**:
```json
{
  "available_times": [
    {"time": "09:00", "display": "09:00-11:00"},
    {"time": "13:00", "display": "13:00-15:00"}
  ],
  "date": "2024-12-27",
  "day_name": "Friday",
  "total_slots": 4,
  "booked_count": 2
}
```

---

### 4. Psikolog - Manage Bookings (NEW!)
**File**: `pages/psychologist/bookings.php`
**Type**: CREATED 🆕
**Purpose**: Psikolog lihat & manage incoming bookings

**Features**:
- **Pending Bookings**: List booking yg perlu accept/reject
  - Tampil: Client info (nama, email, telp, gender)
  - Tampil: Full RH (keluhan, lama, riwayat, background, source)
  - Action: Accept (→ confirmed) / Reject (→ canceled)

- **Confirmed Bookings**: List booking yg sudah terkonfirmasi
  - Read-only untuk referensi jadwal

- **Canceled Bookings**: Riwayat booking ditolak/dibatalkan
  - Last 10 records untuk audit

**UI**:
- Card-based design
- Status badges (pending/confirmed/canceled)
- Responsive layout

---

## 🗄️ Database (No Schema Changes)

### Table: psychologist_schedule_slots
```
✅ No migration needed
📝 Data change: Store per HARI, bukan per jam
- Before: 7 days × 4 slots × psychologist = 28 records
- After: 7 days × 1 marker × psychologist = 7 records
```

### Table: consultation_bookings
```
✅ No changes (already OK)
Columns: booking_id, client_id, psychologist_id, 
         tanggal_konsultasi, jam_mulai, status_booking
```

### Table: booking_riwayat_hidup
```
✅ No changes (already OK)
Stores: RH data per booking
```

### Table: psychologist_off_days
```
✅ No changes (already OK)
Stores: Cuti/libur psikolog per range tanggal
```

---

## 🎯 Alur Sistem (Visual)

```
┌─────────────────────────────────────────────────────────────────┐
│                        ADMIN DASHBOARD                          │
├─────────────────────────────────────────────────────────────────┤
│ 1. Pilih psikolog                                               │
│ 2. Centang hari kerja (Mon-Sun) ← SIMPLE!                       │
│ 3. Add cuti per tanggal range                                   │
│ 4. Save                                                          │
└─────────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT BOOKING                           │
├─────────────────────────────────────────────────────────────────┤
│ 1. Isi data personal + RH form                                  │
│ 2. Pilih layanan → Filter psikolog by spesialisasi             │
│ 3. Pilih tanggal (1-21 hari ke depan)                          │
│    ↓ [AJAX API CALL]                                           │
│    → Cek hari kerja? ✓                                         │
│    → Cek cuti? ✓                                               │
│    → Cek jam booked? ✓                                         │
│    → Return jam kosong                                         │
│ 4. Pilih jam → Upload bukti → Submit                          │
│    Status: PENDING (waiting admin verification)               │
└─────────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────────┐
│                        ADMIN VERIFICATION                       │
├─────────────────────────────────────────────────────────────────┤
│ 1. Review bukti transfer                                        │
│ 2. Approve → Status: CONFIRMED                                  │
│    OR Reject → Status: CANCELED                                │
└─────────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────────┐
│                      PSIKOLOG DASHBOARD                         │
├─────────────────────────────────────────────────────────────────┤
│ 1. View "Menunggu Konfirmasi" (pending bookings)               │
│    - Client info + RH lengkap                                 │
│    - Action: Accept / Reject                                  │
│ 2. View "Booking Terkonfirmasi" (jadwal)                       │
│ 3. View "Booking Ditolak" (riwayat)                           │
└─────────────────────────────────────────────────────────────────┘
```

---

## ✅ Implementation Checklist

### Admin Setup
- [x] Simplify UI (hari saja, bukan tanggal)
- [x] Update POST handler (per hari, bukan per tanggal)
- [x] Keep cuti management

### Client Booking
- [x] Improve validation (hari kerja, off days, booked)
- [x] Add API integration
- [x] Update error messages

### API Endpoint
- [x] Implement `/api/get_available_times.php`
- [x] Logic: hari kerja check
- [x] Logic: off days check
- [x] Logic: booked times check

### Psikolog Dashboard
- [x] Create `/pages/psychologist/bookings.php`
- [x] Show pending bookings
- [x] Show confirmed bookings
- [x] Show canceled bookings
- [x] Accept/Reject functionality
- [x] Display RH data

### Documentation
- [x] QUICK_REFERENCE.md
- [x] SISTEM_BOOKING.md
- [x] TESTING_CHECKLIST.md
- [x] Ini file (INDEX.md)

---

## 🧪 Quick Testing

### Minimal Test (5 min)
```bash
1. Admin: Set psikolog hari kerja (Mon-Fri)
2. Client: Book tanggal Rabu, jam 09:00
3. Check database: consultation_bookings has 1 record
4. Success! ✓
```

### Full Test (30 min)
Refer: TESTING_CHECKLIST.md - Full testing guide

---

## 📞 Support / Questions

### Q: Jam bisa diubah?
**A**: TIDAK! Jam tetap: 09:00, 11:00, 13:00, 15:00

### Q: Client bisa booking berapa hari ke depan?
**A**: 1-21 hari ke depan (3 minggu)

### Q: Psikolog bisa lihat client RH?
**A**: YA! Di `/pages/psychologist/bookings.php`

### Q: Kalau psikolog cuti, gimana client?
**A**: Client lihat jam kosong (API return empty), auto show error

### Q: Payment verification di mana?
**A**: Admin dashboard (future development) - sekarang hanya store di `payments` table

---

## 📌 Key Changes Summary

| Aspek | Before | After |
|-------|--------|-------|
| **Admin UI** | Grid 21×4 (84 checkbox) | 7 checkboxes (1 per hari) |
| **Admin Logic** | Per tanggal & jam | Per HARI (tetap) |
| **Client Load Times** | Manual dropdown | AJAX API (smart filter) |
| **Psikolog View** | Belum ada | NEW! /bookings.php |
| **DB Schema** | - | No changes |
| **JAM** | Tetap | TETAP (09, 11, 13, 15) |

---

## 🎬 Next Steps

1. **Deploy files** ke server
2. **Test menyeluruh** per TESTING_CHECKLIST.md
3. **Verify database** queries
4. **Monitor** untuk error
5. **Go live!** 🚀

---

**Created**: December 26, 2025
**Status**: Ready for Testing ✅
**Version**: 1.0
