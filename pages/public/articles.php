<?php
$path = '../../';
$page_title = 'Artikel & Edukasi - Rali Ra';
include $path . 'components/header.php';

// SIMULASI DATA ARTIKEL
$articles = [
    [
        "title" => "Mengenal Speech Delay pada Anak",
        "date"  => "28 Nov 2025",
        "category" => "Tumbuh Kembang",
        "img"   => "content1.jpg",
        "snippet" => "Waspadai tanda-tanda keterlambatan bicara pada anak sejak dini. Ketahui kapan harus membawa anak ke psikolog..."
    ],
    [
        "title" => "Tips Mengelola Stres di Tempat Kerja",
        "date"  => "25 Nov 2025",
        "category" => "Kesehatan Mental",
        "img"   => "content2.jpg",
        "snippet" => "Beban kerja menumpuk? Simak 5 tips praktis untuk menjaga kesehatan mental Anda tetap stabil di lingkungan kerja..."
    ],
    [
        "title" => "Pentingnya 'Me Time' bagi Ibu Rumah Tangga",
        "date"  => "20 Nov 2025",
        "category" => "Parenting",
        "img"   => "content3.jpg",
        "snippet" => "Menjadi ibu adalah pekerjaan 24 jam. Mengapa meluangkan waktu untuk diri sendiri itu bukan hal yang egois?"
    ],
    [
        "title" => "Persiapan Mental Menjelang Pernikahan",
        "date"  => "15 Nov 2025",
        "category" => "Relationship",
        "img"   => "content4.jpg",
        "snippet" => "Bukan hanya pesta, persiapan mental pasangan adalah kunci langgengnya rumah tangga. Apa saja yang perlu dibahas?"
    ]
];
?>

<div style="padding: 120px 5% 40px; text-align: center;">
    <h1 style="color: var(--color-text);">Artikel & Edukasi</h1>
    <p style="margin-bottom: 30px;">Update wawasan kesehatan mental Anda bersama tim Rali Ra.</p>
    
    <div style="max-width: 500px; margin: 0 auto; position: relative;">
        <input type="text" placeholder="Cari artikel (misal: parenting, stres)..." class="glass-input search-input" style="width: 100%; padding-right: 50px;">
        <button style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem;">🔍</button>
    </div>
</div>

<section style="padding: 0 5% 40px;">
    <div class="glass-panel" style="display: flex; flex-wrap: wrap; overflow: hidden;">
        <div style="flex: 2; min-width: 300px;">
            <img src="<?php echo $path; ?>assets/img/content/content1.jpg" alt="Featured" style="width: 100%; height: 100%; object-fit: cover; min-height: 300px;">
        </div>
        <div style="flex: 1; min-width: 300px; padding: 40px; display: flex; flex-direction: column; justify-content: center;">
            <span class="badge badge-success" style="background: var(--color-accent); width: fit-content; margin-bottom: 10px;">Terpopuler</span>
            <h2 style="color: var(--color-primary); margin-bottom: 15px;"><?php echo $articles[0]['title']; ?></h2>
            <p style="margin-bottom: 20px;"><?php echo $articles[0]['snippet']; ?></p>
            <a href="#" class="btn-primary" style="align-self: start;">Baca Selengkapnya</a>
        </div>
    </div>
</section>

<section style="padding: 20px 5% 60px;">
    <h3 style="margin-bottom: 20px; border-left: 5px solid var(--color-primary); padding-left: 15px;">Artikel Terbaru</h3>
    
    <div class="services-grid">
        <?php foreach($articles as $art): ?>
        <div class="service-card glass-panel" style="text-align: left;">
            <div style="height: 180px; overflow: hidden;">
                <img src="<?php echo $path; ?>assets/img/content/<?php echo $art['img']; ?>" 
                     alt="<?php echo $art['title']; ?>"
                     style="transition: 0.3s; width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="service-info" style="padding: 20px;">
                <div style="font-size: 0.8rem; color: #888; margin-bottom: 5px; display: flex; justify-content: space-between;">
                    <span>📅 <?php echo $art['date']; ?></span>
                    <span style="color: var(--color-accent); font-weight: 600;"><?php echo $art['category']; ?></span>
                </div>
                <h4 style="margin-bottom: 10px; color: var(--color-text); line-height: 1.4;">
                    <a href="#" style="text-decoration: none;"><?php echo $art['title']; ?></a>
                </h4>
                <p style="font-size: 0.9rem; color: #666; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php echo $art['snippet']; ?>
                </p>
                <div style="margin-top: 15px;">
                    <a href="#" style="color: var(--color-primary); font-weight: 600; font-size: 0.9rem;">Baca →</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 40px; text-align: center;">
        <button class="glass-btn" disabled>❮</button>
        <button class="btn-primary" style="padding: 8px 15px;">1</button>
        <button class="glass-btn" style="padding: 8px 15px;">2</button>
        <button class="glass-btn" style="padding: 8px 15px;">3</button>
        <button class="glass-btn">❯</button>
    </div>
</section>

<?php include $path . 'components/footer.php'; ?>