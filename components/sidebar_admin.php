<?php
// Logic sederhana untuk menentukan menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar-overlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="adminSidebar">
    <!-- Logo Button for Desktop/Mobile -->
    <div class="sidebar-header" style="display: flex; align-items: center;">
        <button class="sidebar-logo-toggle" onclick="toggleSidebarDesktop('adminSidebar')" title="Collapse/Expand">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Admin Rali Ra" style="width: 50px;">
        </button>
        <h4 style="color: var(--color-text); margin-left: 15px;">Panel Admin</h4>
    </div>


    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Dashboard">
                <i class="fas fa-chart-line"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="manage_users.php" class="<?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Data Pendaftaran Klien">
                <i class="fas fa-users"></i> <span>Data Pendaftaran Klien</span>
            </a>
        </li>
        <li>
            <a href="manage_psychologists.php" class="<?php echo ($current_page == 'manage_psychologists.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Manajemen Psikolog">
                <i class="fas fa-user-nurse"></i> <span>Manajemen Psikolog</span>
            </a>
        </li>
        <li>
            <a href="manage_psychologist_schedule.php" class="<?php echo ($current_page == 'manage_psychologist_schedule.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Jadwal Kerja Psikolog">
                <i class="fas fa-clock"></i> <span>Jadwal Kerja Psikolog</span>
            </a>
        </li>
        <li>
            <a href="manage_invoices.php" class="<?php echo ($current_page == 'manage_invoices.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Invoice">
                <i class="fas fa-file-invoice-dollar"></i> <span>Invoice</span>
            </a>
        </li>
        <li>
            <a href="whatsapp_logs.php" class="<?php echo ($current_page == 'whatsapp_logs.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Log WhatsApp">
                <i class="fas fa-comments"></i> <span>Log WhatsApp</span>
            </a>
        </li>
        
        <li style="margin-top: 40px;">
            <a href="../auth/login.php?logout=1" style="background: #ff6b6b; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; cursor: pointer;" title="Logout">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>