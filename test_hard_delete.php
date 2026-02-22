<?php
/**
 * Test: Hard delete verification
 */

require_once 'includes/db.php';

$db = new Database();
$db->connect();

echo "=== TEST: HARD DELETE ===\n\n";

$psychologist_id = 6;
$tanggal_test = '2026-01-06';

// Before
echo "1. BEFORE DELETE:\n";
$before = $db->getPrepare(
    "SELECT COUNT(*) as cnt FROM psychologist_schedule_dates WHERE psychologist_id = ? AND tanggal = ?",
    [$psychologist_id, $tanggal_test]
);
echo "   Records on $tanggal_test: {$before['cnt']}\n\n";

// Delete
echo "2. EXECUTING HARD DELETE:\n";
$delete_sql = "DELETE FROM psychologist_schedule_dates WHERE psychologist_id = ? AND tanggal = ?";
$result = $db->executePrepare($delete_sql, [$psychologist_id, $tanggal_test]);
echo "   Delete executed: " . ($result ? 'YES' : 'NO') . "\n\n";

// After
echo "3. AFTER DELETE:\n";
$after = $db->getPrepare(
    "SELECT COUNT(*) as cnt FROM psychologist_schedule_dates WHERE psychologist_id = ? AND tanggal = ?",
    [$psychologist_id, $tanggal_test]
);
echo "   Records on $tanggal_test: {$after['cnt']}\n";
echo "   Deleted: " . ($before['cnt'] - $after['cnt']) . " records\n\n";

// Verify other dates still exist
echo "4. VERIFY OTHER DATES NOT AFFECTED:\n";
$others = $db->queryPrepare(
    "SELECT tanggal, COUNT(*) as cnt FROM psychologist_schedule_dates WHERE psychologist_id = ? GROUP BY tanggal ORDER BY tanggal",
    [$psychologist_id]
);
echo "   Remaining dates:\n";
foreach ($others as $row) {
    echo "   - {$row['tanggal']}: {$row['cnt']} records\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
