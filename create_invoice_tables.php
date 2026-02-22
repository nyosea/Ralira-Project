<?php
require_once 'includes/db.php';

echo "<h2>Creating Invoice Tables...</h2>";

// Read and execute the SQL file
$sql_file = 'database/create_invoice_table.sql';
$sql = file_get_contents($sql_file);

// Split SQL statements by semicolon
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($db->execute($statement)) {
            echo "<p style='color: green;'>✓ Success: " . substr($statement, 0, 50) . "...</p>";
            $success_count++;
        } else {
            echo "<p style='color: red;'>✗ Error: " . substr($statement, 0, 50) . "...</p>";
            echo "<p style='color: red;'>Error: " . $db->conn->error . "</p>";
            $error_count++;
        }
    }
}

echo "<h3>Summary:</h3>";
echo "<p>Success: $success_count statements</p>";
echo "<p>Errors: $error_count statements</p>";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>All invoice tables created successfully!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Some errors occurred. Please check above.</p>";
}
?>
