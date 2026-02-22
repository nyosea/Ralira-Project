<?php
/**
 * Filename: pages/auth/register.php
 * Description: Halaman Pendaftaran Akun Baru (Hanya untuk Klien).
 */

session_start();
$path = '../../';
$page_title = 'Daftar Akun Baru - Rali Ra';

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../client/dashboard.php");
    exit;
}

$error = '';
$success = '';

// Handle Register
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // Validasi
    if (!$name || !$email || !$phone || !$password || !$password_confirm) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek email sudah ada
        $sql_check = "SELECT user_id FROM users WHERE email = ?";
        $existing = $db->getPrepare($sql_check, [$email]);

        if ($existing) {
            $error = 'Email sudah terdaftar! Gunakan email lain.';
        } else {
            // Hash password
            $password_hash = Database::hashPassword($password);

            // Insert user - try with login_method first, fallback if column doesn't exist
            $sql_user = "INSERT INTO users (name, email, phone, password, role, login_method) VALUES (?, ?, ?, ?, ?, 'manual')";
            $success = false;
            
            try {
                if ($db->executePrepare($sql_user, [$name, $email, $phone, $password_hash, 'client'])) {
                    $success = true;
                }
            } catch (Exception $e) {
                // If login_method column doesn't exist, try without it
                if (strpos($e->getMessage(), 'login_method') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                    $sql_user = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
                    if ($db->executePrepare($sql_user, [$name, $email, $phone, $password_hash, 'client'])) {
                        $success = true;
                    }
                } else {
                    throw $e;
                }
            }
            
            if ($success) {
                $user_id = $db->lastId();

                // Insert client details
                $sql_client = "INSERT INTO client_details (user_id, status_pendaftaran) VALUES (?, ?)";
                $db->executePrepare($sql_client, [$user_id, 'pending']);

                $success = 'Pendaftaran berhasil! Silakan login.';
                // Redirect setelah 2 detik
                echo '<script>setTimeout(() => { window.location.href = "login.php"; }, 2000);</script>';
            } else {
                $error = 'Gagal mendaftar. Silakan coba lagi!';
            }
        }
    }
}

include $path . 'components/header.php';
?>

<div class="auth-container">
    
    <div class="auth-box glass-panel" style="max-width: 450px;">
        <div style="width: 70px; height: 70px; background: rgba(255,255,255,0.5); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Logo" style="height: 40px; opacity: 0.9;">
        </div>

        <h2 style="color: var(--color-text); font-size: 1.5rem;">Bergabung dengan Keluarga Rali Ra</h2>
        <p style="font-size: 0.85rem; color: var(--color-text-light); margin-bottom: 25px;">Langkah sederhana untuk Memulai Perjalanan Penyembuhan</p>

        <?php if ($error): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            ✕ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div style="background-color: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            ✓ <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            
            <h4 style="text-align: left; color: var(--color-primary); margin-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 5px;">Informasi Akun</h4>

            <div style="text-align: left; margin-bottom: 15px;">
                <label for="name" style="display: block; font-weight: 500; margin-bottom: 5px; font-size: 0.9rem;">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="glass-input" placeholder="Contoh: Budi Santoso" style="width: 100%;" required>
            </div>

            <div style="text-align: left; margin-bottom: 15px;">
                <label for="email" style="display: block; font-weight: 500; margin-bottom: 5px; font-size: 0.9rem;">Email</label>
                <input type="email" id="email" name="email" class="glass-input" placeholder="nama@email.com" style="width: 100%;" required>
            </div>

            <div style="text-align: left; margin-bottom: 15px;">
                <label for="phone" style="display: block; font-weight: 500; margin-bottom: 5px; font-size: 0.9rem;">Nomor Telepon (WhatsApp)</label>
                <input type="tel" id="phone" name="phone" class="glass-input" placeholder="0812xxxxxx" style="width: 100%;" required>
                <small style="color: #666; font-size: 0.75rem;">Pastikan nomor aktif WhatsApp untuk notifikasi.</small>
            </div>

            <div style="text-align: left; margin-bottom: 15px;">
                <label for="password" style="display: block; font-weight: 500; margin-bottom: 5px; font-size: 0.9rem;">Password</label>
                <input type="password" id="password" name="password" class="glass-input" placeholder="Minimal 6 karakter" style="width: 100%;" minlength="6" required>
            </div>

            <div style="text-align: left; margin-bottom: 25px;">
                <label for="password_confirm" style="display: block; font-weight: 500; margin-bottom: 5px; font-size: 0.9rem;">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="glass-input" placeholder="Ulangi password..." style="width: 100%;" required>
                <div id="passwordError" style="color: #c62828; font-size: 0.8rem; display: none; margin-top: 5px;">Password tidak cocok!</div>
            </div>

            <button type="submit" name="register" class="btn-primary" style="width: 100%; font-size: 1rem; padding: 12px;">Daftar</button>

        </form>

        <div style="margin-top: 20px; font-size: 0.9rem;">
            Sudah punya akun? 
            <a href="login.php" style="color: var(--color-accent); font-weight: 700; text-decoration: underline;">Masuk di sini</a>
        </div>

    </div>
</div>


<script>
    // VALIDASI PASSWORD
    const pass = document.getElementById('password');
    const confirmPass = document.getElementById('password_confirm');
    const errorMsg = document.getElementById('passwordError');

    // Cek kesamaan password saat mengetik
    confirmPass.addEventListener('input', function() {
        if (this.value !== pass.value) {
            errorMsg.style.display = 'block';
            this.style.borderColor = '#c62828';
        } else {
            errorMsg.style.display = 'none';
            this.style.borderColor = '';
        }
    });
</script>

</body>
</html>