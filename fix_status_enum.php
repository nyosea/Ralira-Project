<?php
/**
 * Fix Status Booking ENUM - Browser Migration Script
 * Access: http://localhost/ralira_project/fix_status_enum.php
 */

header('Content-Type: application/json');

require 'includes/db.php';

$response = [
    'success' => false,
    'message' => '',
    'before' => '',
    'after' => ''
];

try {
    $db = new Database();
    $db->connect();
    
    // Check before
    $check_before = $db->query('DESCRIBE consultation_bookings');
    foreach($check_before as $row) {
        if($row['Field'] == 'status_booking') {
            $response['before'] = $row['Type'];
        }
    }
    
    // Run migration
    $alter_sql = "ALTER TABLE consultation_bookings MODIFY status_booking ENUM('pending','confirmed','canceled','rejected') DEFAULT 'pending'";
    $db->query($alter_sql);
    
    // Check after
    $check_after = $db->query('DESCRIBE consultation_bookings');
    foreach($check_after as $row) {
        if($row['Field'] == 'status_booking') {
            $response['after'] = $row['Type'];
        }
    }
    
    $response['success'] = true;
    $response['message'] = '✅ ENUM column berhasil di-update! Sekarang bisa gunakan status "rejected"';
    
} catch (Exception $e) {
    $response['message'] = '❌ Error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
