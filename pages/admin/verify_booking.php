<?php
/**
 * Filename: pages/admin/verify_booking.php
 * Description: Halaman Verifikasi Pembayaran Booking Konsultasi
 */

session_start();
$path = '../../';
$page_title = 'Verifikasi Pembayaran';

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

$booking_id = intval($_GET['booking_id'] ?? 0);
$error = '';
$success = '';

if (!$booking_id) {
    $error = 'ID Booking tidak valid';
}

// Get booking details with payment info
$booking_data = null;
if (!$error) {
    // First get booking with all details
    $sql_booking = "SELECT 
                        cb.booking_id,
                        cb.status_booking,
                        cb.tanggal_konsultasi,
                        cb.jam_konsultasi,
                        cd.client_id,
                        u.name as client_name,
                        u.email as client_email,
                        u.phone as client_phone,
                        psy.name as psychologist_name,
                        pp.spesialisasi,
                        cb.created_at
                    FROM consultation_bookings cb
                    INNER JOIN client_details cd ON cb.client_id = cd.client_id
                    INNER JOIN users u ON cd.user_id = u.user_id
                    INNER JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
                    INNER JOIN users psy ON pp.user_id = psy.user_id
                    WHERE cb.booking_id = ?";
    
    $booking_data = $db->getPrepare($sql_booking, [$booking_id]);
    
    if (!$booking_data) {
        $error = 'Booking tidak ditemukan';
    } else {
        // Then get latest payment for this client
        $payment_data = $db->getPrepare(
            "SELECT payment_id, tanggal_transfer, bukti_transfer, status_pembayaran 
             FROM payments 
             WHERE client_id = ? 
             ORDER BY created_at DESC 
             LIMIT 1",
            [$booking_data['client_id']]
        );
        
        // Merge payment data into booking data
        if ($payment_data) {
            $booking_data['payment_id'] = $payment_data['payment_id'];
            $booking_data['tanggal_transfer'] = $payment_data['tanggal_transfer'];
            $booking_data['bukti_transfer'] = $payment_data['bukti_transfer'];
            $booking_data['status_pembayaran'] = $payment_data['status_pembayaran'];
        }
    }
}

// Handle approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_booking']) && !$error) {
    $update_sql = "UPDATE consultation_bookings SET status_booking = 'confirmed' WHERE booking_id = ?";
    
    if ($db->executePrepare($update_sql, [$booking_id])) {
        $success = 'Pembayaran berhasil diverifikasi! Status booking diubah menjadi Confirmed.';
        // Refresh data
        $booking_data = $db->getPrepare($sql_booking, [$booking_id]);
        if ($booking_data) {
            $payment_data = $db->getPrepare(
                "SELECT payment_id, tanggal_transfer, bukti_transfer, status_pembayaran 
                 FROM payments 
                 WHERE client_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT 1",
                [$booking_data['client_id']]
            );
            if ($payment_data) {
                $booking_data['payment_id'] = $payment_data['payment_id'];
                $booking_data['tanggal_transfer'] = $payment_data['tanggal_transfer'];
                $booking_data['bukti_transfer'] = $payment_data['bukti_transfer'];
                $booking_data['status_pembayaran'] = $payment_data['status_pembayaran'];
            }
        }
    } else {
        $error = 'Gagal memperbarui status booking';
    }
}

// Handle rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_booking']) && !$error) {
    $rejection_reason = trim($_POST['rejection_reason'] ?? '');
    
    if (!$rejection_reason) {
        $error = 'Alasan penolakan harus diisi';
    } else {
        $update_sql = "UPDATE consultation_bookings SET status_booking = 'rejected' WHERE booking_id = ?";
        
        if ($db->executePrepare($update_sql, [$booking_id])) {
            $success = 'Booking ditolak. Klien akan menerima notifikasi.';
            // Refresh data
            $booking_data = $db->getPrepare($sql_booking, [$booking_id]);
            if ($booking_data) {
                $payment_data = $db->getPrepare(
                    "SELECT payment_id, tanggal_transfer, bukti_transfer, status_pembayaran 
                     FROM payments 
                     WHERE client_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 1",
                    [$booking_data['client_id']]
                );
                if ($payment_data) {
                    $booking_data['payment_id'] = $payment_data['payment_id'];
                    $booking_data['tanggal_transfer'] = $payment_data['tanggal_transfer'];
                    $booking_data['bukti_transfer'] = $payment_data['bukti_transfer'];
                    $booking_data['status_pembayaran'] = $payment_data['status_pembayaran'];
                }
            }
        } else {
            $error = 'Gagal memperbarui status booking';
        }
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive_sections.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/verify_booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-check-circle"></i> <?php echo $page_title; ?></h1>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <?php if ($booking_data): ?>
                <div class="verification-container">
                    <!-- Detail Booking Section -->
                    <div class="detail-booking-section">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle"></i> Detail Booking
                        </h4>
                        
                        <div class="detail-grid">
                            <div>
                                <p class="detail-label">Nama Klien</p>
                                <p class="detail-value"><?php echo htmlspecialchars($booking_data['client_name']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Email</p>
                                <p class="detail-value"><?php echo htmlspecialchars($booking_data['client_email']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Nomor Telepon</p>
                                <p class="detail-value"><?php echo htmlspecialchars($booking_data['client_phone']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Psikolog</p>
                                <p class="detail-value"><?php echo htmlspecialchars($booking_data['psychologist_name']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Spesialisasi</p>
                                <p class="detail-value"><?php echo htmlspecialchars($booking_data['spesialisasi']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Tanggal Konsultasi</p>
                                <p class="detail-value"><?php echo date('d M Y', strtotime($booking_data['tanggal_konsultasi'])); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Jam Konsultasi</p>
                                <p class="detail-value">
                                    <?php 
                                    if (!empty($booking_data['jam_konsultasi'])) {
                                        echo date('H:i', strtotime($booking_data['jam_konsultasi'])) . ' WIB';
                                    } else {
                                        echo '<span style="color: #999;">Belum ditentukan</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="detail-label">Biaya Komitmen</p>
                                <p class="detail-value" style="color: var(--color-primary);">Rp 50.000</p>
                            </div>
                            <div>
                                <p class="detail-label">Status</p>
                                <p class="detail-value">
                                    <?php if($booking_data['status_booking'] == 'pending'): ?>
                                        <span class="status-badge badge-warning">Pending Verifikasi</span>
                                    <?php elseif($booking_data['status_booking'] == 'confirmed'): ?>
                                        <span class="status-badge badge-success">Confirmed</span>
                                    <?php elseif($booking_data['status_booking'] == 'rejected'): ?>
                                        <span class="status-badge badge-danger">Rejected</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Bukti Pembayaran Section -->
                    <div class="payment-section">
                        <h4 class="section-title">
                            <i class="fas fa-file-invoice-dollar"></i> Bukti Pembayaran
                        </h4>
                        
                        <?php if ($booking_data['bukti_transfer']): ?>
                            <div class="payment-info-grid">
                                <div>
                                    <p class="detail-label">Tanggal Transfer</p>
                                    <p class="detail-value"><?php echo date('d M Y', strtotime($booking_data['tanggal_transfer'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="payment-preview">
                                <p class="detail-label" style="margin-bottom: 15px;">Preview Bukti Transfer:</p>
                                <?php 
                                $file_ext = strtolower(pathinfo($booking_data['bukti_transfer'], PATHINFO_EXTENSION));
                                if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): 
                                ?>
                                    <img src="<?php echo $path . htmlspecialchars($booking_data['bukti_transfer']); ?>" 
                                         alt="Bukti Transfer">
                                <?php elseif ($file_ext == 'pdf'): ?>
                                    <div class="pdf-preview">
                                        <i class="fas fa-file-pdf"></i>
                                        <p style="margin: 10px 0; color: #666;">Dokumen PDF</p>
                                        <a href="<?php echo $path . htmlspecialchars($booking_data['bukti_transfer']); ?>" 
                                           target="_blank" 
                                           class="btn-download">
                                            <i class="fas fa-external-link-alt"></i> Buka PDF
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="no-payment">
                                        <i class="fas fa-file"></i>
                                        <p style="margin: 10px 0 0 0; color: #856404;">File tidak dikenali</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="payment-actions">
                                <a href="<?php echo $path . htmlspecialchars($booking_data['bukti_transfer']); ?>" 
                                   target="_blank" 
                                   class="btn-download">
                                    <i class="fas fa-download"></i> Download Bukti Transfer
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="no-payment">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p style="margin: 10px 0 0 0; color: #856404;">Bukti pembayaran tidak ditemukan</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if ($booking_data['status_booking'] == 'pending'): ?>
                    <div class="action-buttons">
                        <form method="POST" onsubmit="return confirm('Konfirmasi verifikasi pembayaran?');">
                            <button type="submit" name="approve_booking" class="btn-approve">
                                <i class="fas fa-check"></i> Terima Pembayaran
                            </button>
                        </form>

                        <button type="button" class="btn-reject" onclick="showRejectForm()">
                            <i class="fas fa-times"></i> Tolak Pembayaran
                        </button>
                    </div>
                <?php endif; ?>

            <!-- Reject Form Modal -->
            <div id="rejectForm" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
                <div style="background: white; border-radius: 12px; padding: 0; width: 90%; max-width: 500px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
                    <div style="padding: 20px; border-bottom: 1px solid #e0e0e0;">
                        <h3 style="margin: 0; color: var(--color-text);">
                            <i class="fas fa-times-circle" style="color: #dc3545;"></i> Tolak Pembayaran
                        </h3>
                    </div>
                    <form method="POST">
                        <div style="padding: 20px;">
                            <div style="margin-bottom: 15px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Alasan Penolakan</label>
                                <textarea name="rejection_reason" class="glass-input" style="width: 100%; min-height: 100px; padding: 10px;" required placeholder="Jelaskan alasan penolakan pembayaran..."></textarea>
                            </div>
                        </div>
                        <div style="padding: 15px 20px; border-top: 1px solid #e0e0e0; display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" class="btn-download" onclick="hideRejectForm()" style="background: #6c757d;">
                                <i class="fas fa-times"></i> Batal
                            </button>
                            <button type="submit" name="reject_booking" class="btn-reject">
                                <i class="fas fa-check"></i> Tolak Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php else: ?>
                <div class="no-payment">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Booking tidak ditemukan</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function showRejectForm() {
            document.getElementById('rejectForm').style.display = 'flex';
        }

        function hideRejectForm() {
            document.getElementById('rejectForm').style.display = 'none';
        }
    </script>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>
