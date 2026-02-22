<?php
/**
 * Filename: pages/admin/create_invoice.php
 * Description: Halaman Admin untuk membuat invoice baru
 * Features: Form pembuatan invoice dengan data klien dan layanan
 */

session_start();
$path = '../../';
$page_title = 'Buat Invoice Baru';

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

// Get clients list
$sql_clients = "SELECT u.user_id, u.name, u.email, u.phone 
               FROM users u 
               WHERE u.role = 'client' 
               ORDER BY u.name ASC";
$clients = $db->queryPrepare($sql_clients, []);

// Get psychologists list
$sql_psychologists = "SELECT u.user_id, u.name, u.email 
                     FROM users u 
                     WHERE u.role = 'psychologist' 
                     ORDER BY u.name ASC";
$psychologists = $db->queryPrepare($sql_psychologists, []);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? '';
    $psychologist_id = $_POST['psychologist_id'] ?? '';
    $service_name = $_POST['service_name'] ?? '';
    $service_price = $_POST['service_price'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $due_date = ''; // Removed due date functionality
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
                <!-- Page Header with Gradient -->
                <div class="page-header-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; border-radius: 20px; margin-bottom: 30px; color: white; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-invoice" style="font-size: 28px;"></i>
                            </div>
                            <div>
                                <h1 style="margin: 0; font-size: 32px; font-weight: 700;">Buat Invoice Baru</h1>
                                <p style="margin: 5px 0 0 0; font-size: 16px; opacity: 0.9;">Buat invoice profesional untuk klien Anda</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert-success-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px 25px; border-radius: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                        </div>
                        <div>
                            <strong>Invoice Berhasil Dibuat!</strong><br>
                            <small style="opacity: 0.9;">No. Invoice: <?php echo $invoice_number; ?></small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert-error-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 20px 25px; border-radius: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.2);">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
                        </div>
                        <div>
                            <strong>Terjadi Kesalahan</strong><br>
                            <small style="opacity: 0.9;"><?php echo $error_message; ?></small>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Main Form Container -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <!-- Form Section -->
                    <div class="form-section-modern">
                        <div class="glass-card-enhanced" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
                            <div style="margin-bottom: 30px;">
                                <h3 style="margin: 0; color: #1f2937; font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-edit" style="color: #667eea;"></i>
                                    Informasi Invoice
                                </h3>
                                <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 14px;">Lengkapi data invoice dengan benar</p>
                            </div>

                            <form method="POST" action="" id="invoiceForm">
                                <!-- Client & Psychologist Selection -->
                                <div style="margin-bottom: 25px;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div>
                                            <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500; font-size: 14px;">
                                                <i class="fas fa-user" style="color: #667eea; margin-right: 6px;"></i>
                                                Klien <span style="color: #ef4444;">*</span>
                                            </label>
                                            <select name="client_id" id="client_id" required style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; background: white;">
                                                <option value="">-- Pilih Klien --</option>
                                                <?php foreach ($clients as $client): ?>
                                                    <option value="<?php echo $client['user_id']; ?>" 
                                                            data-email="<?php echo $client['email']; ?>"
                                                            data-phone="<?php echo $client['phone']; ?>">
                                                        <?php echo htmlspecialchars($client['name']); ?> 
                                                        (<?php echo htmlspecialchars($client['email']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div>
                                            <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500; font-size: 14px;">
                                                <i class="fas fa-user-md" style="color: #667eea; margin-right: 6px;"></i>
                                                Psikolog <span style="color: #ef4444;">*</span>
                                            </label>
                                            <select name="psychologist_id" id="psychologist_id" required style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; background: white;">
                                                <option value="">-- Pilih Psikolog --</option>
                                                <?php foreach ($psychologists as $psychologist): ?>
                                                    <option value="<?php echo $psychologist['user_id']; ?>">
                                                        <?php echo htmlspecialchars($psychologist['name']); ?> 
                                                        (<?php echo htmlspecialchars($psychologist['email']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Information -->
                                <div style="margin-bottom: 25px;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div>
                                            <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500; font-size: 14px;">
                                                <i class="fas fa-concierge-bell" style="color: #667eea; margin-right: 6px;"></i>
                                                Nama Layanan <span style="color: #ef4444;">*</span>
                                            </label>
                                            <input type="text" name="service_name" id="service_name" required
                                                   placeholder="Contoh: Konsultasi Psikologi Individu"
                                                   style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        </div>

                                        <div>
                                            <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500; font-size: 14px;">
                                                <i class="fas fa-rupiah-sign" style="color: #667eea; margin-right: 6px;"></i>
                                                Harga Layanan (Rp) <span style="color: #ef4444;">*</span>
                                            </label>
                                            <input type="number" name="service_price" id="service_price" required
                                                   min="0" step="1000" placeholder="Contoh: 500000"
                                                   style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment & Date -->
                                <div style="margin-bottom: 25px;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div>
                                            <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500; font-size: 14px;">
                                                <i class="fas fa-credit-card" style="color: #667eea; margin-right: 6px;"></i>
                                                Metode Pembayaran <span style="color: #ef4444;">*</span>
                                            </label>
                                            <select name="payment_method" id="payment_method" required style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; background: white;">
                                                <option value="">-- Pilih Metode --</option>
                                                <option value="Transfer Bank">Transfer Bank</option>
                                                <option value="E-Wallet">E-Wallet</option>
                                                <option value="Tunai">Tunai</option>
                                                <option value="Kartu Kredit">Kartu Kredit</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500; font-size: 14px;">
                                                <i class="fas fa-calendar" style="color: #667eea; margin-right: 6px;"></i>
                                                Tanggal Invoice <span style="color: #ef4444;">*</span>
                                            </label>
                                            <input type="date" name="invoice_date" id="invoice_date" required
                                                   value="<?php echo date('Y-m-d'); ?>"
                                                   style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div style="margin-bottom: 30px;">
                                    <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500; font-size: 14px;">
                                        <i class="fas fa-sticky-note" style="color: #667eea; margin-right: 6px;"></i>
                                        Catatan
                                    </label>
                                    <textarea name="notes" id="notes" rows="4" 
                                              placeholder="Catatan tambahan untuk invoice..."
                                              style="width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; resize: vertical;"></textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div style="display: flex; gap: 15px; justify-content: flex-end;">
                                    <button type="button" onclick="window.history.back()" 
                                            style="padding: 12px 24px; background: #f3f4f6; color: #6b7280; border: none; border-radius: 10px; font-weight: 500; cursor: pointer; transition: all 0.3s ease;">
                                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                                        Batal
                                    </button>
                                    <button type="submit" 
                                            style="padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);">
                                        <i class="fas fa-save" style="margin-right: 8px;"></i>
                                        Buat Invoice
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="preview-section-modern">
                        <div class="glass-card-enhanced" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); position: sticky; top: 20px;">
                            <div style="margin-bottom: 25px;">
                                <h3 style="margin: 0; color: #1f2937; font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-eye" style="color: #667eea;"></i>
                                    Preview Invoice
                                </h3>
                                <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 14px;">Preview real-time invoice Anda</p>
                            </div>

                            <div id="previewContent" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 15px; padding: 25px; border: 1px solid #e2e8f0;">
                                <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0;">
                                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border-radius: 10px; display: inline-block; margin-bottom: 10px;">
                                        <strong>INVOICE DRAFT</strong>
                                    </div>
                                    <div style="font-size: 18px; font-weight: bold; color: #667eea; margin-top: 10px;">
                                        INV-<?php echo date('Ymd'); ?>-XXXX
                                    </div>
                                </div>

                                <div id="dynamicPreview" style="color: #64748b; font-style: italic; text-align: center; padding: 20px;">
                                    <i class="fas fa-file-invoice" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                                    <p>Preview akan muncul saat form diisi...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
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
                    <div style="color: #64748b; font-style: italic; text-align: center; padding: 20px;">
                        <i class="fas fa-file-invoice" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>Preview akan muncul saat form diisi...</p>
                    </div>
                `;
                return;
            }

            const clientOption = clientSelect.options[clientSelect.selectedIndex];
            const psychologistOption = psychologistSelect.options[psychologistSelect.selectedIndex];
            
            let previewHTML = `
                <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; line-height: 1.6;">
                    <!-- Invoice Header -->
                    <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0;">
                        <div style="font-size: 18px; font-weight: bold; color: #667eea; margin-bottom: 8px;">
                            INV-${new Date().toISOString().slice(0,10).replace(/-/g,'')}-XXXX
                        </div>
                        <div style="color: #6b7280; font-size: 13px;">
                            Tanggal: ${invoiceDate || '-'}
                        </div>
                    </div>

                    <!-- Client & Psychologist Info -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border-left: 4px solid #667eea;">
                            <div style="font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-user" style="margin-right: 5px; color: #667eea;"></i>
                                Klien
                            </div>
                            <div style="color: #1f2937; font-weight: 500;">
                                ${clientOption ? clientOption.text.split('(')[0].trim() : '-'}
                            </div>
                        </div>
                        <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border-left: 4px solid #667eea;">
                            <div style="font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-user-md" style="margin-right: 5px; color: #667eea;"></i>
                                Psikolog
                            </div>
                            <div style="color: #1f2937; font-weight: 500;">
                                ${psychologistOption ? psychologistOption.text.split('(')[0].trim() : '-'}
                            </div>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div style="background: #f1f5f9; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-concierge-bell" style="margin-right: 5px; color: #667eea;"></i>
                            Detail Layanan
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">Layanan</div>
                                <div style="color: #1f2937; font-weight: 500;">${serviceName}</div>
                            </div>
                            <div>
                                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">Harga</div>
                                <div style="color: #1f2937; font-weight: 500;">${formatCurrency(servicePrice)}</div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <div>
                                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">Metode Pembayaran</div>
                                <div style="color: #1f2937; font-weight: 500;">${paymentMethod || '-'}</div>
                            </div>
                            <div>
                                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">Tanggal</div>
                                <div style="color: #1f2937; font-weight: 500;">${invoiceDate || '-'}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                        <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; opacity: 0.9;">
                            Total Pembayaran
                        </div>
                        <div style="font-size: 24px; font-weight: 700;">
                            ${formatCurrency(servicePrice)}
                        </div>
                    </div>

                    ${notes ? `
                    <!-- Notes -->
                    <div style="margin-top: 20px; background: #fef3c7; padding: 15px; border-radius: 10px; border-left: 4px solid #f59e0b;">
                        <div style="font-weight: 600; color: #92400e; margin-bottom: 8px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-sticky-note" style="margin-right: 5px;"></i>
                            Catatan
                        </div>
                        <div style="color: #78350f; font-size: 13px; line-height: 1.5;">
                            ${notes}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            dynamicPreview.innerHTML = previewHTML;
        }

        // Add hover effects and interactions
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = ['client_id', 'psychologist_id', 'service_name', 'service_price', 'payment_method', 'invoice_date', 'notes'];
            
            formElements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    // Add focus effects
                    element.addEventListener('focus', function() {
                        this.style.borderColor = '#667eea';
                        this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
                    });
                    
                    element.addEventListener('blur', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = 'none';
                    });
                    
                    // Update preview on change
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });

            // Add button hover effects
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    if (!this.type || this.type !== 'submit') {
                        this.style.transform = 'translateY(-2px)';
                    }
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>

    <style>
        /* Additional styles for enhanced form */
        select:focus, input:focus, textarea:focus {
            outline: none !important;
        }
        
        /* Ensure consistent width for all form elements */
        select, input[type="text"], input[type="number"], input[type="date"], textarea {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .glass-card-enhanced {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .glass-card-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .main-content > div > div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            
            .preview-section-modern .glass-card-enhanced {
                position: static !important;
            }
        }
        
        @media (max-width: 768px) {
            .page-header-gradient {
                padding: 30px 20px !important;
            }
            
            .page-header-gradient h1 {
                font-size: 24px !important;
            }
            
            .glass-card-enhanced {
                padding: 20px !important;
            }
            
            div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
                gap: 15px !important;
            }
        }
    </style>
</body>
</html>
