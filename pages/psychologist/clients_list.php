<?php
/**
 * Filename: pages/psychologist/clients_list.php
 * Description: Daftar klien dan riwayat hidup yang sudah diapprove admin
 */

session_start();
$path = '../../';
$page_title = 'Daftar Klien & Riwayat';

// Check if user is logged in and is psychologist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];

// Get psychologist profile
$psychologist_data = $db->getPrepare("SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?", [$user_id]);
$psychologist_id = $psychologist_data['psychologist_id'] ?? null;

$clients_data = [];
$search = $_GET['search'] ?? '';

if ($psychologist_id) {
    $sql = "SELECT DISTINCT 
                cb.booking_id, cb.tanggal_konsultasi, cb.status_booking,
                u.name AS client_name, u.email, u.phone,
                cd.gender, cd.nik,
                brh.keluhan_masalah, brh.lama_masalah,
                COALESCE(cs.konsultasi_status, 'belum_ditangani') as konsultasi_status,
                CASE 
                    WHEN cs.konsultasi_status = 'sudah_ditangani' THEN 'Selesai'
                    WHEN cs.konsultasi_status = 'sedang_ditangani' THEN 'Proses'
                    ELSE 'Belum'
                END AS status_konsultasi
            FROM consultation_bookings cb
            INNER JOIN client_details cd ON cb.client_id = cd.client_id
            INNER JOIN users u ON cd.user_id = u.user_id
            LEFT JOIN booking_riwayat_hidup brh ON cb.booking_id = brh.booking_id
            LEFT JOIN consultation_status cs ON cb.booking_id = cs.booking_id
            WHERE cb.psychologist_id = ? AND cb.status_booking IN ('confirmed', 'pending')
            " . (!empty($search) ? "AND u.name LIKE ?" : "") . "
            ORDER BY cb.tanggal_konsultasi DESC";
    
    $params = [$psychologist_id];
    if (!empty($search)) {
        $params[] = '%' . $search . '%';
    }
    
    $result = $db->queryPrepare($sql, $params);
    if (is_array($result)) {
        $clients_data = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Rali Ra</title>
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/glass.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/psychologist.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(90, 61, 43, 0.1);
        }

        table thead {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            color: white;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--color-accent);
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(90, 61, 43, 0.1);
        }

        table tbody tr:hover {
            background: rgba(251, 186, 0, 0.05);
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-sudah {
            background: #d4edda;
            color: #155724;
        }

        .badge-belum {
            background: #fff3cd;
            color: #856404;
        }

        .btn-view {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-view:hover {
            background: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(251, 186, 0, 0.3);
        }

        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid rgba(251, 186, 0, 0.2);
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            transition: border-color 0.2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .search-box button {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .search-box button:hover {
            background: var(--color-primary-hover);
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--color-text-light);
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(90, 61, 43, 0.1);
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: var(--glass-border);
            box-shadow: var(--glass-shadow);
            border-radius: 12px;
        }

        .topbar {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            color: white;
        }

        .dashboard-container {
            background: var(--color-bg);
            min-height: 100vh;
        }

        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        
        <?php include $path . 'components/sidebar_psychologist.php'; ?>
        <?php include $path . 'components/header_psychologist.php'; ?>

        <main class="main-content">
            
            <div class="topbar glass-panel" style="padding: 20px; margin-bottom: 30px; border-radius: 12px;">
                <h2 style="color: var(--color-text); margin: 0;">
                    <i class="fas fa-users"></i> Daftar Klien & Riwayat
                </h2>
            </div>

            <div class="glass-panel" style="padding: 25px;">
                
                <!-- SEARCH BOX -->
                <div class="search-box">
                    <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                        <input type="text" name="search" placeholder="Cari nama klien..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">🔍 Cari</button>
                        <?php if (!empty($search)): ?>
                            <a href="clients_list.php" style="background: #999; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- TABLE -->
                <?php if (empty($clients_data)): ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 15px;">📭</div>
                        <p>Tidak ada klien yang ditemukan</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Nama Klien</th>
                                    <th style="width: 15%;">Tanggal Konsultasi</th>
                                    <th style="width: 12%;">Progress</th>
                                    <th style="width: 25%;">Keluhan/Masalah</th>
                                    <th style="width: 10%;">Lama Masalah</th>
                                    <th style="width: 18%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients_data as $client): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($client['client_name']); ?></strong>
                                        <br>
                                        <small style="color: var(--color-text-light);">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($client['email']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($client['tanggal_konsultasi'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo $client['status_konsultasi'] == 'Sudah Konsul' ? 'badge-sudah' : 'badge-belum'; ?>">
                                            <?php echo $client['status_konsultasi']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            if ($client['keluhan_masalah']) {
                                                echo htmlspecialchars(substr($client['keluhan_masalah'], 0, 50));
                                                if (strlen($client['keluhan_masalah']) > 50) echo '...';
                                            } else {
                                                echo '<em style="color: #999;">-</em>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($client['lama_masalah'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <a href="client_detail.php?booking_id=<?php echo $client['booking_id']; ?>" class="btn-view">
                                            <i class="fas fa-eye"></i> Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--color-border); text-align: right; color: var(--color-text-light);">
                        <small>Total: <strong><?php echo count($clients_data); ?></strong> klien</small>
                    </div>
                <?php endif; ?>

            </div>

        </main>
    </div>

    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>