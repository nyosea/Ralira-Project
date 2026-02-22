<?php
/**
 * Run Database Update: Add has_booking column
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

echo "Connected to database: $database\n";

// SQL to add column
$sql = "ALTER TABLE psychologist_schedule_dates 
        ADD COLUMN has_booking TINYINT(1) DEFAULT 0 COMMENT '1 = has active booking, 0 = no booking'";

// Check if column already exists
$check_sql = "SHOW COLUMNS FROM psychologist_schedule_dates LIKE 'has_booking'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "Column 'has_booking' already exists.\n";
} else {
    // Add column
    if ($conn->query($sql) === TRUE) {
        echo "Column 'has_booking' added successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
}

// Add index for better performance
$index_sql = "CREATE INDEX idx_has_booking ON psychologist_schedule_dates(psychologist_id, tanggal, has_booking)";

// Check if index already exists
$check_index = "SHOW INDEX FROM psychologist_schedule_dates WHERE Key_name = 'idx_has_booking'";
$index_result = $conn->query($check_index);

if ($index_result->num_rows > 0) {
    echo "Index 'idx_has_booking' already exists.\n";
} else {
    // Add index
    if ($conn->query($index_sql) === TRUE) {
        echo "Index 'idx_has_booking' added successfully.\n";
    } else {
        echo "Error adding index: " . $conn->error . "\n";
    }
}

// Update existing data: Set has_booking = 1 for schedules with active bookings
$update_sql = "UPDATE psychologist_schedule_dates psd 
              SET has_booking = 1 
              WHERE EXISTS (
                  SELECT 1 
                  FROM consultation_bookings cb 
                  WHERE cb.schedule_id = psd.schedule_date_id 
                  AND cb.status_booking != 'canceled'
              )";

if ($conn->query($update_sql) === TRUE) {
    echo "Existing booking data updated successfully.\n";
} else {
    echo "Error updating booking data: " . $conn->error . "\n";
}

// Show updated data
echo "\nUpdated booking data:\n";
$select_sql = "SELECT schedule_date_id, psychologist_id, tanggal, jam_mulai, has_booking 
               FROM psychologist_schedule_dates 
               WHERE has_booking = 1 
               ORDER BY psychologist_id, tanggal, jam_mulai 
               LIMIT 10";
$result = $conn->query($select_sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- Schedule ID: {$row['schedule_date_id']}, Psychologist: {$row['psychologist_id']}, Date: {$row['tanggal']}, Time: {$row['jam_mulai']}, Has Booking: {$row['has_booking']}\n";
    }
} else {
    echo "No schedules with bookings found.\n";
}

// Show table structure
echo "\nTable structure:\n";
$structure_sql = "DESCRIBE psychologist_schedule_dates";
$structure_result = $conn->query($structure_sql);

if ($structure_result->num_rows > 0) {
    while ($row = $structure_result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ") " . $row['Extra'] . "\n";
    }
}

$conn->close();
echo "\nDatabase update completed!\n";
?>
