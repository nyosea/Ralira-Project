<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access denied');
}

$path = '../';
require_once $path . 'includes/db.php';

$db = new Database();
$db->connect();

$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($result_id <= 0) {
    http_response_code(400);
    exit('Invalid request');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role !== 'client') {
    http_response_code(403);
    exit('Only clients');
}

$client_result = $db->getPrepare(
    "SELECT client_id FROM client_details WHERE user_id = ?",
    [$user_id]
);

if (!$client_result) {
    http_response_code(403);
    exit('Client not found');
}

$client_id = $client_result['client_id'];

$result = $db->getPrepare(
    "SELECT result_id, file_hasil_tes, jenis_tes FROM test_results 
     WHERE result_id = ? AND client_id = ?",
    [$result_id, $client_id]
);

if (!$result) {
    http_response_code(404);
    exit('Not found');
}

$filename = $result['file_hasil_tes'];
$file_path = $path . 'uploads/results/' . $filename;

if (!file_exists($file_path)) {
    http_response_code(404);
    exit('File not found');
}

ob_end_clean();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Laporan_' . $result['jenis_tes'] . '_' . date('d-m-Y') . '.pdf"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($file_path);
exit;
