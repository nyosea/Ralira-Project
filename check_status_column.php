<?php
require 'includes/db.php';
$db = new Database();
$db->connect();

$result = $db->query('DESCRIBE consultation_bookings');
foreach($result as $row) {
    if($row['Field'] == 'status_booking') {
        echo "Column: " . $row['Field'] . "\n";
        echo "Type: " . $row['Type'] . "\n";
        echo "Null: " . $row['Null'] . "\n";
        echo "Key: " . $row['Key'] . "\n";
        echo "Default: " . $row['Default'] . "\n";
        echo "Extra: " . $row['Extra'] . "\n";
    }
}

// Also check current values
echo "\nCurrent values in status_booking:\n";
$values = $db->query('SELECT DISTINCT status_booking FROM consultation_bookings');
foreach($values as $val) {
    echo "- '" . $val['status_booking'] . "' (length: " . strlen($val['status_booking']) . ")\n";
}
?>
