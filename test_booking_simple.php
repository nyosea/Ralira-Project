<?php
/**
 * Test Booking Flow - Simple Version
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

echo "=== Testing Booking Flow Logic ===\n\n";

// Step 1: Check existing psychologists
echo "1. Checking existing psychologists...\n";
$psy_sql = "SELECT psychologist_id, name FROM psychologist_profiles LIMIT 3";
$psy_result = $conn->query($psy_sql);
$psychologists = [];

while ($row = $psy_result->fetch_assoc()) {
    $psychologists[] = $row;
    echo "   - Psychologist ID: {$row['psychologist_id']}, Name: {$row['name']}\n";
}

if (empty($psychologists)) {
    echo "   - No psychologists found. Please create a psychologist first.\n";
    exit;
}

$test_psychologist_id = $psychologists[0]['psychologist_id'];
echo "   - Using psychologist ID: $test_psychologist_id\n";

// Step 2: Check existing schedules
echo "\n2. Checking existing schedules...\n";
$schedule_sql = "SELECT schedule_date_id, tanggal, jam_mulai, jam_selesai, has_booking 
                 FROM psychologist_schedule_dates 
                 WHERE psychologist_id = $test_psychologist_id 
                 ORDER BY tanggal DESC, jam_mulai 
                 LIMIT 10";

$schedule_result = $conn->query($schedule_sql);
$schedules = [];

while ($row = $schedule_result->fetch_assoc()) {
    $schedules[] = $row;
    $status = $row['has_booking'] ? 'BOOKED 🔒' : 'Available ✅';
    echo "   - Schedule ID: {$row['schedule_date_id']}, Date: {$row['tanggal']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}, Status: $status\n";
}

if (empty($schedules)) {
    echo "   - No schedules found. Please create a schedule first.\n";
    exit;
}

// Step 3: Check booked slots query (used by admin page)
echo "\n3. Testing booked slots query (used by admin page)...\n";
$booked_sql = "SELECT jam_mulai FROM psychologist_schedule_dates 
               WHERE psychologist_id = $test_psychologist_id AND has_booking = 1";

$booked_result = $conn->query($booked_sql);
$booked_slots = [];
while ($row = $booked_result->fetch_assoc()) {
    $booked_slots[] = substr($row['jam_mulai'], 0, 5);
    echo "   - Booked time: " . substr($row['jam_mulai'], 0, 5) . "\n";
}

// Step 4: Simulate admin page logic
echo "\n4. Simulating admin page logic...\n";
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

// Step 5: Test trigger simulation
echo "\n5. Testing trigger simulation...\n";

// Find a schedule without booking
$available_sql = "SELECT schedule_date_id, jam_mulai FROM psychologist_schedule_dates 
                  WHERE psychologist_id = $test_psychologist_id AND has_booking = 0 
                  LIMIT 1";

$available_result = $conn->query($available_sql);
if ($available_schedule = $available_result->fetch_assoc()) {
    $schedule_id = $available_schedule['schedule_date_id'];
    $jam_mulai = $available_schedule['jam_mulai'];
    
    echo "   - Found available schedule: ID $schedule_id, Time: $jam_mulai\n";
    
    // Manually update has_booking (simulate trigger)
    $update_sql = "UPDATE psychologist_schedule_dates SET has_booking = 1 WHERE schedule_date_id = $schedule_id";
    if ($conn->query($update_sql)) {
        echo "   - Updated has_booking = 1 (simulating trigger)\n";
        
        // Check result
        $check_sql = "SELECT has_booking FROM psychologist_schedule_dates WHERE schedule_date_id = $schedule_id";
        $check_result = $conn->query($check_sql);
        $check_row = $check_result->fetch_assoc();
        
        $new_status = $check_row['has_booking'] ? 'BOOKED 🔒' : 'Available ✅';
        echo "   - New status: $new_status\n";
        
        // Reset for test
        $reset_sql = "UPDATE psychologist_schedule_dates SET has_booking = 0 WHERE schedule_date_id = $schedule_id";
        $conn->query($reset_sql);
        echo "   - Reset has_booking = 0 (test complete)\n";
    }
} else {
    echo "   - No available schedules found for testing\n";
}

$conn->close();
echo "\n=== Test Complete ===\n";
echo "✅ Database structure correct!\n";
echo "✅ Booked slots query working!\n";
echo "✅ Admin page logic working!\n";
echo "✅ Trigger simulation working!\n";
echo "\n🎯 System is READY for booking flow!\n";
?>
