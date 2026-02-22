<?php
/**
 * Filename: pages/psychologist/dashboard.php
 * Description: Dashboard Utama Psikolog.
 * Features: Jadwal Hari Ini (Real-time), Notifikasi Laporan Pending, Statistik Klien.
 * [cite_start]Reference: SRS 5.1 Antarmuka Psikolog, Flowchart Psikolog[cite: 785].
 */

session_start();
$path = '../../';
$page_title = 'Dashboard Psikolog';

// Set timezone to WIB (Western Indonesia Time)
date_default_timezone_set('Asia/Jakarta');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Check if user is logged in and is psikolog
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Get psikolog name from session
$psikolog_name = $_SESSION['name'] ?? 'Psikolog';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];

// Get psychologist profile
$psychologist_data = $db->getPrepare("SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?", [$user_id]);
$psychologist_id = $psychologist_data['psychologist_id'] ?? null;
$psychologist_photo = $_SESSION['profile_picture'] ?? NULL;

// REAL DATA - Jadwal Mendatang (30 hari)
$today_sessions = [];
if ($psychologist_id) {
    $sql = "SELECT cb.booking_id, cb.tanggal_konsultasi, cb.jam_konsultasi, cb.status_booking,
                   u.name AS client_name
            FROM consultation_bookings cb
            INNER JOIN client_details cd ON cb.client_id = cd.client_id
            INNER JOIN users u ON cd.user_id = u.user_id
            WHERE cb.psychologist_id = ? AND DATE(cb.tanggal_konsultasi) BETWEEN DATE(NOW()) AND DATE_ADD(DATE(NOW()), INTERVAL 3 MONTH) AND cb.status_booking IN ('confirmed','pending')
            ORDER BY cb.tanggal_konsultasi ASC, cb.jam_konsultasi ASC
            LIMIT 5";
    $result = $db->queryPrepare($sql, [$psychologist_id]);
    if (is_array($result)) {
        $today_sessions = $result;
    }
} 

// REAL DATA - Total Klien Bulan Ini
$total_clients_month = 0;
if ($psychologist_id) {
    $sql = "SELECT COUNT(DISTINCT cb.client_id) as total
            FROM consultation_bookings cb
            WHERE cb.psychologist_id = ? AND MONTH(cb.tanggal_konsultasi) = MONTH(NOW()) AND YEAR(cb.tanggal_konsultasi) = YEAR(NOW())";
    $result = $db->getPrepare($sql, [$psychologist_id]);
    if ($result) {
        $total_clients_month = $result['total'];
    }
}

// REAL DATA - Notifikasi Tugas (Laporan belum diupload)
$pending_reports = [];
if ($psychologist_id) {
    $sql = "SELECT DISTINCT u.name as client, cb.tanggal_konsultasi, u.user_id
            FROM consultation_bookings cb
            INNER JOIN client_details cd ON cb.client_id = cd.client_id
            INNER JOIN users u ON cd.user_id = u.user_id
            LEFT JOIN test_results tr ON tr.client_id = cb.client_id AND tr.psychologist_id = cb.psychologist_id
            WHERE cb.psychologist_id = ? AND cb.status_booking = 'confirmed'
            AND tr.result_id IS NULL
            ORDER BY cb.tanggal_konsultasi DESC
            LIMIT 10";
    $result = $db->queryPrepare($sql, [$psychologist_id]);
    if (is_array($result)) {
        $pending_reports = $result;
    }
}

// Consultation Status Monitoring untuk psikolog
$konsultasi_status_list = [];
if ($psychologist_id) {
    $sql_konsultasi_status = "SELECT 
                                u.name as client_name,
                                cb.booking_id,
                                cb.tanggal_konsultasi,
                                cb.status_booking as booking_status,
                                COALESCE(cs.konsultasi_status, 'belum_ditangani') as konsultasi_status,
                                cs.updated_at as status_updated
                            FROM consultation_bookings cb
                            INNER JOIN client_details cd ON cb.client_id = cd.client_id
                            INNER JOIN users u ON cd.user_id = u.user_id
                            LEFT JOIN consultation_status cs ON cb.booking_id = cs.booking_id
                            WHERE cb.psychologist_id = ? AND cb.status_booking IN ('confirmed', 'completed')
                            ORDER BY cb.tanggal_konsultasi DESC
                            LIMIT 10";
    $result = $db->queryPrepare($sql_konsultasi_status, [$psychologist_id]);
    if (is_array($result)) {
        $konsultasi_status_list = $result;
    }
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/psychologist.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        
        <?php include $path . 'components/sidebar_psychologist.php'; ?>
        <?php include $path . 'components/header_psychologist.php'; ?>


        <main class="main-content">
            
            <!-- Modern Header -->
            <div class="modern-header">
                <div class="header-content">
                    <div class="header-info">
                        <h2><i class="fas fa-chart-line" style="font-size: 24px; opacity: 0.9;"></i> Dashboard Psikolog</h2>
                        <p>
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
                            
                            echo $greeting . ', ' . htmlspecialchars($psikolog_name) . '! <i class="fas fa-hand-wave" style="color: rgba(255,255,255,0.8);"></i>';
                            ?>
                        <!-- <span style="margin-left: 15px; font-size: 14px; opacity: 0.8;">
                                <i class="fas fa-clock" style="margin-right: 5px;"></i>
                                <span id="currentTime"><?php echo date('H:i:s'); ?></span> WIB
                            </span> -->
                        </p>
                    </div>
                    <div class="header-avatar">
                        <?php if ($psychologist_photo && file_exists($path . $psychologist_photo)): ?>
                            <img src="<?php echo $path; ?><?php echo htmlspecialchars($psychologist_photo); ?>" alt="Profile">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Modern Stats Grid -->
            <div class="stats-grid">
                <div class="modern-stat-card primary">
                    <div class="stat-content">
                        <div class="stat-info">
                            <p class="stat-label">Sesi (3 bulan ke depan)</p>
                            <h3 class="stat-number"><?php echo count($today_sessions); ?></h3>
                            <p class="stat-description">Konsultasi dalam 3 bulan ke depan</p> 
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>

                <div class="modern-stat-card success">
                    <div class="stat-content">
                        <div class="stat-info">
                            <p class="stat-label">Total Klien Bulan Ini</p>
                            <h3 class="stat-number"><?php echo $total_clients_month; ?></h3>
                            <p class="stat-description">Klien terlayani</p>
                        </div>
                        <div class="stat-icon success">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="modern-stat-card info">
                    <div class="stat-content">
                        <div class="stat-info">
                            <p class="stat-label">Laporan Pending</p>
                            <h3 class="stat-number"><?php echo count($pending_reports); ?></h3>
                            <p class="stat-description">Menunggu upload</p>
                        </div>
                        <div class="stat-icon info">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Schedule Card -->
            <div class="schedule-card">
                <div class="schedule-header">
                    <div>
                        <h3 class="schedule-title"><i class="fas fa-calendar-day" style="color: var(--color-primary); font-size: 18px;"></i> Jadwal 3 Bulan Mendatang</h3>
                        <p class="schedule-subtitle">Konsultasi dalam 3 bulan ke depan</p> 
                    </div>
                    <div class="schedule-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

                <?php if(empty($today_sessions)): ?>
                    <div class="empty-schedule">
                        <div class="empty-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="empty-title">Tidak Ada Jadwal Dalam 3 Bulan Mendatang</div>
                        <div class="empty-text">Tidak ada sesi konsultasi yang dijadwalkan dalam 3 bulan ke depan</div>
                    </div>
                <?php else: ?>
                    <div class="session-list">
                        <?php foreach($today_sessions as $sesi): ?>
                        <div class="session-item">
                            <div class="session-time">
                                <i class="fas fa-calendar-day"></i>
                                <?php echo date('D, d M Y', strtotime($sesi['tanggal_konsultasi'])); ?>
                                <div style="font-size: 0.95rem; color: #6c757d; margin-top: 6px;">
                                    <i class="fas fa-clock"></i> <?php echo substr($sesi['jam_konsultasi'], 0, 5); ?> WIB
                                </div>
                            </div>
                            <p class="session-client">
                                <i class="fas fa-user" style="color: var(--color-primary); margin-right: 8px;"></i>
                                <?php echo htmlspecialchars($sesi['client_name']); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Consultation Status Monitoring -->
            <div class="schedule-card" style="margin-top: 25px;">
                <div class="schedule-header">
                    <div>
                        <h3 class="schedule-title"><i class="fas fa-tasks" style="color: var(--color-primary); font-size: 18px;"></i> Status Konsultasi</h3>
                        <p class="schedule-subtitle">Monitoring status penanganan klien</p>
                    </div>
                    <div class="schedule-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>

                <?php if(empty($konsultasi_status_list)): ?>
                    <div class="empty-schedule">
                        <div class="empty-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="empty-title">Belum Ada Data Konsultasi</div>
                        <div class="empty-text">Belum ada klien yang terdaftar untuk konsultasi</div>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 13px;">
                                        <i class="fas fa-user" style="margin-right: 6px; color: var(--color-primary);"></i>Klien
                                    </th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 13px;">
                                        <i class="fas fa-calendar" style="margin-right: 6px; color: var(--color-primary);"></i>Tanggal
                                    </th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 13px;">Status</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 13px;">Update</th>
                                    <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 13px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($konsultasi_status_list as $status): ?>
                                <tr style="border-bottom: 1px solid #f1f3f4; transition: all 0.3s ease;">
                                    <td style="padding: 12px;">
                                        <div style="font-weight: 500; color: var(--color-text);"><?php echo htmlspecialchars($status['client_name']); ?></div>
                                    </td>
                                    <td style="padding: 12px;">
                                        <div style="font-weight: 500; color: var(--color-text);"><?php echo date('d M Y', strtotime($status['tanggal_konsultasi'])); ?></div>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php 
                                        if ($status['konsultasi_status'] == 'sudah_ditangani') {
                                            echo '<span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap:4px;">';
                                            echo '<i class="fas fa-check-circle" style="font-size: 8px;"></i> Selesai';
                                            echo '</span>';
                                        } elseif ($status['konsultasi_status'] == 'sedang_ditangani') {
                                            echo '<span style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap:4px;">';
                                            echo '<i class="fas fa-spinner" style="font-size: 8px;"></i> Proses';
                                            echo '</span>';
                                        } else {
                                            echo '<span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap:4px;">';
                                            echo '<i class="fas fa-clock" style="font-size: 8px;"></i> Belum';
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <div style="font-size: 11px; color: #6c757d;">
                                            <?php 
                                            if ($status['status_updated']) {
                                                echo date('d M H:i', strtotime($status['status_updated']));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                            <?php if ($status['konsultasi_status'] == 'belum_ditangani'): ?> 
                                                <button onclick="updateStatus('<?php echo $status['booking_id']; ?>', 'sedang_ditangani')"
                                                        title="Mulai Ditangani">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php elseif ($status['konsultasi_status'] == 'sedang_ditangani'): ?>
                                                <button onclick="updateStatus('<?php echo $status['booking_id']; ?>', 'sudah_ditangani')" 
                                                        style="padding: 4px 8px; background: #d4edda; color: #155724; border: none; border-radius: 4px; font-size: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                                                        title="Selesaikan">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    <script>
        // Update current time every second
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = hours + ':' + minutes + ':' + seconds;
            }
        }
        
        // Update immediately and then every second
        updateTime();
        setInterval(updateTime, 1000);

        // Function to update consultation status
        function updateStatus(bookingId, newStatus) {
            console.log('Updating status:', { bookingId, newStatus }); // Debug log
            
            if (confirm('Apakah Anda yakin ingin mengubah status konsultasi ini?')) {
                fetch('update_consultation_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'booking_id=' + bookingId + '&status=' + newStatus
                })
                .then(response => {
                    console.log('Response status:', response.status); // Debug log
                    
                    // Check if response is OK and has JSON content
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Invalid JSON response:', text);
                            throw new Error('Invalid JSON response from server');
                        }
                    });
                })
                .then(data => {
                    console.log('Response data:', data); // Debug log
                    
                    if (data && data.success) {
                        // Show success message
                        showNotification('Status konsultasi berhasil diperbarui!', 'success');
                        // Reload page after 1 second to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification('Gagal memperbarui status: ' + (data?.message || 'Unknown error'), 'error');
                        if (data && data.debug) {
                            console.error('Debug info:', data.debug);
                        }
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showNotification('Terjadi kesalahan saat memperbarui status', 'error');
                });
            }
        }
        
        // Function to show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 10px;
                color: white;
                font-weight: 600;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
                max-width: 300px;
            `;
            
            if (type === 'success') {
                notification.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
            } else {
                notification.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Add slide animations
        const animStyle = document.createElement('style');
        animStyle.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(animStyle);
    </script>
</body>
</html>