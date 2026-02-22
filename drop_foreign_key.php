<?php
$conn = new mysqli('localhost', 'root', '', 'ralira_db');

echo "=== Dropping Foreign Key Constraint ===\n";

// Drop the problematic foreign key
$drop_sql = "ALTER TABLE consultation_bookings DROP FOREIGN KEY consultation_bookings_ibfk_3";
if ($conn->query($drop_sql)) {
    echo "✅ Dropped foreign key consultation_bookings_ibfk_3\n";
} else {
    echo "❌ Error dropping foreign key: " . $conn->error . "\n";
}

// Check remaining constraints
echo "\n=== Remaining Foreign Keys ===\n";
$result = $conn->query('SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = "ralira_db" AND REFERENCED_TABLE_NAME IS NOT NULL');
while ($row = $result->fetch_assoc()) {
    echo "Constraint: {$row['CONSTRAINT_NAME']}, Table: {$row['TABLE_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

$conn->close();
echo "\n=== Foreign Key Removed ===\n";
?>
