<?php
/**
 * Filename: pages/client/invoices.php
 * Description: Halaman Klien untuk melihat daftar invoice
 * Features: Daftar invoice, detail invoice, status pembayaran
 */

session_start();
$path = '../../';
$page_title = 'Invoice Saya';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Check if user is logged in and is client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../auth/login.php');
    exit;
}

// Get client name from session
$client_name = $_SESSION['name'] ?? 'Klien';

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];

// Get user profile picture from database
$sql = "SELECT profile_picture FROM users WHERE user_id = ?";
$user_data = $db->getPrepare($sql, [$user_id]);
if ($user_data && $user_data['profile_picture']) {
    $_SESSION['profile_picture'] = $user_data['profile_picture'];
}

// Get invoices for this client
$sql_invoices = "SELECT i.*, 
                u1.name as psychologist_name,
                u1.email as psychologist_email
                FROM invoices i
                LEFT JOIN users u1 ON i.psychologist_id = u1.user_id
                WHERE i.client_id = ?
                ORDER BY i.created_at DESC";

$invoices = $db->queryPrepare($sql_invoices, [$user_id]);

// Get invoice details if ID is provided
$invoice_detail = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $invoice_id = $_GET['id'];
    $sql_detail = "SELECT i.*, 
                   u1.name as psychologist_name, u1.email as psychologist_email,
                   u2.name as client_name, u2.email as client_email, u2.phone as client_phone
                   FROM invoices i
                   LEFT JOIN users u1 ON i.psychologist_id = u1.user_id
                   LEFT JOIN users u2 ON i.client_id = u2.user_id
                   WHERE i.id = ? AND i.client_id = ?";
    
    $invoice_detail = $db->getPrepare($sql_detail, [$invoice_id, $user_id]);
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/client.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        
        <?php include $path . 'components/sidebar_client.php'; ?>
        <?php include $path . 'components/header_client.php'; ?>
        
        <main class="main-content">
            <div class="content-section">
                <!-- Page Header -->
                <div class="section-header">
                    <h2 style="margin: 0; font-size: 28px; font-weight: 700; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-file-invoice-dollar" style="font-size: 24px; opacity: 0.9;"></i>
                        Invoice Saya
                    </h2>
                    <p style="margin: 8px 0 0 0; font-size: 16px; opacity: 0.9;">
                        Kelola dan pantau status pembayaran invoice Anda
                    </p>
                </div>

                <?php if ($invoice_detail): ?>
                    <!-- Invoice Detail View -->
                    <div class="invoice-detail-container">
                        <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Invoice
                        </button>

                        <div class="glass-card" style="padding: 30px; border-radius: 16px;">
                            <!-- Invoice Header -->
                            <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb;">
                                <h1 style="margin: 0; color: #1f2937; font-size: 32px;">INVOICE</h1>
                                <div style="font-size: 18px; font-weight: bold; color: #3b82f6; margin-top: 10px;">
                                    <?php echo htmlspecialchars($invoice_detail['invoice_number']); ?>
                                </div>
                                <div style="margin-top: 5px; color: #6b7280;">
                                    Tanggal: <?php echo date('d F Y', strtotime($invoice_detail['invoice_date'])); ?>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div style="text-align: center; margin-bottom: 30px;">
                                <?php
                                $status_class = '';
                                $status_icon = '';
                                switch($invoice_detail['status']) {
                                    case 'paid':
                                        $status_class = 'status-paid';
                                        $status_icon = 'fa-check-circle';
                                        break;
                                    case 'pending':
                                        $status_class = 'status-pending';
                                        $status_icon = 'fa-clock';
                                        break;
                                    case 'overdue':
                                        $status_class = 'status-overdue';
                                        $status_icon = 'fa-exclamation-triangle';
                                        break;
                                }
                                ?>
                                <span class="<?php echo $status_class; ?>" style="padding: 8px 16px; border-radius: 20px; font-weight: bold;">
                                    <i class="fas <?php echo $status_icon; ?>"></i>
                                    <?php echo ucfirst($invoice_detail['status']); ?>
                                </span>
                            </div>

                            <!-- Bill To and Service Info -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                                <div>
                                    <h3 style="margin: 0 0 15px 0; color: #374151;">Ditujukan Kepada:</h3>
                                    <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                                        <strong><?php echo htmlspecialchars($invoice_detail['client_name']); ?></strong><br>
                                        <?php echo htmlspecialchars($invoice_detail['client_email']); ?><br>
                                        <?php if ($invoice_detail['client_phone']): ?>
                                            <?php echo htmlspecialchars($invoice_detail['client_phone']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <h3 style="margin: 0 0 15px 0; color: #374151;">Informasi Layanan:</h3>
                                    <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                                        <strong>Psikolog:</strong> <?php echo htmlspecialchars($invoice_detail['psychologist_name']); ?><br>
                                        <strong>Layanan:</strong> <?php echo htmlspecialchars($invoice_detail['service_name']); ?><br>
                                        <strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($invoice_detail['payment_method']); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <div style="margin-bottom: 30px;">
                                <h3 style="margin: 0 0 15px 0; color: #374151;">Rincian Pembayaran:</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #f3f4f6;">
                                            <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">Layanan</th>
                                            <th style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="padding: 12px; border: 1px solid #e5e7eb;">
                                                <?php echo htmlspecialchars($invoice_detail['service_name']); ?>
                                            </td>
                                            <td style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">
                                                Rp <?php echo number_format($invoice_detail['service_price'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f9fafb; font-weight: bold;">
                                            <td style="padding: 12px; border: 1px solid #e5e7eb;">Total Pembayaran</td>
                                            <td style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; color: #3b82f6; font-size: 18px;">
                                                Rp <?php echo number_format($invoice_detail['total_payment'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <?php if ($invoice_detail['notes']): ?>
                            <!-- Notes -->
                            <div style="margin-bottom: 30px;">
                                <h3 style="margin: 0 0 15px 0; color: #374151;">Catatan:</h3>
                                <div style="background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                                    <?php echo nl2br(htmlspecialchars($invoice_detail['notes'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div style="text-align: center; margin-top: 30px;">
                                <button onclick="window.print()" class="btn btn-primary" style="margin-right: 10px;">
                                    <i class="fas fa-print"></i> Cetak Invoice
                                </button>
                                <button onclick="downloadInvoice(<?php echo $invoice_detail['id']; ?>)" class="btn btn-secondary">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Invoice List View -->
                    <div class="invoices-list">
                        <?php if (empty($invoices)): ?>
                            <div class="glass-card" style="padding: 40px; text-align: center; border-radius: 16px;">
                                <i class="fas fa-file-invoice" style="font-size: 48px; color: #d1d5db; margin-bottom: 20px;"></i>
                                <h3 style="margin: 0 0 10px 0; color: #6b7280;">Belum Ada Invoice</h3>
                                <p style="margin: 0; color: #9ca3af;">Anda belum memiliki invoice saat ini.</p>
                            </div>
                        <?php else: ?>
                            <div class="glass-card" style="padding: 20px; border-radius: 16px;">
                                <div class="table-responsive">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: #f9fafb;">
                                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e5e7eb;">No. Invoice</th>
                                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e5e7eb;">Tanggal</th>
                                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e5e7eb;">Psikolog</th>
                                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e5e7eb;">Layanan</th>
                                                <th style="padding: 15px; text-align: right; border-bottom: 2px solid #e5e7eb;">Total</th>
                                                <th style="padding: 15px; text-align: center; border-bottom: 2px solid #e5e7eb;">Status</th>
                                                <th style="padding: 15px; text-align: center; border-bottom: 2px solid #e5e7eb;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($invoices as $invoice): ?>
                                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                                <td style="padding: 15px;">
                                                    <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                                </td>
                                                <td style="padding: 15px;">
                                                    <?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?>
                                                </td>
                                                <td style="padding: 15px;">
                                                    <?php echo htmlspecialchars($invoice['psychologist_name']); ?>
                                                </td>
                                                <td style="padding: 15px;">
                                                    <?php echo htmlspecialchars($invoice['service_name']); ?>
                                                </td>
                                                <td style="padding: 15px; text-align: right; font-weight: bold;">
                                                    Rp <?php echo number_format($invoice['total_payment'], 0, ',', '.'); ?>
                                                </td>
                                                <td style="padding: 15px; text-align: center;">
                                                    <?php
                                                    $status_class = '';
                                                    switch($invoice['status']) {
                                                        case 'paid':
                                                            $status_class = 'badge-paid';
                                                            break;
                                                        case 'pending':
                                                            $status_class = 'badge-pending';
                                                            break;
                                                        case 'overdue':
                                                            $status_class = 'badge-overdue';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="<?php echo $status_class; ?>" style="padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                                        <?php echo ucfirst($invoice['status']); ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 15px; text-align: center;">
                                                    <a href="?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none; background: #3b82f6; color: white; border-radius: 4px;">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <style>
        .badge-paid {
            background: #dcfce7;
            color: #16a34a;
        }
        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }
        .badge-overdue {
            background: #fee2e2;
            color: #dc2626;
        }
        .status-paid {
            background: #dcfce7;
            color: #16a34a;
        }
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
        .status-overdue {
            background: #fee2e2;
            color: #dc2626;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        @media print {
            /* Hide all dashboard elements */
            .dashboard-container > *:not(main) {
                display: none !important;
            }
            main {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .content-section {
                margin: 0 !important;
                padding: 0 !important;
            }
            .section-header {
                display: none !important;
            }
            .btn {
                display: none !important;
            }
            .glass-card {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
                margin: 0 !important;
                padding: 20px !important;
            }
            /* Ensure invoice takes full page */
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            /* Hide navigation and other elements */
            nav, header, aside, .sidebar, .no-print {
                display: none !important;
            }
        }
    </style>

    <!-- Global JavaScript -->
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    <script src="<?php echo $path; ?>assets/js/invoice_pdf.js"></script>
    
    <script>
        function downloadInvoice(invoiceId) {
            // Enhanced PDF download with loading indicator
            downloadInvoiceWithLoading(invoiceId);
        }
    </script>
</body>
</html>
