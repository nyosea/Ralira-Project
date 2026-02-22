<?php
/**
 * Filename: pages/client/history.php
 * Description: Riwayat Transaksi dan Jadwal Konsultasi Klien.
 */

session_start();
$path = '../../';
$page_title = 'Riwayat Konsultasi';

// Check if user is logged in and is client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];

// Get client_id
$client_detail = $db->getPrepare("SELECT client_id FROM client_details WHERE user_id = ?", [$user_id]);
$client_id = $client_detail['client_id'] ?? null;

$history = [];
if ($client_id) {
    // Get consultation bookings with psychologist details
    $sql_history = "SELECT 
                        cb.booking_id,
                        cb.tanggal_konsultasi,
                        cb.jam_konsultasi,
                        cb.status_booking,
                        u.name as psychologist_name,
                        pp.spesialisasi
                    FROM consultation_bookings cb
                    INNER JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
                    INNER JOIN users u ON pp.user_id = u.user_id
                    WHERE cb.client_id = ?
                    ORDER BY cb.tanggal_konsultasi DESC";
    
    $history = $db->queryPrepare($sql_history, [$client_id]);
    
    if (!is_array($history)) {
        $history = [];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/glass.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/client.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive_sections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_client.php'; ?>
        <?php include $path . 'components/header_client.php'; ?>

        <main class="main-content">
            <div class="history-header">
                <div class="history-title-section">
                    <h2>Riwayat & Jadwal</h2>
                    <p class="history-subtitle">Lihat semua konsultasi Anda yang telah dilakukan</p>
                </div>
                
                <div class="search-box">
                    <div class="search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" class="search-input" data-target="#historyTable" placeholder="Cari dokter atau layanan...">
                </div>
            </div>

            <div class="history-table-wrapper">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-history"></i>
                        <span>Riwayat Konsultasi</span>
                    </div>
                </div>
            </div>
            
            <div class="history-table-container">
                <div class="table-scroll-wrapper">
                    <table id="historyTable" class="history-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="table-th-content">
                                        <i class="fas fa-calendar"></i>
                                        <span>Tanggal</span>
                                </div>
                            </th>
                            <th>
                                <div class="table-th-content">
                                    <i class="fas fa-user-md"></i>
                                    <span>Psikolog</span>
                                </div>
                            </th>
                            <th>
                                <div class="table-th-content">
                                    <i class="fas fa-stethoscope"></i>
                                    <span>Layanan</span>
                                </div>
                            </th>
                            <th>
                                <div class="table-th-content">
                                    <i class="fas fa-money-bill"></i>
                                    <span>Biaya</span>
                                </div>
                            </th>
                            <th>
                                <div class="table-th-content">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach($history as $row): ?>
                            <tr class="history-row">
                                <td>
                                    <div class="date-cell">
                                        <div class="date-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="date-info">
                                            <span class="date-main"><?php echo date('d M Y', strtotime($row['tanggal_konsultasi'])); ?></span>
                                            <span class="date-time"><?php echo date('H:i', strtotime($row['jam_konsultasi'])); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="psychologist-cell">
                                        <div class="psychologist-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="psychologist-info">
                                            <span class="psychologist-name"><?php echo htmlspecialchars($row['psychologist_name']); ?></span>
                                            <span class="psychologist-role">Psikolog Klinis</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="service-cell">
                                        <i class="fas fa-heartbeat"></i>
                                        <span><?php echo htmlspecialchars($row['spesialisasi']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="price-cell">
                                        <span class="price-amount">Rp 50.000</span>
                                        <span class="price-label">Komitmen</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['status_booking'] == 'confirmed'): ?>
                                        <span class="status-badge confirmed">
                                            <i class="fas fa-check-circle"></i>
                                            Terjadwal
                                        </span>
                                    <?php elseif($row['status_booking'] == 'pending'): ?>
                                        <span class="status-badge pending">
                                            <i class="fas fa-clock"></i>
                                            Verifikasi
                                        </span>
                                    <?php elseif($row['status_booking'] == 'completed'): ?>
                                        <span class="status-badge completed">
                                            <i class="fas fa-check-double"></i>
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge cancelled">
                                            <i class="fas fa-times-circle"></i>
                                            Dibatalkan
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-history">
                                        <div class="empty-icon">
                                            <i class="fas fa-history"></i>
                                        </div>
                                        <h4>Belum Ada Riwayat</h4>
                                        <p>Anda belum memiliki riwayat konsultasi</p>
                                        <a href="booking.php" class="btn-primary">
                                            <i class="fas fa-calendar-plus"></i>
                                            Booking Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    <script src="<?php echo $path; ?>assets/js/search.js"></script>
</body>
</html>