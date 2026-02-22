<?php
/**
 * Test Download Flow untuk Test Results
 */

session_start();

// Simulate logged in as Joshua (user_id 28, client_id 6)
$_SESSION['user_id'] = 28;
$_SESSION['role'] = 'client';
$_SESSION['email'] = 'joshua@test.com';
$_SESSION['name'] = 'Joshua';

echo "=== TEST DOWNLOAD FLOW ===\n";
echo "Simulated Session:\n";
echo "- user_id: " . $_SESSION['user_id'] . "\n";
echo "- role: " . $_SESSION['role'] . "\n\n";

$path = './';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

// Step 1: Get client_id from user_id
echo "Step 1: Get client_id from user_id\n";
$client_result = $db->getPrepare(
    "SELECT client_id FROM client_details WHERE user_id = ?",
    [$_SESSION['user_id']]
);

if (!$client_result) {
    echo "❌ ERROR: Client not found\n";
    exit;
}

$client_id = $client_result['client_id'];
echo "✓ client_id: " . $client_id . "\n\n";

// Step 2: Get test results for this client
echo "Step 2: Get test results for this client\n";
$results = $db->queryPrepare(
    "SELECT result_id, file_hasil_tes, jenis_tes FROM test_results WHERE client_id = ?",
    [$client_id]
);

if (empty($results)) {
    echo "❌ No test results found for this client\n";
} else {
    echo "✓ Found " . count($results) . " test result(s):\n";
    foreach ($results as $res) {
        echo "  - result_id: " . $res['result_id'];
        echo ", jenis_tes: " . $res['jenis_tes'];
        echo ", file: " . $res['file_hasil_tes'] . "\n";
        
        // Step 3: Check if file exists
        $file_path = 'uploads/results/' . $res['file_hasil_tes'];
        if (file_exists($file_path)) {
            echo "    ✓ File exists: $file_path (" . filesize($file_path) . " bytes)\n";
            echo "    → Download link: api/download_test_result_new.php?id=" . $res['result_id'] . "\n";
        } else {
            echo "    ❌ File NOT found: $file_path\n";
        }
    }
}

echo "\n=== END TEST ===\n";
?>
