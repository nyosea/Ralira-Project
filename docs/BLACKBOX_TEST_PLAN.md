# BLACKBOX TESTING PLAN
## Biro Psikologi Rali Ra Web Application

### TEST ENVIRONMENT
- **URL**: http://localhost/ralira_project
- **Browser**: Chrome/Firefox (latest version)
- **Database**: Local XAMPP MySQL
- **Date**: 7 Januari 2026

### TEST SCENARIOS PER ROLE

---

## 1. ADMIN ROLE TESTING

### 1.1 Login Authentication
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Login dengan email admin yang valid | Redirect ke dashboard admin | |
| Login dengan password salah | Error message "Invalid credentials" | |
| Login dengan email tidak terdaftar | Error message "User not found" | |
| Login dengan field kosong | Validation error | |

### 1.2 Dashboard Functionality
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| View statistics real-time | Tampil data statistik yang benar | |
| View pending registrations | Tampil jumlah pendaftaran pending | |
| View today's consultations | Tampil jadwal konsultasi hari ini | |
| Click WhatsApp gateway status | Tampil status gateway | |
| View recent bookings table | Tampil data booking terbaru | |

### 1.3 User Management
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Add new user | User berhasil ditambahkan | |
| Edit existing user | Data user berhasil diupdate | |
| Delete user | User berhasil dihapus | |
| Change user role | Role user berhasil diubah | |

### 1.4 Psychologist Management
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Add new psychologist | Psikolog berhasil ditambahkan | |
| Edit psychologist profile | Data psikolog berhasil diupdate | |
| Upload psychologist photo | Photo berhasil diupload | |
| Set psychologist schedule | Jadwal berhasil disimpan | |

### 1.5 Booking Verification
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| View pending bookings | Tampil daftar booking pending | |
| Approve booking | Status berubah menjadi "Approved" | |
| Reject booking | Status berubah menjadi "Rejected" | |
| Verify payment upload | Bukti pembayaran berhasil diverifikasi | |

---

## 2. PSYCHOLOGIST ROLE TESTING

### 2.1 Login Authentication
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Login dengan email psikolog valid | Redirect ke dashboard psikolog | |
| Login dengan password salah | Error message | |
| Login dengan akon non-aktif | Error message "Account inactive" | |

### 2.2 Dashboard Functionality
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| View today's schedule | Tampil jadwal konsultasi hari ini | |
| View pending reports | Tampil notifikasi laporan pending | |
| View monthly client statistics | Tampil statistik klien bulan ini | |
| View profile photo | Tampil foto profil psikolog | |

### 2.3 Schedule Management
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Add available time slot | Jadwal berhasil ditambahkan | |
| Edit existing schedule | Jadwal berhasil diupdate | |
| Delete time slot | Jadwal berhasil dihapus | |
| View schedule calendar | Tampil kalender jadwal | |

### 2.4 Client Management
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| View bookings list | Tampil daftar booking klien | |
| Access client detail | Tampil informasi lengkap klien | |
| View consultation history | Tampil riwayat konsultasi | |
| Upload test results | Hasil tes berhasil diupload | |

---

## 3. CLIENT ROLE TESTING

### 3.1 Registration & Login
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Register new account | Akun berhasil dibuat | |
| Login dengan Google | Redirect ke dashboard client | |
| Login manual | Redirect ke dashboard client | |
| Forgot password | Email reset terkirim | |

### 3.2 Dashboard Functionality
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| View registration status | Tampil status pendaftaran | |
| View upcoming consultations | Tampil jadwal konsultasi mendatang | |
| Access booking form | Redirect ke halaman booking | |
| Access test results | Redirect ke halaman hasil | |

### 3.3 Booking System
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Fill registration form | Data tersimpan dengan valid | |
| Select psychologist | Psikolog berhasil dipilih | |
| Select date & time | Jadwal berhasil dipilih | |
| Upload payment proof | Bukti pembayaran berhasil diupload | |
| Submit booking | Booking berhasil dibuat | |

### 3.4 History & Results
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| View consultation history | Tampil riwayat konsultasi | |
| Download test results | File hasil tes terdownload | |
| Track booking status | Tampil status terkini | |

---

## 4. CROSS-FUNCTIONAL TESTING

### 4.1 Role-Based Access Control
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Admin access client pages | Should be blocked | |
| Client access admin pages | Should be blocked | |
| Psychologist access admin pages | Should be blocked | |
| Direct URL access without login | Redirect to login page | |

### 4.2 Data Consistency
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| New booking appears in admin dashboard | Data sync real-time | |
| Schedule update reflects in client booking | Data konsisten | |
| Payment verification updates client status | Status terupdate | |

### 4.3 File Upload Testing
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Upload valid image format | File berhasil diupload | |
| Upload invalid file type | Error message | |
| Upload oversized file | Error message | |
| Upload without file | Validation error | |

---

## 5. PERFORMANCE & USABILITY TESTING

### 5.1 Page Load Time
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Dashboard load time | < 3 seconds | |
| Booking page load time | < 3 seconds | |
| Image upload processing | < 5 seconds | |

### 5.2 Responsive Design
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Mobile view compatibility | Layout responsive | |
| Tablet view compatibility | Layout responsive | |
| Desktop view compatibility | Layout normal | |

---

## 6. ERROR HANDLING TESTING

### 6.1 Network Issues
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Disconnect during form submission | Error message, data preserved | |
| Slow network response | Loading indicator | |
| API timeout | Graceful error handling | |

### 6.2 Input Validation
| Test Case | Expected Result | Status |
|-----------|----------------|--------|
| Invalid email format | Validation error | |
| Required field empty | Validation error | |
| Invalid date selection | Validation error | |

---

## TEST EXECUTION CHECKLIST

### Pre-Test Preparation
- [ ] Database backup created
- [ ] Test accounts prepared (Admin, Psikolog, Client)
- [ ] Browser cache cleared
- [ ] Development tools ready

### During Testing
- [ ] Screenshot captured for each test
- [ ] Error messages documented
- [ ] Performance metrics recorded
- [ ] Browser console checked for errors

### Post-Test
- [ ] Test results compiled
- [ ] Bug reports created
- [ ] Recommendations documented
- [ ] Test environment cleaned

---

## NOTES & OBSERVATIONS

### Critical Issues Found:
1. 
2. 
3. 

### Minor Issues Found:
1. 
2. 
3. 

### Recommendations:
1. 
2. 
3. 

---

**Tester**: [Your Name]
**Date Completed**: [Date]
**Total Test Cases**: [Number]
**Passed**: [Number]
**Failed**: [Number]
**Blocked**: [Number]
