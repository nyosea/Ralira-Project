<?php
require_once 'includes/db.php';

echo "<h2>Removing Due Date Field from Invoice System</h2>";

$db = new Database();
$db->connect();

// Remove due_date column from invoices table
$sql = "ALTER TABLE invoices DROP COLUMN due_date";

if ($db->execute($sql)) {
    echo "<p style='color: green;'>✓ Due date column removed successfully from invoices table</p>";
} else {
    echo "<p style='color: red;'>✗ Failed to remove due date column: " . $db->conn->error . "</p>";
}

// Verify the change
echo "<h3>Updated Invoice Table Structure:</h3>";
$result = $db->query("DESCRIBE invoices");

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error describing invoices table: " . $db->conn->error;
}

echo "<h3>Due date field removed successfully!</h3>";
?>
