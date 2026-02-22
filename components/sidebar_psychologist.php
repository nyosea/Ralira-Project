<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<div class="mobile-toggle" data-sidebar-action="open" onclick="openSidebar()" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; position: fixed; top: 0; left: 0; width: 100%; height: 50px; border-bottom: 1px solid rgba(255,255,255,0.2); cursor: pointer; font-size: 1.3rem; z-index: 950; display: flex; align-items: center; justify-content: center; padding: 0 15px;">
    <img src="<?php echo $path; ?>assets/img/logo.png" alt="Rali Ra" style="height: 28px; width: auto; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.25));">
</div>

<div class="sidebar-overlay" onclick="closeSidebar()"></div>

<aside class="sidebar glass-solid" id="psychologistSidebar">
    <button class="sidebar-close" onclick="closeSidebar()">
        <i class="fas fa-arrow-left"></i>
    </button>
    
    <!-- Logo Button for Desktop/Mobile -->
    <div class="sidebar-header">
        <button class="sidebar-logo-toggle" onclick="toggleSidebarDesktop('psychologistSidebar')" title="Collapse/Expand">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Psikolog Rali Ra" style="width: 50px;">
        </button>
        <h4 style="color: var(--color-text);">Dr. Psikolog</h4>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Dashboard">
                <i class="fas fa-chart-line"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="schedule.php" class="<?php echo ($current_page == 'schedule.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Atur Jadwal Praktik">
                <i class="fas fa-calendar-check"></i> <span>Atur Jadwal Praktik</span>
            </a>
        </li>
        <li>
            <a href="clients_list.php" class="<?php echo ($current_page == 'clients_list.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Daftar Klien">
                <i class="fas fa-list"></i> <span>Daftar Klien</span>
            </a>
        </li>
        <li>
            <a href="upload_result.php" class="<?php echo ($current_page == 'upload_result.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Upload Hasil Tes">
                <i class="fas fa-upload"></i> <span>Upload Hasil Tes</span>
            </a>
        </li>
        <li>
            <a href="profile_edit.php" class="<?php echo ($current_page == 'profile_edit.php') ? 'active' : ''; ?>" onclick="closeSidebar()" title="Edit Profil">
                <i class="fas fa-user-edit"></i> <span>Edit Profil</span>
            </a>
        </li>
        
        <li style="margin-top: 40px;">
            <a href="../auth/login.php?logout=1" style="background: #ff6b6b; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; cursor: pointer;" title="Logout">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>