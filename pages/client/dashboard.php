<?php
/**
 * Filename: pages/client/dashboard.php
 * Description: Dashboard Utama Klien.
 * Features: Ringkasan Jadwal, Status Pembayaran, dan Quick Links.
 * [cite_start]Reference: SRS 5.1 Antarmuka Pengguna (Klien)[cite: 800].
 */

session_start();
$path = '../../';
$page_title = 'Dashboard Klien';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Check if user is logged in and is klien
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../auth/login.php');
    exit;
}

// Get klien name from session
$klien_name = $_SESSION['name'] ?? 'Klien';

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];

// Get user profile picture from database
$sql = "SELECT profile_picture FROM users WHERE user_id = ?";
$user_data = $db->getPrepare($sql, [$user_id]);
if ($user_data && $user_data['profile_picture']) {
    $_SESSION['profile_picture'] = $user_data['profile_picture'];
}

// REAL DATA - Status Pendaftaran (dari booking terakhir)
$registration_status = 'Belum Ada Booking';
$status_color = 'var(--color-text-light)';
$status_icon = '❓';

// Get latest booking status
$sql = "SELECT status_booking, created_at FROM consultation_bookings 
        WHERE client_id = (SELECT client_id FROM client_details WHERE user_id = ?)
        ORDER BY created_at DESC LIMIT 1";
$result = $db->getPrepare($sql, [$user_id]);

if ($result) {
    $booking_status = $result['status_booking'];
    if ($booking_status == 'pending') {
        $registration_status = 'Menunggu Verifikasi';
        $status_color = 'var(--status-warning)';
        $status_icon = '⏳';
    } elseif ($booking_status == 'confirmed') {
        $registration_status = 'Sudah Disetujui';
        $status_color = 'var(--status-success)';
        $status_icon = '✓';
    } elseif ($booking_status == 'cancelled') {
        $registration_status = 'Dibatalkan';
        $status_color = 'var(--status-danger)';
        $status_icon = '✗';
    }
}

// REAL DATA - Jadwal Berikutnya
$next_schedule_date = '-';
$next_schedule_time = '-';
$sql = "SELECT cb.tanggal_konsultasi, cb.jam_konsultasi FROM consultation_bookings cb
        WHERE cb.client_id = (SELECT client_id FROM client_details WHERE user_id = ?)
        AND DATE(cb.tanggal_konsultasi) >= DATE(NOW()) AND cb.status_booking = 'confirmed'
        ORDER BY cb.tanggal_konsultasi ASC LIMIT 1";
$result = $db->getPrepare($sql, [$user_id]);
if ($result) {
    $next_schedule_date = date('d M Y', strtotime($result['tanggal_konsultasi']));
    $next_schedule_time = substr($result['jam_konsultasi'], 0, 5);
}

// REAL DATA - Hasil Tes Tersedia
$test_results_count = 0;
$sql = "SELECT COUNT(*) as count FROM test_results 
        WHERE client_id = (SELECT client_id FROM client_details WHERE user_id = ?)";
$result = $db->getPrepare($sql, [$user_id]);
if ($result) {
    $test_results_count = $result['count'];
}

// REAL DATA - Jadwal Konsultasi Mendatang (all upcoming bookings)
$upcoming_schedules = [];
$sql = "SELECT cb.booking_id, cb.tanggal_konsultasi, cb.jam_konsultasi, cb.status_booking,
               pp.spesialisasi as jenis_konseling,
               u.name as psychologist_name
        FROM consultation_bookings cb
        INNER JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
        INNER JOIN users u ON pp.user_id = u.user_id
        WHERE cb.client_id = (SELECT client_id FROM client_details WHERE user_id = ?)
        AND DATE(cb.tanggal_konsultasi) >= DATE(NOW())
        ORDER BY cb.tanggal_konsultasi ASC";
$result = $db->queryPrepare($sql, [$user_id]);
if (is_array($result)) {
    $upcoming_schedules = $result;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Rali Ra</title>
    
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/glass.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/client.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive_sections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        
        <?php include $path . 'components/sidebar_client.php'; ?>
        <?php include $path . 'components/header_client.php'; ?>

        <main class="main-content">
            
            <!-- MOVED FROM INLINE: Topbar Section -->
            <div class="client-dashboard-topbar glass-panel">
                <h3>
                    <?php
                    date_default_timezone_set('Asia/Jakarta');
                    $hour = date('H');
                    if ($hour >= 4 && $hour < 10) {
                        echo 'Selamat Pagi';
                    } elseif ($hour >= 11 && $hour < 15) {
                        echo 'Selamat Siang';
                    } elseif ($hour >= 16 && $hour < 18) {
                        echo 'Selamat Sore';
                    } else {
                        echo 'Selamat Malam';
                    }
                    ?>, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : htmlspecialchars($klien_name); ?>! <i class="fas fa-hand-wave" style="color: var(--color-accent);"></i>
                </h3>
                <div class="user-profile">
                    <?php if (isset($_SESSION['profile_picture']) && $_SESSION['profile_picture']): ?>
                        <img src="<?php echo $path . $_SESSION['profile_picture']; ?>" alt="Profile">
                    <?php else: ?>
                        <div class="user-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="user-name">
                            <?php echo htmlspecialchars($klien_name); ?>
                        </div>
                        <div class="user-email">
                            <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '<span style=\'color:#aaa\'>Email tidak terdeteksi</span>'; ?>
                        </div>
                        <div class="user-phone">
                            <?php echo isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : '<span style=\'color:#aaa\'>No HP tidak terdeteksi</span>'; ?>
                        </div>
                        <span class="badge badge-success account-badge">Akun Aktif</span>
                    </div>
                </div>
            </div>

            <!-- MOVED FROM INLINE: Modern Stats Grid -->
            <div class="client-stats-grid">
                <div class="client-stat-card status">
                    <div class="client-stat-content">
                        <div class="client-stat-info">
                            <p>Status Pendaftaran</p>
                            <h3 class="status-value"><?php echo $registration_status; ?></h3>
                            <p class="stat-meta">
                                <i class="<?php echo $status_icon; ?>"></i> <?php echo $registration_status; ?>
                            </p>
                        </div>
                        <div class="client-stat-icon status">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>

                <div class="client-stat-card schedule">
                    <div class="client-stat-content">
                        <div class="client-stat-info">
                            <p>Jadwal Berikutnya</p>
                            <h3 class="schedule-value"><?php echo $next_schedule_date; ?></h3>
                            <p class="stat-meta">
                                <i class="fas fa-clock"></i> <?php echo $next_schedule_time; ?> WIB
                            </p>
                        </div>
                        <div class="client-stat-icon schedule">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>

                <div class="client-stat-card results">
                    <div class="client-stat-content">
                        <div class="client-stat-info">
                            <p>Hasil Tes Tersedia</p>
                            <h3 class="results-value"><?php echo $test_results_count; ?></h3>
                            <p class="stat-meta">
                                <i class="fas fa-file-download"></i> Dokumen
                            </p>
                        </div>
                        <div class="client-stat-icon results">
                            <i class="fas fa-file-medical"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="client-schedule-section glass-panel">
                <div class="client-schedule-header">
                    <h3><i class="fas fa-calendar-check" style="color: var(--color-primary); margin-right: 10px;"></i>Jadwal Konsultasi Mendatang</h3>
                    <a href="history.php" class="view-all-btn">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <?php if (empty($upcoming_schedules)): ?>
                    <!-- MOVED FROM INLINE: Empty State -->
                    <div class="schedule-empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h4>Tidak Ada Jadwal Mendatang</h4>
                        <p>Anda belum memiliki jadwal konsultasi yang akan datang</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcoming_schedules as $schedule): ?>
                    <div class="client-schedule-item">
                        <div class="schedule-info">
                            <h4><?php echo date('d F Y', strtotime($schedule['tanggal_konsultasi'])); ?></h4>
                            <p class="schedule-date">
                                <i class="fas fa-clock"></i><?php echo substr($schedule['jam_konsultasi'], 0, 5); ?> WIB
                            </p>
                            <p class="schedule-type">
                                <i class="fas fa-stethoscope"></i><?php echo htmlspecialchars($schedule['jenis_konseling'] ?? 'Konseling'); ?>
                            </p>
                            <p class="schedule-psychologist">
                                <i class="fas fa-user-md"></i>Bersama: <?php echo htmlspecialchars($schedule['psychologist_name']); ?>
                            </p>
                        </div>
                        <div class="schedule-status">
                            <?php
                                if ($schedule['status_booking'] == 'confirmed') {
                                    echo '<span class="schedule-badge confirmed">Terkonfirmasi</span>';
                                } elseif ($schedule['status_booking'] == 'pending') {
                                    echo '<span class="schedule-badge pending">Menunggu Verifikasi</span>';
                                }
                            ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- MOVED FROM INLINE: Quick Actions Grid -->

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
    <script src="<?php echo $path; ?>assets/js/script.js"></script>

</body>
</html>