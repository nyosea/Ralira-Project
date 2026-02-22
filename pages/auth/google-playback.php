<?php
/**
 * Filename: pages/auth/google-callback.php
 * Description: Handle Google OAuth Callback
 */

session_start();
$path = '../../';

require_once $path . 'includes/db.php';
require_once $path . 'includes/google-config.php';

// Whitelist email untuk admin & psychologist
$admin_emails = [
    'admin@ralira.com',
    'thomasselaluberuntung@gmail.com',
    'damarioimmanuel@gmail.com',  // Tambahin email lu di sini kalau mau jadi admin
];

$psychologist_emails = [
    'psychologist@ralira.com',
    'lookbehindme384@gmail.com',
    'ira@ralira.com',
];

// Initialize Google Client
$client = getGoogleClient();

// Check if we have code from Google
if (!isset($_GET['code'])) {
    header('Location: login.php?error=no_code');
    exit;
}

try {
    // Exchange authorization code for access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        throw new Exception('Error fetching access token');
    }
    
    $client->setAccessToken($token['access_token']);
    
    // Get user info from Google
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $google_id = $google_account_info->id;
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $picture = $google_account_info->picture;
    
    if (!$email) {
        header('Location: login.php?error=no_email');
        exit;
    }
    
    // Connect to database
    $db = new Database();
    $db->connect();
    
    // Check if user exists
    $sql = "SELECT user_id, name, email, role, google_id FROM users WHERE email = ? OR google_id = ?";
    $user = $db->getPrepare($sql, [$email, $google_id]);
    
    if ($user) {
        // User exists - LOGIN
        $user_id = $user['user_id'];
        $role = $user['role'];
        
        // Normalize role to English (support legacy Indonesian roles)
        if ($role === 'klien') {
            $role = 'client';
            // Update database
            $sql_update = "UPDATE users SET role = 'client' WHERE user_id = ?";
            $db->executePrepare($sql_update, [$user_id]);
        } elseif ($role === 'psikolog') {
            $role = 'psychologist';
            // Update database
            $sql_update = "UPDATE users SET role = 'psychologist' WHERE user_id = ?";
            $db->executePrepare($sql_update, [$user_id]);
        }
        
        // Update google_id and profile picture if not set
        if (empty($user['google_id'])) {
            $sql_update = "UPDATE users SET google_id = ?, profile_picture = ? WHERE user_id = ?";
            $db->executePrepare($sql_update, [$google_id, $picture, $user_id]);
        }
        
    } else {
        // New user - REGISTER
        // Determine role based on email whitelist (use English roles)
        if (in_array(strtolower($email), array_map('strtolower', $admin_emails))) {
            $role = 'admin';
        } elseif (in_array(strtolower($email), array_map('strtolower', $psychologist_emails))) {
            $role = 'psychologist';  // English role
        } else {
            $role = 'client';  // English role - default for new users
        }
        
        $password_hash = Database::hashPassword('google_' . time() . '_' . $google_id);
        
        $sql_insert = "INSERT INTO users (name, email, password, role, google_id, profile_picture, created_at, updated_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        if ($db->executePrepare($sql_insert, [$name, $email, $password_hash, $role, $google_id, $picture])) {
            $user_id = $db->lastId();
            
            // Create additional details based on role
            if ($role === 'client') {
                $sql_client = "INSERT INTO client_details (user_id, status_pendaftaran) VALUES (?, 'pending')";
                $db->executePrepare($sql_client, [$user_id]);
            } elseif ($role === 'psychologist') {
                $sql_psychologist = "INSERT INTO psychologist_details (user_id) VALUES (?)";
                $db->executePrepare($sql_psychologist, [$user_id]);
            }
        } else {
            header('Location: login.php?error=register_failed');
            exit;
        }
    }
    
    // Set session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['profile_picture'] = $picture;
    $_SESSION['google_id'] = $google_id;
    
    // Redirect based on role (English roles)
    if ($role === 'admin') {
        header('Location: ../admin/dashboard.php');
    } elseif ($role === 'psychologist') {
        header('Location: ../psychologist/dashboard.php');
    } elseif ($role === 'client') {
        header('Location: ../client/dashboard.php');
    } else {
        // Fallback for unknown roles
        die("Error: Role tidak dikenali - " . htmlspecialchars($role) . ". Silakan hubungi administrator.");
    }
    exit;
    
} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    header('Location: login.php?error=google_failed');
    exit;
}
?>