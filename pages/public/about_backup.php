<?php
$path = '../../';
$page_title = 'Tentang Kami - Profil & Tim Psikolog';
include $path . 'components/header.php';

// Ambil data psikolog dari database
require_once $path . 'includes/db.php';
$db = new Database();
$db->connect();

$sql = "SELECT u.name, u.email, u.phone, pp.spesialisasi, pp.nomor_sipp, pp.bio, pp.foto_profil, pp.pengalaman_tahun, pp.bersedia_konsul 
        FROM users u 
        LEFT JOIN psychologist_profiles pp ON u.user_id = pp.user_id 
        WHERE u.role = 'psychologist' AND u.status = 'active' 
        ORDER BY pp.pengalaman_tahun DESC, u.name ASC";

$team = $db->query($sql);

if (!$team) {
    $team = [];
}
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
                <div class="service-card glass-panel psychologist-card" style="text-align: left; position: relative; overflow: hidden;">
                    <!-- Status Badge -->
                    <?php 
                    $status = $psikolog['bersedia_konsul'] ?? 'Offline & Online';
                    $is_available = !in_array($status, ['Tidak Tersedia']);
                    if ($is_available): 
                    ?>
                    <div style="position: absolute; top: 15px; right: 15px; background: <?php echo ($status === 'By Appointment') ? '#ffc107' : '#28a745'; ?>; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 2; display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-<?php echo ($status === 'By Appointment') ? 'clock' : 'check-circle'; ?>" style="font-size: 10px;"></i>
                        <?php echo htmlspecialchars($status); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Photo Section -->
                    <div style="height: 280px; overflow: hidden; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); position: relative;">
                        <?php if(!empty($psikolog['foto_profil'])): ?>
                            <img src="<?php echo $path; ?>assets/img/<?php echo $psikolog['foto_profil']; ?>" 
                                 alt="<?php echo htmlspecialchars($psikolog['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                 onerror="this.src='https://via.placeholder.com/400x300?text=Foto+Psikolog'">
                        <?php else: ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);">
                                <div style="text-align: center; color: white;">
                                    <i class="fas fa-user-nurse" style="font-size: 48px; margin-bottom: 10px;"></i>
                                    <p style="margin: 0; font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($psikolog['name']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="service-info" style="padding: 25px;">
                        <!-- Name & SIPP -->
                        <h3 style="font-size: 1.2rem; color: var(--color-primary); margin: 0 0 8px 0; line-height: 1.3;">
                            <?php echo htmlspecialchars($psikolog['name']); ?>
                        </h3>
                        
                        <!-- SIPP Badge -->
                        <div style="margin-bottom: 15px;">
                            <span class="badge badge-warning" style="background: var(--color-eggshell); color: var(--color-brown); border: 1px solid var(--color-brown); display: inline-block; padding: 4px 10px; font-size: 0.75rem; border-radius: 15px; font-weight: 600;">
                                <i class="fas fa-certificate" style="margin-right: 4px; font-size: 10px;"></i>
                                <?php echo htmlspecialchars($psikolog['nomor_sipp'] ?? 'SIPP Terdaftar'); ?>
                            </span>
                        </div>
                        
                        <!-- Specialization -->
                        <?php if(!empty($psikolog['spesialisasi'])): ?>
                        <div style="margin-bottom: 12px;">
                            <p style="font-weight: 600; color: var(--color-text-light); margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-stethoscope" style="color: var(--color-primary); font-size: 12px;"></i>
                                <?php echo htmlspecialchars($psikolog['spesialisasi']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Experience -->
                        <?php if(!empty($psikolog['pengalaman_tahun'])): ?>
                        <div style="margin-bottom: 12px;">
                            <p style="color: var(--color-text-light); margin: 0; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-clock" style="color: var(--color-primary); font-size: 12px;"></i>
                                <?php echo $psikolog['pengalaman_tahun']; ?> tahun pengalaman
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Bio -->
                        <?php if(!empty($psikolog['bio'])): ?>
                        <div style="margin-bottom: 20px;">
                            <p style="font-size: 0.9rem; line-height: 1.6; color: var(--color-text); margin: 0;">
                                <?php echo htmlspecialchars(substr($psikolog['bio'], 0, 120)) . (strlen($psikolog['bio']) > 120 ? '...' : ''); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Contact Info -->
                        <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid var(--color-primary);">
                            <?php if(!empty($psikolog['email'])): ?>
                            <div style="margin-bottom: 8px; font-size: 0.85rem; color: #6c757d; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-envelope" style="color: var(--color-primary); font-size: 12px;"></i>
                                <?php echo htmlspecialchars($psikolog['email']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($psikolog['phone'])): ?>
                            <div style="font-size: 0.85rem; color: #6c757d; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-phone" style="color: var(--color-primary); font-size: 12px;"></i>
                                <?php echo htmlspecialchars($psikolog['phone']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Button -->
                        <a href="../auth/login.php?ref=booking&psikolog=<?php echo urlencode($psikolog['name']); ?>" 
                           class="btn-primary" 
                           style="width: 100%; text-align: center; display: block; padding: 12px 20px; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; <?php 
                           $btn_style = '';
                           $btn_text = 'Ajukan Konsultasi';
                           
                           if ($status === 'Offline & Online' || $status === 'Offline Only' || $status === 'Online Only') {
                               $btn_style = 'background: linear-gradient(135deg, #28a745 0%, #20c997 100%);';
                               $btn_text = 'Buat Janji Sekarang';
                           } elseif ($status === 'By Appointment') {
                               $btn_style = 'background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #333;';
                               $btn_text = 'Janji Temu';
                           } else {
                               $btn_style = 'background: #6c757d; cursor: not-allowed;';
                               $btn_text = 'Tidak Tersedia';
                           }
                           echo $btn_style;
                           ?>">
                           <i class="fas fa-<?php echo ($status === 'Tidak Tersedia') ? 'times-circle' : 'calendar-check'; ?>" style="margin-right: 8px;"></i>
                           <?php echo $btn_text; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<style>
/* Psychologist Card Styling */
.psychologist-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.psychologist-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
    border-color: var(--color-primary);
}

.psychologist-card:hover img {
    transform: scale(1.05);
}

.psychologist-card .service-info {
    position: relative;
    z-index: 1;
}

/* Responsive Grid for Psychologist Cards */
@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr !important;
        gap: 25px !important;
    }
    
    .psychologist-card {
        margin: 0 auto;
        max-width: 100%;
    }
    
    .psychologist-card .service-info {
        padding: 20px !important;
    }
    
    .psychologist-card h3 {
        font-size: 1.1rem !important;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .services-grid {
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) !important;
        gap: 30px !important;
    }
}

@media (min-width: 1025px) {
    .services-grid {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)) !important;
        gap: 35px !important;
    }
}

/* Button Hover Effects */
.psychologist-card .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(251, 186, 0, 0.4);
}

.psychologist-card .btn-primary[href*="bersedia_konsul=yes"]:hover {
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

/* Status Badge Animation */
.psychologist-card .status-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<?php include $path . 'components/footer.php'; ?>