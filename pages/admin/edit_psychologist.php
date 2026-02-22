<?php
/**
 * Filename: pages/admin/edit_psychologist.php
 * Description: Edit data psikolog
 */

session_start();
$path = '../../';
$page_title = 'Edit Psikolog';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

$user_id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';
$psychologist = null;

if ($user_id <= 0) {
    header('Location: manage_psychologists.php');
    exit;
}

// Get psychologist data
$sql_get = "SELECT u.user_id, u.name, u.email, u.phone, pp.spesialisasi, pp.nomor_sipp, pp.bio, pp.pengalaman_tahun, pp.foto_profil, pp.show_on_landing 
            FROM users u
            LEFT JOIN psychologist_profiles pp ON u.user_id = pp.user_id
            WHERE u.user_id = ? AND u.role = 'psychologist'";
$psychologist = $db->getPrepare($sql_get, [$user_id]);

if (!$psychologist) {
    header('Location: manage_psychologists.php');
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $sipp = trim($_POST['sipp'] ?? '');
    $spesialisasi = trim($_POST['spesialisasi'] ?? '');
    $pengalaman = intval($_POST['pengalaman'] ?? 0);
    $bio = trim($_POST['bio'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $show_on_landing = isset($_POST['show_on_landing']) ? 1 : 0;
    
    // Handle photo upload
    $photo_path = $psychologist['foto_profil'] ?? ''; // Keep existing photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = $path . 'assets/img/psychologists/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = time() . '_' . basename($_FILES['photo']['name']);
        $target_path = $upload_dir . $filename;
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['photo']['type'], $allowed_types) && $_FILES['photo']['size'] <= 2 * 1024 * 1024) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                $photo_path = 'psychologists/' . $filename;
            }
        }
    }

    // Validasi
    if (!$name || !$email || !$sipp) {
        $error = 'Nama, email, dan SIPP harus diisi!';
    } else {
        // Check email unik (exclude current user)
        $sql_check = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        $existing = $db->getPrepare($sql_check, [$email, $user_id]);

        if ($existing) {
            $error = 'Email sudah terdaftar oleh user lain!';
        } else {
            // Update user
            if ($password) {
                // Update dengan password baru
                $password_hash = Database::hashPassword($password);
                $sql_update = "UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE user_id = ?";
                $db->executePrepare($sql_update, [$name, $email, $phone, $password_hash, $user_id]);
            } else {
                // Update tanpa password
                $sql_update = "UPDATE users SET name = ?, email = ?, phone = ? WHERE user_id = ?";
                $db->executePrepare($sql_update, [$name, $email, $phone, $user_id]);
            }

            // Update psychologist profile
            $sql_profile = "UPDATE psychologist_profiles SET spesialisasi = ?, nomor_sipp = ?, bio = ?, foto_profil = ?, pengalaman_tahun = ?, show_on_landing = ? WHERE user_id = ?";
            $db->executePrepare($sql_profile, [$spesialisasi, $sipp, $bio, $photo_path, $pengalaman, $show_on_landing, $user_id]);

            $success = 'Data psikolog berhasil diperbarui!';

            // Reload data
            $psychologist = $db->getPrepare($sql_get, [$user_id]);
        }
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
            
            <!-- Alert Messages -->
            <?php if ($error): ?>
            <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ✕ <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div style="background-color: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <!-- Form Edit -->
            <div class="glass-panel" style="max-width: 800px; padding: 40px; position: relative; overflow: hidden;">
                <!-- Decorative Header -->
                <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: linear-gradient(45deg, var(--color-primary) 0%, var(--color-accent) 100%); border-radius: 0 0 0 150px; opacity: 0.05;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div style="text-align: center; margin-bottom: 40px;">
                        <div style="display: inline-flex; align-items: center; gap: 15px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; padding: 15px 30px; border-radius: 50px; box-shadow: 0 8px 25px rgba(251, 186, 0, 0.3);">
                            <i class="fas fa-user-edit" style="font-size: 24px;"></i>
                            <div style="text-align: left;">
                                <h2 style="margin: 0; font-size: 20px; font-weight: 700;">Edit Psikolog</h2>
                                <p style="margin: 0; font-size: 12px; opacity: 0.9;">Kelola informasi dan pengaturan psikolog</p>
                            </div>
                        </div>
                    </div>
                <form method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
                        <!-- Kolom Kiri -->
                        <div>
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                    <i class="fas fa-user" style="margin-right: 8px; color: var(--color-primary);"></i>Nama Lengkap *
                                </label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($psychologist['name']); ?>" required style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                    <i class="fas fa-envelope" style="margin-right: 8px; color: var(--color-primary);"></i>Email *
                                </label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($psychologist['email']); ?>" required style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                    <i class="fas fa-phone" style="margin-right: 8px; color: var(--color-primary);"></i>Nomor Telepon
                                </label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($psychologist['phone'] ?? ''); ?>" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                    <i class="fas fa-certificate" style="margin-right: 8px; color: var(--color-primary);"></i>SIPP (Nomor Registrasi) *
                                </label>
                                <input type="text" name="sipp" value="<?php echo htmlspecialchars($psychologist['nomor_sipp'] ?? ''); ?>" required style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div>
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                    <i class="fas fa-stethoscope" style="margin-right: 8px; color: var(--color-primary);"></i>Spesialisasi
                                </label>
                                <input type="text" name="spesialisasi" value="<?php echo htmlspecialchars($psychologist['spesialisasi'] ?? ''); ?>" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                    <i class="fas fa-clock" style="margin-right: 8px; color: var(--color-primary);"></i>Pengalaman (Tahun)
                                </label>
                                <input type="number" name="pengalaman" value="<?php echo $psychologist['pengalaman_tahun'] ?? 0; ?>" min="0" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                    <i class="fas fa-lock" style="margin-right: 8px; color: var(--color-primary);"></i>Password Baru (Opsional)
                                </label>
                                <input type="password" name="password" placeholder="Minimal 6 karakter" minlength="6" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                <small style="color: #6c757d; font-size: 12px; margin-top: 5px; display: block;">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                        </div>
                    </div>

                    <!-- Bio Section -->
                    <div style="margin-bottom: 30px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                            <i class="fas fa-align-left" style="margin-right: 8px; color: var(--color-primary);"></i>Bio / Deskripsi
                        </label>
                        <textarea name="bio" style="width: 100%; padding: 15px; border: 2px solid #e1e8ed; border-radius: 8px; min-height: 120px; box-sizing: border-box; font-size: 14px; font-family: inherit; resize: vertical; transition: all 0.3s ease;"><?php echo htmlspecialchars($psychologist['bio'] ?? ''); ?></textarea>
                    </div>

                    <!-- Photo Section -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                <i class="fas fa-image" style="margin-right: 8px; color: var(--color-primary);"></i>Foto Profil Saat Ini
                            </label>
                            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 12px; border: 2px dashed #dee2e6;">
                                <?php 
                                $current_photo = $psychologist['foto_profil'] ?? '';
                                if ($current_photo && file_exists($path . 'assets/img/' . $current_photo)) {
                                    echo '<img src="' . $path . 'assets/img/' . $current_photo . '" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid var(--color-primary); box-shadow: 0 8px 25px rgba(251, 186, 0, 0.2);">';
                                } else {
                                    echo '<div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid white; box-shadow: 0 8px 25px rgba(0,0,0,0.1);"><i class="fa-solid fa-user fa-3x" style="color: white;"></i></div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--color-text);">
                                <i class="fas fa-camera" style="margin-right: 8px; color: var(--color-primary);"></i>Ganti Foto Profil (Opsional)
                            </label>
                            <div style="position: relative;">
                                <input type="file" name="photo" accept="image/*" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease; cursor: pointer;">
                                <div style="margin-top: 8px; padding: 10px; background: #e3f2fd; border-radius: 6px; border-left: 4px solid var(--color-primary);">
                                    <small style="color: #1565c0; font-size: 12px; font-weight: 500;">
                                        <i class="fas fa-info-circle" style="margin-right: 4px;"></i>
                                        Format: JPG, PNG, WebP. Maksimal: 2MB. Kosongkan jika tidak ingin mengubah foto.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px; padding: 20px; background: linear-gradient(135deg, #f8f9ff 0%, #e8f4ff 100%); border-radius: 12px; border: 2px solid #e1f5ff; position: relative; overflow: hidden;">
                        <!-- Decorative Corner -->
                        <div style="position: absolute; top: 0; right: 0; width: 40px; height: 40px; background: linear-gradient(45deg, #4A90E2 0%, #357ABD 100%); border-radius: 0 12px 0 40px; opacity: 0.1;"></div>
                        
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="flex: 1;">
                                <label style="display: flex; align-items: center; cursor: pointer; font-weight: 600; color: #2c3e50; position: relative;">
                                    <div style="position: relative; margin-right: 15px;">
                                        <input type="checkbox" name="show_on_landing" value="1" <?php echo ($psychologist['show_on_landing'] ?? 0) == 1 ? 'checked' : ''; ?> style="position: absolute; opacity: 0; width: 20px; height: 20px; cursor: pointer;" id="landingToggle">
                                        <div style="width: 50px; height: 26px; background: #ddd; border-radius: 13px; position: relative; transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);" id="landingToggleVisual">
                                            <div style="width: 22px; height: 22px; background: white; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" id="landingToggleCircle"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size: 16px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-star" style="color: #4A90E2; font-size: 14px;"></i>
                                            Tampilkan di Landing Page
                                        </div>
                                        <div style="font-size: 13px; color: #6c757d; font-weight: 400; line-height: 1.4;">
                                            Psikolog akan muncul di halaman utama bagian "Tim Profesional Kami"
                                        </div>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Status Badge -->
                            <div id="landingStatus" style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                <?php if (($psychologist['show_on_landing'] ?? 0) == 1): ?>
                                    <span style="color: #155724; background: #d4edda; padding: 4px 8px; border-radius: 12px;">
                                        <i class="fas fa-check-circle" style="margin-right: 4px;"></i>Aktif
                                    </span>
                                <?php else: ?>
                                    <span style="color: #856404; background: #fff3cd; padding: 4px 8px; border-radius: 12px;">
                                        <i class="fas fa-times-circle" style="margin-right: 4px;"></i>Nonaktif
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <style>
                    /* Form Field Styles */
                    input[type="text"]:focus,
                    input[type="email"]:focus,
                    input[type="tel"]:focus,
                    input[type="number"]:focus,
                    input[type="password"]:focus,
                    textarea:focus {
                        border-color: var(--color-primary) !important;
                        box-shadow: 0 0 0 3px rgba(251, 186, 0, 0.1) !important;
                        outline: none !important;
                    }
                    
                    input[type="text"]:hover,
                    input[type="email"]:hover,
                    input[type="tel"]:hover,
                    input[type="number"]:hover,
                    input[type="password"]:hover,
                    textarea:hover {
                        border-color: #c4d5f7 !important;
                    }
                    
                    input[type="file"]:hover {
                        border-color: var(--color-primary) !important;
                        background-color: #f8f9ff !important;
                    }
                    
                    /* Button Hover Effects */
                    .btn-primary:hover {
                        transform: translateY(-2px) !important;
                        box-shadow: 0 8px 25px rgba(251, 186, 0, 0.4) !important;
                    }
                    
                    .glass-btn:hover {
                        transform: translateY(-2px) !important;
                        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
                        background: rgba(255, 255, 255, 0.9) !important;
                    }
                    
                    /* Landing Toggle Styles */
                    #landingToggle:checked + #landingToggleVisual {
                        background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
                        box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
                    }
                    
                    #landingToggle:checked + #landingToggleVisual #landingToggleCircle {
                        transform: translateX(24px);
                        background: white;
                        box-shadow: 0 2px 8px rgba(255,255,255,0.8);
                    }
                    
                    #landingToggleVisual:hover {
                        transform: scale(1.02);
                    }
                    
                    #landingToggleCircle:hover {
                        transform: scale(1.1);
                    }
                    </style>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const toggle = document.getElementById('landingToggle');
                        const status = document.getElementById('landingStatus');
                        
                        function updateStatus() {
                            if (toggle.checked) {
                                status.innerHTML = '<span style="color: #155724; background: #d4edda; padding: 4px 8px; border-radius: 12px;"><i class="fas fa-check-circle" style="margin-right: 4px;"></i>Aktif</span>';
                            } else {
                                status.innerHTML = '<span style="color: #856404; background: #fff3cd; padding: 4px 8px; border-radius: 12px;"><i class="fas fa-times-circle" style="margin-right: 4px;"></i>Nonaktif</span>';
                            }
                        }
                        
                        if (toggle) {
                            toggle.addEventListener('change', updateStatus);
                        }
                    });
                    </script>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 15px; margin-top: 40px;">
                        <button type="submit" class="btn-primary" style="flex: 1; padding: 15px 30px; font-size: 16px; font-weight: 600; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s ease;">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                        <a href="manage_psychologists.php" class="glass-btn" style="flex: 1; padding: 15px 30px; font-size: 16px; font-weight: 600; border-radius: 8px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>
