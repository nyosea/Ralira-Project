<?php
/**
 * Database Migration Runner
 * Run this file to add password reset columns to users table
 * Access: http://localhost/ralira_project/run_migration.php
 */

require_once 'config.php';
require_once 'includes/db.php';

$db = new Database();
$db->connect();

echo "<h1>Running Database Migrations...</h1>";
echo "<hr>";

// Migration 1: Add reset_token and reset_token_expires columns
try {
    echo "<h3>Migration 1: Add password reset columns</h3>";
    
    // Check if column already exists
    $check_sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_token'";
    $result = $db->query($check_sql);
    
    if (empty($result)) {
        echo "<p>Adding reset_token column...</p>";
        $db->query("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL");
        echo "✓ reset_token column added<br>";
        
        echo "<p>Adding reset_token_expires column...</p>";
        $db->query("ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL DEFAULT NULL");
        echo "✓ reset_token_expires column added<br>";
    } else {
        echo "⚠️ reset_token column already exists. Skipping.<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Migration 2: Add login_method column
try {
    echo "<h3>Migration 2: Add login_method column</h3>";
    
    $check_sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'login_method'";
    $result = $db->query($check_sql);
    
    if (empty($result)) {
        echo "<p>Adding login_method column...</p>";
        $db->query("ALTER TABLE users ADD COLUMN login_method ENUM('manual', 'google') DEFAULT 'manual' AFTER password");
        echo "✓ login_method column added<br>";
    } else {
        echo "⚠️ login_method column already exists. Skipping.<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Migration 3: Add indexes
try {
    echo "<h3>Migration 3: Add indexes for performance</h3>";
    
    $check_sql = "SELECT * FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'idx_reset_token'";
    $result = $db->query($check_sql);
    
    if (empty($result)) {
        echo "<p>Adding idx_reset_token index...</p>";
        $db->query("CREATE INDEX idx_reset_token ON users(reset_token)");
        echo "✓ idx_reset_token index added<br>";
        
        echo "<p>Adding idx_reset_token_expires index...</p>";
        $db->query("CREATE INDEX idx_reset_token_expires ON users(reset_token_expires)");
        echo "✓ idx_reset_token_expires index added<br>";
    } else {
        echo "⚠️ Indexes already exist. Skipping.<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>✓ All migrations completed!</h2>";
echo "<p><a href='pages/auth/login.php'>Go to Login</a></p>";
?>
