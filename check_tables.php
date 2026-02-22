<?php
require_once 'includes/db.php';

echo "<h2>Current Database Tables:</h2>";
$result = $db->query("SHOW TABLES");

if ($result) {
    echo "<ul>";
    foreach ($result as $row) {
        echo "<li>" . $row['Tables_in_ralira_db'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Error showing tables: " . $db->conn->error;
}
?>
