<?php
require_once 'includes/db.php';

$db = new Database();
$db->connect();

// Get all psychologists
$result = $db->queryPrepare('SELECT psychologist_id, user_id FROM psychologist_profiles', []);
echo "Available psychologists:\n";
foreach ($result as $p) {
    echo "- ID: {$p['psychologist_id']}, User ID: {$p['user_id']}\n";
}
?>
