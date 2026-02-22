<?php
/**
 * Filename: pages/admin/manage_invoices.php
 * Description: Halaman Admin untuk mengelola semua invoice (buat & kelola)
 * Features: Daftar invoice, buat invoice, detail view, print, download, status management
 */

session_start();
$path = '../../';
$page_title = 'Invoice';

// Set timezone to WIB (Western Indonesia Time)
date_default_timezone_set('Asia/Jakarta');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Get admin name from session
$admin_name = $_SESSION['name'] ?? 'Admin';

// Initialize database
$db = new Database();
$db->connect();

// Get clients and psychologists for form
$sql_clients = "SELECT u.user_id, u.name, u.email, u.phone 
               FROM users u 
               WHERE u.role = 'client' 
               ORDER BY u.name ASC";
$clients = $db->queryPrepare($sql_clients, []);

$sql_psychologists = "SELECT u.user_id, u.name, u.email 
                     FROM users u 
                     WHERE u.role = 'psychologist' 
                     ORDER BY u.name ASC";
$psychologists = $db->queryPrepare($sql_psychologists, []);

// Process form submission for creating invoice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_invoice') {
    $client_id = $_POST['client_id'] ?? '';
    $psychologist_id = $_POST['psychologist_id'] ?? '';
    $service_name = $_POST['service_name'] ?? '';
    $service_price = $_POST['service_price'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $notes = $_POST['notes'] ?? '';
    
    // Generate invoice number
    $invoice_number = 'INV-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
    
    // Calculate total payment (same as service price for single service)
    $total_payment = $service_price;
    
    // Insert invoice
    $sql_insert = "INSERT INTO invoices (invoice_number, client_id, psychologist_id, service_name, 
                   service_price, total_payment, payment_method, invoice_date, notes) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [$invoice_number, $client_id, $psychologist_id, $service_name, 
               $service_price, $total_payment, $payment_method, $invoice_date, $notes];
    
    if ($db->executePrepare($sql_insert, $params)) {
        $success_message = "Invoice $invoice_number berhasil dibuat!";
        $invoice_id = $db->lastId();
    } else {
        $error_message = "Gagal membuat invoice. Silakan coba lagi.";
    }
}

// Get all invoices with client and psychologist info
$sql_invoices = "SELECT i.*, 
                u1.name as psychologist_name, u1.email as psychologist_email,
                u2.name as client_name, u2.email as client_email, u2.phone as client_phone
                FROM invoices i
                LEFT JOIN users u1 ON i.psychologist_id = u1.user_id
                LEFT JOIN users u2 ON i.client_id = u2.user_id
                ORDER BY i.created_at DESC";

$invoices = $db->queryPrepare($sql_invoices, []);

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
                   WHERE i.id = ?";
    
    $invoice_detail = $db->getPrepare($sql_detail, [$invoice_id]);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $invoice_id = $_POST['invoice_id'] ?? '';
    $new_status = $_POST['status'] ?? '';
    
    if ($invoice_id && in_array($new_status, ['pending', 'paid', 'overdue'])) {
        $sql_update = "UPDATE invoices SET status = ?, updated_at = NOW() WHERE id = ?";
        if ($db->executePrepare($sql_update, [$new_status, $invoice_id])) {
            $success_message = "Status invoice berhasil diperbarui!";
            // Refresh data
            header("Location: manage_invoices.php?success=1");
            exit;
        }
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>
        
        <main class="main-content">
            <div class="content-section">
                <!-- Page Header -->
                <div class="page-header-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; border-radius: 20px; margin-bottom: 30px; color: white; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-invoice-dollar" style="font-size: 28px;"></i>
                            </div>
                            <div>
                                <h1 style="margin: 0; font-size: 32px; font-weight: 700;">Invoice</h1>
                                <p style="margin: 5px 0 0 0; font-size: 16px; opacity: 0.9;">Buat dan kelola semua invoice</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert-success-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px 25px; border-radius: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                        </div>
                        <div>
                            <strong>Status Invoice Berhasil Diperbarui!</strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($invoice_detail): ?>
                    <!-- Invoice Detail View -->
                    <div class="invoice-detail-container">
                        <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Invoice
                        </button>

                        <div class="glass-card-enhanced" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
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

                            <!-- Status Badge & Update Form -->
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
                                <span class="<?php echo $status_class; ?>" style="padding: 8px 16px; border-radius: 20px; font-weight: bold; margin-bottom: 15px; display: inline-block;">
                                    <i class="fas <?php echo $status_icon; ?>"></i>
                                    <?php echo ucfirst($invoice_detail['status']); ?>
                                </span>
                                
                                <!-- Status Update Form -->
                                <form method="POST" style="margin-top: 15px;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice_detail['id']; ?>">
                                    <div style="display: inline-flex; gap: 10px; align-items: center;">
                                        <select name="status" style="padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                                            <option value="pending" <?php echo $invoice_detail['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $invoice_detail['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="overdue" <?php echo $invoice_detail['status'] === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                                        </select>
                                        <button type="submit" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 500; cursor: pointer;">
                                            <i class="fas fa-sync"></i> Update Status
                                        </button>
                                    </div>
                                </form>
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
                    <!-- Invoice Creation Form -->
                    <div class="invoice-creation-section" style="margin-bottom: 30px;">
                        <div class="glass-card-enhanced" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
                            <h2 style="margin: 0 0 25px 0; color: #1f2937; font-size: 24px; font-weight: 700;">
                                <i class="fas fa-plus-circle" style="color: #3b82f6; margin-right: 10px;"></i>
                                Buat Invoice Baru
                            </h2>
                            
                            <?php if (isset($success_message)): ?>
                                <div class="alert-success-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error_message)): ?>
                                <div class="alert-error-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="invoiceForm" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                                <input type="hidden" name="action" value="create_invoice">
                                
                                <!-- Form Fields -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                                            <i class="fas fa-user" style="margin-right: 5px; color: #3b82f6;"></i>
                                            Klien *
                                        </label>
                                        <select name="client_id" id="client_id" required style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; background: white; box-sizing: border-box;">
                                            <option value="">-- Pilih Klien --</option>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?php echo $client['user_id']; ?>">
                                                    <?php echo htmlspecialchars($client['name'] . ' - ' . $client['email']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                                            <i class="fas fa-user-nurse" style="margin-right: 5px; color: #3b82f6;"></i>
                                            Psikolog *
                                        </label>
                                        <select name="psychologist_id" id="psychologist_id" required style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; background: white; box-sizing: border-box;">
                                            <option value="">-- Pilih Psikolog --</option>
                                            <?php foreach ($psychologists as $psychologist): ?>
                                                <option value="<?php echo $psychologist['user_id']; ?>">
                                                    <?php echo htmlspecialchars($psychologist['name'] . ' - ' . $psychologist['email']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                                            <i class="fas fa-briefcase-medical" style="margin-right: 5px; color: #3b82f6;"></i>
                                            Nama Layanan *
                                        </label>
                                        <input type="text" name="service_name" id="service_name" required placeholder="Contoh: Konsultasi Individu" 
                                               style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; box-sizing: border-box;">
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                                            <i class="fas fa-money-bill-wave" style="margin-right: 5px; color: #3b82f6;"></i>
                                            Harga Layanan (Rp) *
                                        </label>
                                        <input type="number" name="service_price" id="service_price" required min="0" step="1000" placeholder="Contoh: 500000" 
                                               style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; box-sizing: border-box;">
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                                            <i class="fas fa-credit-card" style="margin-right: 5px; color: #3b82f6;"></i>
                                            Metode Pembayaran *
                                        </label>
                                        <select name="payment_method" id="payment_method" required style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; background: white; box-sizing: border-box;">
                                            <option value="">-- Pilih Metode --</option>
                                            <option value="Transfer Bank">Transfer Bank</option>
                                            <option value="Tunai">Tunai</option>
                                            <option value="E-Wallet">E-Wallet</option>
                                            <option value="Kartu Kredit">Kartu Kredit</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                                            <i class="fas fa-calendar" style="margin-right: 5px; color: #3b82f6;"></i>
                                            Tanggal Invoice *
                                        </label>
                                        <input type="date" name="invoice_date" id="invoice_date" required value="<?php echo date('Y-m-d'); ?>" 
                                               style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; box-sizing: border-box;">
                                    </div>
                                    
                                    <div style="grid-column: 1 / -1;">
                                        <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                                            <i class="fas fa-sticky-note" style="margin-right: 5px; color: #3b82f6;"></i>
                                            Catatan (Opsional)
                                        </label>
                                        <textarea name="notes" id="notes" rows="3" placeholder="Catatan tambahan untuk invoice..." 
                                                  style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; resize: vertical; box-sizing: border-box;"></textarea>
                                    </div>
                                    
                                    <div style="grid-column: 1 / -1; text-align: right;">
                                        <button type="submit" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);">
                                            <i class="fas fa-plus-circle" style="margin-right: 8px;"></i>
                                            Buat Invoice
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Preview Section -->
                                <div>
                                    <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 15px; padding: 20px; border: 1px solid #e2e8f0; position: sticky; top: 20px;">
                                        <div style="margin-bottom: 20px;">
                                            <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-eye" style="color: #3b82f6;"></i>
                                                Preview Invoice
                                            </h3>
                                            <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 13px;">Preview real-time invoice Anda</p>
                                        </div>

                                        <div id="previewContent" style="background: white; border-radius: 10px; padding: 20px; border: 1px solid #e5e7eb;">
                                            <div style="text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                                                <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 8px 15px; border-radius: 8px; display: inline-block; margin-bottom: 8px;">
                                                    <strong>INVOICE DRAFT</strong>
                                                </div>
                                                <div style="font-size: 16px; font-weight: bold; color: #3b82f6;">
                                                    INV-<?php echo date('Ymd'); ?>-XXXX
                                                </div>
                                            </div>

                                            <div id="dynamicPreview" style="color: #64748b; font-style: italic; text-align: center; padding: 15px;">
                                                <i class="fas fa-file-invoice" style="font-size: 36px; margin-bottom: 10px; opacity: 0.3;"></i>
                                                <p style="font-size: 13px;">Preview akan muncul saat form diisi...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Invoice List View -->
                    <div class="invoices-list">
                        <div class="glass-card-enhanced" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
                            <h2 style="margin: 0 0 25px 0; color: #1f2937; font-size: 24px; font-weight: 700;">
                                <i class="fas fa-history" style="color: #3b82f6; margin-right: 10px;"></i>
                                Riwayat Invoice
                            </h2>
                            
                            <?php if (empty($invoices)): ?>
                                <div style="padding: 40px; text-align: center;">
                                    <i class="fas fa-file-invoice" style="font-size: 48px; color: #d1d5db; margin-bottom: 20px;"></i>
                                    <h3 style="margin: 0 0 10px 0; color: #6b7280;">Belum Ada Invoice</h3>
                                    <p style="margin: 0; color: #9ca3af;">Belum ada invoice yang dibuat dalam sistem.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: #f9fafb;">
                                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e5e7eb;">No. Invoice</th>
                                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e5e7eb;">Tanggal</th>
                                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e5e7eb;">Klien</th>
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
                                                    <?php echo htmlspecialchars($invoice['client_name']); ?>
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
                            <?php endif; ?>
                        </div>
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
        .glass-card-enhanced {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
        }
        
        /* Form styling */
        #invoiceForm input,
        #invoiceForm select,
        #invoiceForm textarea {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        #invoiceForm input:focus,
        #invoiceForm select:focus,
        #invoiceForm textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        #invoiceForm button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        /* Table row hover */
        table tbody tr:hover {
            background: #f9fafb;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            #invoiceForm {
                grid-template-columns: 1fr !important;
            }
            
            #invoiceForm > div:first-child {
                grid-template-columns: 1fr !important;
            }
            
            #invoiceForm > div > div[style*="grid-column: 1 / -1"] {
                grid-column: 1 !important;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-creation-section .glass-card-enhanced {
                padding: 20px !important;
            }
            
            #invoiceForm > div:first-child > div {
                grid-column: 1 !important;
            }
            
            #invoiceForm button {
                width: 100%;
                margin-top: 10px;
            }
        }
        
        @media print {
            .dashboard-container > *:not(main) {
                display: none !important;
            }
            main {
                margin: 0 !important;
                padding: 20px !important;
            }
            .btn {
                display: none !important;
            }
            form {
                display: none !important;
            }
        }
    </style>

    <script>
        function downloadInvoice(invoiceId) {
            // Placeholder for PDF download functionality
            alert('Fitur download PDF akan segera tersedia. Invoice ID: ' + invoiceId);
        }
        
        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Update preview with modern design
        function updatePreview() {
            const clientSelect = document.getElementById('client_id');
            const psychologistSelect = document.getElementById('psychologist_id');
            const serviceName = document.getElementById('service_name').value;
            const servicePrice = document.getElementById('service_price').value;
            const paymentMethod = document.getElementById('payment_method').value;
            const invoiceDate = document.getElementById('invoice_date').value;
            const notes = document.getElementById('notes').value;

            const dynamicPreview = document.getElementById('dynamicPreview');
            
            if (!clientSelect.value || !serviceName || !servicePrice) {
                dynamicPreview.innerHTML = `
                    <div style="color: #64748b; font-style: italic; text-align: center; padding: 15px;">
                        <i class="fas fa-file-invoice" style="font-size: 36px; margin-bottom: 10px; opacity: 0.3;"></i>
                        <p style="font-size: 13px;">Preview akan muncul saat form diisi...</p>
                    </div>
                `;
                return;
            }

            const clientOption = clientSelect.options[clientSelect.selectedIndex];
            const psychologistOption = psychologistSelect.options[psychologistSelect.selectedIndex];
            
            let previewHTML = `
                <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px; line-height: 1.5;">
                    <!-- Client & Psychologist Info -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 3px solid #3b82f6;">
                            <div style="font-weight: 600; color: #374151; margin-bottom: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-user" style="margin-right: 4px; color: #3b82f6;"></i>
                                Klien
                            </div>
                            <div style="color: #1f2937; font-weight: 500; font-size: 12px;">
                                ${clientOption ? clientOption.text.split(' - ')[0].trim() : '-'}
                            </div>
                        </div>
                        <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 3px solid #3b82f6;">
                            <div style="font-weight: 600; color: #374151; margin-bottom: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-user-md" style="margin-right: 4px; color: #3b82f6;"></i>
                                Psikolog
                            </div>
                            <div style="color: #1f2937; font-weight: 500; font-size: 12px;">
                                ${psychologistOption ? psychologistOption.text.split(' - ')[0].trim() : '-'}
                            </div>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-concierge-bell" style="margin-right: 4px; color: #3b82f6;"></i>
                            Detail Layanan
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <div style="color: #6b7280; font-size: 11px; margin-bottom: 3px;">Layanan</div>
                                <div style="color: #1f2937; font-weight: 500; font-size: 12px;">${serviceName}</div>
                            </div>
                            <div>
                                <div style="color: #6b7280; font-size: 11px; margin-bottom: 3px;">Harga</div>
                                <div style="color: #1f2937; font-weight: 500; font-size: 12px;">${formatCurrency(servicePrice)}</div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                            <div>
                                <div style="color: #6b7280; font-size: 11px; margin-bottom: 3px;">Metode</div>
                                <div style="color: #1f2937; font-weight: 500; font-size: 12px;">${paymentMethod || '-'}</div>
                            </div>
                            <div>
                                <div style="color: #6b7280; font-size: 11px; margin-bottom: 3px;">Tanggal</div>
                                <div style="color: #1f2937; font-weight: 500; font-size: 12px;">${invoiceDate ? new Date(invoiceDate).toLocaleDateString('id-ID') : '-'}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; opacity: 0.9;">
                            Total Pembayaran
                        </div>
                        <div style="font-size: 18px; font-weight: 700;">
                            ${formatCurrency(servicePrice)}
                        </div>
                    </div>

                    ${notes ? `
                    <!-- Notes -->
                    <div style="margin-top: 15px; background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 3px solid #f59e0b;">
                        <div style="font-weight: 600; color: #92400e; margin-bottom: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-sticky-note" style="margin-right: 4px;"></i>
                            Catatan
                        </div>
                        <div style="color: #78350f; font-size: 12px; line-height: 1.4;">
                            ${notes}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            dynamicPreview.innerHTML = previewHTML;
        }
        
        // Auto refresh page after successful invoice creation
        <?php if (isset($success_message)): ?>
            setTimeout(function() {
                // Scroll to the invoice list to see the newly created invoice
                document.querySelector('.invoices-list').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }, 1000);
        <?php endif; ?>
        
        // Form validation feedback
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuat Invoice...';
            submitBtn.disabled = true;
        });
        
        // Initialize preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = ['client_id', 'psychologist_id', 'service_name', 'service_price', 'payment_method', 'invoice_date', 'notes'];
            
            formElements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    // Update preview on change
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });
        });
    </script>
</body>
</html>
