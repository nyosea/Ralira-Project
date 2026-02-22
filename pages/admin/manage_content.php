<?php
$path = '../../';
$page_title = 'Manajemen Artikel & Edukasi';

$contents = [
    ["title" => "Mengenal Speech Delay pada Anak", "cat" => "Tumbuh Kembang", "date" => "28 Nov 2025", "views" => 120],
    ["title" => "Tips Mengelola Stres Kerja", "cat" => "Kesehatan Mental", "date" => "25 Nov 2025", "views" => 85],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/glass.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive_sections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>

    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>

        <main class="main-content">
            <div class="manage-content-container">
                <div class="topbar">
                    <h2>Konten Edukasi</h2>
                    <button class="btn-primary" onclick="document.getElementById('uploadModal').style.display='flex'">+ Buat Artikel Baru</button>
                </div>

                <div class="content-table-container">
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th>Judul Artikel</th>
                                <th>Kategori</th>
                                <th>Tanggal Upload</th>
                                <th>Dilihat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($contents as $con): ?>
                            <tr>
                                <td class="content-title"><?php echo $con['title']; ?></td>
                                <td><span class="category-badge <?php echo strtolower(str_replace(' ', '-', $con['cat'])); ?>"><?php echo $con['cat']; ?></span></td>
                                <td class="stats-cell"><?php echo $con['date']; ?></td>
                                <td class="stats-cell"><strong><?php echo $con['views']; ?></strong>x</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="#" class="action-btn edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="#" class="action-btn delete">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="uploadModal" class="content-modal">
                <div class="modal-content">
                    <button class="close-btn" onclick="document.getElementById('uploadModal').style.display='none'">&times;</button>
                    
                    <div class="modal-header">
                        <i class="fas fa-plus-circle"></i>
                        <h3>Tambah Artikel Baru</h3>
                    </div>
                    
                    <form class="content-form">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-heading"></i>
                                Judul Artikel
                            </label>
                            <input type="text" class="form-input" placeholder="Masukkan judul artikel yang menarik...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-folder"></i>
                                Kategori
                            </label>
                            <select class="form-select">
                                <option value="">Pilih Kategori</option>
                                <option value="kesehatan-mental">Kesehatan Mental</option>
                                <option value="parenting">Parenting</option>
                                <option value="tumbuh-kembang">Tumbuh Kembang</option>
                                <option value="berita-ralira">Berita Rali Ra</option>
                                <option value="tips-trik">Tips & Trik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-image"></i>
                                Thumbnail Artikel
                            </label>
                            <input type="file" class="form-input" accept="image/*">
                            <small class="form-help">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i>
                                Isi Konten
                            </label>
                            <textarea class="form-textarea" placeholder="Tulis konten artikel yang informatif dan menarik..."></textarea>
                            <small class="form-help">Gunakan format yang mudah dibaca dan informatif</small>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-submit" onclick="alert('Simulasi: Artikel berhasil diterbitkan!')">
                                <i class="fas fa-paper-plane"></i>
                                Terbitkan Artikel
                            </button>
                            <button type="button" class="btn-cancel" onclick="document.getElementById('uploadModal').style.display='none'">
                                <i class="fas fa-times"></i>
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>