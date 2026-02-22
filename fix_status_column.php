<?php
require 'includes/db.php';
$db = new Database();
$db->connect();

// Update ENUM
$alter_sql = "ALTER TABLE consultation_bookings MODIFY status_booking ENUM('pending','confirmed','canceled','rejected') DEFAULT 'pending'";
try {
    $db->query($alter_sql);
    echo "✅ ENUM column updated successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Verify
$result = $db->query('DESCRIBE consultation_bookings');
foreach($result as $row) {
    if($row['Field'] == 'status_booking') {
        echo "\nColumn Type: " . $row['Type'] . "\n";
    }
}
?>
