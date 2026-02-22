<?php
session_start();
// Test basic setup
echo "<h1>Test Schedule Setup</h1>";

// Check session
echo "<h2>Session Info:</h2>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";
echo "Name: " . ($_SESSION['name'] ?? 'NOT SET') . "<br>";

// Check if user is psychologist
if ($_SESSION['role'] !== 'psychologist') {
    die("ERROR: You are not logged in as a psychologist!");
}

// Test database
require_once './includes/db.php';
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];
$sql = "SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?";
$result = $db->getPrepare($sql, [$user_id]);

echo "<h2>Psychologist Info:</h2>";
if ($result) {
    echo "✓ Psychologist ID: " . $result['psychologist_id'] . "<br>";
    $psychologist_id = $result['psychologist_id'];
    
    // Check saved schedules
    $sql = "SELECT COUNT(*) as count FROM psychologist_schedule_dates 
            WHERE psychologist_id = ? AND is_available = 1";
    $count_result = $db->queryPrepare($sql, [$psychologist_id]);
    $count = $count_result[0]['count'] ?? 0;
    echo "✓ Saved schedules: " . $count . "<br>";
    
    if ($count > 0) {
        echo "<h3>Recent schedules:</h3>";
        $sql = "SELECT * FROM psychologist_schedule_dates 
                WHERE psychologist_id = ? AND is_available = 1
                ORDER BY tanggal DESC LIMIT 5";
        $schedules = $db->queryPrepare($sql, [$psychologist_id]);
        echo "<pre>";
        foreach ($schedules as $s) {
            echo $s['tanggal'] . " | " . substr($s['jam_mulai'], 0, 5) . "-" . substr($s['jam_selesai'], 0, 5) . "\n";
        }
        echo "</pre>";
    }
} else {
    die("✗ Psychologist profile not found!");
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Go to <a href='./pages/psychologist/schedule.php'>Schedule Page</a></li>";
echo "<li>Open browser Console (F12)</li>";
echo "<li>Try to select dates and times</li>";
echo "<li>Check if console logs appear</li>";
echo "<li>Screenshot and send the results</li>";
echo "</ol>";

?>
