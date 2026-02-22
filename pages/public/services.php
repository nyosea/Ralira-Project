<?php
$path = '../../';
$page_title = 'Layanan Kami - Biro Psikologi Rali Ra';
include $path . 'components/header.php';

// SIMULASI DATA LAYANAN
$services = [
    [
        "title" => "Konseling Individu (Dewasa)",
        "desc"  => "Sesi tatap muka atau online one-on-one dengan psikolog untuk mengatasi kecemasan, depresi, trauma, atau masalah pengembangan diri.",
        "img"   => "konselingpribadi.jpg",
        "tags"  => ["Dewasa", "Online/Offline", "Privasi"]
    ],
    [
        "title" => "Konseling Pernikahan & Pasangan",
        "desc"  => "Membantu pasangan menyelesaikan konflik, memperbaiki komunikasi, dan mempersiapkan pernikahan (Pre-marital counseling).",
        "img"   => "konselingpernikahan.jpg",
        "tags"  => ["Pasangan", "Keluarga", "Mediasi"]
    ],
    [
        "title" => "Asesmen Tumbuh Kembang Anak",
        "desc"  => "Pemeriksaan psikologis menyeluruh untuk mendeteksi keterlambatan bicara (speech delay), ADHD, Autisme, dan kesiapan sekolah.",
        "img"   => "assessment.jpg",
        "tags"  => ["Anak", "Observasi", "Laporan Resmi"]
    ],
    [
        "title" => "Psikotes Pendidikan & Minat Bakat",
        "desc"  => "Tes IQ dan penelusuran minat bakat untuk penjurusan SMA, pemilihan jurusan kuliah, hingga pemetaan karir.",
        "img"   => "psikotespendidikan.jpg",
        "tags"  => ["Remaja", "Sekolah", "Tes IQ"]
    ],
    [
        "title" => "Psikotes Industri & Rekrutmen",
        "desc"  => "Layanan B2B untuk perusahaan: Seleksi karyawan, promosi jabatan, dan asesmen kepemimpinan.",
        "img"   => "psikotespekerjaan.jpg",
        "tags"  => ["Perusahaan", "Massal", "HR"]
    ]
];
?>

<div style="background: var(--color-bg); padding-top: 100px; padding-bottom: 40px; text-align: center;">
    <h1 style="color: var(--color-text);">Daftar Layanan</h1>
    <p>Pilih layanan yang sesuai dengan kebutuhan Anda atau keluarga.</p>
</div>

<section style="padding: 20px 5% 60px;">
    <?php foreach($services as $index => $svc): ?>
    <div class="glass-panel" style="display: flex; flex-wrap: wrap; margin-bottom: 40px; align-items: center; overflow: hidden;">
        
        <div style="flex: 1; min-width: 300px; order: <?php echo ($index % 2 == 0) ? '1' : '2'; ?>;">
            <img src="<?php echo $path; ?>assets/img/services/<?php echo $svc['img']; ?>" 
                 alt="<?php echo $svc['title']; ?>" 
                 style="width: 100%; height: 300px; object-fit: cover;"
                 onerror="this.src='https://via.placeholder.com/600x400?text=Layanan+Rali+Ra'">
        </div>

        <div style="flex: 1; min-width: 300px; padding: 40px; order: <?php echo ($index % 2 == 0) ? '2' : '1'; ?>;">
            <h2 style="color: var(--color-primary); margin-bottom: 15px;"><?php echo $svc['title']; ?></h2>
            
            <div style="margin-bottom: 20px;">
                <?php foreach($svc['tags'] as $tag): ?>
                    <span style="background: rgba(229, 120, 30, 0.1); color: var(--color-accent); padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; margin-right: 5px; font-weight: 600;">
                        #<?php echo $tag; ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <p style="margin-bottom: 25px;"><?php echo $svc['desc']; ?></p>
            
            <div style="display: flex; gap: 15px; align-items: center;">
                <a href="../auth/login.php?ref=booking" class="btn-primary">Daftar Sekarang</a>
                
                <button onclick="document.querySelector('.chat-trigger').click()" class="glass-btn" style="border: 1px solid var(--color-brown);">
                    Tanya Harga
                </button>
            </div>
            <p style="font-size: 0.75rem; color: #888; margin-top: 10px;">*Harga layanan bersifat rahasia, silakan hubungi admin.</p>
        </div>

    </div>
    <?php endforeach; ?>
</section>

<?php include $path . 'components/footer.php'; ?>