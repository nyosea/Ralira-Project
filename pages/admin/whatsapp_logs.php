<?php
$path = '../../';
$page_title = 'Log Notifikasi WhatsApp';

// Simulasi Log API
$logs = [
    ["time" => "10:05:22", "target" => "081234567890 (Siti)", "type" => "Reminder", "msg" => "Jadwal konsul hari ini jam 13.00", "status" => "Sent"],
    ["time" => "09:30:10", "target" => "Admin Group", "type" => "New Order", "msg" => "Pendaftar baru: Budi Santoso", "status" => "Sent"],
    ["time" => "08:00:05", "target" => "0856xxxxxx", "type" => "Broadcast", "msg" => "Promo Akhir Tahun", "status" => "Failed"],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/glass.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive_sections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>

        <main class="main-content">
            <div class="topbar">
                <h2 style="color: var(--color-text);">Monitoring WhatsApp Gateway</h2>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span style="width: 10px; height: 10px; background: #25D366; border-radius: 50%;"></span>
                    <span>API Connected</span>
                </div>
            </div>

            <div class="glass-panel" style="background: #1e1e1e; color: #00ff00; font-family: monospace; padding: 20px; height: 500px; overflow-y: auto; border: 1px solid #333;">
                <?php foreach($logs as $log): ?>
                <div style="margin-bottom: 10px; border-bottom: 1px dashed #333; padding-bottom: 5px;">
                    <span style="color: #888;">[<?php echo $log['time']; ?>]</span> 
                    <span style="color: #00bfff;">To: <?php echo $log['target']; ?></span>
                    <span style="color: #ff00ff;">[<?php echo $log['type']; ?>]</span>
                    <span style="color: #fff;"><?php echo $log['msg']; ?></span>
                    <?php if($log['status'] == 'Sent'): ?>
                        <span style="color: #00ff00; float: right;">✔ SENT</span>
                    <?php else: ?>
                        <span style="color: #ff0000; float: right;">✖ FAILED</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <div style="margin-top: 20px; color: #fff;">>_ Menunggu request baru...</div>
            </div>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>