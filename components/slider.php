<?php
/**
 * Filename: components/slider.php
 * Description: Carousel Slider untuk Landing Page (Hero Section).
 * Requirement: Fitur Swipe di Mobile [Source: 666]
 */
?>
<div class="slider-container glass-panel" style="overflow: hidden; position: relative; border-radius: var(--border-radius);">
    <div class="slider-wrapper" style="display: flex; transition: transform 0.5s ease-in-out;">
        
        <div class="slide" style="min-width: 100%; position: relative;">
            <img src="<?php echo $path; ?>assets/img/content/content1.jpg" alt="Selamat Datang" style="width: 100%; height: 400px; object-fit: cover;">
            <div class="slide-caption glass-solid" style="position: absolute; bottom: 20px; left: 20px; padding: 15px 25px; border-radius: 12px; max-width: 80%;">
                <h2 style="color: var(--color-primary);">Selamat Datang di Rali Ra</h2>
                <p style="color: var(--color-text);">Biro Psikologi yang hangat dan terpercaya dari Timur Indonesia.</p>
            </div>
        </div>

        <div class="slide" style="min-width: 100%; position: relative;">
            <img src="<?php echo $path; ?>assets/img/content/content2.jpg" alt="Layanan Anak" style="width: 100%; height: 400px; object-fit: cover;">
            <div class="slide-caption glass-solid" style="position: absolute; bottom: 20px; left: 20px; padding: 15px 25px; border-radius: 12px; max-width: 80%;">
                <h2 style="color: var(--color-accent);">Tumbuh Kembang Anak</h2>
                <p>Deteksi dini dan konseling tumbuh kembang buah hati Anda.</p>
            </div>
        </div>

        <div class="slide" style="min-width: 100%; position: relative;">
            <img src="<?php echo $path; ?>assets/img/content/content3.jpg" alt="Psikotes" style="width: 100%; height: 400px; object-fit: cover;">
            <div class="slide-caption glass-solid" style="position: absolute; bottom: 20px; left: 20px; padding: 15px 25px; border-radius: 12px; max-width: 80%;">
                <h2 style="color: var(--color-primary);">Psikotes Online & Offline</h2>
                <p>Tes minat bakat, IQ, dan kesiapan sekolah dengan hasil cepat.</p>
            </div>
        </div>

    </div>

    <button id="prevBtn" class="glass-btn" style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%); z-index: 10;">❮</button>
    <button id="nextBtn" class="glass-btn" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); z-index: 10;">❯</button>
</div>