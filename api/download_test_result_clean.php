<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
session_start();
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    header('Content-Type: text/plain');
    die('Not logged in. Session: ' . print_r($_SESSION, true));
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
$filename = $result['file_hasil_tes'];
$file_path = $path . 'uploads/results/' . $filename;
$file_path = str_replace('/', '\\', $file_path);
if (!file_exists($file_path)) {
    ob_clean();
    header('Content-Type: text/plain');
    die('File not found: ' . basename($file_path));
}
if (!is_readable($file_path)) {
    http_response_code(403);
    die('File cannot be read');
}
ob_clean();
header('Content-Type: application/pdf', true);
header('Content-Disposition: attachment; filename="Laporan_' . $result['jenis_tes'] . '_' . date('d-m-Y') . '.pdf"', true);
header('Content-Length: ' . filesize($file_path), true);
header('Cache-Control: no-cache, must-revalidate', true);
header('Pragma: no-cache', true);
header('X-Content-Type-Options: nosniff', true);
// readfile($file_path);
exit;
?>
