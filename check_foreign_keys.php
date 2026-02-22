<?php
$conn = new mysqli('localhost', 'root', '', 'ralira_db');

echo "=== Foreign Key Constraints ===\n";
$result = $conn->query('SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = "ralira_db" AND REFERENCED_TABLE_NAME IS NOT NULL');
while ($row = $result->fetch_assoc()) {
    echo "Constraint: {$row['CONSTRAINT_NAME']}, Table: {$row['TABLE_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

echo "\n=== Check schedules table ===\n";
$result = $conn->query('SHOW TABLES LIKE "schedules"');
if ($result->num_rows > 0) {
    echo "Table 'schedules' exists\n";
    $result = $conn->query('DESCRIBE schedules');
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "Table 'schedules' does not exist\n";
}

$conn->close();
?>
