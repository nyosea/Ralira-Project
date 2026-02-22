<?php
session_start();

$path = './';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

// Simulate API call dengan parameter yang tepat
$psychologist_id = 6;
$date = '2025-12-29';

echo "<h1>Test API: get_available_times.php</h1>";
echo "<h2>Parameters:</h2>";
echo "Psychologist ID: " . $psychologist_id . "<br>";
echo "Date: " . $date . "<br><br>";

// 1. Cek jadwal di tabel
echo "<h2>1. Schedules in psychologist_schedule_dates:</h2>";
$sql = "SELECT * FROM psychologist_schedule_dates 
        WHERE psychologist_id = ? AND tanggal = ? AND is_available = 1";
$schedules = $db->queryPrepare($sql, [$psychologist_id, $date]);

if (is_array($schedules) && count($schedules) > 0) {
    echo "<pre>";
    echo json_encode($schedules, JSON_PRETTY_PRINT);
    echo "</pre>";
} else {
    echo "No schedules found<br>";
}

// 2. Cek booking yang sudah ada
echo "<h2>2. Existing Bookings:</h2>";
$sql_bookings = "SELECT * FROM consultation_bookings 
                 WHERE psychologist_id = ? AND tanggal_konsultasi = ? 
                 AND status_booking IN ('pending', 'confirmed')";
$bookings = $db->queryPrepare($sql_bookings, [$psychologist_id, $date]);

if (is_array($bookings) && count($bookings) > 0) {
    echo "Found " . count($bookings) . " bookings:<br>";
    echo "<pre>";
    echo json_encode($bookings, JSON_PRETTY_PRINT);
    echo "</pre>";
} else {
    echo "No bookings found<br>";
}

// 3. Simulasi API response
echo "<h2>3. Simulated API Response:</h2>";

$response = [
    'success' => false,
    'available_times' => [],
    'error' => null
];

if (is_array($schedules) && count($schedules) > 0) {
    $booked_times = [];
    if (is_array($bookings)) {
        foreach ($bookings as $booking) {
            $booked_times[] = $booking['jam_konsultasi'];
        }
    }
    
    foreach ($schedules as $slot) {
        $jam_mulai = $slot['jam_mulai'];
        
        if (!in_array($jam_mulai, $booked_times)) {
            $jam_mulai_display = substr($jam_mulai, 0, 5);
            $jam_selesai_display = substr($slot['jam_selesai'], 0, 5);
            $display = $jam_mulai_display . '-' . $jam_selesai_display;
            
            $response['available_times'][] = [
                'time' => $jam_mulai,
                'display' => $display
            ];
        }
    }
}

$response['success'] = count($response['available_times']) > 0;
$response['date'] = $date;
$response['total_slots'] = count($response['available_times']);
$response['booked_count'] = is_array($bookings) ? count($bookings) : 0;
$response['day_working'] = count($response['available_times']) == 0;

echo "<pre>";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "</pre>";

echo "<h2>4. Test Real API Endpoint:</h2>";
echo "URL: api/get_available_times.php?psychologist_id=" . $psychologist_id . "&date=" . $date . "<br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/ralira_project/api/get_available_times.php?psychologist_id=" . $psychologist_id . "&date=" . $date);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response_text = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
echo "Response:<br>";
echo "<pre>";
echo htmlspecialchars($response_text);
echo "</pre>";

if ($error) {
    echo "cURL Error: " . $error;
}

?>
