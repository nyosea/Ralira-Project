<?php
require_once 'includes/db.php';

echo "<h2>Create Admin Account</h2>";

$db = new Database();
$db->connect();

// Data admin baru
$name = "Lookbehind Admin";
$email = "lookbehindme384@gmail.com";
$phone = "081234567890";
$password = "admin123";
$role = "admin";

// Hash password
$password_hash = Database::hashPassword($password);

// Cek apakah email sudah ada
$sql_check = "SELECT user_id FROM users WHERE email = ?";
$existing = $db->getPrepare($sql_check, [$email]);

if ($existing) {
    echo "❌ Email sudah terdaftar!<br>";
    echo "Email: " . $email . "<br>";
    echo "User ID: " . $existing['user_id'] . "<br>";
} else {
    // Insert user admin
    $sql_insert = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
    
    if ($db->executePrepare($sql_insert, [$name, $email, $phone, $password_hash, $role])) {
        $user_id = $db->lastId();
        echo "✓ Admin account created successfully!<br>";
        echo "User ID: " . $user_id . "<br>";
        echo "Name: " . $name . "<br>";
        echo "Email: " . $email . "<br>";
        echo "Role: " . $role . "<br>";
        echo "Password: " . $password . "<br>";
        echo "<hr>";
        echo "<p style='color: green; font-weight: bold;'>✓ Silakan login dengan email dan password di atas!</p>";
    } else {
        echo "❌ Failed to create admin account!<br>";
    }
}
?>
