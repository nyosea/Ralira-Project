<?php
require_once 'includes/db.php';

$db = new Database();
$db->connect();

$result = $db->queryPrepare(
    'SELECT psychologist_id, COUNT(*) as total, SUM(is_available) as active FROM psychologist_schedule_dates GROUP BY psychologist_id', 
    []
);

foreach ($result as $r) {
    $inactive = $r['total'] - $r['active'];
    echo "Psychologist {$r['psychologist_id']}: Total={$r['total']}, Active={$r['active']}, Deleted={$inactive}\n";
}
?>
