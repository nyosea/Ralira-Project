<?php
/**
 * DEBUG VERSION - google-callback.php
 */

session_start();
$path = '../../';

require_once $path . 'includes/db.php';
require_once $path . 'includes/google-config.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Debug Google Callback</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #f5f5f5; }
.success { color: green; }
.error { color: red; }
.info { color: blue; }
h2 { border-bottom: 2px solid #333; padding-bottom: 10px; }
pre { background: white; padding: 10px; border-radius: 5px; }
</style></head><body>";

echo "<h1>🔍 Debug Google OAuth Callback</h1>";

// Check if code exists
if (!isset($_GET['code'])) {
    echo "<p class='error'>❌ ERROR: No authorization code received from Google</p>";
    echo "<a href='login.php'>← Back to Login</a>";
    exit;
}

echo "<h2>Step 1: Authorization Code ✅</h2>";
echo "<p class='success'>Authorization code received from Google</p>";

// Initialize Google Client
try {
    $client = getGoogleClient();
    echo "<h2>Step 2: Google Client Initialized ✅</h2>";
} catch (Exception $e) {
    echo "<h2>Step 2: Google Client Failed ❌</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    exit;
}

// Exchange code for token
try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        echo "<h2>Step 3: Token Exchange Failed ❌</h2>";
        echo "<pre>" . print_r($token, true) . "</pre>";
        exit;
    }
    
    echo "<h2>Step 3: Token Exchange ✅</h2>";
    $client->setAccessToken($token['access_token']);
    
} catch (Exception $e) {
    echo "<h2>Step 3: Token Exchange Error ❌</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    exit;
}

// Get user info
try {
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $google_id = $google_account_info->id;
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $picture = $google_account_info->picture;
    
    echo "<h2>Step 4: User Info from Google ✅</h2>";
    echo "<pre>";
    echo "Google ID: " . $google_id . "\n";
    echo "Email: " . $email . "\n";
    echo "Name: " . $name . "\n";
    echo "Picture: " . $picture . "\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Step 4: Get User Info Failed ❌</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    exit;
}

// Connect to database
try {
    $db = new Database();
    $db->connect();
    echo "<h2>Step 5: Database Connection ✅</h2>";
} catch (Exception $e) {
    echo "<h2>Step 5: Database Connection Failed ❌</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    exit;
}

// Check if user exists
try {
    $sql = "SELECT user_id, name, email, role, google_id FROM users WHERE email = ? OR google_id = ?";
    $user = $db->getPrepare($sql, [$email, $google_id]);
    
    echo "<h2>Step 6: Check User in Database</h2>";
    
    if ($user) {
        echo "<p class='success'>✅ User EXISTS in database</p>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        $user_id = $user['user_id'];
        $role = $user['role'];
        
        echo "<p class='info'>Current role: <strong>" . $role . "</strong></p>";
        
    } else {
        echo "<p class='info'>ℹ️ User NOT FOUND - Will create new account</p>";
        
        // Determine role
        $admin_emails = ['admin@ralira.com', 'thomasselaluberuntung@gmail.com', 'damarioimmanuel@gmail.com', 'lookbehindme384@gmail.com'];
        $psychologist_emails = ['lookbehindme384@gmail.com', 'ira@ralira.com'];
        
        if (in_array(strtolower($email), array_map('strtolower', $admin_emails))) {
            $role = 'admin';
        } elseif (in_array(strtolower($email), array_map('strtolower', $psychologist_emails))) {
            $role = 'psychologist';
        } else {
            $role = 'client';
        }
        
        echo "<p class='info'>Assigned role: <strong>" . $role . "</strong></p>";
        
        // Create new user
        $password_hash = Database::hashPassword('google_' . time() . '_' . $google_id);
        $sql_insert = "INSERT INTO users (name, email, password, role, google_id, profile_picture, created_at, updated_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        if ($db->executePrepare($sql_insert, [$name, $email, $password_hash, $role, $google_id, $picture])) {
            $user_id = $db->lastId();
            echo "<p class='success'>✅ User created with ID: " . $user_id . "</p>";
            
            // Create details
            if ($role === 'client') {
                $sql_client = "INSERT INTO client_details (user_id, status_pendaftaran) VALUES (?, 'pending')";
                $db->executePrepare($sql_client, [$user_id]);
                echo "<p class='success'>✅ Client details created</p>";
            }
        } else {
            echo "<p class='error'>❌ Failed to create user</p>";
            exit;
        }
    }
    
} catch (Exception $e) {
    echo "<h2>Step 6: Database Query Failed ❌</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    exit;
}

// Set session
$_SESSION['user_id'] = $user_id;
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['role'] = $role;
$_SESSION['profile_picture'] = $picture;
$_SESSION['google_id'] = $google_id;

echo "<h2>Step 7: Session Created ✅</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Determine redirect path
echo "<h2>Step 8: Determine Redirect Path</h2>";

$redirect_paths = [
    'admin' => '../admin/dashboard.php',
    'psychologist' => '../psychologist/dashboard.php',
    'client' => '../client/dashboard.php'
];

if (isset($redirect_paths[$role])) {
    $redirect_path = $redirect_paths[$role];
    echo "<p class='info'>Redirect path: <strong>" . $redirect_path . "</strong></p>";
    
    // Check if file exists
    $full_path = __DIR__ . '/' . $redirect_path;
    echo "<p class='info'>Full path: <strong>" . $full_path . "</strong></p>";
    
    if (file_exists($full_path)) {
        echo "<p class='success'>✅ Dashboard file EXISTS</p>";
        
        // Show redirect button
        echo "<h2>Step 9: Ready to Redirect ✅</h2>";
        echo "<p>Klik tombol di bawah untuk redirect ke dashboard:</p>";
        echo "<a href='" . $redirect_path . "' style='display: inline-block; padding: 10px 20px; background: #4285f4; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                → Go to Dashboard
              </a>";
        
        echo "<hr>";
        echo "<p style='color: gray; font-size: 12px;'>Atau tunggu 5 detik untuk auto redirect...</p>";
        echo "<script>setTimeout(function(){ window.location.href = '" . $redirect_path . "'; }, 5000);</script>";
        
    } else {
        echo "<p class='error'>❌ Dashboard file NOT FOUND!</p>";
        echo "<p class='error'>Expected location: " . $full_path . "</p>";
        echo "<p class='info'>Please create this file first!</p>";
    }
} else {
    echo "<p class='error'>❌ Unknown role: " . $role . "</p>";
}

echo "</body></html>";
?>