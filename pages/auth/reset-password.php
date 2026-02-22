<?php
/**
 * Filename: pages/auth/reset-password.php
 * Description: Halaman untuk reset password dengan token
 * Features: Validate token, handle password reset
 */

session_start();
$path = '../../';
$page_title = 'Reset Password - Biro Psikologi Rali Ra';

// Include database helper
require_once $path . 'includes/db.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../client/dashboard.php");
    exit;
}

$error = '';
$success = '';
$token = trim($_GET['token'] ?? '');
$token_valid = false;
$user_id = null;
$user_email = '';

// Validate token
if ($token) {
    $db = new Database();
    $db->connect();
    
    $sql = "SELECT user_id, email FROM users WHERE reset_token = ? AND reset_token_expires > NOW()";
    $user = $db->getPrepare($sql, [$token]);
    
    if ($user) {
        $token_valid = true;
        $user_id = $user['user_id'];
        $user_email = htmlspecialchars($user['email']);
    } else {
        $error = 'Link reset password tidak valid atau sudah kadaluarsa. Silakan minta link baru.';
    }
} else {
    $error = 'Token tidak ditemukan. Link reset tidak valid.';
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    if (!$token_valid || !$user_id) {
        $error = 'Session reset tidak valid. Silakan minta link baru.';
    } else {
        $password = trim($_POST['password'] ?? '');
        $password_confirm = trim($_POST['password_confirm'] ?? '');
        
        if (!$password || !$password_confirm) {
            $error = 'Password dan konfirmasi password harus diisi!';
        } elseif ($password !== $password_confirm) {
            $error = 'Password dan konfirmasi password tidak sama!';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            // Hash and update password
            $db = new Database();
            $db->connect();
            
            $hashed_password = Database::hashPassword($password);
            
            $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?";
            $db->executePrepare($update_sql, [$hashed_password, $user_id]);
            
            $success = 'Password berhasil direset! Silakan masuk dengan password baru Anda.';
            $token_valid = false; // Hide form after success
        }
    }
}

include $path . 'components/header.php';
?>

<div class="auth-container">
    <div class="auth-box glass-panel">
        
        <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.5); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Logo" style="height: 50px; opacity: 0.9;">
        </div>

        <h2 style="color: var(--color-text); margin-bottom: 5px;">Reset Password</h2>
        <p style="font-size: 0.9rem; color: var(--color-text-light); margin-bottom: 30px;">Masukkan password baru Anda</p>

        <?php if ($error): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            ✕ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div style="background-color: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            ✓ <?php echo htmlspecialchars($success); ?>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="login.php" style="display: block; padding: 12px; background: var(--color-primary); color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                Masuk Sekarang
            </a>
        </div>
        <?php elseif ($token_valid): ?>
        <form id="resetPasswordForm" action="" method="POST">
            <div style="text-align: left; margin-bottom: 15px; padding: 12px; background: rgba(79, 195, 247, 0.1); border-radius: 8px; border-left: 4px solid #4fc3f7;">
                <span style="font-size: 0.85rem; color: var(--color-text);">Email: <strong><?php echo $user_email; ?></strong></span>
            </div>

            <div style="text-align: left; margin-bottom: 20px;">
                <label for="password" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--color-text);">Password Baru</label>
                <input type="password" id="password" name="password" class="glass-input" placeholder="Masukkan password baru..." style="width: 100%;" required>
                <small style="color: var(--color-text-light); display: block; margin-top: 4px;">Minimal 6 karakter</small>
            </div>

            <div style="text-align: left; margin-bottom: 25px;">
                <label for="password_confirm" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--color-text);">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="glass-input" placeholder="Konfirmasi password baru..." style="width: 100%;" required>
            </div>

            <button type="submit" name="reset_password" class="btn-primary" style="width: 100%; font-size: 1rem;">Reset Password</button>
        </form>

        <div style="margin-top: 20px; text-align: center;">
            <p style="color: var(--color-text-light); font-size: 0.9rem;">
                <a href="login.php" style="color: var(--color-accent); font-weight: 600; text-decoration: none;">Kembali ke Login</a>
            </p>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
