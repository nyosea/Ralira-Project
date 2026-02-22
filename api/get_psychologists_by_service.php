<?php
/**
 * API untuk get psychologist by service type
 * Mengembalikan daftar psikolog yang sesuai dengan layanan yang dipilih
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db.php';

$response = [
    'success' => false,
    'data' => [],
    'error' => null
];

try {
    $service_type = isset($_GET['service_type']) ? trim($_GET['service_type']) : '';
    
    if (!$service_type) {
        throw new Exception('Tipe layanan tidak diberikan');
    }
    
    $db = new Database();
    $db->connect();
    
    // Query untuk mendapatkan psikolog berdasarkan spesialisasi/layanan
    $sql = "SELECT pp.psychologist_id, u.user_id, u.name, pp.spesialisasi 
            FROM psychologist_profiles pp 
            INNER JOIN users u ON pp.user_id = u.user_id 
            WHERE u.role = 'psychologist'
            ORDER BY u.name ASC";
    
    $all_psychologists = $db->queryPrepare($sql);
    
    if (!$all_psychologists) {
        $all_psychologists = [];
    }
    
    // Filter berdasarkan spesialisasi atau nama mapping
    $filtered = [];
    
    // Mapping nama psikolog ke layanan (dari booking.php)
    $nameMapping = [
        'konseling_anak' => ['ira puspitawati', 'ratriana naila syafira'],
        'konseling_dewasa' => ['refandi irfan faisal', 'claudia morin'],
        'rekrutmen_karyawan' => ['nurul qomariah'],
        'pengembangan_diri' => ['adisti natalia']
    ];
    
    // Mapping spesialisasi ke layanan
    $specMapping = [
        'konseling_anak' => ['anak', 'children'],
        'konseling_remaja' => ['remaja', 'adolescent', 'teenager'],
        'konseling_dewasa' => ['dewasa', 'adult'],
        'konseling_keluarga_pernikahan' => ['keluarga', 'family', 'pernikahan', 'marriage', 'pasangan', 'couple'],
        'pengembangan_diri' => ['pengembangan diri', 'self development', 'personal growth'],
        'rekrutmen_karyawan' => ['hrd', 'industri', 'organisasi', 'recruitment', 'industry', 'organizational']
    ];
    
    $nameTargets = $nameMapping[$service_type] ?? [];
    $specTargets = $specMapping[$service_type] ?? [];
    
    foreach ($all_psychologists as $psych) {
        $name_lower = strtolower($psych['name']);
        $spec_lower = strtolower($psych['spesialisasi'] ?? '');
        
        // Cek nama mapping
        $match_name = false;
        foreach ($nameTargets as $target_name) {
            if (stripos($name_lower, $target_name) !== false) {
                $match_name = true;
                break;
            }
        }
        
        // Cek spesialisasi mapping
        $match_spec = false;
        foreach ($specTargets as $target_spec) {
            if (stripos($spec_lower, $target_spec) !== false) {
                $match_spec = true;
                break;
            }
        }
        
        if ($match_name || $match_spec) {
            $filtered[] = $psych;
        }
    }
    
    $response['success'] = true;
    $response['data'] = $filtered;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>