<?php
session_start();
$path = '../../';
require_once $path . 'includes/db.php';
$page_title = 'Profil Saya';

$user_id = $_SESSION['user_id'];

// Fetch user profile terbaru
$user = $db->getPrepare("SELECT * FROM users WHERE user_id = ?", [$user_id]);

// Proses update foto saja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_photo'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file = $_FILES['profile_picture'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $file['size'] <= 2*1024*1024) {
            $name = 'pp-' . $user_id . '-' . time() . '.' . $ext;
            $destination = '../../uploads/profile_pics/' . $name;
            if (!is_dir('../../uploads/profile_pics/')) { mkdir('../../uploads/profile_pics/', 0777, true); }
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $db_path = 'uploads/profile_pics/' . $name;
                $db->executePrepare("UPDATE users SET profile_picture = ? WHERE user_id = ?", [$db_path, $user_id]);
                $_SESSION['profile_picture'] = $db_path;
                $user['profile_picture'] = $db_path;
            }
        }
    }
}
// Proses update username saja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
    if (isset($_POST['username'])) {
        $_SESSION['username'] = $_POST['username'];
        $db->executePrepare("UPDATE users SET username = ? WHERE user_id = ?", [$_POST['username'], $user_id]);
        $user['username'] = $_POST['username'];
    }
}
// proses hapus foto profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
    // Hapus file lokal jika bukan remote link & bukan default
    if (!empty($user['profile_picture']) && strpos($user['profile_picture'], 'uploads/profile_pics/') === 0) {
        $fpath = '../../' . $user['profile_picture'];
        if (file_exists($fpath)) unlink($fpath);
    }
    $db->executePrepare("UPDATE users SET profile_picture = NULL WHERE user_id = ?", [$user_id]);
    $_SESSION['profile_picture'] = null;
    $user['profile_picture'] = '';
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
        <?php include $path . 'components/sidebar_client.php'; ?>
        <?php include $path . 'components/header_client.php'; ?>

        <main class="main-content">
            <h2 style="color: var(--color-text); margin-bottom: 20px;">Pengaturan Akun</h2>

            <div class="glass-panel" style="padding: 32px 35px 23px 35px; max-width: 530px; margin:20px auto; box-shadow:0 2px 12px rgba(0,0,0,0.05);">
                <form action="" method="POST" enctype="multipart/form-data" style="margin-bottom: 30px; display: flex; flex-direction: column; align-items: center; gap:10px;">
                    <img src="<?php echo isset($user['profile_picture']) && $user['profile_picture'] ? $path . $user['profile_picture'] : $path.'assets/img/default-avatar.png'; ?>" alt="Profile" style="width: 110px; height: 110px; border-radius: 50%; margin-bottom: 8px; border: 3px solid var(--color-primary); object-fit:cover; background:#fff">
                    <div style="display:flex; gap:10px; width: 100%; justify-content: center;">
                        <input type="file" name="profile_picture" accept="image/png,image/jpeg" id="fileInput" style="display:none;">
                        <label for="fileInput" class="glass-btn" style="cursor:pointer; padding:8px 15px; color:var(--color-primary); border:1px solid var(--color-primary); background:#fff; font-size:0.97rem; margin-bottom:0;">Pilih Foto</label>
                        <button type="submit" name="update_photo" class="btn-primary" style="padding:8px 18px; font-size:0.97rem;">Simpan Foto Profil</button>
                        <button type="submit" name="delete_photo" class="glass-btn" style="background:#ea5757; color:white; padding:8px 14px; border:none; font-size:0.97rem; margin-left:8px;">Hapus Foto</button>
                    </div>
                    <span style="font-size:0.81rem; color:#888; margin-top:0">*.jpg/.jpeg/.png. Max 2MB</span>
                </form>
                <form action="" method="POST" style="margin-bottom: 21px;">
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Nama Panggilan / Username</label>
                        <input type="text" class="glass-input" name="username" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" style="width: 100%;">
                    </div>
                    <button type="submit" name="update_username" class="btn-primary" style="width:100%">Simpan Nama Panggilan</button>
                </form>
                <!-- Form lainnya lanjut dibawah dengan styling rapi  -->

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Nama Lengkap</label>
                    <input type="text" class="glass-input" value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" style="width: 100%;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Email</label>
                    <input type="email" class="glass-input" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" style="width: 100%;" readonly style="background: rgba(0,0,0,0.05);">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Nomor Telepon (WA)</label>
                    <input type="tel" class="glass-input" value="<?php echo isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : ''; ?>" style="width: 100%;">
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Password Baru (Opsional)</label>
                    <input type="password" class="glass-input" placeholder="Kosongkan jika tidak ingin mengubah" style="width: 100%;">
                </div>

                <button type="submit" class="btn-primary" onclick="alert('Profil berhasil diperbarui!')">Simpan Perubahan</button>
            </div>
        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>