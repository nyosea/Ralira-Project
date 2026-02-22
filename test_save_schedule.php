<?php
session_start();
$path = './';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

// Simulate save schedule request
$psychologist_id = 6;
$dates = ['2025-12-29'];
$times = ['09:00-11:00', '11:00-13:00'];

echo "<h1>Debug: Test Save Schedule</h1>";
echo "<h2>Parameters:</h2>";
echo "Psychologist ID: " . $psychologist_id . "<br>";
echo "Dates: " . implode(", ", $dates) . "<br>";
echo "Times: " . implode(", ", $times) . "<br><br>";

try {
    // Simulate the save logic
    foreach ($dates as $tanggal) {
        foreach ($times as $time) {
            $time_parts = explode('-', $time);
            $jam_mulai = $time_parts[0];
            $jam_selesai = $time_parts[1] ?? $time_parts[0];
            
            echo "Processing: $tanggal | $jam_mulai - $jam_selesai<br>";
            
            // Check if already exists
            $check_sql = "SELECT schedule_date_id FROM psychologist_schedule_dates 
                          WHERE psychologist_id = ? AND tanggal = ? AND jam_mulai = ?";
            $check = $db->getPrepare($check_sql, [$psychologist_id, $tanggal, $jam_mulai]);
            
            if ($check) {
                echo "  ✓ Already exists (ID: " . $check['schedule_date_id'] . ")<br>";
            } else {
                echo "  → Inserting...<br>";
                
                // Insert new record
                $insert_sql = "INSERT INTO psychologist_schedule_dates 
                              (psychologist_id, tanggal, jam_mulai, jam_selesai, is_available) 
                              VALUES (?, ?, ?, ?, 1)";
                $result = $db->executePrepare($insert_sql, [$psychologist_id, $tanggal, $jam_mulai, $jam_selesai]);
                
                if ($result) {
                    echo "  ✓ Inserted successfully<br>";
                } else {
                    echo "  ✗ Insert failed<br>";
                }
            }
        }
    }
    
    echo "<h2>Verification:</h2>";
    echo "Checking database after insert:<br>";
    $sql = "SELECT * FROM psychologist_schedule_dates 
            WHERE psychologist_id = ? AND tanggal IN (" . implode(",", array_map(function($d) { return "'$d'"; }, $dates)) . ")
            ORDER BY tanggal, jam_mulai";
    $result = $db->queryPrepare($sql, [$psychologist_id]);
    
    if (is_array($result) && count($result) > 0) {
        echo "✓ Found " . count($result) . " schedules:<br>";
        echo "<pre>";
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "</pre>";
    } else {
        echo "✗ No schedules found<br>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
}

?>
