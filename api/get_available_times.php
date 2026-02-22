<?php
/**
 * API Endpoint: Get Available Times for Booking
 * GET /api/get_available_times.php?psychologist_id=X&date=Y
 * 
 * Returns available time slots for a psychologist on a specific date
 * based on their working days schedule and existing bookings.
 */

// Set header BEFORE any output
header('Content-Type: application/json; charset=utf-8');

session_start();
$path = '../';

require_once $path . 'includes/db.php';

// Initialize response array
$response = [
    'success' => false,
    'available_times' => [],
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

    // Validate date is not in past
    $timestamp = strtotime($date);
    if ($timestamp === false || $timestamp < strtotime('today')) {
        http_response_code(400);
        $response['error'] = 'Date must be today or later';
        echo json_encode($response);
        exit;
    }

    // Get available times from psychologist_schedule_dates table
    $sql_available = "SELECT DISTINCT jam_mulai, jam_selesai 
                     FROM psychologist_schedule_dates 
                     WHERE psychologist_id = ? AND tanggal = ? AND COALESCE(has_booking, 0) = 0
                     ORDER BY jam_mulai ASC";
    
    $available_results = $db->queryPrepare($sql_available, [$psychologist_id, $date]);

    if (!is_array($available_results) || count($available_results) == 0) {
        // Tidak ada jadwal yang tersedia pada tanggal ini
        http_response_code(200);
        $response['success'] = true;
        $response['day_working'] = false;
        echo json_encode($response);
        exit;
    }

    // Build list of available times
    // Generate available times (has_booking = 0 already excludes booked ones)
    $available_times = [];
    foreach ($available_results as $slot) {
        $jam_mulai = $slot['jam_mulai'];
        
        // Create display format (e.g., "09:00-11:00")
        $jam_mulai_display = substr($jam_mulai, 0, 5);
        $jam_selesai_display = substr($slot['jam_selesai'], 0, 5);
        $display = $jam_mulai_display . '-' . $jam_selesai_display;
        
        // Ensure time is in correct format HH:MM:00
        $jam_mulai_formatted = substr($jam_mulai, 0, 8); // Take first 8 chars: HH:MM:SS
        
        $available_times[] = [
            'time' => $jam_mulai_formatted,
            'display' => $display
        ];
    }

    http_response_code(200);
    $response['success'] = true;
    $response['available_times'] = $available_times;
    $response['date'] = $date;
    $response['total_slots'] = count($available_times);
    $response['booked_count'] = 0; // Not needed anymore, has_booking handles this
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
    echo json_encode($response);
}
?>

