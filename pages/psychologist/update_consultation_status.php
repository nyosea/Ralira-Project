<?php
/**
 * Filename: pages/psychologist/update_consultation_status.php
 * Description: API endpoint untuk update status konsultasi oleh psikolog
 */

// Disable error display for clean JSON response
error_reporting(0);
ini_set('display_errors', 0);

session_start();
$path = '../../';

// Check if user is logged in and is psychologist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Get POST data
$booking_id = $_POST['booking_id'] ?? '';
$new_status = $_POST['status'] ?? '';

// Validate input
if (empty($booking_id) || empty($new_status)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Validate status value
$valid_statuses = ['belum_ditangani', 'sedang_ditangani', 'sudah_ditangani'];
if (!in_array($new_status, $valid_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

// Get psychologist ID from session
$user_id = $_SESSION['user_id'];
$db = new Database();
$db->connect();

$psychologist_data = $db->getPrepare("SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?", [$user_id]);
$psychologist_id = $psychologist_data['psychologist_id'] ?? null;

if (!$psychologist_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Psikolog tidak ditemukan']);
    exit;
}

try {
    // Verify that this booking belongs to the current psychologist
    $sql_verify = "SELECT psychologist_id FROM consultation_bookings WHERE booking_id = ?";
    $booking = $db->getPrepare($sql_verify, [$booking_id]);
    
    if (!$booking || $booking['psychologist_id'] != $psychologist_id) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Anda tidak memiliki akses untuk booking ini',
            'debug' => [
                'booking_id' => $booking_id,
                'psychologist_id' => $psychologist_id,
                'booking_psychologist_id' => $booking['psychologist_id'] ?? 'not found'
            ]
        ]);
        exit;
    }
    
    // Check if consultation status already exists for this booking
    $sql_check = "SELECT status_id FROM consultation_status WHERE booking_id = ?";
    $existing = $db->getPrepare($sql_check, [$booking_id]);
    
    if ($existing) {
        // Update existing status
        $sql_update = "UPDATE consultation_status 
                      SET konsultasi_status = ? 
                      WHERE booking_id = ?";
        $result = $db->executePrepare($sql_update, [$new_status, $booking_id]);
        
        // Check if update was successful
        if ($result === false) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal update status di database',
                'debug' => [
                    'query' => $sql_update,
                    'params' => [$new_status, $booking_id],
                    'booking_id' => $booking_id
                ]
            ]);
            exit;
        }
    } else {
        // Get client_id from booking
        $sql_client = "SELECT client_id FROM consultation_bookings WHERE booking_id = ?";
        $client_data = $db->getPrepare($sql_client, [$booking_id]);
        $client_id = $client_data['client_id'] ?? null;
        
        if (!$client_id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Client tidak ditemukan']);
            exit;
        }
        
        // Insert new status
        $sql_insert = "INSERT INTO consultation_status 
                      (booking_id, psychologist_id, client_id, konsultasi_status, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, NOW(), NOW())";
        $result = $db->executePrepare($sql_insert, [$booking_id, $psychologist_id, $client_id, $new_status]);
    }
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Status berhasil diperbarui'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Gagal memperbarui status di database',
            'debug' => [
                'booking_id' => $booking_id,
                'new_status' => $new_status,
                'existing' => $existing,
                'query_result' => $result
            ]
        ]);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        'debug' => [
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
