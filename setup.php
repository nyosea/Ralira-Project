<?php
/**
 * Setup Page - Auto-run SQL migrations
 */

require_once 'includes/db.php';

$db = new Database();
$db->connect();
$conn = $db->getConnection();

$error = '';
$success = '';

// Run migrations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    
    // 1. Create psychologist_schedule_slots table
    $sql1 = "CREATE TABLE IF NOT EXISTS `psychologist_schedule_slots` (
      `slot_id` INT AUTO_INCREMENT PRIMARY KEY,
      `psychologist_id` INT NOT NULL,
      `hari` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
      `jam` TIME NOT NULL,
      `is_available` TINYINT(1) DEFAULT 1,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
      UNIQUE KEY unique_slot (psychologist_id, hari, jam),
      INDEX idx_psychologist (psychologist_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 2. Create psychologist_off_days table
    $sql2 = "CREATE TABLE IF NOT EXISTS `psychologist_off_days` (
      `off_id` INT AUTO_INCREMENT PRIMARY KEY,
      `psychologist_id` INT NOT NULL,
      `tanggal_mulai` DATE NOT NULL,
      `tanggal_selesai` DATE NOT NULL,
      `alasan` VARCHAR(255),
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (psychologist_id) REFERENCES psychologist_profiles(psychologist_id) ON DELETE CASCADE,
      INDEX idx_psychologist (psychologist_id),
      INDEX idx_tanggal (tanggal_mulai, tanggal_selesai)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 3. Drop old tables if exist
    $sql3 = "DROP TABLE IF EXISTS `psychologist_unavailability`";
    $sql4 = "DROP TABLE IF EXISTS `schedules`";
    
    try {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        // 3. Add jam_mulai column to consultation_bookings if doesn't exist
        $sql3_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_NAME='consultation_bookings' AND COLUMN_NAME='jam_mulai'";
        $has_jam_mulai = $conn->query($sql3_check)->fetch_assoc();
        
        if (!$has_jam_mulai) {
            $sql3 = "ALTER TABLE consultation_bookings ADD COLUMN jam_mulai TIME AFTER tanggal_konsultasi";
            $conn->query($sql3);
        }
        
        // Drop old tables
        $conn->query("DROP TABLE IF EXISTS `psychologist_unavailability`");
        $conn->query("DROP TABLE IF EXISTS `schedules`");
        
        // Create new tables
        if ($conn->query($sql1) && $conn->query($sql2)) {
            // Re-enable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            $success = '✓ Migration berhasil! Database sudah siap digunakan.';
        } else {
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            $error = '✕ Migration gagal: ' . $conn->error;
        }
    } catch (Exception $e) {
        $error = '✕ Error: ' . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Rali Ra</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/glass.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
            padding: 20px;
        }
        .setup-container {
            max-width: 600px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .setup-container h1 {
            color: var(--color-primary);
            margin-bottom: 20px;
            text-align: center;
        }
        .setup-container p {
            color: var(--color-text-light);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }
        .btn-setup {
            width: 100%;
            padding: 12px;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-setup:hover {
            background: var(--color-accent);
        }
        .btn-setup:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .steps {
            background: rgba(0,0,0,0.05);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .steps h3 {
            color: var(--color-primary);
            margin-bottom: 10px;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin-bottom: 8px;
            color: var(--color-text);
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🔧 Setup Database</h1>
        
        <div class="steps">
            <h3>Yang akan di-setup:</h3>
            <ol>
                <li>Buat tabel <code>psychologist_schedule_slots</code> (slot jam kerja)</li>
                <li>Buat tabel <code>psychologist_off_days</code> (cuti per hari)</li>
                <li>Hapus tabel lama jika ada</li>
            </ol>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">
            ✕ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            ✓ <?php echo htmlspecialchars($success); ?>
        </div>
        <p style="text-align: center; margin-top: 20px;">
            <a href="pages/admin/dashboard.php" style="color: var(--color-primary); font-weight: 600; text-decoration: none;">
                → Lanjut ke Dashboard Admin
            </a>
        </p>
        <?php else: ?>
        <p>Klik tombol di bawah untuk menjalankan setup database secara otomatis.</p>
        
        <form method="POST">
            <button type="submit" name="run_migration" class="btn-setup">
                Jalankan Setup Database
            </button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
