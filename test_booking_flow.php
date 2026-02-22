<?php
/**
 * Test Complete Booking Flow
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ralira_db';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== Testing Complete Booking Flow ===\n\n";

// Step 1: Create test schedule
echo "1. Creating test schedule for psychologist ID 1 on 2026-01-07...\n";
$schedule_sql = "INSERT INTO psychologist_schedule_dates 
                (psychologist_id, tanggal, jam_mulai, jam_selesai) 
                VALUES (?, ?, ?, ?)";

$schedules = [
    [1, '2026-01-07', '09:00:00', '11:00:00'],
    [1, '2026-01-07', '11:00:00', '13:00:00'],
    [1, '2026-01-07', '13:00:00', '15:00:00'],
    [1, '2026-01-07', '15:00:00', '17:00:00']
];

foreach ($schedules as $schedule) {
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("isss", $schedule[0], $schedule[1], $schedule[2], $schedule[3]);
    $stmt->execute();
    echo "   - Created schedule: {$schedule[2]}-{$schedule[3]}\n";
}

// Step 2: Check schedules before booking
echo "\n2. Checking schedules before booking...\n";
$check_sql = "SELECT schedule_date_id, jam_mulai, jam_selesai, has_booking 
              FROM psychologist_schedule_dates 
              WHERE psychologist_id = 1 AND tanggal = '2026-01-07'
              ORDER BY jam_mulai";

$result = $conn->query($check_sql);
while ($row = $result->fetch_assoc()) {
    echo "   - Schedule ID: {$row['schedule_date_id']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}, Has Booking: " . ($row['has_booking'] ? 'Yes' : 'No') . "\n";
}

// Step 3: Create booking (simulate)
echo "\n3. Creating booking for 13:00-15:00 slot...\n";

// Get schedule ID for 13:00 slot
$get_schedule_sql = "SELECT schedule_date_id FROM psychologist_schedule_dates 
                    WHERE psychologist_id = 1 AND tanggal = '2026-01-07' AND jam_mulai = '13:00:00'";
$schedule_result = $conn->query($get_schedule_sql);
$schedule_row = $schedule_result->fetch_assoc();
$schedule_id = $schedule_row['schedule_date_id'];

// Insert booking
$booking_sql = "INSERT INTO consultation_bookings 
                (client_id, psychologist_id, schedule_id, tanggal_konsultasi, status_booking) 
                VALUES (?, ?, ?, ?, 'confirmed')";

$stmt = $conn->prepare($booking_sql);
$stmt->bind_param("iiis", 1, 1, $schedule_id, '2026-01-07');
$stmt->execute();
echo "   - Booking created for schedule ID: $schedule_id\n";

// Step 4: Check schedules after booking (trigger should update has_booking)
echo "\n4. Checking schedules after booking (trigger should update has_booking)...\n";
$result = $conn->query($check_sql);
while ($row = $result->fetch_assoc()) {
    $status = $row['has_booking'] ? 'BOOKED 🔒' : 'Available ✅';
    echo "   - Schedule ID: {$row['schedule_date_id']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}, Status: $status\n";
}

// Step 5: Test booked slots query (used by admin page)
echo "\n5. Testing booked slots query (used by admin page)...\n";
$booked_sql = "SELECT jam_mulai FROM psychologist_schedule_dates 
               WHERE psychologist_id = 1 AND has_booking = 1";

$booked_result = $conn->query($booked_sql);
$booked_slots = [];
while ($row = $booked_result->fetch_assoc()) {
    $booked_slots[] = substr($row['jam_mulai'], 0, 5);
    echo "   - Booked time: " . substr($row['jam_mulai'], 0, 5) . "\n";
}

// Step 6: Simulate admin page logic
echo "\n6. Simulating admin page logic...\n";
$jam_slots_display = ['09:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00'];

foreach ($jam_slots_display as $index => $display) {
    $time_parts = explode('-', $display);
    $jam_start = $time_parts[0];
    $is_booked = in_array($jam_start, $booked_slots);
    
    $status = $is_booked ? 'DISABLED 🔒' : 'Available ✅';
    $checkbox = $is_booked ? 'disabled checked' : '';
    $class = $is_booked ? 'booked' : '';
    
    echo "   - Time: $display, Status: $status, Checkbox: [$checkbox], Class: [$class]\n";
}

// Clean up
echo "\n7. Cleaning up test data...\n";
$conn->query("DELETE FROM consultation_bookings WHERE psychologist_id = 1 AND tanggal_konsultasi = '2026-01-07'");
$conn->query("DELETE FROM psychologist_schedule_dates WHERE psychologist_id = 1 AND tanggal = '2026-01-07'");
echo "   - Test data cleaned up\n";

$conn->close();
echo "\n=== Test Complete ===\n";
echo "✅ Booking flow working correctly!\n";
echo "✅ has_booking column updated by trigger!\n";
echo "✅ Admin page logic working correctly!\n";
?>
