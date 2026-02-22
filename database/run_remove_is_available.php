<?php
/**
 * Remove is_available column (use hard delete instead)
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

// Drop the index first
$drop_index_sql = "DROP INDEX IF EXISTS idx_availability ON psychologist_schedule_dates";
if ($conn->query($drop_index_sql)) {
    echo "Index 'idx_availability' dropped successfully.\n";
} else {
    echo "Index 'idx_availability' not found or already dropped.\n";
}

// Drop the column
$drop_column_sql = "ALTER TABLE psychologist_schedule_dates DROP COLUMN IF EXISTS is_available";
if ($conn->query($drop_column_sql)) {
    echo "Column 'is_available' dropped successfully.\n";
} else {
    echo "Column 'is_available' not found or already dropped.\n";
}

// Show table structure after removal
echo "\nTable structure after removal:\n";
$structure_sql = "DESCRIBE psychologist_schedule_dates";
$structure_result = $conn->query($structure_sql);

if ($structure_result->num_rows > 0) {
    while ($row = $structure_result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ") " . $row['Extra'] . "\n";
    }
}

// Show remaining indexes
echo "\nRemaining indexes:\n";
$index_sql = "SHOW INDEX FROM psychologist_schedule_dates";
$index_result = $conn->query($index_sql);

if ($index_result->num_rows > 0) {
    $shown_indexes = [];
    while ($row = $index_result->fetch_assoc()) {
        if (!in_array($row['Key_name'], $shown_indexes)) {
            echo "- Index: " . $row['Key_name'] . " on " . $row['Column_name'] . "\n";
            $shown_indexes[] = $row['Key_name'];
        }
    }
} else {
    echo "No indexes found.\n";
}

$conn->close();
echo "\nColumn removal completed!\n";
?>
