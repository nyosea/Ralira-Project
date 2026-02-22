<?php
require '../../includes/db.php';
$db = new Database();
$db->connect();

echo "=== CHECK CONSULTATION_BOOKINGS COLUMNS ===\n\n";

$columns = $db->query('DESCRIBE consultation_bookings');
foreach($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}

echo "\n=== CHECK IF JAM_KONSULTASI EXISTS ===\n";
$exists = false;
foreach($columns as $col) {
    if($col['Field'] == 'jam_konsultasi') {
        $exists = true;
        echo "✅ jam_konsultasi column EXISTS: " . $col['Type'] . "\n";
        break;
    }
}
if(!$exists) {
    echo "❌ jam_konsultasi column DOES NOT EXIST\n";
    echo "\nNeed to add column:\n";
    echo "ALTER TABLE consultation_bookings ADD COLUMN jam_konsultasi TIME AFTER tanggal_konsultasi;\n";
}

echo "\n=== CHECK SAMPLE BOOKING DATA ===\n";
$booking = $db->getPrepare('SELECT * FROM consultation_bookings WHERE booking_id = ?', [24]);
if($booking) {
    echo "Booking #24:\n";
    foreach($booking as $key => $val) {
        echo "  $key: " . ($val ?? 'NULL') . "\n";
    }
}
?>
