<?php
// pages/public/landing.php
$path = '../../';
$page_title = 'Beranda - Terbit dari Timur Terangi Kehidupan';
include $path . 'components/header.php';

// Include database untuk load data psikolog
require_once $path . 'includes/db.php';
$db = new Database();
$db->connect();

// DATA DUMMY LAYANAN (Sesuai Referensi Gambar 1)
$services = [
    [
        "title" => "Psikologi Anak",
        "desc" => "Membantu mengatasi masalah tumbuh kembang, emosi, dan perilaku pada anak dengan pendekatan bermain.",
        "icon" => "fa-child-reaching",
        "color_bg" => "bg-[#FFF4E0]", 
        "color_text" => "text-[#E5781E]" 
    ],
    [
        "title" => "Konseling Remaja",
        "desc" => "Pendampingan pencarian jati diri, masalah akademik, dan hubungan sosial remaja.",
        "icon" => "fa-user-graduate",
        "color_bg" => "bg-[#FFF9C4]", 
        "color_text" => "text-[#FBBA00]" 
    ],
    [
        "title" => "Konseling Keluarga",
        "desc" => "Harmonisasi hubungan keluarga dan penyelesaian konflik rumah tangga.",
        "icon" => "fa-people-roof",
        "color_bg" => "bg-[#E3F2FD]", 
        "color_text" => "text-[#4A90E2]" 
    ],
    [
        "title" => "Asesmen & Psikotes",
        "desc" => "Tes IQ, Minat Bakat, Kesiapan Sekolah, dan Rekrutmen Karyawan dengan hasil akurat.",
        "icon" => "fa-clipboard-check",
        "color_bg" => "bg-[#E8F5E9]", 
        "color_text" => "text-[#66BB6A]" 
    ]
];

// DATA DUMMY TIM (Sesuai Referensi Gambar 2)
// Load dari database psychologist_profiles (psikolog yang ditampilkan di landing page)
$team = [];
try {
    $sql = "SELECT u.name, pp.spesialisasi as role, 
                   CONCAT(pp.pengalaman_tahun, '+ Tahun Pengalaman') as exp, 
                   pp.foto_profil as img,
                   pp.nomor_sipp
            FROM users u
            INNER JOIN psychologist_profiles pp ON u.user_id = pp.user_id
            WHERE u.role = 'psychologist' 
            AND pp.show_on_landing = 1
            AND pp.foto_profil IS NOT NULL AND pp.foto_profil != ''
            ORDER BY pp.created_at ASC 
            LIMIT 4";
    $result = $db->query($sql);
    if (is_array($result)) {
        $team = $result;
    }
    
    // Debug: Tampilkan data di browser console
    echo "<script>console.log('Landing photos data:', " . json_encode($team) . ");</script>";
    
} catch (Exception $e) {
    // Debug: Tampilkan error
    echo "<script>console.log('Database error:', " . json_encode($e->getMessage()) . ");</script>";
    
    // Fallback ke dummy data jika error
    $team = [
        ["name" => "Dr. Ira Puspitawati", "role" => "Psikolog Anak & Owner", "exp" => "30+ Tahun Pengalaman", "img" => "psikolog1.jpg", "nomor_sipp" => "0506-22-2-2"],
        ["name" => "Bu Nurul", "role" => "Psikolog Industri", "exp" => "Spesialis Rekrutmen", "img" => "psikolog2.jpg", "nomor_sipp" => "0506-22-3-3"],
        ["name" => "Bu Claudia", "role" => "Psikolog Remaja", "exp" => "Konseling Online", "img" => "psikolog3.jpg", "nomor_sipp" => "0506-22-4-4"],
        ["name" => "Pak Refandi", "role" => "Psikolog Dewasa", "exp" => "Masalah Karir", "img" => "psikolog4.jpg", "nomor_sipp" => "0506-22-5-5"]
    ];
}

?>

<section id="home" class="pt-36 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen flex items-center relative overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center w-full max-w-6xl mx-auto">
        
        <div class="space-y-8 z-10 animate-fade-in-up">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-ralira-primary/10 text-ralira-text border border-ralira-primary/20 backdrop-blur-sm">
                <i class="fa-solid fa-sparkles text-ralira-accent"></i>
                <span class="text-sm font-semibold">Kesehatan Mental Anda Prioritas Kami</span>
            </div>
            
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight text-ralira-text">
                Terbit dari Timur, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-ralira-accent to-ralira-primary drop-shadow-sm">
                    Terangi Kehidupan
                </span>
            </h1>
            
            <p class="text-lg md:text-xl text-ralira-text/80 leading-relaxed max-w-lg">
                Selamat datang di Biro Psikologi Rali Ra. Kami hadir dengan pendekatan yang hangat, ceria, dan family-friendly untuk membantu setiap langkah perjalanan kesehatan mental Anda.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 pt-2">
                <a href="<?php echo $path; ?>pages/auth/login.php" 
                    class="px-8 py-4 bg-ralira-accent text-white rounded-full font-bold shadow-lg shadow-ralira-accent/30 hover:bg-[#d66a15] hover:scale-105 transition transform flex items-center justify-center gap-3">
                    Mulai Konsultasi Sekarang <i class="fa-solid fa-arrow-right"></i>
                </a>
                <button onclick="openWhatsApp()" class="px-8 py-4 bg-white/60 border border-white text-ralira-text rounded-full font-bold hover:bg-white transition flex items-center justify-center gap-3 backdrop-blur-sm">
                    <i class="fa-brands fa-whatsapp text-green-600 text-xl"></i> Hubungi Admin
                </button>
            </div>
            
            <div class="grid grid-cols-3 gap-8 pt-8 border-t border-ralira-text/10 max-w-md">
                <div>
                    <h3 class="text-3xl font-bold text-ralira-primary">5+</h3>
                    <p class="text-sm text-ralira-text/70 font-medium">Psikolog Senior</p>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-ralira-primary">500+</h3>
                    <p class="text-sm text-ralira-text/70 font-medium">Klien Terbantu</p>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-ralira-primary">4.9</h3>
                    <p class="text-sm text-ralira-text/70 font-medium">Rating Kepuasan</p>
                </div>
            </div>
        </div>

        <div class="relative hidden lg:block h-full min-h-[500px]">
            <div class="absolute top-0 right-0 w-96 h-96 bg-ralira-primary rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
            <div class="absolute bottom-0 left-10 w-96 h-96 bg-ralira-accent rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 2s"></div>
            
            <div class="relative glass-card rounded-[2.5rem] p-4 transform rotate-2 hover:rotate-0 transition duration-700 ease-in-out">
                <div class="bg-white/50 rounded-[2rem] w-full h-[450px] flex items-center justify-center overflow-hidden relative shadow-inner">
                    
                    <img src="<?php echo $path; ?>assets/img/background.png" alt="Ilustrasi Rali Ra" class="absolute inset-0 w-full h-full object-cover opacity-90 hover:scale-105 transition duration-700">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-white/80 to-transparent flex flex-col justify-end p-8 text-center">
                        <!-- Tulisan dan icon dihapus -->
                    </div>

                    <div class="absolute bottom-8 right-8 bg-white/90 backdrop-blur-xl px-5 py-3 rounded-2xl flex items-center gap-4 shadow-lg border border-white animate-bounce" style="animation-duration: 3s;">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-lg">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-xs text-ralira-text/60 font-semibold uppercase">Status</p>
                            <p class="text-sm font-extrabold text-ralira-text">Open for Booking</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="layanan" class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-ralira-text mb-3">Layanan Kami</h2>
                <p class="text-ralira-text/70 max-w-xl text-lg">Berbagai layanan profesional untuk kebutuhan psikologis Anda dan keluarga.</p>
            </div>
        </div>

        <div class="relative">
            <!-- Scroll Navigation Buttons -->
            <button onclick="scrollServices('left')" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-20 w-12 h-12 bg-white/90 backdrop-blur-xl rounded-full shadow-lg flex items-center justify-center text-ralira-primary hover:bg-white hover:scale-110 transition-all duration-300 border border-white/50">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button onclick="scrollServices('right')" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-20 w-12 h-12 bg-white/90 backdrop-blur-xl rounded-full shadow-lg flex items-center justify-center text-ralira-primary hover:bg-white hover:scale-110 transition-all duration-300 border border-white/50">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div id="service-slider" class="flex gap-8 overflow-x-auto hide-scroll snap-x snap-mandatory py-4 px-2 scroll-smooth">
                <?php foreach($services as $svc): ?>
                <div class="min-w-[300px] md:min-w-[350px] glass-card rounded-3xl p-8 snap-center flex flex-col justify-between h-[400px] group cursor-pointer relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 <?php echo $svc['color_bg']; ?> rounded-bl-full opacity-50 group-hover:scale-150 transition duration-500"></div>

                    <div class="relative z-10">
                        <div class="w-16 h-16 rounded-2xl <?php echo $svc['color_bg']; ?> <?php echo $svc['color_text']; ?> flex items-center justify-center text-3xl mb-6 group-hover:rotate-6 transition duration-300 shadow-sm">
                            <i class="fa-solid <?php echo $svc['icon']; ?>"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-ralira-text mb-3"><?php echo $svc['title']; ?></h3>
                        <p class="text-ralira-text/70 leading-relaxed"><?php echo $svc['desc']; ?></p>
                    </div>
                    
                    <a href="services.php" class="inline-flex items-center gap-2 <?php echo $svc['color_text']; ?> font-bold text-sm hover:underline mt-4 group-hover:translate-x-2 transition">
                        Selengkapnya <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section id="psikolog" class="py-20 bg-white/40 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 max-w-2xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-ralira-text mb-4">Tim Profesional Kami</h2>
            <p class="text-lg text-ralira-text/70">Psikolog berpengalaman yang siap mendengarkan dan membantu permasalahan Anda.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:overflow-x-auto md:flex md:gap-6 md:pb-4 md:snap-x md:snap-mandatory md:px-2 lg:grid lg:overflow-visible">
            <?php foreach($team as $member): ?>
            <div class="glass-card rounded-3xl p-6 text-center group hover:bg-white/80 transition duration-300 min-w-[280px] md:snap-center flex-shrink-0">
                <div class="w-32 h-32 mx-auto rounded-full bg-gray-200 mb-6 p-1 border-2 border-white shadow-lg relative group-hover:scale-105 transition duration-300">
                    <?php 
                    $photo_path = $path . 'assets/img/' . $member['img'];
                    if ($member['img'] && file_exists($photo_path)): ?>
                        <img src="<?php echo $photo_path; ?>" class="w-full h-full object-cover rounded-full">
                    <?php else: ?>
                        <div class="w-full h-full bg-ralira-text/5 rounded-full flex items-center justify-center text-4xl text-ralira-text/30">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="absolute bottom-2 right-2 w-5 h-5 bg-green-500 border-2 border-white rounded-full" title="Tersedia"></div>
                </div>
                
                <h3 class="font-bold text-xl text-ralira-text mb-1"><?php echo $member['name']; ?></h3>
                <p class="text-xs text-ralira-accent font-bold uppercase tracking-wide mb-2 bg-ralira-accent/10 py-1 px-2 rounded-full inline-block">
                    <?php echo $member['role']; ?>
                </p>
                <?php if (!empty($member['nomor_sipp'])): ?>
                <p class="text-xs text-ralira-text/50 font-medium mb-1 flex items-center justify-center gap-1">
                    <i class="fa-solid fa-certificate text-ralira-primary"></i>
                    SIPP: <?php echo htmlspecialchars($member['nomor_sipp']); ?>
                </p>
                <?php endif; ?>
                <p class="text-sm text-ralira-text/60"><?php echo $member['exp']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include $path . 'components/footer.php'; ?>

<script>
// Slider functionality
function scrollSlider(direction) {
    const slider = document.getElementById('service-slider');
    const scrollAmount = 300; // Adjust scroll amount as needed
    
    if (direction === 'left') {
        slider.scrollLeft -= scrollAmount;
    } else {
        slider.scrollLeft += scrollAmount;
    }
}

// WhatsApp Integration
function openWhatsApp() {
    const phoneNumber = '+6281293605651'; // Admin WhatsApp number
    const message = encodeURIComponent('Halo Admin Rali Ra, saya ingin bertanya tentang layanan psikologi.');
    const whatsappUrl = `https://wa.me/${phoneNumber.replace(/[^\d]/g, '')}?text=${message}`;
    window.open(whatsappUrl, '_blank');
}

// Service Slider Scroll Function
function scrollServices(direction) {
    const slider = document.getElementById('service-slider');
    const scrollAmount = 400; // Adjust scroll amount as needed
    
    if (direction === 'left') {
        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Auto-hide scroll buttons on mobile
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('service-slider');
    const leftBtn = slider.parentElement.querySelector('button:first-child');
    const rightBtn = slider.parentElement.querySelector('button:last-child');
    
    // Hide buttons on mobile (screen width < 768px)
    function toggleButtons() {
        if (window.innerWidth < 768) {
            leftBtn.style.display = 'none';
            rightBtn.style.display = 'none';
        } else {
            leftBtn.style.display = 'flex';
            rightBtn.style.display = 'flex';
        }
    }
    
    toggleButtons();
    window.addEventListener('resize', toggleButtons);
});
</script>
