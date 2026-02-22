# Ralira Project - AI Copilot Instructions

**Project**: Biro Psikologi Rali Ra - Psychologist Booking & Schedule Management System  
**Tech Stack**: PHP 7.4+, MySQL 5.7+, Tailwind CSS, Vanilla JS  
**Language**: Indonesian (primary) with English documentation

## 🏗️ Architecture Overview

### Directory Structure & Responsibilities

```
├── pages/
│   ├── public/          → Landing, services, about pages (unauthenticated)
│   ├── auth/            → Login, registration, Google OAuth
│   ├── admin/           → Admin dashboards (manage schedules, psychologists, users)
│   ├── psychologist/    → Psychologist views (view bookings, manage schedule)
│   └── client/          → Client views (booking, profile, history)
├── api/                 → JSON endpoints (get_available_dates, get_available_times)
├── includes/            → Core infrastructure (db.php, google-config.php)
├── components/          → Reusable UI (header.php, footer.php, sidebars)
├── assets/              → CSS, JS, images (Tailwind + custom CSS)
└── database/            → SQL schemas (ralira_db.sql)
```

### Core Data Model

**3 Main User Roles**:
- **Admin**: Manage psychologists, their schedules, verify payments
- **Psychologist**: View pending bookings, accept/reject consultations, manage availability
- **Client**: Browse psychologists, book consultations, upload payment proof

**Key Tables**:
- `users` - Email, name, password, role (admin/psychologist/client)
- `client_details` - Gender, birth date, NIK, address, registration status
- `psychologist_profiles` - Specialization, SIPP number, experience
- `psychologist_schedule_dates` - Daily schedule (tanggal, jam_mulai, jam_selesai, is_available)
- `psychologist_off_days` - Leave periods (tanggal_mulai, tanggal_selesai)
- `consultation_bookings` - Reservations (client→psychologist, status: pending/confirmed/canceled)

## 🔄 Critical Workflows

### 1. Psychologist Schedule Management (Admin)
**File**: `pages/admin/manage_psychologist_schedule.php`

**Pattern**: Admin sets working **DAYS** (Mon-Sun), not per-date slots.
- Days are stored as ENUM in database or derived from `psychologist_schedule_dates`
- Fixed time slots: **09:00-11:00, 11:00-13:00, 13:00-15:00, 15:00-17:00** (2-hour sessions)
- Admin can add **off-day ranges** (e.g., vacation) via `psychologist_off_days` table
- **Database query pattern**:
  ```php
  // Get available slots for a psychologist
  SELECT jam_mulai, jam_selesai FROM psychologist_schedule_dates 
  WHERE psychologist_id = ? AND is_available = 1 AND tanggal = ?
  ```

### 2. Client Booking with Smart Filtering
**Files**: `pages/client/booking.php` + `api/get_available_times.php`

**Flow**:
1. Client selects psychologist & date (1-21 days ahead)
2. **AJAX** calls `api/get_available_times.php?psychologist_id=X&date=YYYY-MM-DD`
3. API **smart filtering**:
   - ✅ Check if psychologist works on that day
   - ✅ Check if date is not in off-days
   - ✅ Filter out already-booked slots
4. Return JSON with available times only

**Database query flow**:
```php
// Step 1: Get working slots for date
SELECT jam_mulai FROM psychologist_schedule_dates 
WHERE psychologist_id = ? AND tanggal = ? AND is_available = 1

// Step 2: Get booked times
SELECT jam_konsultasi FROM consultation_bookings 
WHERE psychologist_id = ? AND tanggal_konsultasi = ? 
AND status_booking IN ('pending', 'confirmed')

// Step 3: Return difference (available = working - booked)
```

### 3. Psychologist Booking Management
**File**: `pages/psychologist/bookings.php` (NEW!)

**Tabs**:
- **Menunggu Konfirmasi**: Pending bookings - shows client full history + accept/reject buttons
- **Booking Terkonfirmasi**: Confirmed appointments (psychologist's calendar)
- **Booking Ditolak**: Reject history (last 10)

**Key SQL patterns**:
```php
// Get pending for psychologist
SELECT cb.*, u.name, u.email, u.phone, cd.gender 
FROM consultation_bookings cb 
JOIN client_details cd ON cb.client_id = cd.client_id
JOIN users u ON cd.user_id = u.user_id
WHERE cb.psychologist_id = ? AND status_booking = 'pending'
```

## 💾 Database Helper Class

**File**: `includes/db.php`  
**Class**: `Database`

**Key Methods** (always use these, don't write raw SQL):
```php
$db = new Database();
$db->connect();

// SELECT all - returns array of rows
$results = $db->query($sql);

// SELECT one - returns single row
$row = $db->get($sql);

// SELECT with parameters (safe) - use for user input
$results = $db->queryPrepare($sql, [$param1, $param2]);
$row = $db->getPrepare($sql, [$param1]);

// INSERT/UPDATE/DELETE (safe)
$db->executePrepare($sql, [$param1, $param2]);

// Utility methods
$db->lastId()  // Last inserted ID
$db->escape($string)  // Escape string
Database::hashPassword($pw)  // bcrypt hash
Database::verifyPassword($pw, $hash)  // bcrypt verify
```

**CRITICAL**: Always use `*Prepare()` methods with user input to prevent SQL injection.

## 🎨 UI & CSS Conventions

**Design System**: Tailwind CSS + custom color variables  
**File**: `assets/css/style.css` + Tailwind config in `components/header.php`

**Brand Colors** (defined in tailwind.config):
```js
ralira: {
  bg: '#F4EED8',       // Eggshell background
  primary: '#FBBA00',  // Selective Yellow (accents)
  accent: '#E5781E',   // Vivid Tangelo (highlights)
  text: '#5A3D2B',     // Royal Brown (text)
}
```

**Usage**:
```html
<!-- Use these consistently -->
<div class="bg-ralira-bg text-ralira-text border border-ralira-primary">
  <button class="bg-ralira-primary hover:bg-ralira-accent">
```

**Layout**: Mobile-first responsive (Tailwind breakpoints: sm, md, lg)  
**Components**: Cards, modals, tables in `assets/css/` files (e.g., `schedule_management.css`)

## 🔐 Authentication & Session

**File**: `pages/auth/login.php`  
**Pattern**: Session-based (traditional PHP sessions)

**Session variables** (set after login):
```php
$_SESSION['user_id']     // int - user_id from users table
$_SESSION['role']        // enum: 'admin', 'psychologist', 'client'
$_SESSION['is_logged_in'] // bool: true
$_SESSION['name']        // string - user display name
$_SESSION['email']       // string - user email
```

**Authentication check** (required in every protected page):
```php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
```

## 📱 API Endpoints Pattern

**Location**: `api/` folder  
**Format**: JSON responses with consistent structure

**Response template**:
```php
header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'data' => [],
    'error' => null
];

// ... processing ...

echo json_encode($response);
```

**Example usage** (from booking page):
```js
fetch(`api/get_available_times.php?psychologist_id=${psychId}&date=${date}`)
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // Populate time slots
    }
  });
```

## 🌐 URL Routing

**No dedicated router** - file structure = URL structure  
- `/pages/public/landing.php` → URL: `index.php` (homepage)
- `/pages/client/booking.php` → URL: `pages/client/booking.php`
- `/pages/admin/dashboard.php` → URL: `pages/admin/dashboard.php`

**Entry point**: `index.php` - checks session & redirects by role

**Base URL helper** (defined in `config.php`):
```php
define('BASE_URL', 'http://localhost/ralira_project/');
echo base_url('pages/client/booking.php');  // Safe URL building
```

## 📋 Common Development Tasks

### Adding a New Page
1. Create file in appropriate `pages/[role]/` folder
2. Add session check at top (see Authentication pattern above)
3. Include header/footer: `include $path . 'components/header.php'`
4. Use Database class for queries
5. Use brand colors (ralira-primary, ralira-accent, etc.)

### Adding a New API Endpoint
1. Create file in `api/` folder
2. Set header: `header('Content-Type: application/json; charset=utf-8');`
3. Return JSON in standard response format (success, data, error)
4. Always validate & sanitize inputs

### Modifying Booking Flow
- **Time slots are hardcoded**: 09:00, 11:00, 13:00, 15:00 (don't parameterize)
- **Working days are flexible**: Stored in `psychologist_schedule_dates` as 7 records/week
- **Off-days are ranges**: Check both start & end dates in `psychologist_off_days`

## ⚠️ Project-Specific Patterns & Gotchas

1. **Timezone**: Always set to `Asia/Jakarta` (WIB) in `config.php`
2. **Date format**: Store as `YYYY-MM-DD`, display localized in Indonesian
3. **Time slots**: Always 2-hour blocks (09:00-11:00, etc.) - fixed by design
4. **Role-based access**: No endpoint-level middleware; each page checks `$_SESSION['role']` manually
5. **Google OAuth**: Configured in `includes/google-config.php` (separate from `config.php`)
6. **Status enums**: booking status = `'pending' | 'confirmed' | 'canceled'`; registration status = `'pending' | 'verified' | 'rejected'`

## 📚 Documentation References

Key docs in `/docs/`:
- `QUICK_REFERENCE.md` - Booking flow summary
- `README_IMPLEMENTASI.md` - Implementation details
- `JADWAL_IMPLEMENTATION.md` - Schedule system specifics
- `TESTING_CHECKLIST.md` - QA procedures

## 🚀 Quick Setup for New Contributors

1. **Database**: Import `database/ralira_db.sql` into MySQL
2. **Config**: Update `config.php` with local DB credentials
3. **Dependencies**: `composer install` (Google API client)
4. **Timezone**: Ensure `config.php` has `date_default_timezone_set('Asia/Jakarta')`
5. **Server**: Run on XAMPP/MAMP at `http://localhost/ralira_project/`

## 📝 Code Style Notes

- **Language**: Indonesian for comments/variable names (following existing convention)
- **Comments**: Use `/* */` for blocks, `//` for inline, with Indonesian descriptions
- **Error handling**: Use try-catch for database operations; display user-friendly messages
- **SQL**: Always prepared statements for user inputs; never concatenate strings
- **DateTime**: Use `date()`, `strtotime()` with Asia/Jakarta timezone already set
