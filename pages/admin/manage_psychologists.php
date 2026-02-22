<?php
/**
 * Filename: pages/admin/manage_psychologists.php
 * Description: Manajemen Psikolog - Admin dapat membuat, edit, hapus akun psikolog
 */

session_start();
$path = '../../';
$page_title = 'Manajemen Psikolog';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

$error = '';
$success = '';

// Handle tambah psikolog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $sipp = trim($_POST['sipp'] ?? '');
    $spesialisasi = trim($_POST['spesialisasi'] ?? '');
    $pengalaman = intval($_POST['pengalaman'] ?? 0);
    $bio = trim($_POST['bio'] ?? '');
    
    // Handle photo upload
    $photo_path = '';
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
    if (!$name || !$email || !$password || !$sipp) {
        $error = 'Nama, email, password, dan SIPP harus diisi!';
    } else {
        // Cek email sudah ada
        $sql_check = "SELECT user_id FROM users WHERE email = ?";
        $existing = $db->getPrepare($sql_check, [$email]);

        if ($existing) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $password_hash = Database::hashPassword($password);

            // Insert user
            $sql_user = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
            if ($db->executePrepare($sql_user, [$name, $email, $phone, $password_hash, 'psychologist'])) {
                $user_id = $db->lastId();

                // Insert psychologist profile
                $sql_profile = "INSERT INTO psychologist_profiles (user_id, spesialisasi, nomor_sipp, bio, foto_profil, pengalaman_tahun) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                $db->executePrepare($sql_profile, [$user_id, $spesialisasi, $sipp, $bio, $photo_path, $pengalaman]);

                $success = 'Akun psikolog berhasil dibuat!';
            } else {
                $error = 'Gagal membuat akun psikolog!';
            }
        }
    }
}

// Handle hapus psikolog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        $sql_delete = "DELETE FROM users WHERE user_id = ? AND role = 'psychologist'";
        if ($db->executePrepare($sql_delete, [$user_id])) {
            $success = 'Psikolog berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus psikolog!';
        }
    }
}

// Get semua psikolog dari database
$sql_psychologists = "SELECT u.user_id, u.name, u.email, u.phone, pp.spesialisasi, pp.nomor_sipp, pp.pengalaman_tahun 
                     FROM users u
                     LEFT JOIN psychologist_profiles pp ON u.user_id = pp.user_id
                     WHERE u.role = 'psychologist'
                     ORDER BY u.name ASC";
$psychologists = $db->query($sql_psychologists);

if (!$psychologists) {
    $psychologists = [];
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

            <!-- Header -->
            <div class="title-section">
                <div class="title-section-content">
                    <div>
                        <h2>
                            <i class="fas fa-user-nurse"></i>
                            Manajemen Psikolog
                        </h2>
                        <p>Kelola data psikolog dan informasi praktik</p>
                    </div>
                    <button class="btn-add-psychologist" onclick="document.getElementById('addForm').style.display='block'">
                        <i class="fas fa-plus"></i>
                        Tambah Psikolog
                    </button>
                </div>
            </div>

            <!-- Form Tambah Psikolog (Modal-like) -->
            <div id="addForm" style="display:none; background: rgba(0,0,0,0.6); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; overflow: auto; padding: 20px; backdrop-filter: blur(5px);">
                <div style="max-width: 700px; margin: 50px auto; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; position: relative;">
                    <!-- Modal Header -->
                    <div style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; padding: 25px 30px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-user-plus" style="font-size: 18px; opacity: 0.9;"></i>
                                Tambah Psikolog Baru
                            </h3>
                            <button onclick="document.getElementById('addForm').style.display='none'" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div style="padding: 30px;">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <!-- Kolom Kiri -->
                                <div>
                                    <div style="margin-bottom: 15px;">
                                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                            <i class="fas fa-user" style="margin-right: 8px; color: var(--color-primary);"></i>Nama Lengkap *
                                        </label>
                                        <input type="text" name="name" placeholder="Dr. Ira Puspitawati" required style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                            <i class="fas fa-envelope" style="margin-right: 8px; color: var(--color-primary);"></i>Email *
                                        </label>
                                        <input type="email" name="email" placeholder="ira@ralira.com" required style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                            <i class="fas fa-phone" style="margin-right: 8px; color: var(--color-primary);"></i>Nomor Telepon
                                        </label>
                                        <input type="tel" name="phone" placeholder="081234567890" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                            <i class="fas fa-lock" style="margin-right: 8px; color: var(--color-primary);"></i>Password *
                                        </label>
                                        <input type="password" name="password" placeholder="Minimal 6 karakter" required minlength="6" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                    </div>
                                </div>
                                
                                <!-- Kolom Kanan -->
                                <div>
                                    <div style="margin-bottom: 15px;">
                                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                            <i class="fas fa-certificate" style="margin-right: 8px; color: var(--color-primary);"></i>SIPP (Nomor Registrasi) *
                                        </label>
                                        <input type="text" name="sipp" placeholder="0506-22-2-2" required style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                            <i class="fas fa-stethoscope" style="margin-right: 8px; color: var(--color-primary);"></i>Spesialisasi
                                        </label>
                                        <input type="text" name="spesialisasi" placeholder="Psikolog Anak" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                            <i class="fas fa-clock" style="margin-right: 8px; color: var(--color-primary);"></i>Pengalaman (Tahun)
                                        </label>
                                        <input type="number" name="pengalaman" placeholder="5" min="0" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease;">
                                    </div>
                                </div>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                    <i class="fas fa-align-left" style="margin-right: 8px; color: var(--color-primary);"></i>Bio / Deskripsi
                                </label>
                                <textarea name="bio" placeholder="Deskripsi singkat tentang psikolog..." style="width: 100%; padding: 15px; border: 2px solid #e1e8ed; border-radius: 8px; min-height: 100px; box-sizing: border-box; font-size: 14px; font-family: inherit; resize: vertical; transition: all 0.3s ease;"></textarea>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50;">
                                    <i class="fas fa-camera" style="margin-right: 8px; color: var(--color-primary);"></i>Foto Profil
                                </label>
                                <div style="position: relative;">
                                    <input type="file" name="photo" accept="image/*" style="width: 100%; padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: all 0.3s ease; cursor: pointer;">
                                    <div style="margin-top: 8px; padding: 10px; background: #e3f2fd; border-radius: 6px; border-left: 4px solid var(--color-primary);">
                                        <small style="color: #1565c0; font-size: 12px; font-weight: 500;">
                                            <i class="fas fa-info-circle" style="margin-right: 4px;"></i>
                                            Format: JPG, PNG, WebP. Maksimal: 2MB.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 15px; margin-top: 25px;">
                                <button type="submit" style="flex: 1; padding: 15px 30px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="fas fa-save"></i>
                                    Simpan Psikolog
                                </button>
                                <button type="button" onclick="document.getElementById('addForm').style.display='none'" style="flex: 1; padding: 15px 30px; background: #6c757d; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="fas fa-times"></i>
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Daftar Psikolog -->
            <div>
                <div class="psychologists-list-header">
                    <h3>
                        <i class="fas fa-users"></i>
                        Daftar Psikolog
                        <span class="psychologists-count"><?php echo count($psychologists); ?></span>
                    </h3>
                    
                    <!-- Search Box -->
                    <div class="psychologists-search-container">
                        <div class="psychologists-search-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" id="searchPsychologists" placeholder="Cari psikolog..." class="psychologists-search-input">
                    </div>
                </div>

                <?php if (empty($psychologists)): ?>
                    <div style="text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; border: 2px solid #dee2e6;">
                        <i class="fas fa-user-slash" style="font-size: 48px; color: #6c757d; margin-bottom: 20px;"></i>
                        <h4 style="color: #6c757d; margin: 0; font-size: 18px;">Belum ada psikolog terdaftar</h4>
                        <p style="color: #868e96; margin: 10px 0 0 0; font-size: 14px;">Silakan tambah psikolog baru untuk memulai</p>
                    </div>
                <?php else: ?>
                    <div class="psychologists-cards" id="psychologistsGrid">
                        <?php foreach ($psychologists as $psi): ?>
                        <div class="psychologist-card">
                            <!-- Card Header -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0; color: var(--color-text); font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-user-nurse" style="color: var(--color-primary); font-size: 16px;"></i>
                                        <?php echo htmlspecialchars($psi['name']); ?>
                                    </h4>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
                                        <span style="background: #e3f2fd; color: #1565c0; padding: 4px 10px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                                            <i class="fas fa-certificate" style="margin-right: 4px;"></i>
                                            <?php echo htmlspecialchars($psi['nomor_sipp'] ?? 'No SIPP'); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Status Indicator - Default Active -->
                                <div style="width: 12px; height: 12px; background: #28a745; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);" title="Active"></div>
                            </div>
                            
                            <!-- Card Body -->
                            <div style="margin-bottom: 20px;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; color: #6c757d; font-size: 14px;">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo htmlspecialchars($psi['email']); ?></span>
                                </div>
                                
                                <?php if (!empty($psi['phone'])): ?>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; color: #6c757d; font-size: 14px;">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($psi['phone']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($psi['spesialisasi'])): ?>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; color: #6c757d; font-size: 14px;">
                                    <i class="fas fa-stethoscope"></i>
                                    <span><?php echo htmlspecialchars($psi['spesialisasi']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($psi['pengalaman_tahun'])): ?>
                                <div style="display: flex; align-items: center; gap: 10px; color: #6c757d; font-size: 14px;">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $psi['pengalaman_tahun']; ?> tahun pengalaman</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Card Actions -->
                            <div style="display: flex; gap: 10px; padding-top: 15px; border-top: 1px solid #f1f3f4;">
                                <a href="edit_psychologist.php?id=<?php echo $psi['user_id']; ?>" style="flex: 1; padding: 10px 15px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; box-sizing: border-box;">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <form method="POST" style="flex: 1; margin: 0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $psi['user_id']; ?>">
                                    <button type="submit" style="width: 100%; padding: 10px 15px; background: #dc3545; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; box-sizing: border-box;">
                                        <i class="fas fa-trash"></i>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    
    <style>
    /* Psychologist Cards Styling */
    .psychologist-card {
        transform: translateY(0);
    }
    
    .psychologist-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12) !important;
        border-color: var(--color-primary) !important;
    }
    
    /* Search Box Styling */
    #searchPsychologists:focus {
        border-color: var(--color-primary) !important;
        box-shadow: 0 0 0 3px rgba(251, 186, 0, 0.1) !important;
        outline: none !important;
    }
    
    /* Button Hover Effects */
    .psychologist-card a:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(251, 186, 0, 0.4) !important;
    }
    
    .psychologist-card button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4) !important;
        background: #c82333 !important;
    }
    
    /* Responsive Grid */
    @media (max-width: 768px) {
        #psychologistsGrid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
    
    @media (min-width: 769px) and (max-width: 1200px) {
        #psychologistsGrid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchPsychologists');
        const cards = document.querySelectorAll('.psychologist-card');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                cards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    const shouldShow = searchTerm === '' || text.includes(searchTerm);
                    
                    card.style.display = shouldShow ? 'block' : 'none';
                });
            });
        }
        
        // Add hover effects to cards
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    </script>
</body>
</html>
