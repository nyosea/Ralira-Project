<?php
/**
 * FULL API TEST - Simulate exact flow dari download_test_result_new.php
 */

// EXACT replica dari API endpoint
ob_start();
session_start();

// Simulate session
$_SESSION['user_id'] = 28;
$_SESSION['role'] = 'client';

echo "=== TRACING EXACT API FLOW ===\n\n";

// Check session
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('❌ Access denied - no session\n');
}

echo "✓ Session OK - user_id: " . $_SESSION['user_id'] . "\n";

$path = './';
echo "✓ Path: " . $path . "\n";

// Require DB
echo "Loading DB...\n";
require_once $path . 'includes/db.php';
echo "✓ DB loaded\n";

// Connect
$db = new Database();
$db->connect();
echo "✓ DB connected\n\n";

// Get result_id from GET
$_GET['id'] = 1;  // Simulate request
$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

echo "result_id from GET: " . $result_id . "\n";

if ($result_id <= 0) {
    http_response_code(400);
    exit('❌ Invalid request\n');
}

echo "✓ result_id valid\n\n";

// Check role
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'NOT SET';

echo "user_id: " . $user_id . "\n";
echo "role: " . $role . "\n";

if ($role !== 'client') {
    http_response_code(403);
    exit('❌ Only clients - your role: ' . $role . '\n');
}

echo "✓ Role is client\n\n";

// Get client_id
echo "Getting client_id...\n";
$client_result = $db->getPrepare(
    "SELECT client_id FROM client_details WHERE user_id = ?",
    [$user_id]
);

echo "client_result: " . json_encode($client_result) . "\n";

if (!$client_result) {
    http_response_code(403);
    exit('❌ Client not found\n');
}

$client_id = $client_result['client_id'];
echo "✓ client_id: " . $client_id . "\n\n";

// Get test result
echo "Getting test result...\n";
$result = $db->getPrepare(
    "SELECT result_id, file_hasil_tes, jenis_tes FROM test_results 
     WHERE result_id = ? AND client_id = ?",
    [$result_id, $client_id]
);

echo "result: " . json_encode($result) . "\n";

if (!$result) {
    http_response_code(404);
    exit('❌ Not found\n');
}

echo "✓ Test result found\n\n";

// Build file path
$filename = $result['file_hasil_tes'];
$file_path = $path . 'uploads/results/' . $filename;

echo "filename: " . $filename . "\n";
echo "file_path: " . $file_path . "\n";
echo "Absolute: " . realpath($file_path) . "\n";

// Check file
if (!file_exists($file_path)) {
    http_response_code(404);
    exit('❌ File not found: ' . $file_path . '\n');
}

echo "✓ File exists!\n";
echo "✓ File size: " . filesize($file_path) . " bytes\n";
echo "✓ Readable: " . (is_readable($file_path) ? "YES" : "NO") . "\n\n";

// Clear buffer
ob_end_clean();

// Now test header setting
echo "=== WOULD SET THESE HEADERS ===\n";
$headers = [
    'Content-Type: application/pdf',
    'Content-Disposition: attachment; filename="Laporan_' . $result['jenis_tes'] . '_' . date('d-m-Y') . '.pdf"',
    'Content-Length: ' . filesize($file_path),
    'Cache-Control: no-cache, must-revalidate',
    'Pragma: no-cache'
];

foreach ($headers as $h) {
    echo $h . "\n";
}

echo "\n✓ ✓ ✓ ALL CHECKS PASSED ✓ ✓ ✓\n";
echo "\nAPI endpoint should work!\n";
?>
