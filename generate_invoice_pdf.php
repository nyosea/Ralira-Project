<?php
/**
 * Filename: generate_invoice_pdf.php
 * Description: Generate PDF for invoice download (HTML format for printing)
 */

session_start();
require_once 'includes/db.php';

// Check if user is logged in and is client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: pages/auth/login.php');
    exit;
}

// Get invoice ID from GET parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid invoice ID');
}

$invoice_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Initialize database
$db = new Database();
$db->connect();

// Get invoice details
$sql = "SELECT i.*, 
           u1.name as psychologist_name, u1.email as psychologist_email,
           u2.name as client_name, u2.email as client_email, u2.phone as client_phone
           FROM invoices i
           LEFT JOIN users u1 ON i.psychologist_id = u1.user_id
           LEFT JOIN users u2 ON i.client_id = u2.user_id
           WHERE i.id = ? AND i.client_id = ?";
    
$invoice = $db->getPrepare($sql, [$invoice_id, $user_id]);

if (!$invoice) {
    die('Invoice not found or access denied');
}

// Generate HTML content for PDF
function generateInvoiceHTML($invoice) {
    $status_class = '';
    switch($invoice['status']) {
        case 'paid':
            $status_class = 'status-paid';
            break;
        case 'pending':
            $status_class = 'status-pending';
            break;
        case 'overdue':
            $status_class = 'status-overdue';
            break;
    }
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #' . htmlspecialchars($invoice['invoice_number']) . '</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        body {
            font-family: "Arial", sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header h1 {
            margin: 0;
            color: #1f2937;
            font-size: 32px;
            font-weight: bold;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #3b82f6;
            margin-top: 10px;
        }
        .invoice-date {
            margin-top: 5px;
            color: #6b7280;
            font-size: 14px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 30px;
        }
        .info-box {
            flex: 1;
        }
        .info-box h3 {
            margin: 0 0 15px 0;
            color: #374151;
            font-size: 16px;
            font-weight: bold;
        }
        .info-content {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-paid {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #16a34a;
        }
        .status-pending {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #d97706;
        }
        .status-overdue {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #dc2626;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background: #f9fafb;
            font-weight: bold;
        }
        .total-amount {
            color: #3b82f6;
            font-size: 18px;
            font-weight: bold;
        }
        .notes-section {
            margin-bottom: 30px;
        }
        .notes-content {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            border: 1px solid #f59e0b;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #5A3D2B;
            margin-bottom: 5px;
        }
        .company-tagline {
            font-size: 12px;
            color: #FBBA00;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            /* Remove all margins and spacing for print */
            .logo-section {
                margin-bottom: 15px;
            }
            .header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
            .info-section {
                margin-bottom: 20px;
            }
            table {
                margin-bottom: 20px;
            }
            .footer {
                margin-top: 30px;
            }
            /* Ensure content fits on one page if possible */
            * {
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="logo-section">
        <div class="company-name">Biro Psikologi Rali Ra</div>
        <div class="company-tagline">Terbit dari Timur, Terangi Kehidupan</div>
    </div>

    <div class="header">
        <h1>INVOICE</h1>
        <div class="invoice-number">' . htmlspecialchars($invoice['invoice_number']) . '</div>
        <div class="invoice-date">Tanggal: ' . date('d F Y', strtotime($invoice['invoice_date'])) . '</div>
    </div>

    <div style="text-align: center;">
        <div class="status-badge ' . $status_class . '">
            Status: ' . ucfirst($invoice['status']) . '
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>Ditujukan Kepada:</h3>
            <div class="info-content">
                <strong>' . htmlspecialchars($invoice['client_name']) . '</strong><br>
                ' . htmlspecialchars($invoice['client_email']) . '<br>
                ' . ($invoice['client_phone'] ? htmlspecialchars($invoice['client_phone']) : '') . '
            </div>
        </div>
        <div class="info-box">
            <h3>Informasi Layanan:</h3>
            <div class="info-content">
                <strong>Psikolog:</strong> ' . htmlspecialchars($invoice['psychologist_name']) . '<br>
                <strong>Layanan:</strong> ' . htmlspecialchars($invoice['service_name']) . '<br>
                <strong>Metode Pembayaran:</strong> ' . htmlspecialchars($invoice['payment_method']) . '
            </div>
        </div>
    </div>

    <h3 style="margin: 0 0 15px 0; color: #374151; font-size: 16px; font-weight: bold;">Rincian Pembayaran:</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 70%;">Layanan</th>
                <th style="width: 30%;" class="text-right">Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>' . htmlspecialchars($invoice['service_name']) . '</td>
                <td class="text-right">Rp ' . number_format($invoice['service_price'], 0, ',', '.') . '</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Total Pembayaran</td>
                <td class="text-right total-amount">Rp ' . number_format($invoice['total_payment'], 0, ',', '.') . '</td>
            </tr>
        </tfoot>
    </table>';

    if ($invoice['notes']) {
        $html .= '
        <div class="notes-section">
            <h3 style="margin: 0 0 15px 0; color: #374151; font-size: 16px; font-weight: bold;">Catatan:</h3>
            <div class="notes-content">' . nl2br(htmlspecialchars($invoice['notes'])) . '</div>
        </div>';
    }

    $html .= '
        <div class="footer">
            <p><strong>Biro Psikologi Rali Ra</strong></p>
            <p>Jl. Sentani Harmoni No. 12, Jayapura, Papua, Indonesia</p>
            <p>+62 812-9360-5651 | admin@ralira.id</p>
            <p style="margin-top: 10px;">&copy; ' . date('Y') . ' Biro Psikologi Rali Ra. All rights reserved.</p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 15px;">
                Gunakan Ctrl+P atau File > Print untuk menyimpan sebagai PDF
            </p>
            <button onclick="window.print()" style="background: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                <i class="fas fa-print"></i> Cetak/Simpan PDF
            </button>
        </div>
    </body>
    
    <script>
        // Auto-open print dialog after page loads
        window.onload = function() {
            setTimeout(function() {
                // Uncomment the next line if you want auto-print dialog
                // window.print();
            }, 1000);
        };
        
        // Add keyboard shortcut for print
        document.onkeydown = function(e) {
            if (e.ctrlKey && e.key === "p") {
                e.preventDefault();
                window.print();
            }
        };
    </script>
</html>';

    return $html;
}

// Check if download parameter is set
$is_download = isset($_GET['download']) && $_GET['download'] == '1';

// Generate HTML content
$html_content = generateInvoiceHTML($invoice);

// Set headers for HTML display/download
$filename = 'Invoice_' . $invoice['invoice_number'] . '.html';

if ($is_download) {
    // Force download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    header('Pragma: public');
} else {
    // Display in browser
    header('Content-Type: text/html');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
}

echo $html_content;
?>
