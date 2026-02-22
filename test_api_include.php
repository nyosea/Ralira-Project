<?php
/**
 * Test API with HTTP simulation
 */

// Create temp session file
$session_file = 'temp_session_test.php';
file_put_contents($session_file, '<?php session_start(); $_SESSION["user_id"] = 28; $_SESSION["role"] = "client"; ?>');

echo "Testing API endpoint download_test_result_new.php\n\n";

// Try to get the file via direct include (simulating HTTP request)
ob_start();

$_GET['id'] = '1';
$_SESSION['user_id'] = 28;
$_SESSION['role'] = 'client';

include 'api/download_test_result_new.php';

$output = ob_get_clean();

if ($output) {
    echo "Output length: " . strlen($output) . " bytes\n";
    echo "First 100 chars: " . substr($output, 0, 100) . "\n";
} else {
    echo "No output (file was sent)\n";
}

// Clean up
@unlink($session_file);
?>
