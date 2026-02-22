**UPDATE JADWAL - FITUR BARU SELESAI ✅**

---

## 📝 FITUR YANG DITAMBAHKAN

### 1. **EDIT JADWAL (Mode Edit)**

**Cara Pakai:**
1. Klik tanggal hijau (yang sudah ada jadwalnya)
2. Otomatis masuk "Mode Mengedit Jadwal" (lihat notif kuning)
3. Di bagian "Pilih Jam Kerja", jam yang sudah di-set akan ter-check otomatis
4. Bisa uncheck jam yang tidak ingin dijadwalkan lagi
5. Klik "Simpan Jadwal" untuk update

**Contoh:**
- Jadwal awal: 09:00-11:00, 11:00-13:00, 13:00-15:00
- Mau di-edit: hapus jam 09:00-11:00, keep yang lain
- Uncheck jam 09:00-11:00, klik Save
- Hasilnya: tanggal tetap hijau, tapi hanya punya 2 jam

---

### 2. **BATALKAN JADWAL (Bulk Delete)**

**Cara Pakai:**
1. Klik tombol merah "Batalkan Jadwal"
2. Modal terbuka, semua jadwal yang tersimpan ditampilkan
3. Pilih jadwal mana yang mau dihapus (bisa pilih multiple)
4. Klik "OK" untuk hapus
5. Tanggal berubah dari hijau ke putih (jika semua jam dihapus)

---

## ⚠️ FITUR KEAMANAN - WARNING BOOKING

**Jika tanggal+jam sudah di-booking klien:**
- Sistem akan menampilkan warning: "Ada X jadwal dengan booking dari klien (jam: ...)"
- Jadwal yang sudah di-booking **TIDAK akan dihapus**
- Hanya jadwal yang tidak ada booking yang dihapus

**Contoh:**
- Mau hapus: 09:00-11:00, 11:00-13:00, 13:00-15:00
- Ternyata 09:00-11:00 sudah di-booking klien
- Hasil: hanya 11:00-13:00 dan 13:00-15:00 yang dihapus
- 09:00-11:00 tetap ada (protected dari deletion)

---

## 🔧 PERUBAHAN FILE

### Backend (PHP)
- **[pages/psychologist/schedule.php](pages/psychologist/schedule.php)**
  - Tambah handler AJAX: `get_schedules_for_date`
  - Tambah handler AJAX: `delete_multiple_schedules` (dengan booking detection)
  - Tambah modal HTML untuk delete

- **[pages/admin/manage_psychologist_schedule.php](pages/admin/manage_psychologist_schedule.php)**
  - Sama seperti psychologist page
  - `get_schedules_for_date` dengan psychologist_id param
  - `delete_multiple_schedules` dengan booking check
  - Tambah modal HTML untuk delete

### Frontend (JavaScript)
- **[assets/js/schedule.js](assets/js/schedule.js)**
  - Tambah: `enterEditMode()` - load existing schedules
  - Tambah: `exitEditMode()` - reset form
  - Tambah: `showDeleteModal()` - show bulk delete UI
  - Tambah: `closeDeleteModal()`
  - Tambah: `confirmDeleteSchedules()` - execute deletion
  - Update: save handler untuk support edit mode

### Admin Inline Script (manage_psychologist_schedule.php)
- Tambah: `editingMode`, `editingDate`, `schedulesToDelete` variables
- Tambah: `enterEditMode()` function
- Tambah: `exitEditMode()` function
- Tambah: `showDeleteModal()` function
- Tambah: `closeDeleteModal()` function
- Tambah: `confirmDeleteSchedules()` function

---

## 🎨 UI ELEMENTS ADDED

1. **Edit Mode Notification** (notif kuning)
   - Muncul saat klik tanggal hijau
   - Menunjukkan user sedang dalam mode edit
   - Hilang otomatis saat save/exit

2. **Delete Modal** (overlay dengan checkbox list)
   - Menampilkan semua jadwal yang ada
   - User bisa multi-select jadwal untuk hapus
   - Tombol "Batal" dan "OK"

3. **Delete Button** (tombol merah)
   - "Batalkan Jadwal"
   - Letaknya di bawah tombol Save/Reset

---

## 🚀 TESTING CHECKLIST

### Psikolog Page
- [ ] Klik tanggal hijau → masuk edit mode
- [ ] Notif kuning muncul
- [ ] Jam yang sudah di-set otomatis ter-check
- [ ] Uncheck jam, klik Save → jam dihapus
- [ ] Tanggal tetap hijau (jika masih ada jam)
- [ ] Tanggal jadi putih (jika semua jam dihapus)
- [ ] Klik "Batalkan Jadwal" → modal muncul
- [ ] Select jadwal di modal → checkbox change work
- [ ] Klik OK → jadwal dihapus, page reload
- [ ] Jika ada booking → warning muncul, jadwal yang booked tidak dihapus

### Admin Page
- [ ] Sama seperti psikolog page
- [ ] Pilih psikolog dulu
- [ ] Semua fitur berfungsi sama

---

## 💾 DATABASE

Table: `psychologist_schedule_dates`
- Tetap pakai column yang sama
- `is_available = 1` untuk aktif
- `is_available = 0` untuk dihapus (soft delete)

Check booking di: `consultation_bookings`
- Column: `tanggal_konsultasi`, `jam_konsultasi`, `psychologist_id`
- Status != 'canceled' artinya masih aktif/booked

---

**Status: ✅ SELESAI - SIAP TESTING**
