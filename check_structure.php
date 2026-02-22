<?php
$conn = new mysqli('localhost', 'root', '', 'ralira_db');

echo "=== psychologist_profiles structure ===\n";
$result = $conn->query('DESCRIBE psychologist_profiles');
while ($row = $result->fetch_assoc()) {
    echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}

echo "\n=== psychologist_schedule_dates structure ===\n";
$result = $conn->query('DESCRIBE psychologist_schedule_dates');
while ($row = $result->fetch_assoc()) {
    echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}

echo "\n=== consultation_bookings structure ===\n";
$result = $conn->query('DESCRIBE consultation_bookings');
while ($row = $result->fetch_assoc()) {
    echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}

$conn->close();
?>
