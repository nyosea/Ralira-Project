<?php
/**
 * Filename: pages/psychologist/client_detail.php
 * Description: Detail lengkap klien dengan riwayat hidup
 */

session_start();
$path = '../../';
$page_title = 'Detail Klien';

// Check if user is logged in and is psychologist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];
$booking_id = intval($_GET['booking_id'] ?? 0);

if (!$booking_id) {
    header('Location: clients_list.php');
    exit;
}

// Get psychologist profile
$psychologist_data = $db->getPrepare("SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?", [$user_id]);
$psychologist_id = $psychologist_data['psychologist_id'] ?? null;

// Get booking detail with client info and riwayat hidup
$client_data = null;
if ($psychologist_id) {
    $sql = "SELECT 
                cb.booking_id, cb.tanggal_konsultasi, cb.jam_konsultasi, cb.status_booking,
                u.name AS client_name, u.email, u.phone,
                cd.gender, cd.alamat, cd.tanggal_lahir,
                brh.keluhan_masalah, brh.lama_masalah, brh.pernah_konsultasi, 
                brh.latar_belakang, brh.tahu_dari, brh.agama, brh.suku, brh.hobi, brh.alamat_rh AS alamat_rh,
                psy.name as psychologist_name
            FROM consultation_bookings cb
            INNER JOIN client_details cd ON cb.client_id = cd.client_id
            INNER JOIN users u ON cd.user_id = u.user_id
            LEFT JOIN booking_riwayat_hidup brh ON cb.booking_id = brh.booking_id
            LEFT JOIN psychologist_profiles pp ON cb.psychologist_id = pp.psychologist_id
            LEFT JOIN users psy ON pp.user_id = psy.user_id
            WHERE cb.booking_id = ? AND cb.psychologist_id = ?";
    
    $client_data = $db->getPrepare($sql, [$booking_id, $psychologist_id]);
}

if (!$client_data) {
    header('Location: clients_list.php');
    exit;
}

// Get consultation status
$konsultasi_status = null;
try {
    $sql_status = "SELECT konsultasi_status, updated_at FROM consultation_status WHERE booking_id = ?";
    $status_data = $db->getPrepare($sql_status, [$booking_id]);
    if ($status_data) {
        $konsultasi_status = $status_data['konsultasi_status'];
    }
} catch (Exception $e) {
    $konsultasi_status = null;
}

// Session messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

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
    <style>
        .detail-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
        }
        
        @media (max-width: 768px) {
            .detail-container {
                grid-template-columns: 1fr;
            }
        }
        
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(90, 61, 43, 0.1);
            margin-bottom: 20px;
        }
        
        .info-card h3 {
            color: var(--color-primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-primary);
        }
        
        .info-item {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--color-text);
            min-width: 120px;
            margin-right: 10px;
        }
        
        .info-value {
            color: var(--color-text-light);
            flex: 1;
        }
        
        .rh-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .rh-item {
            margin-bottom: 15px;
        }
        
        .rh-label {
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 5px;
        }
        
        .rh-value {
            color: var(--color-text-light);
            line-height: 1.6;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .back-btn {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            background: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(251, 186, 0, 0.3);
        }
        
        .booking-info {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .booking-info h3 {
            margin: 0 0 15px 0;
            color: white;
        }
        
        .booking-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .booking-date {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .booking-time {
            font-size: 1rem;
            opacity: 0.9;
        }

        .dashboard-container {
            background: var(--color-bg);
            min-height: 100vh;
        }

        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_psychologist.php'; ?>
        <?php include $path . 'components/header_psychologist.php'; ?>

        <main class="main-content">
            <!-- Alert Messages -->
            <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <a href="clients_list.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Klien
            </a>

            <h2 style="color: var(--color-text); margin-bottom: 25px;">
                <i class="fas fa-user"></i> Detail Klien
            </h2>

            <div class="detail-container">
                <!-- Client Info -->
                <div>
                    <div class="info-card">
                        <h3><i class="fas fa-user"></i> Informasi Klien</h3>
                        
                        <div class="info-item">
                            <span class="info-label">Nama:</span>
                            <span class="info-value"><?php echo htmlspecialchars($client_data['client_name']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($client_data['email']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Telepon:</span>
                            <span class="info-value"><?php echo htmlspecialchars($client_data['phone']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Gender:</span>
                            <span class="info-value"><?php echo htmlspecialchars($client_data['gender'] ?? '-'); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Tanggal Lahir:</span>
                            <span class="info-value">
                                <?php 
                                if ($client_data['tanggal_lahir']) {
                                    echo date('d M Y', strtotime($client_data['tanggal_lahir']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </span>
                        </div>
                    </div>

                    <!-- Konsultasi Status Section -->
                    <?php if ($client_data['status_booking'] == 'completed'): ?>
                    <div class="info-card">
                        <h3><i class="fas fa-tasks"></i> Status Konsultasi</h3>
                        
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                <?php
                                if ($konsultasi_status) {
                                    if ($konsultasi_status == 'belum_ditangani') {
                                        echo '<span style="color: #dc3545; font-weight: 600;">⏳ Belum Ditangani</span>';
                                    } elseif ($konsultasi_status == 'sedang_ditangani') {
                                        echo '<span style="color: #ffc107; font-weight: 600;">🔄 Sedang Ditangani</span>';
                                    } elseif ($konsultasi_status == 'sudah_ditangani') {
                                        echo '<span style="color: #28a745; font-weight: 600;">✅ Sudah Ditangani</span>';
                                    }
                                } else {
                                    echo '<span style="color: #dc3545; font-weight: 600;">⏳ Belum Ditangani</span>';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <h4 style="margin-bottom: 15px; color: var(--color-text);">Update Status Konsultasi:</h4>
                            
                            <form action="update_konsultasi_status.php" method="POST" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                                
                                <?php if (!$konsultasi_status || $konsultasi_status == 'belum_ditangani'): ?>
                                <button type="submit" name="konsultasi_status" value="sedang_ditangani" 
                                        class="btn-primary" style="background: #ffc107; color: #000; margin-right: 10px;">
                                    <i class="fas fa-play"></i> Mulai Tangani
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($konsultasi_status == 'sedang_ditangani'): ?>
                                <button type="submit" name="konsultasi_status" value="sudah_ditangani" 
                                        class="btn-primary" style="background: #28a745;">
                                    <i class="fas fa-check"></i> Selesaikan
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($konsultasi_status == 'sudah_ditangani'): ?>
                                <button type="submit" name="konsultasi_status" value="belum_ditangani" 
                                        class="btn-secondary" style="background: #6c757d;">
                                    <i class="fas fa-undo"></i> Reset Status
                                </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="booking-info">
                        <h3><i class="fas fa-calendar-check"></i> Informasi Booking</h3>
                        
                        <div class="booking-detail">
                            <div>
                                <div class="booking-date">
                                    <?php echo date('l, d M Y', strtotime($client_data['tanggal_konsultasi'])); ?>
                                </div>
                                <div class="booking-time">
                                    <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($client_data['jam_konsultasi'])); ?> WIB
                                </div>
                            </div>
                            
                            <span class="status-badge status-confirmed">
                                <?php echo ucfirst($client_data['status_booking']); ?>
                            </span>
                        </div>
                        
                        <?php if ($client_data['psychologist_name']): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.3);">
                            <small style="opacity: 0.9;">Psikolog:</small>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($client_data['psychologist_name']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Riwayat Hidup -->
                <div>
                    <div class="info-card">
                        <h3><i class="fas fa-file-medical"></i> Riwayat Hidup Klien</h3>
                        
                        <?php if ($client_data['keluhan_masalah'] || $client_data['lama_masalah'] || $client_data['pernah_konsultasi'] || $client_data['latar_belakang'] || $client_data['tahu_dari'] || $client_data['agama'] || $client_data['suku'] || $client_data['hobi'] || $client_data['alamat_rh']): ?>
                        
                            <div class="rh-section">
                                <div class="rh-item">
                                    <div class="rh-label">Keluhan/Masalah Utama</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['keluhan_masalah'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>
                                
                                <div class="rh-item">
                                    <div class="rh-label">Lama Masalah</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['lama_masalah'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>
                                
                                <div class="rh-item">
                                    <div class="rh-label">Pernah Konsultasi Sebelumnya</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['pernah_konsultasi'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>
                                
                                <div class="rh-item">
                                    <div class="rh-label">Latar Belakang</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['latar_belakang'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>
                                
                                <div class="rh-item">
                                    <div class="rh-label">Tahu dari</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['tahu_dari'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>

                                <div class="rh-item">
                                    <div class="rh-label">Agama</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['agama'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>

                                <div class="rh-item">
                                    <div class="rh-label">Suku</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['suku'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>

                                <div class="rh-item">
                                    <div class="rh-label">Hobi</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['hobi'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>

                                <div class="rh-item">
                                    <div class="rh-label">Alamat (Riwayat Hidup)</div>
                                    <div class="rh-value">
                                        <?php echo nl2br(htmlspecialchars($client_data['alamat_rh'] ?? 'Tidak ada informasi')); ?>
                                    </div>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px; color: var(--color-text-light);">
                                <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p>Belum ada data riwayat hidup untuk klien ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>