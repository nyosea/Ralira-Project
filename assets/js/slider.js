/**
 * Filename: slider.js
 * Description: Logic Slider/Carousel dengan dukungan Touch Swipe (Mobile-First).
 * Context: Digunakan di Landing Page untuk menampilkan info klinik/layanan.
 * Requirement: Tampilan di HP tidak terlalu panjang[cite: 521].
 */

document.addEventListener('DOMContentLoaded', () => {
    const sliderContainer = document.querySelector('.slider-container');
    
    // Hanya jalankan jika elemen slider ada di halaman
    if (sliderContainer) {
        initSlider(sliderContainer);
    }
});

function initSlider(container) {
    const wrapper = container.querySelector('.slider-wrapper');
    const slides = container.querySelectorAll('.slide');
    const prevBtn = container.querySelector('#prevBtn');
    const nextBtn = container.querySelector('#nextBtn');
    
    let currentIndex = 0;
    const totalSlides = slides.length;
    let autoPlayInterval;

    // --- Core Navigation Logic ---
    
    const showSlide = (index) => {
        // Validasi Index (Looping)
        if (index >= totalSlides) currentIndex = 0;
        else if (index < 0) currentIndex = totalSlides - 1;
        else currentIndex = index;

        // Geser wrapper menggunakan CSS Transform
        wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
    };

    const nextSlide = () => {
        showSlide(currentIndex + 1);
        resetAutoPlay();
    };

    const prevSlide = () => {
        showSlide(currentIndex - 1);
        resetAutoPlay();
    };

    // --- Event Listeners (Click) ---
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);

    // --- Auto Play Feature ---
    const startAutoPlay = () => {
        autoPlayInterval = setInterval(() => {
            showSlide(currentIndex + 1);
        }, 5000); // Ganti slide setiap 5 detik
    };

    const resetAutoPlay = () => {
        clearInterval(autoPlayInterval);
        startAutoPlay();
    };

    // --- Touch Swipe Logic (Mobile Support) ---
    // Fitur ini penting untuk akses via Android/iPhone/iPad
    let touchStartX = 0;
    let touchEndX = 0;

    container.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    container.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        resetAutoPlay();
    }, { passive: true });

    const handleSwipe = () => {
        const threshold = 50; // Jarak minimum geser untuk trigger
        if (touchEndX < touchStartX - threshold) {
            nextSlide(); // Swipe Kiri -> Next
        }
        if (touchEndX > touchStartX + threshold) {
            prevSlide(); // Swipe Kanan -> Prev
        }
    };

    // Mulai Slider
    startAutoPlay();
}