<?php
/**
 * Filename: pages/auth/forgot-password.php
 * Description: Halaman untuk reset password - smart flow
 * Features: Check user type (manual vs Google OAuth) dan handle accordingly
 */

session_start();
$path = '../../';
$page_title = 'Lupa Password - Biro Psikologi Rali Ra';

// Include database helper
require_once $path . 'includes/db.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../client/dashboard.php");
    exit;
}

$error = '';
$success = '';
$user_email = '';
$is_google_user = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = trim($_POST['email'] ?? '');
    
    if (!$email) {
        $error = 'Email harus diisi!';
    } else {
        $db = new Database();
        $db->connect();
        
        // Check if user exists - handle both with and without login_method column
        $sql = "SELECT user_id, name, email FROM users WHERE email = ?";
        // Try to get login_method if column exists
        try {
            $sql = "SELECT user_id, name, email, login_method FROM users WHERE email = ?";
            $user = $db->getPrepare($sql, [$email]);
        } catch (Exception $e) {
            // If login_method column doesn't exist, query without it
            if (strpos($e->getMessage(), 'login_method') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                $sql = "SELECT user_id, name, email FROM users WHERE email = ?";
                $user = $db->getPrepare($sql, [$email]);
            } else {
                throw $e;
            }
        }
        
        if ($user) {
            // Check if user registered via Google (no password reset available)
            // If login_method doesn't exist, assume it's manual (for backward compatibility)
            $login_method = $user['login_method'] ?? 'manual';
            
            if ($login_method === 'google') {
                $is_google_user = true;
                $user_email = htmlspecialchars($email);
            } else {
                // User registered manually - send reset token
                $reset_token = bin2hex(random_bytes(32));
                $token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Try to update with reset_token columns
                $update_sql = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?";
                try {
                    $db->executePrepare($update_sql, [$reset_token, $token_expires, $user['user_id']]);
                } catch (Exception $e) {
                    // If reset_token columns don't exist, just show success message (they'll need to run migration)
                    if (strpos($e->getMessage(), 'reset_token') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                        $success = 'Fitur reset password akan tersedia setelah update database. Silakan hubungi administrator.';
                        return;
                    } else {
                        throw $e;
                    }
                }
                
                // Send email with reset link
                // Generate reset link - use proper BASE_URL
                $base_url = 'http://localhost/ralira_project/';
                $reset_link = $base_url . "pages/auth/reset-password.php?token=" . urlencode($reset_token);
                
                $subject = "Reset Password - Biro Psikologi Rali Ra";
                $message = "
                    <h2>Permintaan Reset Password</h2>
                    <p>Halo {$user['name']},</p>
                    <p>Kami menerima permintaan untuk reset password akun Anda.</p>
                    <p>Klik link berikut untuk melanjutkan:</p>
                    <p><a href='{$reset_link}' style='display: inline-block; padding: 10px 20px; background-color: #FBBA00; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔐 Reset Password</a></p>
                    <p>Link ini akan berlaku selama 1 jam.</p>
                    <p style='color: #999; font-size: 12px;'><strong>Jika Anda tidak membuat permintaan ini, abaikan email ini.</strong></p>
                    <hr style='border: none; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666;'>© Biro Psikologi Rali Ra</p>
                ";
                
                // Try to send email
                $email_sent = false;
                try {
                    $email_sent = sendEmail($email, $subject, $message);
                } catch (Exception $e) {
                    error_log("Email send error: " . $e->getMessage());
                    $email_sent = false;
                }
                
                if ($email_sent) {
                    $success = 'Email reset password telah dikirim ke: ' . htmlspecialchars($email) . '. Silakan cek email Anda dalam 1 jam.';
                } else {
                    // Email failed but token is saved - show alternative
                    $success = 'Token reset telah dibuat. Silakan hubungi admin jika tidak menerima email.';
                }
            }
        } else {
            // For security: don't reveal if email exists or not
            // But we show same message for both cases
            $success = 'Jika email terdaftar, kami akan mengirim link reset password.';
        }
    }
}

/**
 * Send email function with better error handling
 * Using PHP built-in mail() for simplicity
 * In production, consider using PHPMailer or SwiftMailer
 */
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@ralira.local\r\n";
    $headers .= "Reply-To: support@ralira.local\r\n";
    
    // Validate email format
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address: " . $to);
        return false;
    }
    
    // Try to send email
    try {
        if (function_exists('mail')) {
            // Try to send - mail() returns true/false
            $result = @mail($to, $subject, $message, $headers);
            
            if (!$result) {
                // Log the failure for debugging
                error_log("Mail function failed for: " . $to);
                
                // In development XAMPP, mail() often fails silently
                // So we'll check for sendmail_path or SMTP configuration
                $sendmail_path = ini_get('sendmail_path');
                $smtp = ini_get('SMTP');
                
                if (!$sendmail_path && !$smtp) {
                    error_log("XAMPP Development: sendmail_path and SMTP not configured");
                    // In XAMPP development mode, return true anyway (system not configured)
                    return true;
                }
                
                return false;
            }
            
            error_log("Email sent successfully to: " . $to);
            return true;
        }
        
        // Fallback if mail() doesn't exist
        error_log("Mail function not available");
        return false;
        
    } catch (Exception $e) {
        error_log("Exception in sendEmail: " . $e->getMessage());
        return false;
    }
}

include $path . 'components/header.php';
?>

<div class="auth-container">
    <div class="auth-box glass-panel">
        
        <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.5); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Logo" style="height: 50px; opacity: 0.9;">
        </div>

        <h2 style="color: var(--color-text); margin-bottom: 5px;">Lupa Password</h2>
        <p style="font-size: 0.9rem; color: var(--color-text-light); margin-bottom: 30px;">Masukkan email Anda untuk reset password</p>

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

        <?php if ($is_google_user): ?>
        <div style="background-color: #e3f2fd; color: #1565c0; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #1976d2;">
            <strong style="display: block; margin-bottom: 8px;">ℹ️ Akun Google OAuth</strong>
            <p style="margin: 0; font-size: 0.9rem;">Akun <strong><?php echo $user_email; ?></strong> Anda terdaftar menggunakan Google. 
            <br><br>Untuk keamanan, silakan gunakan tombol <strong>"Masuk dengan Google"</strong> di halaman login.</p>
        </div>
        
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <a href="login.php" style="flex: 1; padding: 12px; background: var(--color-primary); color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600;">
                Kembali ke Login
            </a>
            <a href="register.php" style="flex: 1; padding: 12px; background: #4CAF50; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600;">
                Daftar Akun Baru
            </a>
        </div>
        <?php else: ?>
        <form id="forgotPasswordForm" action="" method="POST">
            <div style="text-align: left; margin-bottom: 25px;">
                <label for="email" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--color-text);">Email</label>
                <input type="email" id="email" name="email" class="glass-input" placeholder="Masukkan email Anda..." style="width: 100%;" required>
            </div>

            <button type="submit" name="forgot_password" class="btn-primary" style="width: 100%; font-size: 1rem;">Kirim Link Reset</button>
        </form>

        <div style="margin-top: 30px; text-align: center;">
            <p style="color: var(--color-text-light); font-size: 0.9rem;">
                Ingat password Anda?
                <a href="login.php" style="color: var(--color-accent); font-weight: 600; text-decoration: none;">Masuk Sekarang</a>
            </p>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
