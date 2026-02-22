<?php
require_once 'includes/db.php';

$db = new Database();
$db->connect();

// Get sample client and psychologist
$client = $db->get("SELECT user_id, name FROM users WHERE role = 'client' LIMIT 1");
$psychologist = $db->get("SELECT user_id, name FROM users WHERE role = 'psychologist' LIMIT 1");

if ($client && $psychologist) {
    $invoice_number = 'TEST-' . date('Ymd') . '-001';
    
    $sql = "INSERT INTO invoices (invoice_number, client_id, psychologist_id, service_name, 
            service_price, total_payment, payment_method, invoice_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $invoice_number,
        $client['user_id'],
        $psychologist['user_id'],
        'Konsultasi Psikologi Individu',
        750000,
        750000,
        'Transfer Bank',
        date('Y-m-d'),
        'Test invoice untuk demonstrasi'
    ];
    
    if ($db->executePrepare($sql, $params)) {
        $invoice_id = $db->lastId();
        echo "<h2>✅ Test Invoice Berhasil Dibuat!</h2>";
        echo "<p><strong>No. Invoice:</strong> $invoice_number</p>";
        echo "<p><strong>Client:</strong> " . htmlspecialchars($client['name']) . "</p>";
        echo "<p><strong>Psychologist:</strong> " . htmlspecialchars($psychologist['name']) . "</p>";
        echo "<p><strong>Amount:</strong> Rp 750.000</p>";
        
        echo "<h3>📍 Cara Melihat Invoice:</h3>";
        echo "<ol>";
        echo "<li><strong>Login sebagai Client</strong> dengan email: " . htmlspecialchars($client['name']) . "</li>";
        echo "<li><strong>Buka halaman Invoice</strong> dari sidebar menu</li>";
        echo "<li><strong>Atau langsung buka:</strong> <a href='pages/client/invoices.php' target='_blank'>pages/client/invoices.php</a></li>";
        echo "<li><strong>Klik 'Detail'</strong> pada invoice untuk melihat lengkap</li>";
        echo "</ol>";
        
        echo "<h3>🔗 Link Langsung:</h3>";
        echo "<p><a href='pages/client/invoices.php?id=$invoice_id' target='_blank' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Lihat Invoice Detail</a></p>";
        
        echo "<h3>🗂️ Admin juga bisa melihat di:</h3>";
        echo "<p><a href='pages/admin/dashboard.php' target='_blank'>Admin Dashboard</a> (nanti akan ada menu manage invoices)</p>";
        
    } else {
        echo "<h2>❌ Gagal membuat test invoice</h2>";
    }
} else {
    echo "<h2>❌ Tidak ada client atau psychologist untuk testing</h2>";
}
?>
