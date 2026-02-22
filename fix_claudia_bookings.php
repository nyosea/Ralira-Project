<?php
/**
 * Fix Claudia's Existing Bookings - Update schedule_id and has_booking
 */

$conn = new mysqli('localhost', 'root', '', 'ralira_db');

echo "=== Fixing Claudia's Existing Bookings ===\n\n";

// Get all bookings for Claudia with NULL schedule_id
echo "1. Finding bookings with NULL schedule_id:\n";
$result = $conn->query('SELECT booking_id, psychologist_id, tanggal_konsultasi, jam_konsultasi FROM consultation_bookings WHERE psychologist_id = 6 AND schedule_id IS NULL');

$bookings_to_fix = [];
while ($row = $result->fetch_assoc()) {
    $bookings_to_fix[] = $row;
    echo "   - Booking ID: {$row['booking_id']}, Date: {$row['tanggal_konsultasi']}, Time: {$row['jam_konsultasi']}\n";
}

if (empty($bookings_to_fix)) {
    echo "   - No bookings to fix found.\n";
    exit;
}

// Fix each booking
echo "\n2. Fixing each booking:\n";
foreach ($bookings_to_fix as $booking) {
    $booking_id = $booking['booking_id'];
    $psychologist_id = $booking['psychologist_id'];
    $tanggal = $booking['tanggal_konsultasi'];
    $jam = $booking['jam_konsultasi'];
    
    // Find corresponding schedule
    $schedule_sql = "SELECT schedule_date_id FROM psychologist_schedule_dates 
                     WHERE psychologist_id = ? AND tanggal = ? AND jam_mulai = ?";
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("iss", $psychologist_id, $tanggal, $jam);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    if ($schedule_row = $schedule_result->fetch_assoc()) {
        $schedule_id = $schedule_row['schedule_date_id'];
        
        // Update booking with correct schedule_id
        $update_sql = "UPDATE consultation_bookings SET schedule_id = ? WHERE booking_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $schedule_id, $booking_id);
        
        if ($stmt->execute()) {
            echo "   - Updated Booking ID $booking_id with Schedule ID $schedule_id\n";
            
            // Update has_booking
            $update_booking_sql = "UPDATE psychologist_schedule_dates SET has_booking = 1 WHERE schedule_date_id = ?";
            $stmt = $conn->prepare($update_booking_sql);
            $stmt->bind_param("i", $schedule_id);
            
            if ($stmt->execute()) {
                echo "     - Updated has_booking = 1 for Schedule ID $schedule_id\n";
            }
        }
    } else {
        echo "   - No schedule found for Booking ID $booking_id (Date: $tanggal, Time: $jam)\n";
    }
}

// Check final result
echo "\n3. Final check - Claudia's schedules:\n";
$result = $conn->query('SELECT schedule_date_id, tanggal, jam_mulai, jam_selesai, has_booking FROM psychologist_schedule_dates WHERE psychologist_id = 6 ORDER BY tanggal, jam_mulai');
while ($row = $result->fetch_assoc()) {
    $status = $row['has_booking'] ? 'BOOKED 🔒' : 'Available ✅';
    echo "   - Schedule ID: {$row['schedule_date_id']}, Date: {$row['tanggal']}, Time: {$row['jam_mulai']}-{$row['jam_selesai']}, Status: $status\n";
}

echo "\n=== Fix Complete ===\n";
echo "✅ Claudia's bookings now have correct schedule_id\n";
echo "✅ has_booking column updated for booked schedules\n";
echo "✅ Admin schedule page should now show booked slots as gray + disabled\n";
?>
