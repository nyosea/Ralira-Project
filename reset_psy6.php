<?php
/**
 * Reset psychologist 6 schedule for testing
 */

require_once 'includes/db.php';

$db = new Database();
$db->connect();

echo "=== RESETTING PSYCHOLOGIST 6 SCHEDULE ===\n\n";

// Delete all soft-deleted records for psychologist 6
$delete_sql = "DELETE FROM psychologist_schedule_dates WHERE psychologist_id = 6";
$result = $db->executePrepare($delete_sql, []);
echo "Deleted all old records: " . ($result ? 'YES' : 'NO') . "\n\n";

// Insert fresh test data - January 6-10, full times
$psychologist_id = 6;
$dates = ['2026-01-06', '2026-01-07', '2026-01-08', '2026-01-09', '2026-01-10'];
$times = [
    ['09:00', '11:00'],
    ['11:00', '13:00'],
    ['13:00', '15:00'],
    ['15:00', '17:00']
];

$count = 0;
foreach ($dates as $date) {
    foreach ($times as $time) {
        $insert_sql = "INSERT INTO psychologist_schedule_dates 
                      (psychologist_id, tanggal, jam_mulai, jam_selesai, is_available) 
                      VALUES (?, ?, ?, ?, 1)";
        if ($db->executePrepare($insert_sql, [$psychologist_id, $date, $time[0], $time[1]])) {
            $count++;
            echo "Inserted: $date {$time[0]}-{$time[1]}\n";
        }
    }
}

echo "\nTotal inserted: $count records\n";

// Verify
$verify = $db->getPrepare(
    "SELECT COUNT(*) as cnt FROM psychologist_schedule_dates WHERE psychologist_id = 6 AND is_available = 1",
    []
);
echo "Final count: {$verify['cnt']} (expected: 20)\n\n";

echo "=== RESET COMPLETE ===\n";
?>
