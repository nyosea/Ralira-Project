<?php
session_start();

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Only admin can use this");
}

require_once './includes/db.php';
$db = new Database();
$db->connect();

if (isset($_POST['action']) && $_POST['action'] === 'delete_all') {
    $sql = "DELETE FROM psychologist_schedule_dates WHERE is_available = 1";
    $result = $db->executePrepare($sql, []);
    
    if ($result) {
        echo "<h1 style='color: green;'>✓ All schedules deleted!</h1>";
        echo "<p>Total deleted schedules</p>";
        echo "<a href='./pages/admin/manage_psychologist_schedule.php'>Go back to admin</a>";
    } else {
        echo "<h1 style='color: red;'>✗ Error deleting schedules</h1>";
    }
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete All Schedules</title>
    <link rel="stylesheet" href="./assets/css/glass.css">
    <style>
        body { padding: 40px; font-family: Arial; }
        .danger-box { background: #ffebee; padding: 20px; border-radius: 5px; border-left: 4px solid #f44336; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px 0; }
        .btn-danger { background: #f44336; color: white; }
        .btn-cancel { background: #999; color: white; }
    </style>
</head>
<body>

<div class="danger-box">
    <h1>⚠️ Delete All Schedules</h1>
    <p>This will permanently delete ALL psychologist schedules from the database.</p>
    <p><strong>This action cannot be undone!</strong></p>
    
    <form method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="delete_all">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you SURE? This will delete ALL schedules!\\n\\nThis action cannot be undone.')">
            🗑️ Delete All Schedules
        </button>
        <a href="./pages/admin/manage_psychologist_schedule.php">
            <button type="button" class="btn btn-cancel">Cancel</button>
        </a>
    </form>
</div>

</body>
</html>

<?php
?>
