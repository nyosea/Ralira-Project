<?php
/**
 * Test: Full delete flow like user clicking button
 */

session_start();
require_once 'includes/db.php';

$db = new Database();
$db->connect();

echo "=== TEST: FULL DELETE FLOW ===\n\n";

// Simulate: Psychologist 6 login (from session)
$psychologist_id = 6;

// Show before
echo "1. BEFORE DELETE:\n";
$before = $db->getPrepare(
    "SELECT COUNT(*) as active FROM psychologist_schedule_dates 
     WHERE psychologist_id = ? AND is_available = 1",
    [$psychologist_id]
);
echo "   Active schedules: {$before['active']}\n\n";

// Simulate: User clicks "Batalkan Jadwal" for dates 2026-01-06 and 2026-01-07
echo "2. USER CLICKS DELETE BUTTON:\n";
$dates_to_delete = ['2026-01-06', '2026-01-07'];
echo "   Selected dates: " . implode(', ', $dates_to_delete) . "\n\n";

// Simulate: Backend processes delete_multiple_schedules action
echo "3. BACKEND PROCESSES:\n";

$dates_with_bookings = [];
$deleted_count = 0;

foreach ($dates_to_delete as $tanggal) {
    // Check for bookings
    $check_booking_sql = "SELECT COUNT(*) as count FROM consultation_bookings 
                         WHERE psychologist_id = ? 
                         AND tanggal_konsultasi = ? 
                         AND status_booking != 'canceled'";
    $booking_result = $db->getPrepare($check_booking_sql, [$psychologist_id, $tanggal]);
    
    echo "   Checking $tanggal... Bookings: {$booking_result['count']}\n";
    
    if ($booking_result['count'] > 0) {
        $dates_with_bookings[] = $tanggal;
        echo "      ✗ BLOCKED (has booking)\n";
    } else {
        $delete_sql = "UPDATE psychologist_schedule_dates 
                      SET is_available = 0 
                      WHERE psychologist_id = ? AND tanggal = ?";
        if ($db->executePrepare($delete_sql, [$psychologist_id, $tanggal])) {
            $deleted_count++;
            echo "      ✓ DELETED\n";
        } else {
            echo "      ✗ ERROR\n";
        }
    }
}

echo "\nResult: $deleted_count dates deleted\n\n";

// Show after
echo "4. AFTER DELETE:\n";
$after = $db->getPrepare(
    "SELECT COUNT(*) as active FROM psychologist_schedule_dates 
     WHERE psychologist_id = ? AND is_available = 1",
    [$psychologist_id]
);
echo "   Active schedules: {$after['active']}\n";
echo "   Deleted: " . ($before['active'] - $after['active']) . " schedules\n\n";

// Show detailed after state
echo "5. DETAILED STATE (by date):\n";
$detail = $db->queryPrepare(
    "SELECT tanggal, COUNT(*) as total, SUM(is_available) as active 
     FROM psychologist_schedule_dates 
     WHERE psychologist_id = ?
     GROUP BY tanggal
     ORDER BY tanggal",
    [$psychologist_id]
);

foreach ($detail as $row) {
    $deleted = $row['total'] - $row['active'];
    echo "   {$row['tanggal']}: Active={$row['active']}, Deleted={$deleted}\n";
}

echo "\n6. VERIFY QUERY RESULT (what UI will show):\n";
$ui_result = $db->queryPrepare(
    "SELECT tanggal, jam_mulai, jam_selesai FROM psychologist_schedule_dates 
     WHERE psychologist_id = ? AND is_available = 1
     ORDER BY tanggal, jam_mulai LIMIT 5",
    [$psychologist_id]
);

if (count($ui_result) > 0) {
    echo "   Showing first 5 schedules:\n";
    foreach ($ui_result as $row) {
        echo "   - {$row['tanggal']} {$row['jam_mulai']}-{$row['jam_selesai']}\n";
    }
} else {
    echo "   (empty - all deleted!)\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
