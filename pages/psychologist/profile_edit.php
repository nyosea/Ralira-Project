<?php
session_start();
$path = '../../';
$page_title = 'Edit Profil Psikolog';

// Check if user is logged in and is psikolog
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

// Get current psychologist profile
$psychologist_data = $db->getPrepare("SELECT * FROM psychologist_profiles WHERE user_id = ?", [$user_id]);
$current_photo = $_SESSION['profile_picture'] ?? NULL;

// Process update photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file = $_FILES['photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $file['size'] <= 2*1024*1024) {
            $name = 'pp-' . $user_id . '-' . time() . '.' . $ext;
            $destination = $path . 'uploads/profile_pics/' . $name;
            
            if (!is_dir($path . 'uploads/profile_pics/')) {
                mkdir($path . 'uploads/profile_pics/', 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Update users table
                $db_path = 'uploads/profile_pics/' . $name;
                $db->executePrepare("UPDATE users SET profile_picture = ? WHERE user_id = ?", [$db_path, $user_id]);
                $_SESSION['profile_picture'] = $db_path;
                $current_photo = $db_path;
                $success_message = "Foto profil berhasil diperbarui!";
            }
        }
    }
    
    // Handle other profile updates
    // TODO: Import fix_columns.sql dulu baru enable ini
    if (isset($_POST['spesialisasi'])) {
        $db->executePrepare("UPDATE psychologist_profiles SET spesialisasi = ? WHERE user_id = ?", [$_POST['spesialisasi'], $user_id]);
    }
    
    if (isset($_POST['bio'])) {
        $db->executePrepare("UPDATE psychologist_profiles SET bio = ? WHERE user_id = ?", [$_POST['bio'], $user_id]);
    }
    
    if (isset($_POST['nomor_sipp'])) {
        $db->executePrepare("UPDATE psychologist_profiles SET nomor_sipp = ? WHERE user_id = ?", [$_POST['nomor_sipp'], $user_id]);
    }
    
    if (!isset($success_message)) {
        $success_message = "Profil berhasil diperbarui!";
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/psychologist.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_psychologist.php'; ?>
        <?php include $path . 'components/header_psychologist.php'; ?>

        <main class="main-content">
            <div class="profile-container">
                
                <!-- Modern Header -->
                <div class="modern-header">
                    <div class="header-content">
                        <div class="header-info">
                            <h2><i class="fas fa-user-edit" style="font-size: 24px; opacity: 0.9;"></i> Edit Profil</h2>
                            <p>Kelola informasi profil dan kredensial Anda</p>
                        </div>
                        <div class="header-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </div>
                </div>

                <div class="profile-form-card">
                    
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Sukses:</strong> <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        
                        <div class="profile-grid">
                            
                            <!-- Avatar Section -->
                            <div class="avatar-section">
                                <div class="avatar-container" onclick="document.getElementById('photoInput').click()">
                                    <?php if ($current_photo && file_exists($path . $current_photo)): ?>
                                        <img id="avatarPreview" src="<?php echo $path; ?><?php echo htmlspecialchars($current_photo); ?>" 
                                             class="avatar-preview" alt="Profile Photo">
                                    <?php else: ?>
                                        <div id="avatarPreview" class="avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="avatar-overlay">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                                
                                <button type="button" class="avatar-upload-btn" onclick="document.getElementById('photoInput').click()">
                                    <i class="fas fa-camera"></i>
                                    Pilih Foto
                                </button>
                                <input type="file" id="photoInput" name="photo" style="display: none;" onchange="previewImage(this)" accept="image/jpeg,image/jpg,image/png">
                                
                                <div class="avatar-info">
                                    <i class="fas fa-info-circle"></i>
                                    Format JPG/PNG maksimal 2MB
                                </div>
                            </div>

                            <!-- Form Fields -->
                            <div class="form-fields">
                                
                                <div class="form-section">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Nama Lengkap
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="modern-input" value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" readonly>
                                        <div class="input-icon">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <label class="form-label">
                                        <i class="fas fa-id-card"></i>
                                        Nomor SIPP
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="nomor_sipp" class="modern-input" 
                                               value="<?php echo htmlspecialchars($psychologist_data['nomor_sipp'] ?? ''); ?>" 
                                               placeholder="Masukkan nomor SIPP">
                                        <div class="input-icon">
                                            <i class="fas fa-certificate"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <label class="form-label">
                                        <i class="fas fa-file-alt"></i>
                                        Bio Profesional
                                    </label>
                                    <div class="textarea-group">
                                        <textarea name="bio" class="modern-textarea" 
                                                  placeholder="Jelaskan keahlian dan pengalaman profesional Anda..." maxlength="500"><?php echo htmlspecialchars($psychologist_data['bio'] ?? ''); ?></textarea>
                                        <div class="char-counter">
                                            <span id="charCount">0</span> / 500
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Simpan Profil
                            </button>
                            <button type="button" class="btn-secondary" onclick="window.location.href='dashboard.php'">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    <script>
        // Preview Image Script
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Character Counter
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.querySelector('textarea[name="bio"]');
            const charCount = document.getElementById('charCount');
            
            if (textarea && charCount) {
                // Set initial count
                charCount.textContent = textarea.value.length;
                
                // Update count on input
                textarea.addEventListener('input', function() {
                    const count = this.value.length;
                    charCount.textContent = count;
                    
                    // Change color based on count
                    if (count > 450) {
                        charCount.style.color = '#dc3545';
                    } else if (count > 400) {
                        charCount.style.color = '#ffc107';
                    } else {
                        charCount.style.color = '#6c757d';
                    }
                    
                    // Prevent typing over limit
                    if (count > 500) {
                        this.value = this.value.substring(0, 500);
                        charCount.textContent = 500;
                    }
                });
            }
        });
    </script>
</body>
</html>