<?php
// Simulate the endpoint call
$_SESSION['user_id'] = 28;
$_SESSION['role'] = 'client';
$_GET['id'] = 1;

ob_start();
session_start();

echo "=== Testing Download Endpoint ===\n";
echo "Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Session role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "GET id: " . ($_GET['id'] ?? 'NOT SET') . "\n\n";

if (!isset($_SESSION['user_id'])) {
    echo "[ERROR] Not logged in\n";
    exit;
}

$path = './';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
echo "Result ID (int): " . $result_id . "\n";

if ($result_id <= 0) {
    echo "[ERROR] Invalid ID\n";
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role !== 'client') {
    echo "[ERROR] Not a client\n";
    exit;
}

echo "Getting client_id for user_id: $user_id\n";
$client_result = $db->getPrepare(
    "SELECT client_id FROM client_details WHERE user_id = ?",
    [$user_id]
);

if (!$client_result) {
    echo "[ERROR] Client not found\n";
    exit;
}

$client_id = $client_result['client_id'];
echo "Client ID: $client_id\n\n";

echo "Getting test result for result_id: $result_id, client_id: $client_id\n";
$result = $db->getPrepare(
    "SELECT result_id, file_hasil_tes, jenis_tes FROM test_results 
     WHERE result_id = ? AND client_id = ?",
    [$result_id, $client_id]
);

if (!$result) {
    echo "[ERROR] Test result not found\n";
    
    // Debug
    $all = $db->queryPrepare(
        "SELECT result_id, client_id FROM test_results WHERE client_id = ?",
        [$client_id]
    );
    echo "Available results for client $client_id:\n";
    if (is_array($all)) {
        foreach ($all as $row) {
            echo "- Result ID: " . $row['result_id'] . ", Client: " . $row['client_id'] . "\n";
        }
    }
    exit;
}

echo "✅ Found result:\n";
echo "- Result ID: " . $result['result_id'] . "\n";
echo "- File: " . $result['file_hasil_tes'] . "\n";
echo "- Type: " . $result['jenis_tes'] . "\n\n";

$filename = $result['file_hasil_tes'];
$file_path = $path . 'uploads/results/' . $filename;
echo "File path: $file_path\n";

if (file_exists($file_path)) {
    echo "✅ File exists\n";
    echo "File size: " . filesize($file_path) . " bytes\n";
    echo "\n[SUCCESS] Endpoint should work!\n";
} else {
    echo "[ERROR] File not found at $file_path\n";
    echo "Looking in: " . realpath($path . 'uploads/results/') . "\n";
}
?>
