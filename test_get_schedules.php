<?php
/**
 * Test: Get schedules for date
 */

session_start();
require_once 'includes/db.php';

$db = new Database();
$db->connect();

echo "=== TEST: GET_SCHEDULES_FOR_DATE ===\n\n";

$psychologist_id = 6;
$tanggal = '2026-01-08';

// Test admin page behavior
echo "1. SIMULATING ADMIN PAGE REQUEST:\n";
echo "   Psychologist ID: $psychologist_id\n";
echo "   Date: $tanggal\n\n";

// Simulate the POST data
$_POST['action'] = 'get_schedules_for_date';
$_POST['psychologist_id'] = $psychologist_id;
$_POST['tanggal'] = $tanggal;

// Execute same query as backend
$sql = "SELECT schedule_date_id, jam_mulai, jam_selesai FROM psychologist_schedule_dates 
        WHERE psychologist_id = ? AND tanggal = ? AND is_available = 1
        ORDER BY jam_mulai";

$result = $db->queryPrepare($sql, [$psychologist_id, $tanggal]);

echo "2. BACKEND QUERY RESULT:\n";
if (is_array($result)) {
    echo "   Status: SUCCESS\n";
    echo "   Count: " . count($result) . " records\n\n";
    
    echo "3. RECORDS:\n";
    foreach ($result as $row) {
        echo "   - ID: {$row['schedule_date_id']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}\n";
    }
} else {
    echo "   Status: FAILED\n";
    echo "   Result: " . var_export($result, true) . "\n";
}

echo "\n4. RESPONSE (JSON):\n";
$response = [
    'success' => is_array($result),
    'schedules' => is_array($result) ? $result : []
];
echo json_encode($response, JSON_PRETTY_PRINT) . "\n";

echo "\n=== TEST COMPLETE ===\n";
?>
