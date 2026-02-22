<?php
require '../../includes/db.php';
$db = new Database();
$db->connect();

echo "=== CHECKING DATABASE STRUCTURE ===\n\n";

// Check payments table
echo "1. PAYMENTS TABLE STRUCTURE:\n";
$payments = $db->query('DESCRIBE payments');
foreach($payments as $col) {
    echo "  - {$col['Field']}: {$col['Type']}\n";
}

echo "\n2. CONSULTATION_BOOKINGS TABLE (relevant columns):\n";
$bookings = $db->query('DESCRIBE consultation_bookings');
foreach($bookings as $col) {
    if(in_array($col['Field'], ['booking_id', 'client_id', 'created_at', 'status_booking'])) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }
}

echo "\n3. SAMPLE DATA - Booking #24:\n";
$booking = $db->getPrepare('SELECT booking_id, client_id, created_at FROM consultation_bookings WHERE booking_id = ?', [24]);
if($booking) {
    echo "  - Booking ID: {$booking['booking_id']}\n";
    echo "  - Client ID: {$booking['client_id']}\n";
    echo "  - Created At: {$booking['created_at']}\n";
}

echo "\n4. PAYMENTS DATA for Client from Booking #24:\n";
if($booking) {
    $payments_data = $db->queryPrepare('SELECT * FROM payments WHERE client_id = ?', [$booking['client_id']]);
    if($payments_data) {
        foreach($payments_data as $p) {
            echo "  - Payment ID: {$p['payment_id']}\n";
            echo "    Client ID: {$p['client_id']}\n";
            echo "    Tanggal Transfer: {$p['tanggal_transfer']}\n";
            echo "    Bukti Transfer: {$p['bukti_transfer']}\n";
            echo "    Status: {$p['status_pembayaran']}\n";
            echo "    Created At: {$p['created_at']}\n\n";
        }
    } else {
        echo "  ❌ NO PAYMENTS FOUND for this client!\n";
    }
}

echo "\n5. DIRECT PAYMENT DATA for ALL:\n";
$all_payments = $db->query('SELECT payment_id, client_id, tanggal_transfer, bukti_transfer, status_pembayaran FROM payments LIMIT 5');
foreach($all_payments as $p) {
    echo "  Payment #{$p['payment_id']}: Client {$p['client_id']}, Status: {$p['status_pembayaran']}, Bukti: {$p['bukti_transfer']}\n";
}
?>
