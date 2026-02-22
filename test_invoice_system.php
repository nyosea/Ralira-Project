<?php
/**
 * Test script for invoice system
 */

require_once 'includes/db.php';

echo "<h2>Invoice System Test</h2>";

$db = new Database();
$db->connect();

// Test 1: Check if tables exist
echo "<h3>1. Checking Tables:</h3>";
$tables = $db->query("SHOW TABLES LIKE 'invoice%'");
if ($tables) {
    foreach ($tables as $table) {
        echo "<p style='color: green;'>✓ Table found: " . $table['Tables_in_ralira_db (invoice%)'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ No invoice tables found</p>";
}

// Test 2: Check if we have users for testing
echo "<h3>2. Checking Users:</h3>";
$clients = $db->query("SELECT user_id, name, role FROM users WHERE role = 'client' LIMIT 3");
$psychologists = $db->query("SELECT user_id, name, role FROM users WHERE role = 'psychologist' LIMIT 3");

if ($clients) {
    echo "<p style='color: green;'>✓ Found " . count($clients) . " clients</p>";
} else {
    echo "<p style='color: red;'>✗ No clients found</p>";
}

if ($psychologists) {
    echo "<p style='color: green;'>✓ Found " . count($psychologists) . " psychologists</p>";
} else {
    echo "<p style='color: red;'>✗ No psychologists found</p>";
}

// Test 3: Create a test invoice (if we have users)
if ($clients && $psychologists) {
    echo "<h3>3. Creating Test Invoice:</h3>";
    
    $client = $clients[0];
    $psychologist = $psychologists[0];
    
    $invoice_number = 'TEST-' . date('Ymd') . '-001';
    
    $sql = "INSERT INTO invoices (invoice_number, client_id, psychologist_id, service_name, 
            service_price, total_payment, payment_method, invoice_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $invoice_number,
        $client['user_id'],
        $psychologist['user_id'],
        'Konsultasi Psikologi Test',
        500000,
        500000,
        'Transfer Bank',
        date('Y-m-d'),
        'Test invoice untuk verifikasi sistem'
    ];
    
    if ($db->executePrepare($sql, $params)) {
        $invoice_id = $db->lastId();
        echo "<p style='color: green;'>✓ Test invoice created successfully!</p>";
        echo "<p>Invoice Number: $invoice_number</p>";
        echo "<p>Invoice ID: $invoice_id</p>";
        
        // Test 4: Retrieve the invoice
        echo "<h3>4. Retrieving Test Invoice:</h3>";
        $sql_retrieve = "SELECT i.*, u1.name as psychologist_name, u2.name as client_name 
                        FROM invoices i 
                        LEFT JOIN users u1 ON i.psychologist_id = u1.user_id 
                        LEFT JOIN users u2 ON i.client_id = u2.user_id 
                        WHERE i.id = ?";
        
        $result = $db->getPrepare($sql_retrieve, [$invoice_id]);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Invoice retrieved successfully!</p>";
            echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($result as $key => $value) {
                echo "<tr><td>$key</td><td>$value</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>✗ Failed to retrieve invoice</p>";
        }
        
        // Clean up test invoice
        echo "<h3>5. Cleaning Up:</h3>";
        if ($db->executePrepare("DELETE FROM invoices WHERE id = ?", [$invoice_id])) {
            echo "<p style='color: green;'>✓ Test invoice cleaned up</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Could not clean up test invoice</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Failed to create test invoice</p>";
    }
}

echo "<h3>Test Complete!</h3>";
echo "<p><a href='pages/admin/create_invoice.php'>Go to Admin Invoice Creation</a></p>";
echo "<p><a href='pages/client/invoices.php'>Go to Client Invoice View</a></p>";
?>
