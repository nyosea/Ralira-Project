<?php
/**
 * Filename: pages/client/test_results.php
 * Description: Halaman Download Hasil Tes (PDF).
 * [cite_start]Reference: SRS 3.4 Manajemen Hasil Tes[cite: 682].
 */

session_start();
$path = '../../';
$page_title = 'Hasil Tes & Laporan';

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

// Get client ID from session
$user_id = $_SESSION['user_id'];

// Get real test results from database for this client
$results = [];
try {
    $sql = "SELECT 
                tr.result_id,
                tr.jenis_tes,
                tr.tanggal_pelaksanaan,
                tr.file_hasil_tes,
                tr.created_at,
                u.name as psychologist_name
            FROM test_results tr
            INNER JOIN psychologist_profiles pp ON tr.psychologist_id = pp.psychologist_id
            INNER JOIN users u ON pp.user_id = u.user_id
            WHERE tr.client_id = (SELECT client_id FROM client_details WHERE user_id = ?)
            ORDER BY tr.created_at DESC";
    
    $results = $db->queryPrepare($sql, [$user_id]);
    if (!is_array($results)) {
        $results = [];
    }
} catch (Exception $e) {
    $results = [];
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
            <div class="test-results-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div class="header-text">
                        <h2>Hasil Tes & Laporan</h2>
                        <p class="header-subtitle">Unduh laporan hasil tes psikologi Anda</p>
                    </div>
                </div>
            </div>

            <div class="security-notice">
                <div class="notice-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="notice-content">
                    <h4>Dokumen Terenkripsi</h4>
                    <p>
                        Untuk alasan keamanan privasi, dokumen PDF dikunci dengan password. 
                        Gunakan <strong>Tanggal Lahir Anda (DD-MM-YYYY)</strong> sebagai password saat membuka file.
                    </p>
                </div>
            </div>

            <?php if(empty($results)): ?>
                <div class="empty-results">
                    <div class="empty-icon">
                        <i class="fas fa-file-medical-alt"></i>
                    </div>
                    <h4>Belum Ada Hasil Tes</h4>
                    <p>Hasil tes psikologi Anda akan muncul di sini setelah konsultasi selesai.</p>
                    <a href="booking.php" class="btn-primary">
                        <i class="fas fa-calendar-plus"></i>
                        Booking Konsultasi
                    </a>
                </div>
            <?php else: ?>
                <div class="test-results-grid">
                    <?php foreach($results as $res): ?>
                    <div class="result-card">
                        <div class="result-header">
                            <div class="result-icon">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div class="result-badge">
                                <span>PDF</span>
                            </div>
                        </div>
                        
                        <div class="result-content">
                            <h3>Laporan Psikotes <?php echo htmlspecialchars($res['jenis_tes']); ?></h3>
                            <div class="result-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user-md"></i>
                                    <span><?php echo htmlspecialchars($res['psychologist_name']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo date('d M Y', strtotime($res['created_at'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-clipboard-list"></i>
                                    <span><?php echo htmlspecialchars($res['jenis_tes']); ?></span>
                                </div>
                            </div>
                            <div class="result-description">
                                <p>Laporan hasil tes psikologi Anda dengan analisis dan rekomendasi dari psikolog.</p>
                            </div>
                        </div>
                        
                        <div class="download-section">
                            <a href="<?php echo $path . 'uploads/results/' . $res['file_hasil_tes']; ?>" 
                               class="download-btn" 
                               download>
                               <i class="fas fa-download"></i>
                               <span>Download PDF</span>
                               <small>Password: DDMMYYYY</small>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>