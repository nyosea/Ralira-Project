<?php
/**
 * Filename: pages/auth/login.php
 * Description: Halaman Login Pengguna (Admin, Psikolog, Klien).
 * Support: Manual Login + Google Sign-In
 */

session_start();
$path = '../../';
$page_title = 'Masuk - Biro Psikologi Rali Ra';

// Include database helper dan Google config
require_once $path . 'includes/db.php';
require_once $path . 'includes/google-config.php';

// Initialize Google Client
$client = getGoogleClient();
$loginUrl = $client->createAuthUrl();

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Location: login.php');
    exit;
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($role === 'psychologist') {
        header("Location: ../psychologist/dashboard.php");
    } else {
        header("Location: ../client/dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

// Handle Manual Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_manual'])) {
    $email = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Email dan password harus diisi!';
    } else {
        $db = new Database();
        $db->connect();
        $sql = "SELECT user_id, name, email, password, role FROM users WHERE email = ?";
        $user = $db->getPrepare($sql, [$email]);

        if ($user && Database::verifyPassword($password, $user['password'])) {
            // Login berhasil - set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect berdasarkan role
            $role = $user['role'];
            if ($role === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($role === 'psychologist') {
                header("Location: ../psychologist/dashboard.php");
            } else {
                header("Location: ../client/dashboard.php");
            }
            exit;
        } else {
            $error = 'Email atau password salah!';
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

        <h2 style="color: var(--color-text); margin-bottom: 5px;">Masukan Akun Anda</h2>
        <p style="font-size: 0.9rem; color: var(--color-text-light); margin-bottom: 30px;">Selamat Datang Kembali Di Keluarga Rali Ra</p>

        <?php if ($error): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            ✕ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            ✕ 
            <?php 
                if ($_GET['error'] == 'google_failed') {
                    echo 'Login dengan Google gagal. Silakan coba lagi!';
                } elseif ($_GET['error'] == 'no_email') {
                    echo 'Email Google tidak ditemukan!';
                } else {
                    echo 'Terjadi kesalahan. Silakan coba lagi!';
                }
            ?>
        </div>
        <?php endif; ?>

        <form id="loginForm" action="" method="POST">
            
            <div style="text-align: left; margin-bottom: 20px;">
                <label for="username" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--color-text);">Username / Email</label>
                <input type="text" id="username" name="username" class="glass-input" placeholder="Masukkan email anda..." style="width: 100%;" required>
            </div>

            <div style="text-align: left; margin-bottom: 10px;">
                <label for="password" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--color-text);">Password</label>
                <input type="password" id="password" name="password" class="glass-input" placeholder="Masukkan password..." style="width: 100%;" required>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 25px;">
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                    <input type="checkbox" name="remember"> Ingat Saya
                </label>
                <a href="forgot-password.php" style="color: var(--color-accent); font-weight: 600;">Lupa Password?</a>
            </div>

            <button type="submit" name="login_manual" class="btn-primary" style="width: 100%; font-size: 1rem;">Masuk</button>

            <div style="margin: 20px 0; position: relative; text-align: center;">
                <span style="background: transparent; padding: 0 10px; color: var(--color-text-light); font-size: 0.9rem; position: relative; z-index: 1;">Atau</span>
                <hr style="position: absolute; top: 50%; width: 100%; border: 0; border-top: 1px solid rgba(0,0,0,0.1); z-index: 0;">
            </div>

            <!-- REAL Google Login Button -->
            <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="btn-google glass-btn" style="width: 100%; border-radius: 50px; display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(255,255,255,0.7); border: none; padding: 12px; text-decoration: none; color: inherit;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span style="font-weight: 500;">Masuk dengan Google</span>
            </a>

        </form>

        <div style="margin-top: 30px; font-size: 0.9rem;">
            Belum Punya Akun? 
            <a href="register.php" style="color: var(--color-accent); font-weight: 700; text-decoration: underline;">Daftar Sekarang</a>
        </div>

    </div>
</div>


</body>
</html>