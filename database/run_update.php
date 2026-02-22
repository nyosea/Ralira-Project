<?php
/**
 * Run Database Update: Add is_date_selected column
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
        ADD COLUMN is_date_selected TINYINT(1) DEFAULT 0 COMMENT '1 = tanggal dipilih, 0 = tanggal tidak dipilih'";

// Check if column already exists
$check_sql = "SHOW COLUMNS FROM psychologist_schedule_dates LIKE 'is_date_selected'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "Column 'is_date_selected' already exists.\n";
} else {
    // Add column
    if ($conn->query($sql) === TRUE) {
        echo "Column 'is_date_selected' added successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
}

// Add index for better performance
$index_sql = "CREATE INDEX idx_psychologist_date_selected ON psychologist_schedule_dates(psychologist_id, tanggal, is_date_selected)";

// Check if index already exists
$check_index = "SHOW INDEX FROM psychologist_schedule_dates WHERE Key_name = 'idx_psychologist_date_selected'";
$index_result = $conn->query($check_index);

if ($index_result->num_rows > 0) {
    echo "Index 'idx_psychologist_date_selected' already exists.\n";
} else {
    // Add index
    if ($conn->query($index_sql) === TRUE) {
        echo "Index 'idx_psychologist_date_selected' added successfully.\n";
    } else {
        echo "Error adding index: " . $conn->error . "\n";
    }
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
