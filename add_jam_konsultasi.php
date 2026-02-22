<?php
/**
 * Add jam_konsultasi column to consultation_bookings
 * Access: http://localhost/ralira_project/add_jam_konsultasi.php
 */

header('Content-Type: application/json');

require 'includes/db.php';

$response = [
    'success' => false,
    'message' => '',
    'before' => [],
    'after' => []
];

try {
    $db = new Database();
    $db->connect();
    
    // Check before
    $check_before = $db->query('DESCRIBE consultation_bookings');
    foreach($check_before as $col) {
        if(in_array($col['Field'], ['booking_id', 'tanggal_konsultasi', 'jam_konsultasi', 'status_booking'])) {
            $response['before'][] = $col['Field'];
        }
    }
    
    // Check if column already exists
    $column_exists = false;
    foreach($check_before as $col) {
        if($col['Field'] == 'jam_konsultasi') {
            $column_exists = true;
            break;
        }
    }
    
    if($column_exists) {
        $response['success'] = true;
        $response['message'] = '✅ Column jam_konsultasi sudah ada!';
    } else {
        // Add column
        $alter_sql = "ALTER TABLE consultation_bookings ADD COLUMN jam_konsultasi TIME AFTER tanggal_konsultasi";
        $db->query($alter_sql);
        
        // Check after
        $check_after = $db->query('DESCRIBE consultation_bookings');
        foreach($check_after as $col) {
            if(in_array($col['Field'], ['booking_id', 'tanggal_konsultasi', 'jam_konsultasi', 'status_booking'])) {
                $response['after'][] = $col['Field'];
            }
        }
        
        $response['success'] = true;
        $response['message'] = '✅ Column jam_konsultasi berhasil ditambahkan!';
    }
    
} catch (Exception $e) {
    $response['message'] = '❌ Error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
