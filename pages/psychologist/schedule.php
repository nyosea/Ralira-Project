<?php
/**
 * Filename: pages/psychologist/schedule.php
 * Description: Manajemen Jadwal Psikolog dengan Admin-style Layout
 * Features: 2-column layout + Calendar + Time slots + Full admin functionality
 */

session_start();
$path = '../../';
$page_title = 'Atur Jadwal Praktik';

// Check if user is logged in and is psychologist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'psychologist') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();

$error = '';
$success = '';

// Get current psychologist ID
$user_id = $_SESSION['user_id'];
$sql = "SELECT psychologist_id FROM psychologist_profiles WHERE user_id = ?";
$result = $db->getPrepare($sql, [$user_id]);
$selected_psychologist = $result['psychologist_id'] ?? null;

if (!$selected_psychologist) {
    $error = 'Profil psikolog tidak ditemukan!';
}

// Define jam slots (2 jam per slot)
$jam_slots_display = ['09:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00'];

// Get saved schedules for this psychologist
$saved_schedules = [];
if ($selected_psychologist) {
    $sql = "SELECT schedule_date_id, tanggal, jam_mulai, jam_selesai, has_booking
            FROM psychologist_schedule_dates 
            WHERE psychologist_id = ?
            ORDER BY tanggal DESC";
    $result = $db->queryPrepare($sql, [$selected_psychologist]);
    if (is_array($result)) {
        $saved_schedules = $result;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'save_schedule_dates') {
        $dates = json_decode($_POST['dates'], true);
        $times = json_decode($_POST['times'], true);
        
        // Validasi input
        if (!is_array($dates) || !is_array($times) || count($dates) === 0 || count($times) === 0) {
            echo json_encode(['success' => false, 'message' => 'Tanggal dan jam harus dipilih']);
            exit;
        }

        // Log untuk debugging
        error_log('SAVE SCHEDULE REQUEST: ' . json_encode([
            'psychologist_id' => $selected_psychologist,
            'dates_count' => count($dates),
            'times_count' => count($times),
            'dates' => $dates,
            'times' => $times
        ]));
        
        try {
            $inserted_count = 0;
            $skipped_count = 0;
            
            // Validasi dan filter tanggal
            $valid_dates = [];
            foreach ($dates as $tanggal) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                    $valid_dates[] = $tanggal;
                } else {
                    $skipped_count++;
                    error_log("INVALID DATE FORMAT: $tanggal");
                }
            }
            
            // Validasi dan filter jam
            $valid_times = [];
            foreach ($times as $time) {
                if (preg_match('/^\d{2}:\d{2}-\d{2}:\d{2}$/', $time)) {
                    $valid_times[] = $time;
                } else {
                    error_log("INVALID TIME FORMAT: $time");
                }
            }
            
            if (count($valid_dates) === 0 || count($valid_times) === 0) {
                echo json_encode(['success' => false, 'message' => 'Format tanggal atau jam tidak valid']);
                exit;
            }
            
            // Insert untuk setiap kombinasi tanggal dan jam
            foreach ($valid_dates as $tanggal) {
                foreach ($valid_times as $time) {
                    $time_parts = explode('-', $time);
                    $jam_mulai = trim($time_parts[0]);
                    $jam_selesai = trim($time_parts[1]);
                    
                    // Cek duplikat
                    $check_sql = "SELECT schedule_date_id FROM psychologist_schedule_dates 
                                  WHERE psychologist_id = ? AND tanggal = ? AND jam_mulai = ? AND jam_selesai = ? LIMIT 1";
                    $check = $db->getPrepare($check_sql, [$selected_psychologist, $tanggal, $jam_mulai, $jam_selesai]);
                    
                    if (!$check) {
                        try {
                            $insert_sql = "INSERT INTO psychologist_schedule_dates 
                                          (psychologist_id, tanggal, jam_mulai, jam_selesai) 
                                          VALUES (?, ?, ?, ?)";
                            $db->executePrepare($insert_sql, [$selected_psychologist, $tanggal, $jam_mulai, $jam_selesai]);
                            $inserted_count++;
                        } catch (Exception $insertError) {
                            // Handle duplicate entry '0' error - auto fix database issue
                            if (strpos($insertError->getMessage(), "Duplicate entry '0'") !== false || 
                                strpos($insertError->getMessage(), "Duplicate entry") !== false) {
                                error_log("AUTO-FIX: Detected duplicate entry error, attempting to fix...");
                                
                                try {
                                    // Step 1: Get next available ID
                                    $max_id_sql = "SELECT IFNULL(MAX(schedule_date_id), 0) + 1 AS next_id FROM psychologist_schedule_dates";
                                    $max_result = $db->getPrepare($max_id_sql, []);
                                    $next_id = isset($max_result['next_id']) ? intval($max_result['next_id']) : 1;
                                    
                                    // Step 2: Delete any existing row with id = 0 for this exact combination
                                    $delete_zero_sql = "DELETE FROM psychologist_schedule_dates 
                                                       WHERE schedule_date_id = 0 
                                                       AND psychologist_id = ? 
                                                       AND tanggal = ? 
                                                       AND jam_mulai = ? 
                                                       AND jam_selesai = ?";
                                    $db->executePrepare($delete_zero_sql, [$selected_psychologist, $tanggal, $jam_mulai, $jam_selesai]);
                                    
                                    // Step 3: Try to fix any remaining rows with id = 0 (update them to new IDs)
                                    $fix_zero_sql = "SELECT schedule_date_id FROM psychologist_schedule_dates WHERE schedule_date_id = 0 LIMIT 1";
                                    $has_zero = $db->getPrepare($fix_zero_sql, []);
                                    if ($has_zero) {
                                        // Update first zero ID row
                                        $update_zero_sql = "UPDATE psychologist_schedule_dates SET schedule_date_id = ? WHERE schedule_date_id = 0 LIMIT 1";
                                        $db->executePrepare($update_zero_sql, [$next_id]);
                                        $next_id++;
                                    }
                                    
                                    // Step 4: Insert with explicit ID to avoid auto-increment issues
                                    $insert_with_id_sql = "INSERT INTO psychologist_schedule_dates 
                                                          (schedule_date_id, psychologist_id, tanggal, jam_mulai, jam_selesai) 
                                                          VALUES (?, ?, ?, ?, ?)";
                                    $db->executePrepare($insert_with_id_sql, [$next_id, $selected_psychologist, $tanggal, $jam_mulai, $jam_selesai]);
                                    $inserted_count++;
                                    error_log("AUTO-FIX: Successfully inserted with ID $next_id");
                                } catch (Exception $fixError) {
                                    error_log("AUTO-FIX FAILED: " . $fixError->getMessage());
                                    // Re-throw original error if fix fails
                                    throw $insertError;
                                }
                            } else {
                                // Re-throw if it's a different error
                                throw $insertError;
                            }
                        }
                    } else {
                        error_log("DUPLICATE SCHEDULE: psy_id=$selected_psychologist, tanggal=$tanggal, jam_mulai=$jam_mulai");
                    }
                }
            }
            
            // Log hasil
            error_log("SCHEDULE SAVE RESULT: inserted=$inserted_count, skipped=$skipped_count");
            
            echo json_encode(['success' => true, 'message' => "Jadwal berhasil disimpan! ($inserted_count jadwal baru)"]);
        } catch (Exception $e) {
            error_log("SCHEDULE SAVE ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'delete_schedule') {
        $schedule_date_id = intval($_POST['schedule_date_id']);
        
        $sql = "DELETE FROM psychologist_schedule_dates 
                WHERE schedule_date_id = ? AND psychologist_id = ?";
        
        if ($db->executePrepare($sql, [$schedule_date_id, $selected_psychologist])) {
            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus jadwal']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_schedules_for_date') {
        $tanggal = $_POST['tanggal'];
        
        $sql = "SELECT schedule_date_id, jam_mulai, jam_selesai, has_booking FROM psychologist_schedule_dates 
                WHERE psychologist_id = ? AND tanggal = ?
                ORDER BY jam_mulai";
        
        $result = $db->queryPrepare($sql, [$selected_psychologist, $tanggal]);
        
        if (is_array($result)) {
            echo json_encode(['success' => true, 'schedules' => $result]);
        } else {
            echo json_encode(['success' => true, 'schedules' => []]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'delete_multiple_schedules') {
        $schedule_ids = json_decode($_POST['schedule_ids'], true);
        
        if (!is_array($schedule_ids) || count($schedule_ids) === 0) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada jadwal yang dipilih']);
            exit;
        }
        
        try {
            $schedules_with_bookings = [];
            $deleted_count = 0;
            
            foreach ($schedule_ids as $schedule_id) {
                $check_booking_sql = "SELECT cb.booking_id 
                                     FROM consultation_bookings cb
                                     WHERE cb.schedule_id = ? AND cb.status_booking != 'canceled'";
                $has_booking = $db->getPrepare($check_booking_sql, [$schedule_id]);
                
                if ($has_booking) {
                    $schedules_with_bookings[] = $schedule_id;
                } else {
                    $delete_sql = "DELETE FROM psychologist_schedule_dates 
                                  WHERE schedule_date_id = ? AND psychologist_id = ?";
                    if ($db->executePrepare($delete_sql, [$schedule_id, $selected_psychologist])) {
                        $deleted_count++;
                    }
                }
            }
            
            if (count($schedules_with_bookings) > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Beberapa jadwal memiliki booking aktif dan tidak dapat dihapus',
                    'booked_schedule_ids' => $schedules_with_bookings
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => "$deleted_count jadwal berhasil dihapus",
                    'deleted_count' => $deleted_count
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Rali Ra</title>
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/glass.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/psychologist.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/schedule_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>
    <style>
        /* Animasi untuk kalender tanggal */
        @keyframes pulse-select {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(251, 186, 0, 0.7);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 12px rgba(251, 186, 0, 0);
            }
        }

        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce-check {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }

        @keyframes glow {
            0% {
                box-shadow: 0 0 5px rgba(251, 186, 0, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(251, 186, 0, 0.8);
            }
            100% {
                box-shadow: 0 0 5px rgba(251, 186, 0, 0.5);
            }
        }

        .calendar-day {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .calendar-day.selected {
            animation: pulse-select 0.6s ease-out;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(251, 186, 0, 0.3);
        }

        .calendar-day:not(.disabled):not(.other-month):hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        @keyframes shake {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.05) rotate(-1deg); }
            50% { transform: scale(1.08) rotate(1deg); }
            75% { transform: scale(1.05) rotate(-1deg); }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        @keyframes pulse-strong {
            0% {
                box-shadow: 0 0 0 0 rgba(251, 186, 0, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(251, 186, 0, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(251, 186, 0, 0);
            }
        }

        .time-slot-checkbox:checked + .time-slot-label {
            animation: bounce-check 0.4s ease-out, pulse-strong 2s infinite;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(251, 186, 0, 0.4), inset 0 0 10px rgba(255, 255, 255, 0.2);
            border-color: var(--color-primary);
            position: relative;
            overflow: hidden;
        }

        /* Hilangkan animasi saat unchecked */
        .time-slot-checkbox:not(:checked) + .time-slot-label {
            animation: none !important;
        }

        .time-slot-checkbox:checked + .time-slot-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        .time-slot-checkbox:checked + .time-slot-label::after {
            content: '✓';
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            font-size: 18px;
            animation: bounce-check 0.6s ease-out;
        }

        .time-slot-label {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }

        .time-slot-label.enabled:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(251, 186, 0, 0.2);
            border-color: var(--color-primary);
            background: rgba(251, 186, 0, 0.05);
        }

        /* Highlight unchecked enabled slots on hover */
        .time-slot-checkbox:not(:checked).enabled + .time-slot-label:hover {
            border: 2px solid var(--color-primary);
            color: var(--color-text);
        }

        .selected-date-item {
            animation: slide-in 0.4s ease-out;
        }

        .calendar-day.has-schedule:not(.selected) {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border: 2px solid #28a745;
            font-weight: 500;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Button animations */
        button[type="button"] {
            position: relative;
            overflow: hidden;
        }

        button[type="button"]:active {
            transform: scale(0.98);
        }

        /* Smooth transitions untuk list items */
        .selected-date-item button.btn-remove {
            transition: all 0.3s ease;
        }

        .selected-date-item button.btn-remove:hover {
            background: #c82333 !important;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        /* Smooth transitions untuk notifikasi */
        .schedule-notification {
            animation: slide-in 0.4s ease-out;
        }

        /* Calendar day transitions */
        .calendar-day:not(.disabled):not(.other-month):active {
            transform: scale(0.95);
        }

        /* Time slot transitions */
        .time-slot-label:not(.not-allowed):active {
            transform: scale(0.95);
        }
    </style>
        <?php include $path . 'components/sidebar_psychologist.php'; ?>
        <?php include $path . 'components/header_psychologist.php'; ?>

        <main class="main-content">
            
            <!-- Modern Header -->
            <div class="schedule-page-header">
                <div class="schedule-page-content">
                    <div>
                        <h2>
                            <i class="fas fa-calendar-alt"></i>
                            Atur Jadwal Praktik
                        </h2>
                        <p>Kelola jadwal konsultasi Anda dengan mudah</p>
                    </div>
                </div>
            </div>

            <!-- Notification Messages -->
            <?php if ($error): ?>
                <div class="schedule-notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="schedule-notification success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Sukses:</strong> <?php echo htmlspecialchars($success); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($selected_psychologist): ?>
                <!-- Main Schedule Management -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    
                    <!-- Time Slot Selector -->
                    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                            <div>
                                <h3 style="margin: 0; color: var(--color-text); font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-clock" style="color: var(--color-primary); font-size: 18px;"></i>
                                    Pilih Jam Kerja
                                </h3>
                                <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 14px;">Pilih jam yang tersedia untuk konsultasi</p>
                            </div>
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-clock" style="color: white; font-size: 20px;"></i>
                            </div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid var(--color-primary);">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-info-circle" style="color: var(--color-primary); font-size: 16px;"></i>
                                <span style="color: #495057; font-size: 14px; font-weight: 500;">Pilih tanggal terlebih dahulu, lalu pilih jam yang ingin dijadwalkan</span>
                            </div>
                        </div>

                        <div class="time-slot-grid" id="timeSlotGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-bottom: 20px;">
                            <?php foreach ($jam_slots_display as $index => $display): ?>
                                <div class="time-slot-item" style="position: relative;">
                                    <input type="checkbox" 
                                           id="jam_<?php echo $index; ?>" 
                                           class="time-slot-checkbox" 
                                           value="<?php echo $display; ?>"
                                           disabled
                                           style="position: absolute; opacity: 0; width: 0; height: 0;">
                                    <label for="jam_<?php echo $index; ?>" class="time-slot-label" style="display: block; padding: 12px 15px; background: white; border: 2px solid #e1e8ed; color: var(--color-text); border-radius: 10px; text-align: center; cursor: not-allowed; transition: all 0.3s ease; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1); opacity: 0.6;">
                                        <i class="fas fa-clock" style="margin-right: 8px; color: var(--color-primary);"></i>
                                        <?php echo $display; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn-clear-times" id="btnClearTimes" style="flex: 1; padding: 12px 20px; background: #6c757d; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fas fa-times"></i> Bersihkan
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Selector -->
                    <div id="calendarSection" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                            <div>
                                <h3 style="margin: 0; color: var(--color-text); font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-calendar-days" style="color: var(--color-primary); font-size: 18px;"></i>
                                    Pilih Tanggal
                                </h3>
                                <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 14px;">Pilih tanggal untuk jadwal kerja</p>
                            </div>
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar-days" style="color: white; font-size: 20px;"></i>
                            </div>
                        </div>
                        
                        <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px;">
                            <button type="button" class="calendar-nav" id="btnPrevMonth" style="padding: 8px 12px; background: var(--color-primary); color: white; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; min-width: 40px;">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="calendar-month-year" id="calendarMonthYear" style="font-weight: 600; color: var(--color-text); font-size: 16px;"></span>
                            <button type="button" class="calendar-nav" id="btnNextMonth" style="padding: 8px 12px; background: var(--color-primary); color: white; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; min-width: 40px;">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>

                        <div class="calendar-grid" id="calendarGrid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-bottom: 20px;"></div>

                        <div class="calendar-legend" style="display: flex; gap: 20px; justify-content: center; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                            <div class="legend-item" style="display: flex; align-items: center; gap: 8px;">
                                <div class="legend-box available" style="width: 20px; height: 20px; background: #e8f5e9; border-radius: 4px;"></div>
                                <span style="font-size: 14px; color: #495057;">Tersedia</span>
                            </div>
                            <div class="legend-item" style="display: flex; align-items: center; gap: 8px;">
                                <div class="legend-box today" style="width: 20px; height: 20px; background: var(--color-primary); border-radius: 4px;"></div>
                                <span style="font-size: 14px; color: #495057;">Hari Ini</span>
                            </div>
                        </div>
                        
                        <!-- Edit Mode Notification -->
                        <div id="editModeNotif" style="display: none; padding: 15px 20px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 4px solid #ffc107; border-radius: 12px; margin-bottom: 20px; color: #856404;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-edit" style="font-size: 18px;"></i>
                                    <div>
                                        <strong style="display: block; margin-bottom: 4px;">Mode Mengedit Jadwal</strong>
                                        <span style="font-size: 14px;">Uncheck jam yang ingin dihapus, lalu klik Simpan</span>
                                    </div>
                                </div>
                                <button type="button" onclick="exitEditMode()" style="background: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-times"></i> Keluar Edit
                                </button>
                            </div>
                        </div>

                        <div class="selected-dates-container" style="margin-bottom: 20px;">
                            <h4 style="margin: 0 0 15px 0; color: var(--color-text); font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-calendar-check" style="color: var(--color-primary);"></i>
                                Tanggal Terpilih
                            </h4>
                            <div class="selected-dates-list" id="selectedDatesList" style="min-height: 60px; padding: 15px; background: #f8f9fa; border-radius: 10px; border: 2px dashed #dee2e6;">
                                <p style="color: #6c757d; font-style: italic; text-align: center; margin: 0;">Tidak ada tanggal yang dipilih</p>
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <button type="button" class="btn-apply-times" id="btnSaveSchedule" style="flex: 1; padding: 12px 20px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fas fa-check"></i> Simpan Jadwal
                            </button>
                            <button type="button" class="btn-clear-times" id="btnClearDates" style="flex: 1; padding: 12px 20px; background: #6c757d; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                        
                        <div style="display: flex; gap: 15px;">
                            <button type="button" class="btn-apply-times" id="btnDeleteSchedule" style="flex: 1; padding: 12px 20px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fas fa-trash"></i> Batalkan Jadwal
                            </button>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div style="background: white; border-radius: 20px; padding: 60px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed; text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-exclamation-triangle" style="color: #6c757d; font-size: 32px;"></i>
                    </div>
                    <div style="font-weight: 600; font-size: 18px; margin-bottom: 10px; color: var(--color-text);">Profil Tidak Ditemukan</div>
                    <div style="font-size: 14px; color: #6c757d;">Profil psikolog tidak ditemukan. Silakan hubungi administrator.</div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Notification Element -->
    <div id="scheduleNotification" style="display: none; position: fixed; top: 20px; right: 20px; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); overflow: hidden; z-index: 2000; max-width: 400px; animation: slideInRight 0.3s ease;">
        <div style="display: flex; align-items: flex-start; gap: 16px; padding: 16px 20px;">
            <div class="notification-icon" style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="notification-icon-inner fas fa-check" style="font-size: 24px; color: white;"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div id="notificationTitle" style="font-weight: 700; font-size: 15px; color: #1a1a1a; margin-bottom: 4px;">Sukses</div>
                <div id="notificationMessage" style="font-size: 13px; color: #666; line-height: 1.4;">Jadwal berhasil disimpan</div>
            </div>
            <button type="button" onclick="closeNotification()" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: #999; padding: 0; flex-shrink: 0;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Delete Schedule Modal (added to ensure JS can find it) -->
    <div id="deleteScheduleModal" style="display: none; position: fixed; inset: 0; align-items: center; justify-content: center; z-index: 1000;">
        <div style="display:flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
            <div style="background: white; border-radius: 8px; width: 90%; max-width: 540px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
                <div style="padding: 18px; border-bottom: 1px solid #e0e0e0; display:flex; align-items:center; justify-content:space-between;">
                    <h3 style="margin:0; font-size: 16px;">Batalkan Jadwal</h3>
                    <button type="button" onclick="closeDeleteModal()" style="background: transparent; border: none; font-size: 18px; cursor: pointer;">&times;</button>
                </div>
                <div style="padding: 16px; max-height: 300px; overflow-y: auto;">
                    <div id="deleteScheduleList" style="display:flex; flex-direction:column; gap:8px;"></div>
                </div>
                <div style="padding: 12px 16px; border-top: 1px solid #e0e0e0; display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" onclick="closeDeleteModal()" style="padding: 8px 14px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">Batal</button>
                    <button type="button" onclick="confirmDeleteSchedules()" style="padding: 8px 14px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer;">Hapus Terpilih</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Animation Styles -->
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .notification-hide {
            animation: slideOutRight 0.3s ease forwards !important;
        }
    </style>

    <!-- Pass saved schedules to JavaScript -->
    <script>
        window.selectedPsychologistId = <?php echo json_encode($selected_psychologist); ?>;
        window.savedSchedulesData = <?php echo json_encode($saved_schedules); ?>;
        window.apiPath = '<?php echo $path; ?>api/';
    </script>

    <script src="<?php echo $path; ?>assets/js/schedule.js"></script>
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>
