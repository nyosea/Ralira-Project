<?php
// Logic sederhana untuk menentukan menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="mobile-toggle" data-sidebar-action="open" onclick="openSidebar()" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; position: fixed; top: 0; left: 0; width: 100%; height: 50px; border-bottom: 1px solid rgba(255,255,255,0.2); cursor: pointer; font-size: 1.3rem; z-index: 950; display: none; align-items: center; justify-content: center; padding: 0 15px;">
    <img src="<?php echo $path; ?>assets/img/logo.png" alt="Rali Ra" style="height: 28px; width: auto; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.25));">
</div>

<div class="sidebar-overlay" onclick="closeSidebar()"></div>

<aside class="sidebar glass-solid" id="clientSidebar">
    <button class="sidebar-close" onclick="closeSidebar()">
        <i class="fas fa-arrow-left"></i>
    </button>
    
    <!-- Logo Button for Desktop/Mobile -->
    <div class="sidebar-header">
        <button class="sidebar-logo-toggle" onclick="toggleSidebarDesktop('clientSidebar')" title="Collapse/Expand">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Admin Rali Ra" style="width: 50px;">
        </button>
        <h4 style="color: var(--color-text);">Panel Klien</h4>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Dashboard">
                <i class="fas fa-chart-line"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="booking.php" class="<?php echo ($current_page == 'booking.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Booking">
                <i class="fas fa-calendar-alt"></i> <span>Booking</span>
            </a>
        </li>
        <li>
            <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Profile">
                <i class="fas fa-user-circle"></i> <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="test_results.php" class="<?php echo ($current_page == 'test_results.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Hasil Tes">
                <i class="fas fa-file-pdf"></i> <span>Hasil Tes</span>
            </a>
        </li>
        <li>
            <a href="invoices.php" class="<?php echo ($current_page == 'invoices.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Invoice">
                <i class="fas fa-file-invoice-dollar"></i> <span>Invoice</span>
            </a>
        </li>
        <li>
            <a href="history.php" class="<?php echo ($current_page == 'history.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Riwayat dan Jadwal">
                <i class="fas fa-history"></i> <span>Riwayat dan Jadwal</span>
            </a>
        </li>
        
        <li style="margin-top: 40px;">
            <a href="../auth/login.php?logout=1" style="background: #ff6b6b; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; cursor: pointer;" title="Logout">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>