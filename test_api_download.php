<?php
/**
 * Test API Download dengan detail logging
 */

// Simulate session
session_start();
$_SESSION['user_id'] = 28;
$_SESSION['role'] = 'client';

echo "=== TESTING API DOWNLOAD ===\n\n";

// Simulate GET request
$_GET['id'] = 1;

echo "Request: GET api/download_test_result_new.php?id=1\n";
echo "Session user_id: " . $_SESSION['user_id'] . "\n";
echo "Session role: " . $_SESSION['role'] . "\n\n";

// Start capturing
ob_start();

$path = './';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

echo "Step 1: Validate session\n";
if (!isset($_SESSION['user_id'])) {
    echo "❌ Session user_id not set\n";
    exit;
}
echo "✓ user_id: " . $_SESSION['user_id'] . "\n";

echo "\nStep 2: Get result_id from GET\n";
$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
echo "✓ result_id: " . $result_id . "\n";

if ($result_id <= 0) {
    echo "❌ Invalid result_id\n";
    exit;
}

echo "\nStep 3: Check role\n";
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'NOT SET';
echo "✓ role: " . $role . "\n";

if ($role !== 'client') {
    echo "❌ Not a client! role=" . $role . "\n";
    exit;
}

echo "\nStep 4: Get client_id from user_id\n";
$client_result = $db->getPrepare(
    "SELECT client_id FROM client_details WHERE user_id = ?",
    [$user_id]
);

if (!$client_result) {
    echo "❌ Client not found for user_id=" . $user_id . "\n";
    exit;
}

$client_id = $client_result['client_id'];
echo "✓ client_id: " . $client_id . "\n";

echo "\nStep 5: Get test result from DB\n";
$result = $db->getPrepare(
    "SELECT result_id, file_hasil_tes, jenis_tes FROM test_results 
     WHERE result_id = ? AND client_id = ?",
    [$result_id, $client_id]
);

if (!$result) {
    echo "❌ Test result not found for result_id=" . $result_id . ", client_id=" . $client_id . "\n";
    exit;
}

echo "✓ Found test result:\n";
echo "  - result_id: " . $result['result_id'] . "\n";
echo "  - jenis_tes: " . $result['jenis_tes'] . "\n";
echo "  - file_hasil_tes: " . $result['file_hasil_tes'] . "\n";

echo "\nStep 6: Check file exists\n";
$filename = $result['file_hasil_tes'];
$file_path = $path . 'uploads/results/' . $filename;

echo "Looking for file: " . $file_path . "\n";

if (!file_exists($file_path)) {
    echo "❌ File NOT found!\n";
    // Check directory
    echo "\nDEBUG: Checking directory...\n";
    $dir_path = $path . 'uploads/results';
    if (is_dir($dir_path)) {
        echo "✓ Directory exists: " . $dir_path . "\n";
        $files = scandir($dir_path);
        echo "Files in directory:\n";
        foreach ($files as $f) {
            if ($f !== '.' && $f !== '..') {
                echo "  - " . $f . "\n";
            }
        }
    } else {
        echo "❌ Directory not found: " . $dir_path . "\n";
    }
    exit;
}

echo "✓ File found!\n";
echo "  - File size: " . filesize($file_path) . " bytes\n";
echo "  - Is readable: " . (is_readable($file_path) ? 'YES' : 'NO') . "\n";

echo "\n✓ ALL CHECKS PASSED!\n";
echo "\nWould set headers and send file:\n";
echo "  Content-Type: application/pdf\n";
echo "  Content-Disposition: attachment; filename=\"Laporan_" . $result['jenis_tes'] . "_" . date('d-m-Y') . ".pdf\"\n";
echo "  Content-Length: " . filesize($file_path) . "\n";

ob_end_clean();
?>
