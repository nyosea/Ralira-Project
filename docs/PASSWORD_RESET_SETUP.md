# Password Reset Feature - Setup Guide

## 📋 Overview

Fitur **Lupa Password** dengan smart flow yang membedakan:
- **Manual Register** → Reset password via email
- **Google OAuth** → Arahkan ke login dengan Google

## 🔧 Setup Steps

### 1. **Run Database Migration**

Buka browser dan akses:
```
http://localhost/ralira_project/run_migration.php
```

Ini akan menambahkan 3 kolom baru ke tabel `users`:
- `reset_token` - Token untuk reset password
- `reset_token_expires` - Expiry time (1 jam)
- `login_method` - 'manual' atau 'google'

### 2. **File yang Ditambahkan/Modified**

#### ✅ New Files:
- `pages/auth/forgot-password.php` - Form input email
- `pages/auth/reset-password.php` - Form reset password dengan token
- `database/add_password_reset_columns.sql` - SQL migration
- `run_migration.php` - Migration runner script

#### ✏️ Modified Files:
- `pages/auth/login.php` - Link "Lupa Password?" sekarang ke `forgot-password.php`
- `pages/auth/register.php` - Set `login_method = 'manual'` saat daftar
- `pages/auth/google-callback.php` - Set `login_method = 'google'` saat Google OAuth

## 🔄 Flow Diagram

```
1. User klik "Lupa Password?" di login
   ↓
2. Ke forgot-password.php (input email)
   ↓
3. System check di database:
   ├─ Jika Google OAuth → Tampilkan pesan & suggest "Masuk dengan Google"
   └─ Jika Manual → Generate token & kirim email reset link
   ↓
4. User klik link di email (valid 1 jam)
   ↓
5. Ke reset-password.php dengan token
   ↓
6. User input password baru
   ↓
7. Password di-hash & update database
   ↓
8. Redirect ke login page
```

## 📧 Email Configuration

Saat ini menggunakan PHP `mail()` function (development-friendly).

### Production Recommendations:
- Gunakan **PHPMailer** atau **SwiftMailer**
- Setup email credentials di `config.php`
- Contoh:
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
```

## 🔐 Security Features

✅ **Token-based reset** - Secure random token (32 bytes)  
✅ **Time-limited** - Token expires in 1 hour  
✅ **Hashed password** - bcrypt hashing (via `Database::hashPassword()`)  
✅ **Method differentiation** - Google users can't reset (safer)  
✅ **Email verification** - Reset only via email link  

## 🧪 Testing

### Test Manual Register Reset:
1. Register di `register.php` dengan email normal
2. Klik "Lupa Password?" 
3. Input email → Click "Kirim Link Reset"
4. Cek console/logs untuk token (di development)
5. Simulasi email link: `http://localhost/ralira_project/pages/auth/reset-password.php?token=YOUR_TOKEN`
6. Input password baru → Reset

### Test Google OAuth User:
1. Login dengan Google di `login.php`
2. Klik "Lupa Password?"
3. Input email yang login dengan Google
4. Harus muncul pesan: "Akun Anda login via Google. Silakan gunakan tombol 'Masuk dengan Google'"
5. Tombol "Daftar Akun Baru" & "Kembali ke Login" tersedia

## 📱 Responsive Design

- ✅ Mobile-friendly (tested on various screens)
- ✅ Glass-panel design (consistent with login/register)
- ✅ Touch-friendly buttons
- ✅ Clear error/success messages

## 🐛 Troubleshooting

### Email tidak terkirim?
- Check `php.ini` for mail configuration
- Use `run_migration.php` untuk verify database setup
- Check email is in correct format

### Token expired?
- Token valid for 1 hour - adjust di `forgot-password.php` line:
```php
$token_expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Change here
```

### Login method not showing correctly?
- Run migration: `http://localhost/ralira_project/run_migration.php`
- Verify `login_method` column exists: `SELECT * FROM users;`

## 📚 Related Files

- Database schema: `database/ralira_db.sql`
- User authentication: `includes/db.php`
- Google OAuth: `includes/google-config.php`
- Login page: `pages/auth/login.php`

## ✨ Future Enhancements

- SMS-based password reset (optional)
- Two-factor authentication
- Password strength meter
- Recovery codes for Google OAuth users
- Admin panel to manage password resets

---

**Need help?** Check server logs:
```bash
tail -f /var/log/php-errors.log  # Linux
```

For Windows XAMPP:
```
C:\xampp\apache\logs\error.log
C:\xampp\mysql\data\error.log
```
