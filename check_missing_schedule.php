<?php
$conn = new mysqli('localhost', 'root', '', 'ralira_db');

echo "=== Check Missing 13:00 Schedule ===\n";

// Check if 13:00 schedule exists for Claudia on 2026-01-07
echo "1. Looking for 13:00 schedule:\n";
$result = $conn->query('SELECT schedule_date_id, jam_mulai, jam_selesai FROM psychologist_schedule_dates WHERE psychologist_id = 6 AND tanggal = "2026-01-07" AND jam_mulai = "13:00:00"');
if ($row = $result->fetch_assoc()) {
    echo "   - Found: Schedule ID {$row['schedule_date_id']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}\n";
} else {
    echo "   - No 13:00 schedule found for Claudia on 2026-01-07\n";
    
    // Check what schedules exist
    echo "\n2. All schedules for Claudia on 2026-01-07:\n";
    $result = $conn->query('SELECT schedule_date_id, jam_mulai, jam_selesai FROM psychologist_schedule_dates WHERE psychologist_id = 6 AND tanggal = "2026-01-07" ORDER BY jam_mulai');
    while ($row = $result->fetch_assoc()) {
        echo "   - Schedule ID {$row['schedule_date_id']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}\n";
    }
    
    // Create missing 13:00 schedule
    echo "\n3. Creating missing 13:00 schedule:\n";
    $insert_sql = "INSERT INTO psychologist_schedule_dates (psychologist_id, tanggal, jam_mulai, jam_selesai) VALUES (6, '2026-01-07', '13:00:00', '15:00:00')";
    if ($conn->query($insert_sql)) {
        $new_schedule_id = $conn->insert_id;
        echo "   - Created Schedule ID $new_schedule_id for 13:00-15:00\n";
        
        // Update booking with new schedule_id
        $update_sql = "UPDATE consultation_bookings SET schedule_id = $new_schedule_id WHERE booking_id = 9";
        if ($conn->query($update_sql)) {
            echo "   - Updated Booking ID 9 with new Schedule ID $new_schedule_id\n";
            
            // Update has_booking
            $update_booking_sql = "UPDATE psychologist_schedule_dates SET has_booking = 1 WHERE schedule_date_id = $new_schedule_id";
            if ($conn->query($update_booking_sql)) {
                echo "   - Updated has_booking = 1 for Schedule ID $new_schedule_id\n";
            }
        }
    }
}

// Final check
echo "\n4. Final check - Claudia's schedules on 2026-01-07:\n";
$result = $conn->query('SELECT schedule_date_id, jam_mulai, jam_selesai, has_booking FROM psychologist_schedule_dates WHERE psychologist_id = 6 AND tanggal = "2026-01-07" ORDER BY jam_mulai');
while ($row = $result->fetch_assoc()) {
    $status = $row['has_booking'] ? 'BOOKED 🔒' : 'Available ✅';
    echo "   - Schedule ID {$row['schedule_date_id']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}, Status: $status\n";
}

$conn->close();
echo "\n=== Fix Complete ===\n";
?>
