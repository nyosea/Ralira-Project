<?php
session_start();
$path = '../../';
$page_title = 'Jadwal Mendatang';

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

// Get admin name from session
$admin_name = $_SESSION['name'] ?? 'Admin';

// Get filter parameters
$psychologist_filter = $_GET['psychologist'] ?? '';
$today = date('Y-m-d');

// Build query for upcoming bookings (from today onwards)
$sql = "SELECT 
            cb.booking_id,
            cb.tanggal_konsultasi,
            s.jam_mulai,
            s.jam_selesai,
            u_client.name as client_name,
            u_psych.name as psychologist_name,
            pp.spesialisasi,
            cb.status_booking
        FROM consultation_bookings cb
        INNER JOIN schedules s ON cb.schedule_id = s.schedule_id
        INNER JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
        INNER JOIN users u_psych ON pp.user_id = u_psych.user_id
        INNER JOIN client_details cd ON cb.client_id = cd.client_id
        INNER JOIN users u_client ON cd.user_id = u_client.user_id
        WHERE cb.tanggal_konsultasi >= ? AND cb.status_booking IN ('pending', 'confirmed')";

$params = [$today];

// Apply psychologist filter if provided
if ($psychologist_filter) {
    $sql .= " AND pp.psychologist_id = ?";
    $params[] = $psychologist_filter;
}

$sql .= " ORDER BY cb.tanggal_konsultasi ASC, s.jam_mulai ASC";

$schedules = $db->queryPrepare($sql, $params);
if (!is_array($schedules)) {
    $schedules = [];
}

// Get list of psychologists for filter
$sql_psychologists = "SELECT pp.psychologist_id, u.name FROM psychologist_profiles pp INNER JOIN users u ON pp.user_id = u.user_id ORDER BY u.name";
$psychologists = $db->queryPrepare($sql_psychologists, []);
if (!is_array($psychologists)) {
    $psychologists = [];
}

// Get statistics for dashboard
$total_schedules = count($schedules);
$confirmed_count = 0;
$pending_count = 0;
foreach ($schedules as $schedule) {
    if ($schedule['status_booking'] == 'confirmed') $confirmed_count++;
    elseif ($schedule['status_booking'] == 'pending') $pending_count++;
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive_sections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>

        <main class="main-content">
            <div class="manage-schedule-container">
                <div class="schedule-header">
                    <h2>Jadwal Mendatang</h2>
                    
                    <div class="schedule-filters">
                        <div class="filter-group">
                            <label>Psikolog</label>
                            <select name="psychologist">
                                <option value="">Semua Psikolog</option>
                                <?php foreach($psychologists as $psy): ?>
                                <option value="<?php echo $psy['psychologist_id']; ?>" <?php echo ($psychologist_filter == $psy['psychologist_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($psy['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-buttons">
                            <button type="submit" class="filter-btn">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="?" class="filter-btn secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                <div class="schedule-table-container">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam (WIB)</th>
                                <th>Psikolog</th>
                                <th>Klien</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($schedules)): ?>
                                <?php foreach($schedules as $sch): ?>
                                <tr>
                                    <td class="date-cell"><?php echo date('d M Y', strtotime($sch['tanggal_konsultasi'])); ?></td>
                                    <td class="time-cell"><?php echo substr($sch['jam_mulai'], 0, 5) . ' - ' . substr($sch['jam_selesai'], 0, 5); ?></td>
                                    <td class="psychologist-cell"><?php echo htmlspecialchars($sch['psychologist_name']); ?></td>
                                    <td class="client-cell"><?php echo htmlspecialchars($sch['client_name']); ?></td>
                                    <td>
                                        <?php if($sch['status_booking'] == 'confirmed'): ?>
                                            <span class="schedule-badge confirmed">Confirmed</span>
                                        <?php elseif($sch['status_booking'] == 'pending'): ?>
                                            <span class="schedule-badge pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="verify_booking.php?booking_id=<?php echo $sch['booking_id']; ?>" class="action-btn">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">📅</div>
                                            <div class="empty-state-text">Tidak ada jadwal mendatang</div>
                                            <div class="empty-state-subtext">Semua jadwal sudah selesai atau belum ada booking</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    
</body>
</html>
    
