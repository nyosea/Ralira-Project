<?php
/**
 * Google OAuth Callback Handler
 * File: pages/auth/google-callback.php
 * Fungsi: Handle callback dari Google OAuth, create/login user sebagai CLIENT
 */

session_start();
$path = '../../';
require_once $path . 'includes/db.php';
require_once $path . 'includes/google-config.php';

$error = '';
$success = '';

/**
 * Exchange authorization code untuk token via direct cURL
 */
function exchangeCodeForToken($code) {
    $url = 'https://oauth2.googleapis.com/token';
    
    $data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    // Debug info
    if ($http_code !== 200) {
        $result['_debug_http_code'] = $http_code;
        $result['_debug_response'] = $response;
    }
    
    return $result;
}

/**
 * Get user info dari Google API
 */
function getUserInfoFromGoogle($access_token) {
    $url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($access_token);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

try {
    // Step 1: Cek authorization code dari Google
    if (!isset($_GET['code'])) {
        $error = 'No authorization code received from Google';
        throw new Exception($error);
    }

    // Step 3: Exchange authorization code untuk access token (via direct cURL)
    $token = exchangeCodeForToken($_GET['code']);

    if (isset($token['error'])) {
        $error = 'Failed to get access token: ' . (isset($token['error_description']) ? $token['error_description'] : $token['error']);
        if (isset($token['_debug_response'])) {
            $error .= ' | Response: ' . substr($token['_debug_response'], 0, 200);
        }
        throw new Exception($error);
    }

    if (!isset($token['access_token'])) {
        $error = 'No access token in response';
        throw new Exception($error);
    }

    $access_token = $token['access_token'];

    // Step 4: Get user info dari Google (via direct cURL)
    $user_info = getUserInfoFromGoogle($access_token);

    if (!$user_info || !isset($user_info['email'])) {
        $error = 'Failed to get user info from Google';
        throw new Exception($error);
    }

    $google_id = $user_info['id'] ?? null;
    $email = $user_info['email'];
    $name = $user_info['name'] ?? 'Unknown User';
    $picture = $user_info['picture'] ?? null;

    // Step 5: Koneksi ke database
    $db = new Database();
    $db->connect();

    // Step 6: Cek user di database
    $sql_check = "SELECT user_id, name, email, role FROM users WHERE email = ?";
    $user = $db->getPrepare($sql_check, [$email]);

    if ($user) {
        // User sudah terdaftar
        $role = $user['role'];

        // ⚠️ ENFORCE: Google login HANYA untuk CLIENT
        if ($role !== 'client') {
            $error = 'Akun ini (' . $role . ') tidak bisa login pakai Google. Silakan login manual.';
            throw new Exception($error);
        }

        $user_id = $user['user_id'];
        $success = 'Login berhasil!';
    } else {
        // User baru - AUTO CREATE sebagai CLIENT
        if (!$google_id) {
            $google_id = null;
        }
        
        $password_hash = Database::hashPassword('google_' . time() . '_' . ($google_id ?? 'unknown'));
        $role = 'client';
        
        // Debug sebelum insert
        error_log("DEBUG: Inserting user - email=$email, role=$role, google_id=$google_id");

        // Try with login_method first, fallback if column doesn't exist
        $sql_insert = "INSERT INTO users (name, email, password, role, google_id, profile_picture, login_method, created_at, updated_at) 
                       VALUES (?, ?, ?, ?, ?, ?, 'google', NOW(), NOW())";

        try {
            if (!$db->executePrepare($sql_insert, [$name, $email, $password_hash, $role, $google_id, $picture])) {
                throw new Exception('Failed to execute INSERT with login_method');
            }
        } catch (Exception $e) {
            // If login_method column doesn't exist, try without it
            if (strpos($e->getMessage(), 'login_method') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                $sql_insert = "INSERT INTO users (name, email, password, role, google_id, profile_picture, created_at, updated_at) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
                if (!$db->executePrepare($sql_insert, [$name, $email, $password_hash, $role, $google_id, $picture])) {
                    $conn = $db->getConnection();
                    $error = 'Failed to create user account: ' . $conn->error;
                    throw new Exception($error);
                }
            } else {
                $conn = $db->getConnection();
                $error = 'Failed to create user account: ' . $conn->error;
                throw new Exception($error);
            }
        }

        $user_id = $db->lastId();

        // Create client details
        $sql_client = "INSERT INTO client_details (user_id, status_pendaftaran) VALUES (?, 'pending')";
        $db->executePrepare($sql_client, [$user_id]);

        $success = 'Akun baru dibuat! Silakan lengkapi biodata.';
    }

    // Step 7: Set Session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'client';
    $_SESSION['google_id'] = $google_id;
    $_SESSION['profile_picture'] = $picture;

    // Step 8: Redirect ke dashboard client
    header('Location: ../client/dashboard.php');
    exit;

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Jika ada error, tampilkan
if ($error) {
    include $path . 'components/header.php';
    ?>
    <div class="auth-container" style="display: flex; align-items: center; justify-content: center; min-height: 70vh;">
        <div class="auth-box glass-panel" style="max-width: 500px; text-align: center;">
            <h2 style="color: #c62828; margin-bottom: 20px;">❌ Google Login Gagal</h2>
            <div style="background-color: #ffebee; color: #c62828; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <p style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            </div>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="login.php" style="display: inline-block; padding: 12px 30px; background: #2196F3; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    ← Kembali ke Login
                </a>
                <a href="register.php" style="display: inline-block; padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Daftar Manual →
                </a>
            </div>
        </div>
    </div>
    <?php
    include $path . 'components/footer.php';
}
?>
