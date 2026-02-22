<?php
/**
 * API: get_available_dates.php
 * Description: Fetch all available dates for a psychologist within a date range
 * Used by: Client booking form date picker
 */

require_once '../../includes/db.php';

header('Content-Type: application/json');

$psychologist_id = intval($_GET['psychologist_id'] ?? 0);
$start_date = trim($_GET['start_date'] ?? '');
$end_date = trim($_GET['end_date'] ?? '');

if (!$psychologist_id) {
    echo json_encode(['success' => false, 'message' => 'Psychologist ID required']);
    exit;
}

// Default to current month if not specified
if (!$start_date || !$end_date) {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    echo json_encode(['success' => false, 'message' => 'Format tanggal tidak valid']);
    exit;
}

$db = new Database();
$db->connect();

try {
    // Get all available dates for this psychologist
    $sql = "SELECT DISTINCT tanggal, COUNT(*) as slot_count
            FROM psychologist_schedule_dates 
            WHERE psychologist_id = ? 
              AND tanggal BETWEEN ? AND ?
              AND COALESCE(is_available, 1) = 1
            GROUP BY tanggal
            ORDER BY tanggal ASC";
    
    $result = $db->queryPrepare($sql, [$psychologist_id, $start_date, $end_date]);
    
    if (is_array($result) && count($result) > 0) {
        $dates = [];
        foreach ($result as $row) {
            $dates[] = [
                'tanggal' => $row['tanggal'],
                'slot_count' => intval($row['slot_count'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'dates' => $dates,
            'count' => count($dates)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'dates' => [],
            'count' => 0,
            'message' => 'Tidak ada tanggal tersedia dalam periode ini'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
