<?php
session_start();
$path = '../../';
$page_title = 'Data Pendaftaran Klien';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

// Get filter parameters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT 
            cb.booking_id,
            cd.client_id,
            u.name as client_name,
            u.email,
            cb.tanggal_konsultasi,
            cb.status_booking,
            cb.created_at,
            pp.spesialisasi
        FROM consultation_bookings cb
        INNER JOIN client_details cd ON cb.client_id = cd.client_id
        INNER JOIN users u ON cd.user_id = u.user_id
        INNER JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
        WHERE 1=1";

$params = [];

// Apply date filter if provided
if ($date_from) {
    $sql .= " AND DATE(cb.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $sql .= " AND DATE(cb.created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY cb.created_at DESC";

$clients = $db->queryPrepare($sql, $params);
if (!is_array($clients)) {
    $clients = [];
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>

        <main class="main-content">
            <div class="client-management-header">
                <h2>
                    <i class="fas fa-users"></i>
                    Manajemen Pendaftaran Klien
                </h2>
                
                <div class="client-filters">
                    <form method="GET">
                        <div class="date-filter-group">
                            <label>Dari</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="date-filter-group">
                            <label>Sampai</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="date-filter-group">
                            <button type="submit" class="filter-btn">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                        <div class="date-filter-group">
                            <a href="?" class="filter-btn">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                    
                    <div class="client-search-box">
                        <i class="fas fa-search client-search-icon"></i>
                        <input type="text" class="client-search-input" data-target="#clientTable" placeholder="Cari nama...">
                    </div>
                </div>
            </div>

            <div class="client-table-container">
                <table id="clientTable" class="client-table">
                    <thead>
                        <tr>
                            <th>ID Reg</th>
                            <th>Nama Klien</th>
                            <th>Tanggal Daftar</th>
                            <th>Layanan</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clients)): ?>
                            <?php $no = 1; foreach($clients as $client): ?>
                            <tr>
                                <td><span class="client-id"><?php echo 'REG' . str_pad($client['client_id'], 4, '0', STR_PAD_LEFT); ?></span></td>
                                <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($client['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($client['spesialisasi']); ?></td>
                                <td>
                                    <?php if($client['status_booking'] == 'confirmed'): ?>
                                        <span class="client-badge client-badge-success">Terkonfirmasi</span>
                                    <?php elseif($client['status_booking'] == 'pending'): ?>
                                        <span class="client-badge client-badge-warning">Menunggu</span>
                                    <?php elseif($client['status_booking'] == 'rejected'): ?>
                                        <span class="client-badge client-badge-danger">Ditolak</span>
                                    <?php else: ?>
                                        <span class="client-badge client-badge-cancelled">Dibatalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($client['status_booking'] == 'pending'): ?>
                                        <a href="verify_booking.php?booking_id=<?php echo $client['booking_id']; ?>" class="client-action-btn client-action-btn-primary">
                                            <i class="fas fa-check"></i> Verifikasi
                                        </a>
                                    <?php else: ?>
                                        <a href="verify_booking.php?booking_id=<?php echo $client['booking_id']; ?>" class="client-action-btn client-action-btn-detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php $no++; endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="client-empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>Tidak ada data pendaftaran</h4>
                                    <p>Silakan pilih tanggal filter yang berbeda atau tunggu data pendaftaran masuk</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    <script src="<?php echo $path; ?>assets/js/search.js"></script>
</body>
</html>