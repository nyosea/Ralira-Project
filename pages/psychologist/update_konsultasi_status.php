<?php
/**
 * Filename: pages/psychologist/update_konsultasi_status.php
 * Description: Update status konsultasi (belum_ditangani -> sedang_ditangani -> sudah_ditangani)
 */

session_start();
$path = '../../';

// Check if user is logged in and is psychologist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$user_id = $_SESSION['user_id'];
$booking_id = intval($_POST['booking_id'] ?? 0);
$new_status = $_POST['konsultasi_status'] ?? '';

// Get psychologist_id from user_id
$psych_profile = $db->getPrepare("SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?", [$user_id]);
$psychologist_id = $psych_profile['psychologist_id'];

// Validate
if (!$booking_id || !in_array($new_status, ['belum_ditangani', 'sedang_ditangani', 'sudah_ditangani'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: client_detail.php?booking_id=' . $booking_id);
    exit;
}

// Check if consultation_status record exists, if not create it
$check_sql = "SELECT status_id FROM consultation_status WHERE booking_id = ?";
$existing = $db->getPrepare($check_sql, [$booking_id]);

try {
    if ($existing) {
        // Update existing record
        $sql = "UPDATE consultation_status 
                  SET konsultasi_status = ?, 
                      updated_by_user_id = ?,
                      updated_at = NOW() 
                  WHERE booking_id = ?";
        
        $db->queryPrepare($sql, [$new_status, $user_id, $booking_id]);
    } else {
        // Create new record
        $get_booking = $db->getPrepare("SELECT psychologist_id, client_id FROM consultation_bookings WHERE booking_id = ?", [$booking_id]);
        
        if ($get_booking) {
            $sql = "INSERT INTO consultation_status 
                      (booking_id, psychologist_id, client_id, konsultasi_status, updated_by_user_id)
                      VALUES (?, ?, ?, ?, ?)";
            
            $db->queryPrepare($sql, [
                $booking_id,
                $get_booking['psychologist_id'],
                $get_booking['client_id'],
                $new_status,
                $user_id
            ]);
        }
    }
    
    $_SESSION['success'] = 'Status konsultasi berhasil diperbarui!';
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Gagal memperbarui status: ' . $e->getMessage();
}

header('Location: client_detail.php?booking_id=' . $booking_id);
exit;
?>
