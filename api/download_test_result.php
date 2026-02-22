<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Access denied');
}

$path = dirname(__DIR__) . '/';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($result_id <= 0) {
    http_response_code(400);
    die('Invalid request');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'client') {
    $client_result = $db->getPrepare(
        "SELECT client_id FROM client_details WHERE user_id = ?",
        [$user_id]
    );
    
    if (!$client_result) {
        http_response_code(403);
        die('Client not found');
    }
    
    $client_id = $client_result['client_id'];
    
    $result = $db->getPrepare(
        "SELECT result_id, file_hasil_tes, jenis_tes FROM test_results 
         WHERE result_id = ? AND client_id = ?",
        [$result_id, $client_id]
    );
    
    if (!$result) {
        http_response_code(404);
        die('Test result not found or access denied');
    }
    
} else {
    http_response_code(403);
    die('Only clients can download test results');
}

// Validate the result_id parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid request: Missing or invalid result_id');
}

// Ensure no output is sent before headers
if (ob_get_length()) {
    ob_clean();
}

try {
    $filename = $result['file_hasil_tes'] ?? '';
    // Normalize stored filename (DB might store a full/relative path)
    $stored = str_replace('\\', '/', $filename);
    $safe_name = basename($stored);
    if ($safe_name === '' || $safe_name === '.' || $safe_name === '..') {
        http_response_code(404);
        die('File not found');
    }

    // Candidate paths to try
    $candidates = [];
    // 1) If DB already stores a relative path like "uploads/results/abc.pdf"
    if ($stored && $stored !== $safe_name) {
        $candidates[] = $path . ltrim($stored, '/');
    }
    // 2) Default uploads/results/
    $candidates[] = $path . 'uploads/results/' . $safe_name;
    // 3) Alternative folder: uploads/test_results/
    $candidates[] = $path . 'uploads/test_results/' . $safe_name;

    $file_path = null;
    foreach ($candidates as $cand) {
        if (file_exists($cand)) {
            $file_path = $cand;
            break;
        }
    }

    // Add logging to debug file path issues
    error_log("Debugging download_test_result.php");
    error_log("Requested result_id: " . $result_id);
    error_log("Safe name: " . $safe_name);
    error_log("Candidate paths: " . implode(", ", $candidates));
    if ($file_path) {
        error_log("File found at: " . $file_path);
    } else {
        error_log("File not found in any candidate paths");
    }

    // Add additional logging for debugging
    error_log("Starting file download process");
    error_log("Session user_id: " . $_SESSION['user_id']);
    error_log("Session role: " . $_SESSION['role']);
    error_log("Requested result_id: " . $result_id);

    if (!$file_path || !file_exists($file_path)) {
        error_log("File not found: " . $safe_name);
        ob_clean();
        header('Content-Type: text/plain');
        die('File not found: ' . $safe_name);
    }

    if (!is_readable($file_path)) {
        error_log("File is not readable: " . $file_path);
        http_response_code(403);
        die('File cannot be read');
    }

    // Ensure headers are not sent prematurely
    if (headers_sent()) {
        error_log("Headers already sent before file download");
        exit('Headers already sent');
    }

    // Final check headers not sent
    if (headers_sent()) {
        exit('Headers already sent');
    }

    header('Content-Type: application/pdf');
    // Safer download filename (avoid invalid header chars)
    $jenis = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string)($result['jenis_tes'] ?? 'Hasil_Tes'));
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="Laporan_' . $jenis . '_' . date('d-m-Y') . '.pdf"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    readfile($file_path);
    exit;
} catch (Throwable $e) {
    // Fallback: laporkan error sebagai text supaya kelihatan di browser
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'Download failed: ' . $e->getMessage();
    exit;
}
?>