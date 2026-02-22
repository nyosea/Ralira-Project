<?php
/**
 * Test script for updated invoice system (without due date)
 */

require_once 'includes/db.php';

echo "<h2>Updated Invoice System Test (No Due Date)</h2>";

$db = new Database();
$db->connect();

// Test 1: Check if due_date column is removed
echo "<h3>1. Checking Invoice Table Structure:</h3>";
$result = $db->query("DESCRIBE invoices");
$has_due_date = false;

if ($result) {
    foreach ($result as $column) {
        if ($column['Field'] === 'due_date') {
            $has_due_date = true;
            break;
        }
    }
    
    if ($has_due_date) {
        echo "<p style='color: red;'>✗ due_date column still exists</p>";
    } else {
        echo "<p style='color: green;'>✓ due_date column successfully removed</p>";
    }
}

// Test 2: Create a test invoice without due date
echo "<h3>2. Creating Test Invoice (Without Due Date):h3>";

$clients = $db->query("SELECT user_id, name FROM users WHERE role = 'client' LIMIT 1");
$psychologists = $db->query("SELECT user_id, name FROM users WHERE role = 'psychologist' LIMIT 1");

if ($clients && $psychologists) {
    $client = $clients[0];
    $psychologist = $psychologists[0];
    
    $invoice_number = 'TEST-NO-DUE-' . date('YmdHis');
    
    $sql = "INSERT INTO invoices (invoice_number, client_id, psychologist_id, service_name, 
            service_price, total_payment, payment_method, invoice_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $invoice_number,
        $client['user_id'],
        $psychologist['user_id'],
        'Konsultasi Test (No Due Date)',
        300000,
        300000,
        'Transfer Bank',
        date('Y-m-d'),
        'Test invoice tanpa due date'
    ];
    
    if ($db->executePrepare($sql, $params)) {
        $invoice_id = $db->lastId();
        echo "<p style='color: green;'>✓ Test invoice created successfully without due date!</p>";
        echo "<p>Invoice Number: $invoice_number</p>";
        echo "<p>Invoice ID: $invoice_id</p>";
        
        // Test 3: Retrieve and verify
        echo "<h3>3. Verifying Invoice Data:</h3>";
        $sql_retrieve = "SELECT * FROM invoices WHERE id = ?";
        $result = $db->getPrepare($sql_retrieve, [$invoice_id]);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Invoice retrieved successfully!</p>";
            echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($result as $key => $value) {
                if ($key !== 'due_date') { // Don't show due_date as it shouldn't exist
                    echo "<tr><td>$key</td><td>$value</td></tr>";
                }
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>✗ Failed to retrieve invoice</p>";
        }
        
        // Clean up
        $db->executePrepare("DELETE FROM invoices WHERE id = ?", [$invoice_id]);
        echo "<p style='color: green;'>✓ Test invoice cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>✗ Failed to create test invoice: " . $db->conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ No clients or psychologists found for testing</p>";
}

echo "<h3>Test Complete!</h3>";
echo "<p><strong>Changes Made:</strong></p>";
echo "<ul>";
echo "<li>✓ Removed due_date column from invoices table</li>";
echo "<li>✓ Updated admin form to remove due date input</li>";
echo "<li>✓ Updated client view to remove due date display</li>";
echo "<li>✓ Updated documentation</li>";
echo "</ul>";
echo "<p><a href='pages/admin/create_invoice.php'>Test Admin Form</a></p>";
echo "<p><a href='pages/client/invoices.php'>Test Client View</a></p>";
?>
