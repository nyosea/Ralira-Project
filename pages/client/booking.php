<?php
/**
 * Filename: pages/client/booking.php
 * Description: Formulir Pendaftaran Konsultasi & Upload Bukti Pembayaran.
 * [cite_start]Reference: SRS 3.2 Manajemen Jadwal & SRS 3.8 Pembayaran[cite: 645, 747].
 */

session_start();
$path = '../../';
$page_title = 'Daftar Konsultasi';

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

// Check if user is logged in and is client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data from session and database
$user_data = $db->getPrepare("SELECT name, email, phone FROM users WHERE user_id = ?", [$user_id]);
$user_name = $user_data['name'] ?? $_SESSION['name'] ?? '';
$user_email = $user_data['email'] ?? $_SESSION['email'] ?? '';
$user_phone = $user_data['phone'] ?? $_SESSION['phone'] ?? '';

// Get client_id
$client_detail = $db->getPrepare("SELECT client_id FROM client_details WHERE user_id = ?", [$user_id]);
if (!$client_detail) {
    $error = 'Data klien tidak ditemukan. Silakan hubungi administrator.';
} else {
    $client_id = $client_detail['client_id'];
    // Try to get gender if column exists (handle gracefully if column doesn't exist yet)
    $current_gender = '';
    $conn = $db->getConnection();
    $result = $conn->query("SHOW COLUMNS FROM client_details LIKE 'gender'");
    if ($result && $result->num_rows > 0) {
        // Column exists, try to get gender value
        $gender_data = $db->getPrepare("SELECT gender FROM client_details WHERE client_id = ?", [$client_id]);
        $current_gender = $gender_data['gender'] ?? '';
    }
}

// Get all psychologists from database
$all_psychologists = [];
if (!$error) {
    $sql_psychologists = "SELECT pp.psychologist_id, u.name, pp.spesialisasi 
                          FROM psychologist_profiles pp 
                          INNER JOIN users u ON pp.user_id = u.user_id 
                          ORDER BY u.name";
    $all_psychologists = $db->queryPrepare($sql_psychologists);
}

// Handle POST - Booking Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_consultation']) && !$error) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $psychologist_id = intval($_POST['psychologist_id'] ?? 0);
    $service_type = trim($_POST['service_type'] ?? '');
    $tanggal_konsultasi = trim($_POST['date'] ?? '');
    $time_slot = trim($_POST['time_slot'] ?? '');
    
    // Get RH data
    $keluhan_masalah = trim($_POST['keluhan_masalah'] ?? '');
    $lama_masalah = trim($_POST['lama_masalah'] ?? '');
    $pernah_konsultasi = trim($_POST['pernah_konsultasi'] ?? '');
    $latar_belakang = trim($_POST['latar_belakang'] ?? '');
    $tahu_dari = trim($_POST['tahu_dari'] ?? '');
    $agama = trim($_POST['agama'] ?? '');
    $suku = trim($_POST['suku'] ?? '');
    $hobi = trim($_POST['hobi'] ?? '');
    $alamat_rh = trim($_POST['alamat'] ?? '');
    
    // Validasi
    if (!$name || !$phone || !$email || !$gender || !$psychologist_id || !$service_type || !$tanggal_konsultasi || !$time_slot) {
        $error = 'Semua field wajib harus diisi!';
    } elseif (strtotime($tanggal_konsultasi) < strtotime('today')) {
        $error = 'Tanggal konsultasi tidak boleh di masa lalu!';
    } elseif (!$keluhan_masalah || !$lama_masalah || !$pernah_konsultasi || !$latar_belakang || !$tahu_dari || !$agama || !$suku || !$alamat_rh) {
        $error = 'Form Riwayat Hidup harus diisi lengkap!';
    } else {
        // Update user data if changed
        $db->executePrepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE user_id = ?", 
                           [$name, $phone, $email, $user_id]);
        
        // Update client gender (only if column exists)
        $conn = $db->getConnection();
        $result = $conn->query("SHOW COLUMNS FROM client_details LIKE 'gender'");
        if ($result && $result->num_rows > 0) {
            $col = $result->fetch_assoc();
            $colType = $col['Type'] ?? '';

            $allowed = [];
            if (preg_match("/^enum\((.*)\)$/i", $colType, $m)) {
                // Extract enum values: 'A','B',...
                preg_match_all("/'([^']*)'/", $m[1], $matches);
                $allowed = $matches[1] ?? [];
            }

            $gender_to_save = $gender;
            if (!empty($allowed) && !in_array($gender_to_save, $allowed, true)) {
                $map = [
                    'L' => ['L', 'M', 'male', 'Male', 'laki-laki', 'Laki-laki', 'LAKI-LAKI'],
                    'P' => ['P', 'F', 'female', 'Female', 'perempuan', 'Perempuan', 'PEREMPUAN'],
                ];

                $candidates = $map[$gender] ?? [];
                $gender_to_save = null;
                foreach ($candidates as $cand) {
                    if (in_array($cand, $allowed, true)) {
                        $gender_to_save = $cand;
                        break;
                    }
                }
            }

            if ($gender_to_save !== null && $gender_to_save !== '') {
                try {
                    $db->executePrepare("UPDATE client_details SET gender = ? WHERE client_id = ?", 
                                       [$gender_to_save, $client_id]);
                } catch (Throwable $e) {
                    // Skip gender update if schema/value mismatch to avoid blocking booking flow
                }
            }
        }
        
        // Parse waktu slot
        $jam_mulai = $time_slot; // Sudah dalam format HH:MM:00 dari API
        
        // Cek apakah sudah ada booking untuk psikolog yang sama dengan tanggal dan jam yang sama
        $check_existing = $db->getPrepare(
            "SELECT cb.booking_id FROM consultation_bookings cb
             WHERE cb.psychologist_id = ? AND cb.tanggal_konsultasi = ? AND cb.jam_konsultasi = ? AND cb.status_booking IN ('pending', 'confirmed')",
            [$psychologist_id, $tanggal_konsultasi, $jam_mulai]
        );
        
        if ($check_existing) {
            $error = 'Maaf, psikolog ini sudah terbooking pada jam tersebut. Silakan pilih jam atau tanggal lain.';
        } else {
            // Get day of week dari tanggal
            $day_of_week = date('l', strtotime($tanggal_konsultasi));
            
            // Cek apakah slot tersedia di psychologist_schedule_dates
            $slot_check = $db->getPrepare(
                "SELECT schedule_date_id FROM psychologist_schedule_dates 
                 WHERE psychologist_id = ? AND tanggal = ? AND jam_mulai = ? AND COALESCE(has_booking, 0) = 0
                 LIMIT 1",
                [$psychologist_id, $tanggal_konsultasi, $jam_mulai]
            );
            
            if (!$slot_check) {
                $error = 'Maaf, psikolog tidak tersedia pada jam tersebut.';
            } else {
                // Get schedule_id from psychologist_schedule_dates
                $schedule_data = $db->getPrepare(
                    "SELECT schedule_date_id FROM psychologist_schedule_dates 
                     WHERE psychologist_id = ? AND tanggal = ? AND jam_mulai = ? AND COALESCE(has_booking, 0) = 0
                     LIMIT 1",
                    [$psychologist_id, $tanggal_konsultasi, $jam_mulai]
                );
                
                if (!$schedule_data) {
                    $error = 'Maaf, jadwal tidak ditemukan atau sudah terbooking.';
                } else {
                    $schedule_id = $schedule_data['schedule_date_id'];
                    
                    // Insert booking dengan schedule_date_id dari psychologist_schedule_dates
                    $sql_booking = "INSERT INTO consultation_bookings 
                                   (client_id, psychologist_id, schedule_id, tanggal_konsultasi, jam_konsultasi, status_booking) 
                                   VALUES (?, ?, ?, ?, ?, 'pending')";
                    
                    if ($db->executePrepare($sql_booking, [$client_id, $psychologist_id, $schedule_id, $tanggal_konsultasi, $jam_mulai])) {
                        $booking_id = $db->lastId();
                        
                        // Insert Riwayat Hidup (guarded and logged). Check for column name differences and log failures.
                        try {
                            $conn = $db->getConnection();
                            $colName = null;
                            $res = $conn->query("SHOW COLUMNS FROM booking_riwayat_hidup LIKE 'alamat_rh'");
                            if ($res && $res->num_rows > 0) {
                                $colName = 'alamat_rh';
                            } else {
                                $res2 = $conn->query("SHOW COLUMNS FROM booking_riwayat_hidup LIKE 'alamat'");
                                if ($res2 && $res2->num_rows > 0) {
                                    $colName = 'alamat';
                                }
                            }

                            if ($colName) {
                                $sql_rh = "INSERT INTO booking_riwayat_hidup 
                                           (booking_id, keluhan_masalah, lama_masalah, pernah_konsultasi, latar_belakang, tahu_dari, agama, suku, hobi, " . $colName . ") 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                $ok = $db->executePrepare($sql_rh, [$booking_id, $keluhan_masalah, $lama_masalah, $pernah_konsultasi, $latar_belakang, $tahu_dari, $agama, $suku, $hobi, $alamat_rh]);
                                if (!$ok) {
                                    error_log("[booking] Failed to insert booking_riwayat_hidup for booking_id={$booking_id}");
                                }
                            } else {
                                error_log("[booking] booking_riwayat_hidup missing alamat column; skipping RH insert for booking_id={$booking_id}");
                            }
                        } catch (Throwable $e) {
                            error_log("[booking] Exception inserting booking_riwayat_hidup for booking_id={$booking_id}: " . $e->getMessage());
                        }
                        
                        // Handle file upload - Bukti Pembayaran
                        $bukti_transfer = null;
                        $tanggal_transfer = date('Y-m-d');
                        
                        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
                            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                            $file = $_FILES['payment_proof'];
                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            
                            if (in_array($ext, $allowed) && $file['size'] <= 5*1024*1024) {
                                $upload_dir = $path . 'uploads/payments/';
                                if (!is_dir($upload_dir)) {
                                    mkdir($upload_dir, 0777, true);
                                }
                                
                                $filename = 'payment-' . $booking_id . '-' . time() . '.' . $ext;
                                $destination = $upload_dir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $destination)) {
                                    $bukti_transfer = 'uploads/payments/' . $filename;
                                }
                            }
                        }
                        
                        // Insert payment record
                        if ($bukti_transfer) {
                            $sql_payment = "INSERT INTO payments (client_id, tanggal_transfer, bukti_transfer, status_pembayaran) 
                                           VALUES (?, ?, ?, 'pending')";
                            $db->executePrepare($sql_payment, [$client_id, $tanggal_transfer, $bukti_transfer]);
                        }
                        
                        $success = 'Booking berhasil! Silakan tunggu konfirmasi dari admin.';
                        echo '<script>setTimeout(() => { window.location.href = "history.php"; }, 2000);</script>';
                    } else {
                        $error = 'Gagal membuat booking. Silakan coba lagi.';
                    }
                }
            }
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/client.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_client.php'; ?>
        <?php include $path . 'components/header_client.php'; ?>

        <main class="main-content">
            <div class="booking-container">
                
                <!-- Header - ENHANCED -->
                <div class="booking-header">
                    <div class="booking-header-content">
                        <div class="booking-header-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="booking-header-text">
                            <h1>Daftar Konsultasi</h1>
                            <p>Isi formulir untuk melakukan pendaftaran konsultasi</p>
                        </div>
                    </div>
                    <div class="booking-header-decoration">
                        <div class="header-circle circle-1"></div>
                        <div class="header-circle circle-2"></div>
                        <div class="header-circle circle-3"></div>
                    </div>
                </div>

                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <?php endif; ?>

                <?php if (!$error || ($error && strpos($error, 'Data klien tidak ditemukan') === false)): ?>
                <div class="booking-form">
                    <form action="" method="POST" enctype="multipart/form-data" id="bookingForm">
                        
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                Informasi Pribadi
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-input" 
                                           value="<?php echo htmlspecialchars($user_name); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-input" 
                                           value="<?php echo htmlspecialchars($user_email); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="tel" name="phone" class="form-input" 
                                           value="<?php echo htmlspecialchars($user_phone); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="L" <?php echo ($current_gender === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="P" <?php echo ($current_gender === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Service Selection -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                Layanan & Psikolog
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Layanan yang Dibutuhkan</label>
                                    <select name="service_type" id="service_type" class="form-select" required>
                                        <option value="">-- Pilih Layanan --</option>
                                        <option value="konseling_anak">Konseling Anak & Remaja</option>
                                        <option value="konseling_dewasa">Konseling Dewasa</option>
                                        <option value="konseling_keluarga_pernikahan">Konseling Keluarga & Pernikahan</option>
                                        <option value="rekrutmen_karyawan">Rekrutmen Karyawan (Perusahaan)</option>
                                        <option value="pengembangan_diri">Pengembangan Diri</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Psikolog</label>
                                    <select name="psychologist_id" id="psychologist_id" class="form-select" required>
                                        <option value="">-- Pilih Psikolog --</option>
                                        <?php foreach($all_psychologists as $psi): ?>
                                        <option value="<?php echo $psi['psychologist_id']; ?>" 
                                                data-spec="<?php echo htmlspecialchars(strtolower($psi['spesialisasi'])); ?>">
                                            <?php echo htmlspecialchars($psi['name']); ?> - <?php echo htmlspecialchars($psi['spesialisasi']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Selection -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                Jadwal Konsultasi
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" name="date" id="consultation_date" class="form-input" 
                                           min="<?php echo date('Y-m-d'); ?>" required onchange="loadAvailableTimes()">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Jam Mulai</label>
                                    <select name="time_slot" id="time_slot" class="form-select" required>
                                        <option value="">-- Pilih Tanggal & Psikolog --</option>
                                    </select>
                                    <small style="color: #6c757d; margin-top: 6px; display: block;">
                                        <i class="fas fa-info-circle"></i> Sesi berlangsung 2 jam
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Hidup Section -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-file-medical"></i>
                                </div>
                                Riwayat Hidup
                            </h3>
                            
                            <div class="info-box">
                                <h4><i class="fas fa-info-circle" style="margin-right: 8px;"></i>Penting untuk Konsultasi</h4>
                                <p>Form Riwayat Hidup membantu psikolog memahami kondisi Anda dengan lebih baik. Mohon diisi dengan jujur dan detail.</p>
                            </div>
                            
                            <button type="button" class="rh-form-btn" id="btnRH" onclick="openRHModal()">
                                <i class="fas fa-edit"></i>
                                Isi Form Riwayat Hidup
                            </button>
                        </div>

                        <!-- Payment Info -->
                        <div class="info-box">
                            <h4><i class="fas fa-credit-card" style="margin-right: 8px;"></i>Biaya Komitmen: Rp 50.000</h4>
                            <p>Transfer ke <strong>BCA 123-456-7890 a.n Biro Rali Ra</strong> untuk mengamankan jadwal Anda</p>
                        </div>

                        <!-- Upload Payment Proof -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-upload"></i>
                                </div>
                                Bukti Pembayaran
                            </h3>
                            
                            <div class="form-group">
                                <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <div class="file-upload-text">Klik untuk upload bukti transfer</div>
                                    <div class="file-upload-subtitle">Format: JPG, PNG, PDF (Max: 5MB)</div>
                                    <input type="file" id="fileInput" name="payment_proof" accept="image/*,.pdf" style="display: none;">
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="book_consultation" class="btn btn-primary btn-block">
                            <i class="fas fa-check-circle"></i>
                            Konfirmasi Booking
                        </button>

                    </form>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Riwayat Hidup -->
    <div id="rhModal" class="modal">
        <div class="modal-content" style="max-width:800px;">
            <div class="modal-header">
                <h2>Form Riwayat Hidup</h2>
                <button class="close" onclick="closeRHModal()">&times;</button>
            </div>
            <!-- Make modal body scrollable on overflow -->
            <div class="modal-body" style="max-height:60vh; overflow-y:auto; padding-right:12px;">
                <form id="rhForm">
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            1. Apa keluhan/masalah yang ingin dikonsultasikan? <span style="color: red;">*</span>
                        </label>
                        <textarea name="keluhan_masalah" id="keluhan_masalah" class="form-textarea" 
                                  rows="4" maxlength="2500" 
                                  placeholder="Ceritakan keluhan atau masalah yang ingin dikonsultasikan..." required></textarea>
                        <div class="char-count"><span id="count1">0</span>/500 kata (maksimal 2500 karakter)</div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            2. Sudah berapa lama mengalami masalah ini? <span style="color: red;">*</span>
                        </label>
                        <select name="lama_masalah" id="lama_masalah" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="Baru">Baru</option>
                            <option value="1-3 bulan">1-3 bulan</option>
                            <option value="3-6 bulan">3-6 bulan</option>
                            <option value="Lebih dari 6 bulan">Lebih dari 6 bulan</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            3. Apakah pernah konsultasi sebelumnya? <span style="color: red;">*</span>
                        </label>
                        <div style="display: flex; gap: 20px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="radio" name="pernah_konsultasi" value="Ya" style="margin-right: 8px;" required>
                                Ya
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="radio" name="pernah_konsultasi" value="Tidak" style="margin-right: 8px;" required>
                                Tidak
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            4. Ceritakan latar belakang kehidupan Anda <span style="color: red;">*</span>
                        </label>
                        <textarea name="latar_belakang" id="latar_belakang" class="form-textarea" 
                                  rows="5" maxlength="5000" 
                                  placeholder="Ceritakan latar belakang kehidupan Anda secara detail..." required></textarea>
                        <div class="char-count"><span id="count2">0</span>/1000 kata (maksimal 5000 karakter)</div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            5. Tahu Rali Ra dari mana? <span style="color: red;">*</span>
                        </label>
                        <select name="tahu_dari" id="tahu_dari" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="Google">Google</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Rekomendasi">Rekomendasi</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            6. Agama <span style="color: red;">*</span>
                        </label>
                        <select name="agama" id="agama" class="form-select" required>
                            <option value="">-- Pilih Agama --</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katholik">Katholik</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            7. Suku <span style="color: red;">*</span>
                        </label>
                        <input type="text" name="suku" id="suku" class="form-input" placeholder="Isi suku (contoh: Jawa, Batak, Bugis)" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            8. Hobi <span style="color: red;">*</span>
                        </label>
                        <textarea name="hobi" id="hobi" class="form-textarea" rows="2" maxlength="500" placeholder="Tuliskan hobi Anda" required></textarea>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 500; display: block; margin-bottom: 6px;">
                            9. Alamat <span style="color: red;">*</span>
                        </label>
                        <textarea name="alamat" id="alamat" class="form-textarea" rows="3" maxlength="1000" placeholder="Tuliskan alamat lengkap Anda" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRHModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveRHForm()">Simpan</button>
            </div>
        </div>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    <script>
        // Handle file selection
        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                const fileSize = (input.files[0].size / 1024 / 1024).toFixed(2);
                document.getElementById('fileName').textContent = 'File terpilih: ' + fileName + ' (' + fileSize + ' MB)';
                document.getElementById('fileName').style.display = 'block';
                document.getElementById('uploadText').style.display = 'none';
            }
        }

        // RH Modal Functions
        function openRHModal() {
            document.getElementById('rhModal').style.display = 'block';
        }

        function closeRHModal() {
            document.getElementById('rhModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('rhModal');
            if (event.target == modal) {
                closeRHModal();
            }
        }

        // Save RH Form data to hidden fields in main form
        function saveRHForm() {
            const form = document.getElementById('rhForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Get all RH form values
            const keluhan_masalah = document.getElementById('keluhan_masalah').value;
            const lama_masalah = document.getElementById('lama_masalah').value;
            const pernah_konsultasi = document.querySelector('input[name="pernah_konsultasi"]:checked')?.value;
            const latar_belakang = document.getElementById('latar_belakang').value;
            const tahu_dari = document.getElementById('tahu_dari').value;
            const agama = document.getElementById('agama') ? document.getElementById('agama').value : '';
            const suku = document.getElementById('suku') ? document.getElementById('suku').value : '';
            const hobi = document.getElementById('hobi') ? document.getElementById('hobi').value : '';
            const alamat = document.getElementById('alamat') ? document.getElementById('alamat').value : '';

            // Validate all fields filled
            if (!keluhan_masalah || !lama_masalah || !pernah_konsultasi || !latar_belakang || !tahu_dari || !agama || !suku || !alamat) {
                alert('Semua field dalam Form RH harus diisi!');
                return;
            }

            // Copy RH data to main form as hidden fields
            const mainForm = document.getElementById('bookingForm');
            
            // Remove existing hidden fields if any
            const existingFields = mainForm.querySelectorAll('input[type="hidden"][name^="keluhan"], input[type="hidden"][name^="lama"], input[type="hidden"][name^="pernah"], input[type="hidden"][name^="latar"], input[type="hidden"][name^="tahu"], input[type="hidden"][name^="agama"], input[type="hidden"][name^="suku"], input[type="hidden"][name^="hobi"], input[type="hidden"][name^="alamat"]');
            existingFields.forEach(field => field.remove());

            // Add hidden fields with RH data
            const rhData = {
                'keluhan_masalah': keluhan_masalah,
                'lama_masalah': lama_masalah,
                'pernah_konsultasi': pernah_konsultasi,
                'latar_belakang': latar_belakang,
                'tahu_dari': tahu_dari,
                'agama': agama,
                'suku': suku,
                'hobi': hobi,
                'alamat': alamat
            };

            Object.keys(rhData).forEach(fieldName => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = fieldName;
                input.value = rhData[fieldName];
                mainForm.appendChild(input);
            });

            // Update button style with animation
            const btnRH = document.getElementById('btnRH');
            btnRH.classList.add('rh-filled');
            btnRH.innerHTML = '<i class="fas fa-check-circle"></i> ✓ Form RH Sudah Diisi';
            
                        
            // Add success animation
            btnRH.style.animation = 'success-bounce 1s ease';
            
            closeRHModal();
        }

        // Character counter
        document.getElementById('keluhan_masalah').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('count1').textContent = Math.ceil(count / 5); // Approximate word count
        });

        document.getElementById('latar_belakang').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('count2').textContent = Math.ceil(count / 5); // Approximate word count
        });

        // Load available times based on selected psychologist and date
        function loadAvailableTimes() {
            const psychologistId = document.getElementById('psychologist_id').value;
            const consultationDate = document.getElementById('consultation_date').value;
            const timeSlotSelect = document.getElementById('time_slot');
            
            if (!psychologistId || !consultationDate) {
                timeSlotSelect.innerHTML = '<option value="">-- Pilih Tanggal & Psikolog terlebih dahulu --</option>';
                return;
            }
            
            // Show loading with animation
            timeSlotSelect.innerHTML = '<option value="">🔄 Memuat jadwal...</option>';
            
            // Call API
            fetch('<?php echo $path; ?>api/get_available_times.php?psychologist_id=' + psychologistId + '&date=' + consultationDate)
                .then(response => response.json())
                .then(data => {
                    if (data.available_times && data.available_times.length > 0) {
                        let html = '<option value="">-- Pilih Jam --</option>';
                        data.available_times.forEach(time => {
                            html += '<option value="' + time.time + '">' + time.display + '</option>';
                        });
                        timeSlotSelect.innerHTML = html;
                    } else {
                        timeSlotSelect.innerHTML = '<option value="">😔 Tidak ada jam tersedia untuk tanggal ini</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    timeSlotSelect.innerHTML = '<option value="">⚠️ Error loading times</option>';
                });
        }

        // Filter psychologists based on service type (DINAMIS dari database via API)
        function filterPsychologists() {
            const serviceType = document.getElementById('service_type').value;
            const psychologistSelect = document.getElementById('psychologist_id');

            // Restore all options jika tidak ada service dipilih
            if (!serviceType) {
                psychologistSelect.innerHTML = '<option value="">-- Pilih Psikolog --</option>';
                if (window._originalPsychOptions) {
                    window._originalPsychOptions.forEach(opt => 
                        psychologistSelect.appendChild(opt.cloneNode(true))
                    );
                }
                return;
            }

            // Tampilkan loading state
            psychologistSelect.innerHTML = '<option value="">⏳ Memuat psikolog...</option>';
            psychologistSelect.disabled = true;
            
            // Fetch dari database via API (BARU!)
            fetch('<?php echo $path; ?>api/get_psychologists_by_service.php?service_type=' + encodeURIComponent(serviceType))
                .then(response => response.json())
                .then(data => {
                    psychologistSelect.disabled = false;
                    
                    if (data.success && data.data.length > 0) {
                        let html = '<option value="">-- Pilih Psikolog --</option>';
                        data.data.forEach(psych => {
                            html += `<option value="${psych.psychologist_id}" data-spec="${psych.spesialisasi.toLowerCase()}">
                                        ${psych.name} - ${psych.spesialisasi}
                                    </option>`;
                        });
                        psychologistSelect.innerHTML = html;
                    } else {
                        psychologistSelect.innerHTML = '<option value="" disabled>🤷 Tidak ada psikolog untuk layanan ini</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading psychologists:', error);
                    psychologistSelect.disabled = false;
                    psychologistSelect.innerHTML = '<option value="">⚠️ Error memuat data psikolog</option>';
                });
        }

        
        // Add event listeners for service type and psychologist changes
        document.addEventListener('DOMContentLoaded', function() {
            const serviceType = document.getElementById('service_type');
            const psychologistId = document.getElementById('psychologist_id');
            const consultationDate = document.getElementById('consultation_date');
            const timeSlot = document.getElementById('time_slot');
            const fileInput = document.getElementById('fileInput');

            // Store original psychologist options for later filtering
            if (psychologistId) {
                window._originalPsychOptions = Array.from(psychologistId.querySelectorAll('option')).filter(o => o.value !== '');
            }
            
                        
            if (serviceType) {
                serviceType.addEventListener('change', function() {
                    filterPsychologists();
                    // Reset time slot when service changes
                    document.getElementById('time_slot').innerHTML = '<option value="">-- Pilih Psikolog terlebih dahulu --</option>';
                });
            }
            
            if (psychologistId) {
                psychologistId.addEventListener('change', function() {
                    // Reset time slot when psychologist changes
                    document.getElementById('time_slot').innerHTML = '<option value="">-- Pilih Tanggal terlebih dahulu --</option>';
                    loadAvailableTimes();
                });
            }

            if (consultationDate) {
                consultationDate.addEventListener('change', function() {
                    loadAvailableTimes();
                });
            }

            if (timeSlot) {
                timeSlot.addEventListener('change', function() {
                                    });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                                    });
            }

            // Add smooth scroll behavior
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add form validation feedback
            const form = document.getElementById('bookingForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const btnRH = document.getElementById('btnRH');
                    const fileInput = document.getElementById('fileInput');

                    // Ensure RH is filled
                    if (!btnRH.classList.contains('rh-filled')) {
                        e.preventDefault();
                        alert('Mohon isi Form Riwayat Hidup terlebih dahulu!');
                        openRHModal();
                        return;
                    }

                    // Ensure payment proof is uploaded (file input is hidden, so use JS validation)
                    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                        e.preventDefault();
                        alert('Mohon upload bukti pembayaran terlebih dahulu!');
                        // Open file picker so user can choose file immediately
                        if (fileInput) {
                            fileInput.click();
                        }
                        return;
                    }

                    // Add success animation to form
                    form.classList.add('booking-success');
                });
            }

            // Add input field animations
            const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Add file upload feedback
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const fileName = this.files[0].name;
                        const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
                        const uploadText = document.querySelector('.file-upload-text');
                        const uploadSubtitle = document.querySelector('.file-upload-subtitle');
                        
                        if (uploadText) {
                            uploadText.textContent = '✅ File terpilih: ' + fileName;
                        }
                        if (uploadSubtitle) {
                            uploadSubtitle.textContent = 'Ukuran: ' + fileSize + ' MB';
                        }
                    }
                });
            }
        });
    </script>

        </main>
    </div>
</body>
</html>

