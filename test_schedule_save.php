<?php
/**
 * Test script to verify schedule saving works
 */

session_start();
$_SESSION['user_id'] = 1;  // Mock test user
$_SESSION['role'] = 'psychologist';

$path = './';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

// Get psychologist ID for user 1
$sql = "SELECT psychologist_id FROM psychologist_profiles WHERE user_id = 1";
$result = $db->getPrepare($sql, []);
$psychologist_id = $result['psychologist_id'] ?? null;

echo "=== SCHEDULE SAVE TEST ===\n";
echo "Psychologist ID: $psychologist_id\n\n";

// Get saved schedules
if ($psychologist_id) {
    $sql = "SELECT schedule_date_id, tanggal, jam_mulai, jam_selesai, is_available 
            FROM psychologist_schedule_dates 
            WHERE psychologist_id = ? AND is_available = 1
            ORDER BY tanggal DESC
            LIMIT 10";
    
    $result = $db->queryPrepare($sql, [$psychologist_id]);
    
    echo "Saved Schedules (Last 10):\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Group by date
    $byDate = [];
    if (is_array($result)) {
        foreach ($result as $schedule) {
            if (!isset($byDate[$schedule['tanggal']])) {
                $byDate[$schedule['tanggal']] = [];
            }
            $byDate[$schedule['tanggal']][] = $schedule['jam_mulai'] . '-' . $schedule['jam_selesai'];
        }
    }
    
    echo "Grouped by Date:\n";
    foreach ($byDate as $date => $times) {
        echo "  $date: " . implode(', ', $times) . "\n";
    }
}
?>
