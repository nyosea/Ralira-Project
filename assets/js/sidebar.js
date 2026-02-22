// Sidebar Utilities
let touchStartX = 0;
let touchEndX = 0;

// Breakpoint constants (must match CSS)
const MOBILE_BREAKPOINT = 768;

function isMobile() {
    return window.innerWidth <= MOBILE_BREAKPOINT;
}

function isDesktop() {
    return window.innerWidth > MOBILE_BREAKPOINT;
}

function getActiveSidebarElement() {
    return document.getElementById('adminSidebar') || 
           document.getElementById('psychologistSidebar') || 
           document.getElementById('clientSidebar') ||
           document.querySelector('.sidebar');
}

function setHamburgerState(isOpen) {
    const toggles = document.querySelectorAll('.mobile-toggle, .mobile-toggle-inline');
    if (!toggles || toggles.length === 0) return;
    toggles.forEach(toggle => {
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-bars', 'fa-times');
            icon.classList.add(isOpen ? 'fa-times' : 'fa-bars');
        }
    });
}

function getSidebarCollapsedKey(sidebarId) {
    if (sidebarId) return `sidebarCollapsed:${sidebarId}`;
    return 'sidebarCollapsed';
}

function isSidebarCollapsed(sidebarId) {
    const key = getSidebarCollapsedKey(sidebarId);
    const val = localStorage.getItem(key);
    if (val !== null) return val === 'true';
    return localStorage.getItem('sidebarCollapsed') === 'true';
}

function toggleSidebarDesktop(sidebarId = null) {
    if (isMobile()) {
        toggleSidebar();
        return;
    }

    const sidebar = sidebarId ? document.getElementById(sidebarId) : getActiveSidebarElement();
    if (!sidebar) return;

    sidebar.classList.toggle('collapsed');
    localStorage.setItem(getSidebarCollapsedKey(sidebar.id), sidebar.classList.contains('collapsed') ? 'true' : 'false');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? 'true' : 'false');
}

// Toggle sidebar for mobile (hamburger menu)
function toggleSidebar() {
    const sidebar = getActiveSidebarElement();
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar || !overlay) return;
    
    const isActive = sidebar.classList.contains('mobile-active');
    
    if (isActive) {
        sidebar.classList.remove('mobile-active');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        setHamburgerState(false);
    } else {
        sidebar.classList.add('mobile-active');
        overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
        setHamburgerState(true);
    }
}

// Handle window resize - adjust sidebar behavior when switching between breakpoints
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    // If switching to desktop, restore collapsed state from localStorage
    if (isDesktop()) {
        if (sidebar) sidebar.classList.remove('mobile-active');
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        setHamburgerState(false);
        
        // Restore collapsed state if was saved
        const isCollapsed = isSidebarCollapsed(sidebar ? sidebar.id : null);
        if (isCollapsed && sidebar) {
            sidebar.classList.add('collapsed');
        }
    }
    
    // If switching to mobile, close sidebar if open
    if (isMobile()) {
        if (sidebar) {
            sidebar.classList.remove('mobile-active');
            sidebar.classList.remove('collapsed');
        }
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        setHamburgerState(false);
    }
});
