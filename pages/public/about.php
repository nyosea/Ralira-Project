<?php
$path = '../../';
$page_title = 'Tentang Kami - Profil & Tim Psikolog';
include $path . 'components/header.php';

// Tambahkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ambil data psikolog dari database
require_once $path . 'includes/db.php';
$db = new Database();
$db->connect();

// Perbaiki query SQL untuk menggunakan foto dari landing page
$sql = "SELECT u.name, u.email, u.phone, pp.spesialisasi, pp.nomor_sipp, pp.bio, pp.photo, pp.pengalaman_tahun 
        FROM users u 
        LEFT JOIN psychologist_profiles pp ON u.user_id = pp.user_id 
        WHERE u.role = 'psychologist' 
        ORDER BY pp.pengalaman_tahun DESC, u.name ASC";

// Ambil data psikolog
$team = $db->query($sql);
?>

<div style="background: var(--color-primary); padding: 120px 5% 60px; text-align: center; color: var(--color-text);">
    <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Tentang Rali Ra</h1>
    <p style="font-size: 1.2rem;">Sahabat Psikologi Keluarga Anda di Tanah Papua</p>
</div>

<section style="padding: 60px 5%;">
    <div class="glass-panel" style="padding: 40px; display: flex; flex-wrap: wrap; align-items: center; gap: 40px;">
        <div style="flex: 1; min-width: 300px;">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Filosofi Rali Ra" style="width: 100%; max-width: 300px; margin: 0 auto; display: block;">
        </div>
        <div style="flex: 2; min-width: 300px;">
            <h2 style="color: var(--color-accent); margin-bottom: 20px;">Makna "Rali Ra"</h2>
            <p style="margin-bottom: 15px;">
                Dalam bahasa lokal Sentani, <strong>"Rali"</strong> berarti Matahari/Terbit dan <strong>"Ra"</strong> berarti Timur. 
                Secara filosofis, Rali Ra bermakna <em>"Terbit dari Timur"</em>.
            </p>
            <p>
                Kami percaya bahwa setiap individu memiliki harapan baru layaknya matahari terbit. 
                Kami hadir untuk menerangi sisi gelap kehidupan mental Anda dan membimbing menuju 
                kesejahteraan psikologis yang lebih baik.
            </p>
        </div>
    </div>
</section>

<section style="padding: 40px 5%;">
    <div style="text-align: center; margin-bottom: 50px;">
        <h2 style="color: var(--color-text);">Tim Psikolog Kami</h2>
        <p>Profesional, Berlisensi (SIPP), dan Berpengalaman.</p>
    </div>

    <div class="services-grid"> 
        <?php if(empty($team)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; border: 2px solid #dee2e6;">
                <i class="fas fa-user-slash" style="font-size: 48px; color: #6c757d; margin-bottom: 20px;"></i>
                <h4 style="color: #6c757d; margin: 0; font-size: 18px;">Belum ada psikolog tersedia</h4>
                <p style="color: #868e96; margin: 10px 0 0 0; font-size: 14px;">Tim psikolog kami akan segera tersedia</p>
            </div>
        <?php else: ?>
            <?php foreach($team as $psikolog): ?>
                <div class="service-card glass-panel" style="text-align: left;">
                    <div class="service-info" style="padding: 25px;">
                        <h3 style="font-size: 1.5rem; color: var(--color-primary); margin-bottom: 15px;">
                            <?php echo $psikolog['name']; ?>
                        </h3>
                        <div style="background: var(--color-primary); color: white; padding: 5px 12px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 0.9rem;">
                            <?php echo $psikolog['spesialisasi']; ?>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <strong style="color: var(--color-text);">SIPP:</strong>
                            <span style="color: var(--color-text-light); margin-left: 8px;"><?php echo $psikolog['nomor_sipp']; ?></span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: var(--color-text); display: block; margin-bottom: 5px;">Deskripsi:</strong>
                            <p style="color: var(--color-text-light); line-height: 1.5; margin: 0;"><?php echo $psikolog['bio']; ?></p>
                        </div>
                        <a href="../../pages/auth/login.php?ref=booking&psikolog=<?php echo urlencode($psikolog['name']); ?>" 
                           class="btn-primary" 
                           style="width: 100%; text-align: center; display: block; padding: 12px; text-decoration: none; border-radius: 8px;">
                           <i class="fas fa-calendar-check" style="margin-right: 8px;"></i>Buat Janji
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include $path . 'components/footer.php'; ?>
