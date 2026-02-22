<?php
// Session check HARUS di paling atas sebelum output apapun
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Location: ../../pages/auth/login.php');
    exit();
}

$path = '../../';
$page_title = 'Upload Hasil Tes';

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];
$psychologist_data = $db->getPrepare("SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?", [$user_id]);
$psychologist_id = $psychologist_data['psychologist_id'] ?? null;

// Get psychologist's clients - ambil yang konsultasinya sudah ditangani
$clients = [];
try {
    $sql_clients = "SELECT 
                        cd.client_id,
                        u.name as client_name,
                        cb.tanggal_konsultasi
                    FROM consultation_status cs
                    INNER JOIN client_details cd ON cs.client_id = cd.client_id
                    INNER JOIN users u ON cd.user_id = u.user_id
                    INNER JOIN consultation_bookings cb ON cs.booking_id = cb.booking_id
                    WHERE cs.psychologist_id = ? AND cs.konsultasi_status = 'sudah_ditangani'
                    AND NOT EXISTS (
                        SELECT 1 FROM test_results tr 
                        WHERE tr.client_id = cd.client_id 
                        AND tr.psychologist_id = cs.psychologist_id
                        AND DATE(tr.created_at) >= DATE(cb.tanggal_konsultasi)
                    )
                    GROUP BY cd.client_id
                    ORDER BY u.name ASC";
    
    $clients = $db->queryPrepare($sql_clients, [$psychologist_id]);
    if (!is_array($clients)) {
        $clients = [];
    }
} catch (Exception $e) {
    $clients = [];
}

// Get uploaded results for this psychologist
$uploaded_results = [];
try {
    $sql_results = "SELECT 
                       tr.result_id,
                       tr.jenis_tes,
                       tr.file_hasil_tes,
                       tr.created_at,
                       u.name as client_name
                   FROM test_results tr
                   JOIN client_details cd ON tr.client_id = cd.client_id
                   JOIN users u ON cd.user_id = u.user_id
                   WHERE tr.psychologist_id = ?
                   ORDER BY tr.created_at DESC";
    
    $uploaded_results = $db->queryPrepare($sql_results, [$psychologist_id]);
    if (!is_array($uploaded_results)) {
        $uploaded_results = [];
    }
} catch (Exception $e) {
    $uploaded_results = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? '';
    $test_type = $_POST['test_type'] ?? '';
    $test_date = $_POST['test_date'] ?? '';
    $title = $_POST['title'] ?? '';
    
    // Validate inputs
    if (empty($client_id) || empty($test_type) || empty($test_date) || empty($title)) {
        $error = "Semua field harus diisi!";
    } elseif (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== 0) {
        $error = "Silakan pilih file PDF!";
    } else {
        if (empty($error)) {
            $file = $_FILES['pdf_file'];
            
            // Validate file type
            if ($file['type'] !== 'application/pdf') {
                $error = "Hanya file PDF yang diperbolehkan!";
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
                $error = "Ukuran file terlalu besar! Maksimal 5MB.";
            } else {
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'result_' . $client_id . '_' . time() . '.' . $file_extension;
                $upload_path = $path . 'uploads/results/' . $new_filename;
                
                // Create directory if not exists
                if (!is_dir($path . 'uploads/results/')) {
                    mkdir($path . 'uploads/results/', 0777, true);
                }
                
                // Upload file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Save to database
                    try {
                        $sql_insert = "INSERT INTO test_results 
                                      (client_id, psychologist_id, jenis_tes, tanggal_pelaksanaan, file_hasil_tes, created_at)
                                      VALUES (?, ?, ?, ?, ?, NOW())";
                        
                        $db->executePrepare($sql_insert, [
                            $client_id,
                            $psychologist_id,
                            $test_type,
                            $test_date,
                            $new_filename
                        ]);
                        
                        $success = "Laporan hasil tes berhasil diupload dan dikirim ke klien!";
                        
                        // Refresh uploaded results
                        $uploaded_results = $db->queryPrepare($sql_results, [$psychologist_id]);
                        if (!is_array($uploaded_results)) {
                            $uploaded_results = [];
                        }
                        
                    } catch (Exception $e) {
                        $error = "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
                    }
                } else {
                    $error = "Gagal mengupload file. Silakan coba lagi.";
                }
            }
        }
    }
}

// Ambil nama klien dari URL jika ada (legacy support)
$selected_client = isset($_GET['client']) ? $_GET['client'] : '';
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/psychologist.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive_sections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
    <style>
        /* Modern Upload Result Styles */
        .upload-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Modern Header */
        .modern-header {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
            color: white;
            padding: 40px;
            margin: -20px -20px 30px -20px;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }

        .modern-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .modern-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-info h2 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-info p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .header-icon i {
            font-size: 24px;
            color: white;
        }

        /* Modern Upload Form */
        .upload-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .upload-form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e1e8ed;
        }

        .info-card {
            background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(251, 186, 0, 0.15);
            border: 1px solid #ffeaa7;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(251, 186, 0, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--color-primary);
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--color-text);
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .modern-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
        }

        .modern-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(251, 186, 0, 0.1);
            outline: none;
        }

        .modern-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
        }

        .modern-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(251, 186, 0, 0.1);
            outline: none;
        }

        /* Modern Upload Area */
        .upload-area {
            border: 2px dashed #e1e8ed;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-area:hover {
            border-color: var(--color-primary);
            background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(251, 186, 0, 0.15);
        }

        .upload-area.dragover {
            border-color: var(--color-primary);
            background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 48px;
            color: var(--color-primary);
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 8px;
        }

        .upload-subtitle {
            font-size: 14px;
            color: #6c757d;
        }

        .file-name {
            margin-top: 15px;
            padding: 10px 15px;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 8px;
            color: #155724;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-name i {
            color: #28a745;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(251, 186, 0, 0.3);
        }

        /* Security Info */
        .security-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .security-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255,255,255,0.5);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .security-item:hover {
            background: rgba(255,255,255,0.8);
            transform: translateX(5px);
        }

        .security-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .security-icon i {
            color: white;
            font-size: 16px;
        }

        .security-content {
            flex: 1;
        }

        .security-title {
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .security-description {
            color: #6c757d;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Alert Messages */
        .alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 1px solid #f1b0b7;
            color: #721c24;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert i {
            font-size: 18px;
        }

        /* Recent Uploads Section */
        .recent-uploads-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--gray-50);
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .result-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid #e1e8ed;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
        }

        .result-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .result-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .result-info {
            flex: 1;
        }

        .result-info h4 {
            color: var(--color-text);
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }

        .result-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .result-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #6c757d;
        }

        .result-meta i {
            color: var(--color-primary);
            font-size: 14px;
            width: 16px;
        }

        .result-status {
            flex-shrink: 0;
        }

        .status-badge.uploaded {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .upload-container {
                padding: 15px;
            }

            .modern-header {
                padding: 30px 20px;
                margin: -15px -15px 20px -15px;
            }

            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .header-info h2 {
                font-size: 24px;
                justify-content: center;
            }

            .upload-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .upload-form-card, .info-card {
                padding: 30px 20px;
            }

            .upload-area {
                padding: 30px 20px;
            }

            .upload-icon {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_psychologist.php'; ?>
        <?php include $path . 'components/header_psychologist.php'; ?>

        <main class="main-content">
            <div class="upload-container">
                
                <!-- Modern Header -->
                <div class="modern-header">
                    <div class="header-content">
                        <div class="header-info">
                            <h2><i class="fas fa-file-upload" style="font-size: 24px; opacity: 0.9;"></i> Upload Hasil Tes</h2>
                            <p>Unggah laporan hasil tes psikologi dengan aman dan terenkripsi</p>
                        </div>
                        <div class="header-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                </div>

                <div class="upload-grid">
                    
                    <!-- Upload Form Card -->
                    <div class="upload-form-card">
                        <h3 class="section-title">
                            <i class="fas fa-edit"></i>
                            Form Upload Laporan
                        </h3>
                        
                        <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                            
                            <div class="form-group">
                                <label class="form-label">Pilih Klien</label>
                                <select name="client_id" class="modern-select" id="clientSelect" required>
                                    <option value="">-- Pilih Klien --</option>
                                    <?php foreach($clients as $client): 
                                        $consultation_date = strtotime($client['tanggal_konsultasi']);
                                        $today = strtotime(date('Y-m-d'));
                                        $is_past = $consultation_date <= $today;
                                    ?>
                                    <option value="<?php echo $client['client_id']; ?>" 
                                            data-date="<?php echo $client['tanggal_konsultasi']; ?>"
                                            data-is-past="<?php echo $is_past ? '1' : '0'; ?>">
                                        <?php echo htmlspecialchars($client['client_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Judul Laporan</label>
                                <input type="text" name="title" class="modern-input" 
                                       placeholder="Contoh: Laporan Psikotes Pendidikan" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jenis Pemeriksaan</label>
                                <select name="test_type" class="modern-select" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="konseling">Laporan Konseling</option>
                                    <option value="iq">Tes IQ / Minat Bakat</option>
                                    <option value="tumbuh_kembang">Asesmen Tumbuh Kembang</option>
                                    <option value="karakter">Tes Karakter</option>
                                    <option value="kepribadian">Tes Kepribadian</option>
                                    <option value="psikotes_umum">Psikotes Umum</option>
                                    <option value="asesmen_karir">Asesmen Karir</option>
                                    <option value="asesmen_emosional">Asesmen Emosional</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tanggal Pemeriksaan</label>
                                <input type="date" name="test_date" class="modern-input" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">File Laporan (PDF)</label>
                                <div class="upload-area" id="dropZone" onclick="document.getElementById('fileInput').click()">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="upload-text">Klik atau Tarik file PDF ke sini</div>
                                    <div class="upload-subtitle">Maksimal 5MB. Format PDF only.</div>
                                    <input type="file" name="pdf_file" id="fileInput" accept=".pdf" style="display: none;" required>
                                </div>
                                <div id="fileName" class="file-name" style="display: none;">
                                    <i class="fas fa-file-pdf"></i>
                                    <span></span>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary">
                                <i class="fas fa-upload"></i>
                                Simpan & Kirim ke Klien
                            </button>
                        </form>
                    </div>

                    <!-- Security Info Card -->
                    <div class="info-card">
                        <h3 class="section-title">
                            <i class="fas fa-shield-alt"></i>
                            Keamanan Data
                        </h3>
                        
                        <ul class="security-list">
                            <li class="security-item">
                                <div class="security-icon">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <div class="security-content">
                                    <div class="security-title">Final Report</div>
                                    <div class="security-description">Pastikan file yang diupload adalah laporan final yang sudah ditandatangani</div>
                                </div>
                            </li>
                            
                            <li class="security-item">
                                <div class="security-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div class="security-content">
                                    <div class="security-title">Enkripsi Otomatis</div>
                                    <div class="security-description">Sistem akan otomatis melakukan enkripsi pada file PDF sebelum disimpan ke server</div>
                                </div>
                            </li>
                            
                            <li class="security-item">
                                <div class="security-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="security-content">
                                    <div class="security-title">Akses Terbatas</div>
                                    <div class="security-description">Hanya klien yang bersangkutan dan admin yang memiliki akses untuk membuka file ini</div>
                                </div>
                            </li>
                            
                            <li class="security-item">
                                <div class="security-icon">
                                    <i class="fas fa-user-secret"></i>
                                </div>
                                <div class="security-content">
                                    <div class="security-title">Kerahasiaan Alat Tes</div>
                                    <div class="security-description">Jangan mengupload data mentah (raw data) alat tes psikologi demi menjaga kerahasiaan</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>

                <!-- Alert Messages -->
                <?php if (isset($error)): ?>
                <div class="alert alert-error" style="margin: 20px 0;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                <div class="alert alert-success" style="margin: 20px 0;">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <?php endif; ?>

                <!-- Recently Uploaded Results -->
                <?php if (!empty($uploaded_results)): ?>
                <div class="recent-uploads-section">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i>
                        Laporan Terbaru Diupload
                    </h3>
                    
                    <div class="results-grid">
                        <?php foreach($uploaded_results as $result): ?>
                        <div class="result-item">
                            <div class="result-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="result-info">
                                <h4>Laporan Psikotes <?php echo htmlspecialchars($result['jenis_tes']); ?></h4>
                                <div class="result-meta">
                                    <span class="client-name">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($result['client_name']); ?>
                                    </span>
                                    <span class="test-type">
                                        <i class="fas fa-clipboard-list"></i>
                                        <?php echo htmlspecialchars($result['jenis_tes']); ?>
                                    </span>
                                    <span class="upload-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d M Y', strtotime($result['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="result-status">
                                <span class="status-badge uploaded">Terkirim</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    <script>
        // Enhanced Drag & Drop + Preview Filename
        const fileInput = document.getElementById('fileInput');
        const fileNameDisplay = document.getElementById('fileName');
        const dropZone = document.getElementById('dropZone');

        // File input change handler
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleFile(this.files[0]);
            }
        });

        // Drag and drop handlers
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                
                // Check if PDF
                if (file.type !== 'application/pdf') {
                    showNotification('error', 'Error', 'Hanya file PDF yang diperbolehkan');
                    return;
                }
                
                fileInput.files = files;
                handleFile(file);
            }
        });

        function handleFile(file) {
            // Validasi size (Max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showNotification('error', 'Error', 'Ukuran file terlalu besar! Maksimal 5MB.');
                fileInput.value = '';
                fileNameDisplay.style.display = 'none';
                return;
            }
            
            // Validasi type
            if (file.type !== 'application/pdf') {
                showNotification('error', 'Error', 'Hanya file PDF yang diperbolehkan');
                fileInput.value = '';
                fileNameDisplay.style.display = 'none';
                return;
            }
            
            // Show file name
            fileNameDisplay.querySelector('span').textContent = file.name;
            fileNameDisplay.style.display = 'flex';
            
            showNotification('success', 'Success', 'File PDF berhasil dipilih: ' + file.name);
        }

        function showNotification(type, title, message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                z-index: 2000;
                min-width: 300px;
                border-left: 4px solid ${type === 'success' ? '#28a745' : '#dc3545'};
                animation: slideInRight 0.3s ease-out;
                display: flex;
                align-items: center;
                gap: 15px;
            `;
            
            notification.innerHTML = `
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, ${type === 'success' ? '#28a745' : '#dc3545'} 0%, ${type === 'success' ? '#20c997' : '#c82333'} 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas ${type === 'success' ? 'fa-check' : 'fa-exclamation-circle'}" style="color: white; font-size: 18px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--color-text); margin-bottom: 4px;">${title}</div>
                    <div style="color: #6c757d; font-size: 14px;">${message}</div>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto hide after 4 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 4000);
        }

        // Form submission handler
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            const clientSelect = this.querySelector('select[name="client_id"]');
            const titleInput = this.querySelector('input[name="title"]');
            const testTypeSelect = this.querySelector('select[name="test_type"]');
            const dateInput = this.querySelector('input[name="test_date"]');
            
            if (!clientSelect.value) {
                showNotification('error', 'Error', 'Silakan pilih klien terlebih dahulu');
                return;
            }
            
            if (!titleInput.value.trim()) {
                showNotification('error', 'Error', 'Silakan isi judul laporan');
                return;
            }
            
            if (!testTypeSelect.value) {
                showNotification('error', 'Error', 'Silakan pilih jenis pemeriksaan');
                return;
            }
            
            if (!dateInput.value) {
                showNotification('error', 'Error', 'Silakan pilih tanggal pemeriksaan');
                return;
            }
            
            // Validate file
            if (!fileInput.files || !fileInput.files[0]) {
                showNotification('error', 'Error', 'Silakan pilih file PDF terlebih dahulu');
                return;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';
            submitBtn.disabled = true;
            
            // Submit form normally (not AJAX for file upload)
            this.submit();
        });

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
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
        document.head.appendChild(style);
    </script>
</body>
</html>