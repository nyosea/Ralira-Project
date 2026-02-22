<?php
/**
 * Filename: pages/admin/dashboard.php
 * Description: Dashboard Utama Admin.
 * Features: Statistik Real-time (Simulasi), Jadwal Hari Ini, Log Aktivitas.
 * Reference: SRS 5.1 Antarmuka Admin.
 */

session_start();
$path = '../../';
$page_title = 'Dashboard Admin';

// Set timezone to WIB (Western Indonesia Time)
date_default_timezone_set('Asia/Jakarta');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Get admin name from session
$admin_name = $_SESSION['name'] ?? 'Admin';

// Get statistics from database
$db = new Database();
$db->connect();

// Total pending registrations (status = 'pending')
$sql_pending = "SELECT COUNT(*) as count FROM client_details WHERE status_pendaftaran = 'pending'";
$pending_result = $db->getPrepare($sql_pending, []);
$pending_count = $pending_result['count'] ?? 0;

// Total users
$sql_users = "SELECT COUNT(*) as count FROM users WHERE role = 'klien'";
$users_result = $db->getPrepare($sql_users, []);
$total_users = $users_result['count'] ?? 0;

// Today's consultations
$today = date('Y-m-d');
$sql_today = "SELECT COUNT(*) as count FROM consultation_bookings WHERE DATE(tanggal_konsultasi) = ?";
$today_result = $db->getPrepare($sql_today, [$today]);
$today_count = $today_result['count'] ?? 0;

// New registrations today (klien yang mendaftar hari ini)
$sql_today_registrations = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ? AND role = 'klien'";
$today_registrations_result = $db->getPrepare($sql_today_registrations, [$today]);
$today_registrations = $today_registrations_result['count'] ?? 0;

// Get recent bookings (today only) - untuk verifikasi pembayaran
$sql_recent = "SELECT 
                    cb.booking_id,
                    cd.user_id,
                    u.name as client_name,
                    u.email,
                    psy.name as psychologist_name,
                    cb.tanggal_konsultasi,
                    cb.status_booking,
                    cb.created_at
                FROM consultation_bookings cb
                INNER JOIN client_details cd ON cb.client_id = cd.client_id
                INNER JOIN users u ON cd.user_id = u.user_id
                INNER JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
                INNER JOIN users psy ON pp.user_id = psy.user_id
                ORDER BY cb.created_at DESC LIMIT 5";
$recent_users = $db->queryPrepare($sql_recent, []);
if (!is_array($recent_users)) {
    $recent_users = [];
}

// Pending payment verification (booking with pending status)
$sql_pending_payment = "SELECT COUNT(*) as count FROM consultation_bookings WHERE status_booking = 'pending'";
$pending_payment_result = $db->getPrepare($sql_pending_payment, []);
$pending_payment_count = $pending_payment_result['count'] ?? 0;

// Consultation Status Monitoring
$sql_konsultasi_status = "SELECT 
                                u2.name as psychologist_name,
                                u.name as client_name,
                                cb.booking_id,
                                cb.tanggal_konsultasi,
                                cb.status_booking as booking_status,
                                COALESCE(cs.konsultasi_status, 'belum_ditangani') as konsultasi_status,
                                cs.updated_at as status_updated,
                                CASE 
                                    WHEN cs.konsultasi_status = 'sudah_ditangani' THEN ' Siap Invoice'
                                    WHEN cs.konsultasi_status = 'sedang_ditangani' THEN ' Sedang Proses'
                                    ELSE ' Belum Ditangani'
                                END as status_display
                            FROM consultation_bookings cb
                            INNER JOIN client_details cd ON cb.client_id = cd.client_id
                            INNER JOIN users u ON cd.user_id = u.user_id
                            INNER JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
                            INNER JOIN users u2 ON pp.user_id = u2.user_id
                            LEFT JOIN consultation_status cs ON cb.booking_id = cs.booking_id
                            WHERE cb.status_booking IN ('confirmed', 'completed')
                            ORDER BY cb.tanggal_konsultasi DESC
                            LIMIT 10";
$konsultasi_status_list = $db->queryPrepare($sql_konsultasi_status, []);
if (!is_array($konsultasi_status_list)) {
    $konsultasi_status_list = [];
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/admin.css">

    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>
        
        <main class="main-content">
            <div class="welcome-section">
                <!-- Decorative Pattern -->
                <div style="position: relative; z-index: 1;">
                    <h2 style="margin: 0; font-size: 28px; font-weight: 700; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-chart-line" style="font-size: 24px; opacity: 0.9;"></i>
                        Dashboard Admin
                    </h2>
                    <p style="margin: 8px 0 0 0; font-size: 16px; opacity: 0.9;">
                        <?php 
                        $hour = date('H');
                        $greeting = '';
                        if ($hour >= 5 && $hour < 10) {
                            $greeting = 'Selamat pagi';
                        } elseif ($hour >= 10 && $hour < 14) {
                            $greeting = 'Selamat siang';
                        } elseif ($hour >= 14 && $hour < 18) {
                            $greeting = 'Selamat sore';
                        } else {
                            $greeting = 'Selamat malam';
                        }
                        echo $greeting . ', ' . htmlspecialchars($admin_name) . '! <i class="fas fa-hand-wave" style="color: rgba(255,255,255,0.8);"></i>';
                        ?>
                    </p>
                </div>
            </div>
            
            <!-- Modern Stats Grid -->
            <div style="display: grid;grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));gap: 25px;margin-bottom: 30px;border-radius: 20px;overflow-x: auto;-webkit-overflow-scrolling: touch;scrollbar-width: thin;scrollbar-color: var(--color-primary) #f1f3f4;border: 1px solid #e1e8ed;">
                <div class="modern-stat-card" style="background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed; transition: all 0.3s ease; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: #6c757d; font-size: 14px; font-weight: 500; margin: 0 0 8px 0;">Pendaftaran Baru</p>
                            <h3 style="margin: 0; font-size: 32px; font-weight: 700; color: var(--color-text);"><?php echo $today_registrations; ?></h3>
                            <p style="color: #6c757d; font-size: 12px; margin: 5px 0 0 0;">Klien hari ini</p>
                        </div>
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(251, 186, 0, 0.3);">
                            <i class="fas fa-user-plus" style="color: white; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>

                <div class="modern-stat-card" style="background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed; transition: all 0.3s ease; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: #6c757d; font-size: 14px; font-weight: 500; margin: 0 0 8px 0;">Perlu Verifikasi</p>
                            <h3 style="margin: 0; font-size: 32px; font-weight: 700; color: #ffc107;"><?php echo $pending_payment_count; ?></h3>
                            <p style="color: #6c757d; font-size: 12px; margin: 5px 0 0 0;">Bukti pembayaran</p>
                        </div>
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);">
                            <i class="fas fa-exclamation-triangle" style="color: white; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>

                <div class="modern-stat-card" style="background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed; transition: all 0.3s ease; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: #6c757d; font-size: 14px; font-weight: 500; margin: 0 0 8px 0;">Jadwal Hari Ini</p>
                            <h3 style="margin: 0; font-size: 32px; font-weight: 700; color: #28a745;"><?php echo $today_count; ?></h3>
                            <p style="color: #6c757d; font-size: 12px; margin: 5px 0 0 0;">Sesi konsultasi</p>
                        </div>
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);">
                            <i class="fas fa-calendar-check" style="color: white; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>

                <div class="modern-stat-card" style="background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed; transition: all 0.3s ease; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: #6c757d; font-size: 14px; font-weight: 500; margin: 0 0 8px 0;">Gateway WA</p>
                            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #6c757d;">COMING SOON</h3>
                            <p style="color: #6c757d; font-size: 12px; margin: 5px 0 0 0;">Dalam pengembangan</p>
                        </div>
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);">
                            <i class="fas fa-hammer" style="color: white; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Content Grid -->
            <div class="content-grid">
                
                <!-- Recent Bookings Table -->
                <div style="background: white;border-radius: 20px;padding: 30px;box-shadow: 0 10px 30px rgba(0,0,0,0.08);border: 1px solid #e1e8ed;margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <div>
                            <h3 style="margin: 0; color: var(--color-text); font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-users" style="color: var(--color-primary); font-size: 18px;"></i>
                                Pendaftaran Terbaru
                            </h3>
                            <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 14px;">Booking hari ini</p>
                        </div>
                        <a href="manage_users.php" style="padding: 10px 20px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-eye"></i>
                            Lihat Semua
                        </a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-user" style="margin-right: 8px; color: var(--color-primary);"></i>Klien
                                    </th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-user-nurse" style="margin-right: 8px; color: var(--color-primary);"></i>Psikolog
                                    </th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">Status</th>
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_users)): ?>
                                    <?php foreach ($recent_users as $booking): ?>
                                    <tr style="border-bottom: 1px solid #f1f3f4; transition: all 0.3s ease;">
                                        <td style="padding: 15px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user" style="color: white; font-size: 16px;"></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; color: var(--color-text);"><?php echo htmlspecialchars($booking['client_name']); ?></div>
                                                    <div style="font-size: 12px; color: #6c757d;"><?php echo htmlspecialchars($booking['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 15px;">
                                            <div style="font-weight: 500; color: var(--color-text);"><?php echo htmlspecialchars($booking['psychologist_name']); ?></div>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php if($booking['status_booking'] == 'pending'): ?>
                                                <span style="background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                                    <i class="fas fa-clock" style="font-size: 10px;"></i>
                                                    Pending
                                                </span>
                                            <?php elseif($booking['status_booking'] == 'confirmed'): ?>
                                                <span style="background: #d4edda; color: #155724; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                                    <i class="fas fa-check-circle" style="font-size: 10px;"></i>
                                                    Confirmed
                                                </span>
                                            <?php else: ?>
                                                <span style="background: #e2e3e5; color: #383d41; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                                    <?php echo htmlspecialchars($booking['status_booking']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <?php if($booking['status_booking'] == 'pending'): ?>
                                                <a href="verify_booking.php?booking_id=<?php echo $booking['booking_id']; ?>" style="padding: 8px 16px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="fas fa-check"></i>
                                                    Verifikasi
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #6c757d; font-size: 13px;">
                                                    <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                                    Selesai
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 40px;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
                                                <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-inbox" style="color: #6c757d; font-size: 24px;"></i>
                                                </div>
                                                <div>
                                                    <div style="color: #6c757d; font-weight: 600;">Tidak ada booking hari ini</div>
                                                    <div style="color: #adb5bd; font-size: 14px;">Semua sudah diproses</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Consultation Status Monitoring -->
                <div style="background: white;border-radius: 20px;padding: 30px;box-shadow: 0 10px 30px rgba(0,0,0,0.08);border: 1px solid #e1e8ed;margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <div>
                            <h3 style="margin: 0; color: var(--color-text); font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-tasks" style="color: var(--color-primary); font-size: 18px;"></i>
                                Status Konsultasi
                            </h3>
                            <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 14px;">Monitoring status penanganan klien oleh psikolog</p>
                        </div>
                        <a href="manage_users.php" style="padding: 10px 20px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-eye"></i>
                            Lihat Semua
                        </a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-user-nurse" style="margin-right: 8px; color: var(--color-primary);"></i>Psikolog
                                    </th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-user" style="margin-right: 8px; color: var(--color-primary);"></i>Klien
                                    </th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-calendar" style="margin-right: 8px; color: var(--color-primary);"></i>Tanggal
                                    </th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">Status Konsultasi</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 14px;">Update Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($konsultasi_status_list)): ?>
                                    <?php foreach ($konsultasi_status_list as $status): ?>
                                    <tr style="border-bottom: 1px solid #f1f3f4; transition: all 0.3s ease;">
                                        <td style="padding: 15px;">
                                            <div style="font-weight: 500; color: var(--color-text);"><?php echo htmlspecialchars($status['psychologist_name']); ?></div>
                                        </td>
                                        <td style="padding: 15px;">
                                            <div style="font-weight: 500; color: var(--color-text);"><?php echo htmlspecialchars($status['client_name']); ?></div>
                                        </td>
                                        <td style="padding: 15px;">
                                            <div style="font-weight: 500; color: var(--color-text);"><?php echo date('d M Y', strtotime($status['tanggal_konsultasi'])); ?></div>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php 
                                            if ($status['konsultasi_status'] == 'sudah_ditangani') {
                                                echo '<span style="background: #d4edda; color: #155724; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap:5px;">';
                                                echo '<i class="fas fa-check-circle" style="font-size: 10px;"></i> Sudah Ditangani';
                                                echo '</span>';
                                            } elseif ($status['konsultasi_status'] == 'sedang_ditangani') {
                                                echo '<span style="background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap:5px;">';
                                                echo '<i class="fas fa-spinner" style="font-size: 10px;"></i> Sedang Ditangani';
                                                echo '</span>';
                                            } else {
                                                echo '<span style="background: #f8d7da; color: #721c24; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap:5px;">';
                                                echo '<i class="fas fa-clock" style="font-size: 10px;"></i> Belum Ditangani';
                                                echo '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <div style="font-size: 12px; color: #6c757d;">
                                                <?php 
                                                if ($status['status_updated']) {
                                                    echo date('d M Y H:i', strtotime($status['status_updated']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
                                                <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-tasks" style="color: #6c757d; font-size: 24px;"></i>
                                                </div>
                                                <div>
                                                    <div style="color: #6c757d; font-weight: 600;">Belum ada data status konsultasi</div>
                                                    <div style="color: #adb5bd; font-size: 14px;">Psikolog belum mengupdate status konsultasi klien</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- WhatsApp Logs -->
                <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed;">
                    <div style="margin-bottom: 25px;">
                        <h3 style="margin: 0; color: var(--color-text); font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-bell" style="color: #6c757d; font-size: 18px;"></i>
                            Log Notifikasi
                        </h3>
                        <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 14px;">Coming Soon - Dalam pengembangan</p>
                    </div>
                    
                    <div style="space-y: 15px;">
                        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #6c757d; padding: 20px; border-radius: 0 10px 10px 0; transition: all 0.3s ease; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: 10px; right: 10px; background: #6c757d; color: white; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; text-transform: uppercase;">
                                Coming Soon
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #6c757d; border-radius: 50%;"></div>
                                    <strong style="color: #6c757d; font-size: 14px;">System</strong>
                                </div>
                                <span style="color: #6c757d; font-size: 12px;">--:--</span>
                            </div>
                            <p style="margin: 0; color: #6c757d; font-size: 13px; line-height: 1.5;">Fitur notifikasi WhatsApp sedang dalam pengembangan</p>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #6c757d; padding: 20px; border-radius: 0 10px 10px 0; transition: all 0.3s ease; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: 10px; right: 10px; background: #6c757d; color: white; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; text-transform: uppercase;">
                                Coming Soon
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #6c757d; border-radius: 50%;"></div>
                                    <strong style="color: #6c757d; font-size: 14px;">Pendaftaran</strong>
                                </div>
                                <span style="color: #6c757d; font-size: 12px;">--:--</span>
                            </div>
                            <p style="margin: 0; color: #6c757d; font-size: 13px; line-height: 1.5;">Notifikasi pendaftaran baru akan segera tersedia</p>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #6c757d; padding: 20px; border-radius: 0 10px 10px 0; transition: all 0.3s ease; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: 10px; right: 10px; background: #6c757d; color: white; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; text-transform: uppercase;">
                                Coming Soon
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #6c757d; border-radius: 50%;"></div>
                                    <strong style="color: #6c757d; font-size: 14px;">System</strong>
                                </div>
                                <span style="color: #6c757d; font-size: 12px;">--:--</span>
                            </div>
                            <p style="margin: 0; color: #6c757d; font-size: 13px; line-height: 1.5;">Pengingat jadwal otomatis akan segera hadir</p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: center;">
                        <div style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 12px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fas fa-tools"></i>
                            Fitur dalam pengembangan
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate stats numbers on load
        const statNumbers = document.querySelectorAll('.modern-stat-card h3');
        statNumbers.forEach((number, index) => {
            const finalValue = parseInt(number.textContent);
            let currentValue = 0;
            const increment = finalValue / 30;
            
            setTimeout(() => {
                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(counter);
                    }
                    number.textContent = Math.floor(currentValue);
                }, 50);
            }, index * 200);
        });
        
        // Add ripple effect to cards
        const cards = document.querySelectorAll('.modern-stat-card');
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    border-radius: 50%;
                    background: rgba(255,255,255,0.5);
                    left: ${x}px;
                    top: ${y}px;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
        
        // Search functionality
        const searchInput = document.querySelector('input[placeholder="Cari menu..."]');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                // Implement search functionality here
                console.log('Searching for:', searchTerm);
            });
        }
    });
    
    // Add ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Remove active state from buttons */
        .modern-stat-card:active,
        a:active,
        button:active {
            transform: scale(1) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
        }
        
        .modern-stat-card:active {
            transform: translateY(0) !important;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>