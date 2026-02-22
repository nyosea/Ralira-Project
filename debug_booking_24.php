<?php
require '../../includes/db.php';
$db = new Database();
$db->connect();

$booking_id = 24;

echo "=== DEBUGGING BOOKING #24 ===\n\n";

// Check booking data
echo "1. BOOKING DATA:\n";
$booking = $db->getPrepare('SELECT * FROM consultation_bookings WHERE booking_id = ?', [$booking_id]);
if($booking) {
    foreach($booking as $key => $val) {
        echo "  $key: $val\n";
    }
} else {
    echo "  ❌ Booking not found!\n";
}

// Check payment data for this client
if($booking) {
    echo "\n2. PAYMENTS for Client {$booking['client_id']}:\n";
    $payments = $db->queryPrepare('SELECT * FROM payments WHERE client_id = ?', [$booking['client_id']]);
    if($payments && count($payments) > 0) {
        foreach($payments as $p) {
            echo "  - Payment ID: {$p['payment_id']}\n";
            echo "    Tanggal Transfer: {$p['tanggal_transfer']}\n";
            echo "    Bukti: {$p['bukti_transfer']}\n";
            echo "    Status: {$p['status_pembayaran']}\n";
        }
    } else {
        echo "  ❌ No payments found for this client!\n";
    }
}

// Test the existing JOIN query
echo "\n3. TEST EXISTING JOIN QUERY:\n";
$sql = "SELECT 
            cb.booking_id,
            p.payment_id,
            p.tanggal_transfer,
            p.bukti_transfer,
            p.status_pembayaran,
            DATE(cb.created_at) as booking_date
        FROM consultation_bookings cb
        LEFT JOIN payments p ON cb.client_id = p.client_id AND p.tanggal_transfer = DATE(cb.created_at)
        WHERE cb.booking_id = ?";

$result = $db->getPrepare($sql, [$booking_id]);
if($result) {
    echo "  ✅ Query returned data:\n";
    foreach($result as $key => $val) {
        echo "    $key: $val\n";
    }
} else {
    echo "  ❌ Query returned NULL - payment not found with JOIN!\n";
}

// Better approach - get latest payment for client
echo "\n4. BETTER APPROACH - LATEST PAYMENT:\n";
if($booking) {
    $latest = $db->getPrepare(
        'SELECT * FROM payments WHERE client_id = ? ORDER BY created_at DESC LIMIT 1', 
        [$booking['client_id']]
    );
    if($latest) {
        echo "  ✅ Found latest payment:\n";
        echo "    Bukti: {$latest['bukti_transfer']}\n";
        echo "    Status: {$latest['status_pembayaran']}\n";
    } else {
        echo "  ❌ No payment at all!\n";
    }
}
?>
