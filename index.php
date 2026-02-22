<?php
/**
 * Filename: index.php
 * Description: Entry Point (Gerbang Utama) Sistem Informasi Biro Psikologi Rali Ra.
 * Function: 
 * 1. Memulai Sesi (Session Start).
 * 2. Mengatur Konfigurasi Dasar/Environment.
 * 3. Routing Logic: Mengecek status login user.
 * - Jika Guest (Belum Login) -> Arahkan ke Landing Page Publik.
 * - Jika User (Sudah Login) -> Arahkan ke Dashboard sesuai Role (Admin/Psikolog/Klien).
 * * Note: Karena ini tahap Frontend Dev, logika Auth masih disimulasikan.
 */

// Hide deprecation warnings for production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

// 1. Inisialisasi Sesi
// Penting untuk melacak login user di masa mendatang
session_start();

// 2. Definisi Konstanta Global (Untuk keamanan & pathing)
define('APP_NAME', 'Biro Psikologi Rali Ra');
define('APP_VERSION', '2.0.0');
define('BASE_PATH', __DIR__); // Path absolut direktori root

// 3. Error Reporting (Aktifkan saat Development, matikan saat Production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/**
 * --------------------------------------------------------------------------
 * ROUTING LOGIC
 * --------------------------------------------------------------------------
 * Memeriksa apakah pengguna memiliki sesi aktif.
 * Jika Backend Database sudah terhubung, bagian ini akan memvalidasi token/session.
 */

// Mengecek variabel session 'user_role' (Diset saat Login nanti)
if (isset($_SESSION['user_role']) && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    
    $role = $_SESSION['user_role'];

    switch ($role) {
        case 'admin':
            // Redirect ke Dashboard Admin
            // [Source: SRS 5.1 Antarmuka Admin]
            header("Location: pages/admin/dashboard.php");
            exit;
            
        case 'psychologist':
            // Redirect ke Dashboard Psikolog
            // [Source: SRS 5.1 Antarmuka Psikolog]
            header("Location: pages/psychologist/dashboard.php");
            exit;
            
        case 'client':
            // Redirect ke Dashboard Klien/User
            // [Source: SRS 5.1 Antarmuka Klien]
            header("Location: pages/client/dashboard.php");
            exit;
            
        default:
            // Role tidak dikenali, logout paksa & kirim ke login
            session_destroy();
            header("Location: pages/auth/login.php?error=invalid_role");
            exit;
    }

} else {
    /**
     * --------------------------------------------------------------------------
     * PUBLIC VISITOR (GUEST)
     * --------------------------------------------------------------------------
     * Jika tidak ada sesi login, pengguna dianggap pengunjung umum.
     * Arahkan ke Halaman Utama (Landing Page).
     */
    
    // Redirect ke Landing Page Public
    // [Source: SRS 2.1 Perspektif Produk - Platform layanan psikologi berbasis web]
    header("Location: pages/public/landing.php");
    exit;
}

/**
 * --------------------------------------------------------------------------
 * FALLBACK HTML
 * --------------------------------------------------------------------------
 * Tampil hanya jika fungsi header() gagal (misal karena output buffering issue).
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=pages/public/landing.php">
    <title>Redirecting... - <?php echo APP_NAME; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F4EED8; /* Eggshell Color Palette */
            color: #5A3D2B; /* Royal Brown */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #FBBA00; /* Yellow Palette */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div>
        <div class="loader"></div>
        <h3>Sedang memuat Rali Ra...</h3>
        <p>Jika Anda tidak dialihkan secara otomatis, <a href="pages/public/landing.php" style="color: #E5781E;">klik di sini</a>.</p>
    </div>
</body>
</html>