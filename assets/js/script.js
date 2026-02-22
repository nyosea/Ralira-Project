/**
 * Filename: script.js
 * Description: Logic Global untuk Navigasi Mobile, Scroll Effect, dan Utilitas UI.
 * Context: Digunakan di semua halaman (Public, Admin, User, Psikolog).
 */

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initGlassEffects();
    initSidebarToggle(); // AKTIFKAN KEMBALI
});

// Buat fungsi global untuk onclick HTML
window.toggleSidebar = function() {
    const sidebar = document.querySelector('.sidebar') || 
                   document.getElementById('adminSidebar') ||
                   document.getElementById('sidebar');
    
    const overlay = document.querySelector('.sidebar-overlay') || 
                   document.getElementById('overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('mobile-active');
    }
    if (overlay) {
        overlay.classList.toggle('active');
    }
};

window.closeSidebar = function() {
    const sidebar = document.querySelector('.sidebar') || 
                   document.getElementById('adminSidebar') ||
                   document.getElementById('sidebar');
    
    const overlay = document.querySelector('.sidebar-overlay') || 
                   document.getElementById('overlay');
    
    if (sidebar) {
        sidebar.classList.remove('mobile-active');
    }
    if (overlay) {
        overlay.classList.remove('active');
    }
};

// Auto-close sidebar saat resize ke desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const sidebar = document.querySelector('.sidebar') || 
                       document.getElementById('adminSidebar') ||
                       document.getElementById('sidebar');
        
        const overlay = document.querySelector('.sidebar-overlay') || 
                       document.getElementById('overlay');
        
        if (sidebar) {
            sidebar.classList.remove('mobile-active');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
    }
});

/**
 * 1. Logic Navbar Responsif & Sticky Effect
 * Mengubah tampilan navbar saat di-scroll dan toggle menu di HP.
 */
function initNavbar() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');
    const header = document.querySelector('.main-header');

    // Toggle Menu Hamburger (Mobile)
    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            
            // Ubah icon hamburger menjadi 'X' jika aktif
            if (navLinks.classList.contains('active')) {
                mobileToggle.innerHTML = '&times;'; // Simbol X
            } else {
                mobileToggle.innerHTML = '&#9776;'; // Simbol Hamburger
            }
        });

        // Tutup menu jika link diklik (UX Improvement)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                mobileToggle.innerHTML = '&#9776;';
            });
        });
    }

    // Efek Glassmorphism saat Scroll
    window.addEventListener('scroll', () => {
        if (header) {
            if (window.scrollY > 50) {
                header.classList.add('glass-solid'); // Tambah blur & background kuat
                header.style.padding = '10px 5%'; // Perkecil padding (animasi halus)
            } else {
                header.classList.remove('glass-solid');
                header.style.padding = '15px 5%';
            }
        }
    });
}

/**
 * 2. Utilitas Tambahan
 * Menambahkan efek smooth scroll untuk semua anchor link
 */
function initGlassEffects() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * 3. Sidebar toggle (Admin, Psikolog, Klien)
 * - Menjamin sidebar muncul di semua halaman dashboard.
 * - Menambahkan tombol hamburger + overlay jika belum ada.
 */
function initSidebarToggle() {
    // Coba berbagai selector untuk sidebar
    const sidebar = document.querySelector('.sidebar') || 
                   document.getElementById('adminSidebar') ||
                   document.getElementById('sidebar');
    
    if (!sidebar) return; // Keluar jika tidak ada sidebar

    // Coba berbagai selector untuk overlay
    const overlay = document.querySelector('.sidebar-overlay') || 
                   document.getElementById('overlay');

        // MOBILE: Toggle Sidebar (Hamburger)
        function toggleSidebar() {
            sidebar.classList.toggle('mobile-active');
            overlay.classList.toggle('active');
        }

        // Close Sidebar (Mobile)
        function closeSidebar() {
            sidebar.classList.remove('mobile-active');
            overlay.classList.remove('active');
        }

        // Close sidebar ketika tekan ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        // Close sidebar saat resize ke desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-active');
                overlay.classList.remove('active');
            }
        });
}

    // Pastikan overlay tersedia
    // let overlay = document.querySelector('.sidebar-overlay');
    // if (!overlay) {
    //     overlay = document.createElement('div');
    //     overlay.className = 'sidebar-overlay';
    //     document.body.appendChild(overlay);
    // }

    
    // const closeSidebar = () => {
    //     sidebar.classList.remove('active');
    //     overlay.classList.remove('active');
    //     if (toggle) toggle.innerHTML = '&#9776;';
    //     document.body.classList.remove('no-scroll');
    // };

    // const openSidebar = () => {
    //     sidebar.classList.add('active');
    //     overlay.classList.add('active');
    //     if (toggle) toggle.innerHTML = '&times;';
    //     document.body.classList.add('no-scroll');
    // };

    // const toggleSidebar = () => {
    //     if (sidebar.classList.contains('active')) {
    //         closeSidebar();
    //     } else {
    //         openSidebar();
    //     }
    // };

    // toggle.addEventListener('click', toggleSidebar);
    // overlay.addEventListener('click', closeSidebar);

    // // Tutup sidebar jika link diklik (UX mobile)
    // sidebar.querySelectorAll('a').forEach(link => {
    //     link.addEventListener('click', closeSidebar);
    // });

    // // Sinkronisasi saat resize (desktop harus selalu terlihat)
    // const syncSidebarState = () => {
    //     // Desktop: biarkan sidebar apa adanya (selalu terlihat via CSS)
    //     // Mobile: tutup by default
    //     if (window.innerWidth > 768) {
    //         sidebar.classList.remove('active');
    //         overlay.classList.remove('active');
    //     } else {
    //         closeSidebar();
    //     }
    // };

    // // Set state awal sesuai viewport
    // syncSidebarState();
    // window.addEventListener('resize', syncSidebarState);


/**
 * 4. Mobile Menu Toggle untuk Landing Page & Auth Pages
 * Mengatur toggle menu mobile di header.php
 */
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileToggle = document.querySelector('[onclick="toggleMobileMenu()"]');
    
    if (mobileMenu) {
        mobileMenu.classList.toggle('hidden');
        
        // Ubah icon hamburger menjadi X jika menu terbuka
        if (mobileToggle) {
            if (mobileMenu.classList.contains('hidden')) {
                mobileToggle.innerHTML = '<i class="fa-solid fa-bars text-2xl"></i>';
            } else {
                mobileToggle.innerHTML = '<i class="fa-solid fa-times text-2xl"></i>';
            }
        }
        
        // Prevent body scroll saat menu terbuka
        document.body.classList.toggle('overflow-hidden');
    }
}

/**
 * 5. Mobile Search Toggle untuk Landing Page & Auth Pages
 * Mengatur toggle search mobile di header.php
 */
function toggleSearchMobile() {
    const mobileSearch = document.getElementById('mobile-search');
    
    if (mobileSearch) {
        mobileSearch.classList.toggle('hidden');
        
        // Focus ke input saat dibuka
        if (!mobileSearch.classList.contains('hidden')) {
            const searchInput = mobileSearch.querySelector('input');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        }
    }
}

/**
 * 6. Close mobile menu saat klik di luar
 */
document.addEventListener('click', (e) => {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileToggle = document.querySelector('[onclick="toggleMobileMenu()"]');
    
    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
        const isClickInsideMenu = mobileMenu.contains(e.target);
        const isClickOnToggle = mobileToggle && mobileToggle.contains(e.target);
        
        if (!isClickInsideMenu && !isClickOnToggle) {
            mobileMenu.classList.add('hidden');
            if (mobileToggle) {
                mobileToggle.innerHTML = '<i class="fa-solid fa-bars text-2xl"></i>';
            }
            document.body.classList.remove('overflow-hidden');
        }
    }
});