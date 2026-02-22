<?php
/**
 * Filename: components/header_psychologist.php
 * Description: Header component for Psychologist Dashboard (based on admin header)
 */

// Session already started in main file, no need to start again
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Location: ../pages/auth/login.php');
    exit();
}

// Get psychologist name from session
$psychologist_name = $_SESSION['name'] ?? 'Psikolog';
?>

<header>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div class="admin-logo">
            <button class="hamburger" data-sidebar-action="open" onclick="toggleSidebar()">☰</button>
            <div class="logo-icon">
                <i class="fas fa-brain"></i>
            </div>
            <h1>Rali Ra</h1>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: rgba(255, 255, 255, 0.15); border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px);">
                <i class="fas fa-user" style="color: white; font-size: 14px;"></i>
                <span style="font-size: 14px; font-weight: 600; color: white;"><?php echo htmlspecialchars($psychologist_name); ?></span>
            </div>
        </div>
    </div>
</header>
