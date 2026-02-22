<?php
/**
 * Simple DB Test
 */

echo "Test 1: Include DB\n";
$path = './';
require_once $path . 'includes/db.php';
echo "✓ DB included\n\n";

echo "Test 2: Create DB instance\n";
$db = new Database();
echo "✓ Instance created\n\n";

echo "Test 3: Connect to DB\n";
$db->connect();
echo "✓ Connected\n\n";

echo "Test 4: Query test\n";
try {
    $result = $db->getPrepare(
        "SELECT client_id FROM client_details WHERE user_id = ?",
        [28]
    );
    echo "Query result: " . json_encode($result) . "\n";
    echo "✓ Query successful\n";
} catch (Exception $e) {
    echo "❌ Query failed: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
?>
