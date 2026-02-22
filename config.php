<?php
/**
 * Filename: config.php
 * Description: File konfigurasi utama. Menyimpan settingan DB, URL, dan Konstanta.
 * Usage: Include file ini di setiap halaman (biasanya via header.php atau index.php).
 * NOTE: Google OAuth configuration dipindahkan ke includes/google-config.php
 */


// 1. Set Timezone (WIB)
date_default_timezone_set('Asia/Jakarta');

// 2. Mode Pengembangan (Development vs Production)
// Ubah ke 'production' jika sudah live agar error tidak muncul di browser user
define('ENVIRONMENT', 'development'); 

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 3. Konfigurasi Database (Persiapan Sprint Backend)
// [Source: SRS 6.2 Perangkat Lunak - Database MySQL/MariaDB]
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Default XAMPP/MAMP
define('DB_PASS', '');         // Default kosong
define('DB_NAME', 'ralira_db'); // Nama database nanti

// 4. Konstanta Aplikasi
define('APP_NAME', 'Biro Psikologi Rali Ra');
define('APP_VERSION', '2.0');
define('APP_COPYRIGHT', '© ' . date('Y') . ' Rali Ra. All rights reserved.');

// 5. Base URL (PENTING)
// Sesuaikan dengan nama folder di htdocs Anda.
// Jika folder project bernama 'ralira_project', maka:
define('BASE_URL', 'http://localhost/ralira_project/');

// 6. Base Path (Untuk include file PHP server-side)
define('BASE_PATH', __DIR__ . '/');

/**
 * Fungsi Helper Sederhana untuk URL
 * Menggunakan ini lebih aman daripada ngetik manual '../../'
 */
function base_url($url = '') {
    return BASE_URL . $url;
}

/**
 * Fungsi Koneksi Database (Placeholder)
 * Akan diaktifkan nanti saat masuk fase Backend.
 */
function getDBConnection() {
    $conn = null;
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Koneksi Gagal: " . $conn->connect_error);
        }
    } catch (Exception $e) {
        // Di tahap Frontend, kita suppress error dulu jika DB belum ada
        if (ENVIRONMENT === 'development') {
            // echo "Database belum terkoneksi: " . $e->getMessage();
        }
    }
    return $conn;
}
?>