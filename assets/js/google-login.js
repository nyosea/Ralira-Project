/**
 * Filename: google-login.js
 * Description: Handler untuk tombol "Masuk dengan Google".
 * Note: Ini adalah simulasi Frontend. Integrasi OAuth 2.0 sesungguhnya membutuhkan Backend.
 * Requirement: User login via Google Account.
 */

document.addEventListener('DOMContentLoaded', () => {
    const googleBtns = document.querySelectorAll('.btn-google');

    googleBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            handleGoogleLogin(btn);
        });
    });
});

function handleGoogleLogin(buttonElement) {
    // 1. Visual Feedback (Loading State)
    const originalText = buttonElement.innerHTML;
    buttonElement.innerHTML = 'Memproses...';
    buttonElement.style.opacity = '0.7';
    buttonElement.disabled = true;

    // 2. Simulasi Request API (Delay 2 Detik)
    setTimeout(() => {
        // Mock Data User (Simulasi data balik dari Google)
        const mockUser = {
            name: "User Rali Ra",
            email: "user@example.com",
            photo: "assets/img/default-avatar.png"
        };

        // Simpan ke LocalStorage sementara (untuk demo prototype)
        localStorage.setItem('user_logged_in', JSON.stringify(mockUser));

        alert(`Login Berhasil! Selamat datang, ${mockUser.name}`);
        
        // Redirect berdasarkan role (Logic sementara: Redirect ke Dashboard Client)
        // Di real app, ini dihandle oleh server session
        window.location.href = '../../pages/client/dashboard.php';

    }, 2000);
}