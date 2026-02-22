<?php
/**
 * Test: Verify schedule save and delete
 */

session_start();
require_once 'includes/db.php';

$db = new Database();
$db->connect();

// Simulate admin setting schedules
echo "=== TEST SCHEDULE SAVE & DELETE ===\n\n";

// Use psychologist_id = 5 (valid from database)
$psychologist_id = 5;
$test_dates = ['2026-01-04', '2026-01-05', '2026-01-06'];
$test_times = ['09:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00'];

echo "1. INSERTING test schedules for dates: " . implode(', ', $test_dates) . "\n";
echo "   Times: " . implode(', ', $test_times) . "\n\n";

$count = 0;
foreach ($test_dates as $tanggal) {
    foreach ($test_times as $time) {
        $time_parts = explode('-', $time);
        $jam_mulai = $time_parts[0];
        $jam_selesai = $time_parts[1];
        
        // Check if already exists
        $check_sql = "SELECT schedule_date_id FROM psychologist_schedule_dates 
                      WHERE psychologist_id = ? AND tanggal = ? AND jam_mulai = ?";
        $check = $db->getPrepare($check_sql, [$psychologist_id, $tanggal, $jam_mulai]);
        
        if (!$check) {
            $insert_sql = "INSERT INTO psychologist_schedule_dates 
                          (psychologist_id, tanggal, jam_mulai, jam_selesai, is_available) 
                          VALUES (?, ?, ?, ?, 1)";
            if ($db->executePrepare($insert_sql, [$psychologist_id, $tanggal, $jam_mulai, $jam_selesai])) {
                $count++;
                echo "   ✓ Saved: $tanggal $time\n";
            }
        }
    }
}

echo "\nTotal inserted: $count records\n\n";

// Check database
echo "2. VERIFYING IN DATABASE:\n";
$verify_sql = "SELECT COUNT(*) as cnt FROM psychologist_schedule_dates 
               WHERE psychologist_id = ? AND tanggal IN (?, ?, ?) AND is_available = 1";
$verify = $db->getPrepare($verify_sql, [$psychologist_id, '2026-01-04', '2026-01-05', '2026-01-06']);
echo "   Current records in DB: " . $verify['cnt'] . " (expected: " . (count($test_dates) * count($test_times)) . ")\n\n";

// Simulate delete
echo "3. DELETING schedules for dates: 2026-01-04, 2026-01-05\n\n";
$dates_to_delete = ['2026-01-04', '2026-01-05'];
$deleted = 0;

foreach ($dates_to_delete as $tanggal) {
    // Check for bookings
    $check_booking_sql = "SELECT COUNT(*) as count FROM consultation_bookings 
                         WHERE psychologist_id = ? 
                         AND tanggal_konsultasi = ? 
                         AND status_booking != 'canceled'";
    $booking_result = $db->getPrepare($check_booking_sql, [$psychologist_id, $tanggal]);
    
    if ($booking_result['count'] > 0) {
        echo "   ✗ BLOCKED: $tanggal (has " . $booking_result['count'] . " active booking(s))\n";
    } else {
        $delete_sql = "UPDATE psychologist_schedule_dates 
                      SET is_available = 0 
                      WHERE psychologist_id = ? AND tanggal = ?";
        if ($db->executePrepare($delete_sql, [$psychologist_id, $tanggal])) {
            $deleted++;
            echo "   ✓ Deleted: $tanggal\n";
        }
    }
}

echo "\nTotal deleted: $deleted records\n\n";

// Final verification
echo "4. FINAL VERIFICATION:\n";
$final_verify = $db->getPrepare($verify_sql, [$psychologist_id, '2026-01-04', '2026-01-05', '2026-01-06']);
echo "   Remaining records in DB: " . $final_verify['cnt'] . " (expected: " . (count($test_times)) . ")\n";

// Show what's left
$detail_sql = "SELECT tanggal, jam_mulai, jam_selesai, is_available 
               FROM psychologist_schedule_dates 
               WHERE psychologist_id = ? AND tanggal IN (?, ?, ?)
               ORDER BY tanggal, jam_mulai";
$details = $db->queryPrepare($detail_sql, [$psychologist_id, '2026-01-04', '2026-01-05', '2026-01-06']);

if (is_array($details)) {
    echo "\n   Records (is_available=1 only):\n";
    foreach ($details as $d) {
        if ($d['is_available'] == 1) {
            echo "   - {$d['tanggal']} {$d['jam_mulai']}-{$d['jam_selesai']}\n";
        }
    }
}

echo "\n=== TEST COMPLETE ===\n";
?>
