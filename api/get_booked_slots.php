<?php
/**
 * API Endpoint: Get Booked Slots for Specific Date
 * GET /api/get_booked_slots.php?psychologist_id=X&date=Y
 * 
 * Returns booked time slots for a psychologist on a specific date
 */

header('Content-Type: application/json; charset=utf-8');

session_start();
$path = '../';

require_once $path . 'includes/db.php';

// Initialize response array
$response = [
    'success' => false,
    'booked_slots' => [],
    'error' => null
];

try {
    $db = new Database();
    $db->connect();

    // Get parameters
    $psychologist_id = intval($_GET['psychologist_id'] ?? 0);
    $date = trim($_GET['date'] ?? '');

    // Validate
    if (!$psychologist_id || !$date) {
        http_response_code(400);
        $response['error'] = 'Missing psychologist_id or date';
        echo json_encode($response);
        exit;
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        $response['error'] = 'Invalid date format. Use YYYY-MM-DD';
        echo json_encode($response);
        exit;
    }

    // Get booked slots for specific date
    $sql = "SELECT jam_mulai FROM psychologist_schedule_dates 
             WHERE psychologist_id = ? AND tanggal = ? AND has_booking = 1
             ORDER BY jam_mulai";
    
    $result = $db->queryPrepare($sql, [$psychologist_id, $date]);
    
    $booked_slots = [];
    if (is_array($result)) {
        foreach ($result as $row) {
            $booked_slots[] = substr($row['jam_mulai'], 0, 5); // Format: HH:MM
        }
    }

    http_response_code(200);
    $response['success'] = true;
    $response['booked_slots'] = $booked_slots;
    $response['date'] = $date;
    $response['total_booked'] = count($booked_slots);
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
    echo json_encode($response);
}
?>
