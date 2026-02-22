<?php
require_once 'includes/db.php';

$db = new Database();
$db->connect();

$result = $db->queryPrepare('SHOW COLUMNS FROM psychologist_schedule_dates', []);
foreach ($result as $col) {
    if ($col['Field'] == 'tanggal') {
        echo "tanggal column:\n";
        print_r($col);
    }
}

// Also check actual data
echo "\nSample data for psychologist_id=6:\n";
$sample = $db->queryPrepare(
    "SELECT tanggal, jam_mulai FROM psychologist_schedule_dates WHERE psychologist_id = 6 LIMIT 3",
    []
);
foreach ($sample as $row) {
    echo "tanggal: '{$row['tanggal']}' (type: " . gettype($row['tanggal']) . ")\n";
}
?>
