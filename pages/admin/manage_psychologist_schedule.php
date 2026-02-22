<?php
/**
 * Filename: pages/admin/manage_psychologist_schedule.php
 * Description: Manajemen Jadwal Kerja Psikolog (Calendar + Time Slots UI)
 */

session_start();
$path = '../../';
$page_title = 'Manajemen Jadwal Psikolog';

// Handle AJAX requests FIRST - before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Include database helper
    require_once $path . 'includes/db.php';
    
    // Initialize database
    $db = new Database();
    $db->connect();
    
    // Get current output buffer content
    $buffer_content = ob_get_contents();
    
    // Clean buffer completely
    ob_end_clean();
    
    // Check for any unwanted output
    if (!empty($buffer_content)) {
        error_log("Unexpected output before JSON: " . $buffer_content);
    }
    
    // Set JSON header
    header('Content-Type: application/json');
    
    // Disable any further output buffering
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if ($_POST['action'] === 'save_schedule_dates') {
        $psychologist_id = intval($_POST['psychologist_id']);
        $dates = json_decode($_POST['dates'], true);
        $times = json_decode($_POST['times'], true);
        
        if (!is_array($dates) || !is_array($times) || count($dates) === 0 || count($times) === 0) {
            echo json_encode(['success' => false, 'message' => 'Tanggal dan jam harus dipilih']);
            exit;
        }
        
        try {
            foreach ($dates as $tanggal) {
                foreach ($times as $time) {
                    $time_parts = explode('-', $time);
                    $jam_mulai = $time_parts[0] . ':00';
                    $jam_selesai = $time_parts[1] . ':00';
                    
                    $check_sql = "SELECT schedule_date_id FROM psychologist_schedule_dates 
                                  WHERE psychologist_id = ? AND tanggal = ? AND jam_mulai = ?";
                    $check = $db->getPrepare($check_sql, [$psychologist_id, $tanggal, $jam_mulai]);
                    
                    if (!$check) {
                        $insert_sql = "INSERT INTO psychologist_schedule_dates 
                                      (psychologist_id, tanggal, jam_mulai, jam_selesai) 
                                      VALUES (?, ?, ?, ?)";
                        $db->executePrepare($insert_sql, [$psychologist_id, $tanggal, $jam_mulai, $jam_selesai]);
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil disimpan!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'delete_schedule') {
        $psychologist_id = intval($_POST['psychologist_id']);
        $schedule_date_id = intval($_POST['schedule_date_id']);
        
        // Hard delete instead of soft delete
        $sql = "DELETE FROM psychologist_schedule_dates 
                WHERE schedule_date_id = ? AND psychologist_id = ?";
        
        if ($db->executePrepare($sql, [$schedule_date_id, $psychologist_id])) {
            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus jadwal']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_schedules_for_date') {
        $psychologist_id = intval($_POST['psychologist_id']);
        $tanggal = $_POST['tanggal'];
        
        $sql = "SELECT schedule_date_id, jam_mulai, jam_selesai, has_booking FROM psychologist_schedule_dates 
                WHERE psychologist_id = ? AND tanggal = ?
                ORDER BY jam_mulai";
        
        $result = $db->queryPrepare($sql, [$psychologist_id, $tanggal]);
        
        if (is_array($result)) {
            echo json_encode(['success' => true, 'schedules' => $result]);
        } else {
            echo json_encode(['success' => true, 'schedules' => []]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'delete_multiple_schedules') {
        $psychologist_id = intval($_POST['psychologist_id'] ?? 0);
        
        // Handle both old format (dates_to_delete) and new format (schedule_ids)
        if (isset($_POST['schedule_ids'])) {
            // New format: delete specific schedule IDs
            $schedule_ids = json_decode($_POST['schedule_ids'], true);
            
            if (!is_array($schedule_ids) || count($schedule_ids) === 0) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada jadwal yang dipilih']);
                exit;
            }
            
            // Check for bookings using has_booking column
            $placeholders = str_repeat('?,', count($schedule_ids) - 1) . '?';
            $check_sql = "SELECT schedule_date_id, jam_mulai FROM psychologist_schedule_dates 
                          WHERE schedule_date_id IN ($placeholders) AND has_booking = 1";
            $booked_schedules = $db->queryPrepare($check_sql, $schedule_ids);
            
            $booked_schedule_ids = [];
            if (is_array($booked_schedules)) {
                foreach ($booked_schedules as $schedule) {
                    $booked_schedule_ids[] = $schedule['schedule_date_id'];
                }
            }
            
            // Filter out schedules with bookings
            $deletable_ids = array_diff($schedule_ids, $booked_schedule_ids);
            
            if (count($deletable_ids) === 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Tidak dapat menghapus jadwal yang memiliki booking aktif',
                    'booked_schedule_ids' => $booked_schedule_ids
                ]);
                exit;
            }
            
            // Delete schedules without bookings
            $delete_placeholders = str_repeat('?,', count($deletable_ids) - 1) . '?';
            $delete_sql = "DELETE FROM psychologist_schedule_dates 
                          WHERE schedule_date_id IN ($delete_placeholders)";
            
            if ($db->executePrepare($delete_sql, $deletable_ids)) {
                $message = count($deletable_ids) . ' jadwal berhasil dihapus';
                if (count($booked_schedule_ids) > 0) {
                    $message .= '. ' . count($booked_schedule_ids) . ' jadwal tidak dapat dihapus karena memiliki booking aktif';
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'booked_schedule_ids' => $booked_schedule_ids
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus jadwal']);
            }
        } elseif (isset($_POST['dates_to_delete']) && $psychologist_id > 0) {
            // Old format: delete by dates
            $dates_to_delete = json_decode($_POST['dates_to_delete'], true);
            
            if (!is_array($dates_to_delete) || count($dates_to_delete) === 0) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada tanggal yang dipilih']);
                exit;
            }
            
            // Get all schedule IDs for these dates
            $date_placeholders = str_repeat('?,', count($dates_to_delete) - 1) . '?';
            $get_schedules_sql = "SELECT schedule_date_id, tanggal, jam_mulai, has_booking 
                                 FROM psychologist_schedule_dates 
                                 WHERE psychologist_id = ? AND tanggal IN ($date_placeholders)";
            $params = array_merge([$psychologist_id], $dates_to_delete);
            $all_schedules = $db->queryPrepare($get_schedules_sql, $params);
            
            if (!is_array($all_schedules)) {
                $all_schedules = [];
            }
            
            // Separate booked and deletable schedules
            $schedule_ids = [];
            $booked_schedule_ids = [];
            
            foreach ($all_schedules as $schedule) {
                $schedule_ids[] = $schedule['schedule_date_id'];
                if ($schedule['has_booking'] == 1) {
                    $booked_schedule_ids[] = $schedule['schedule_date_id'];
                }
            }
            
            if (count($schedule_ids) === 0) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada jadwal ditemukan untuk tanggal yang dipilih']);
                exit;
            }
            
            // Filter out schedules with bookings
            $deletable_ids = array_diff($schedule_ids, $booked_schedule_ids);
            
            if (count($deletable_ids) === 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Tidak dapat menghapus jadwal yang memiliki booking aktif',
                    'booked_schedule_ids' => $booked_schedule_ids
                ]);
                exit;
            }
            
            // Delete schedules without bookings
            $delete_placeholders = str_repeat('?,', count($deletable_ids) - 1) . '?';
            $delete_sql = "DELETE FROM psychologist_schedule_dates 
                          WHERE schedule_date_id IN ($delete_placeholders)";
            
            if ($db->executePrepare($delete_sql, $deletable_ids)) {
                $message = count($deletable_ids) . ' jadwal berhasil dihapus';
                if (count($booked_schedule_ids) > 0) {
                    $message .= '. ' . count($booked_schedule_ids) . ' jadwal tidak dapat dihapus karena memiliki booking aktif';
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'booked_schedule_ids' => $booked_schedule_ids
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus jadwal']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
        }
        exit;
    }
    
    // If action not found
    echo json_encode(['success' => false, 'message' => 'Action not found']);
    exit;
}

// Clear any output buffer at the start
if (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffer
ob_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database helper
require_once $path . 'includes/db.php';

// Initialize database
$db = new Database();
$db->connect();
$conn = $db->getConnection();

$error = '';
$success = '';

// Get all psychologists
$sql_psychologists = "SELECT pp.psychologist_id, u.name FROM psychologist_profiles pp INNER JOIN users u ON pp.user_id = u.user_id ORDER BY u.name";
$psychologists = $db->queryPrepare($sql_psychologists, []);
if (!is_array($psychologists)) {
    $psychologists = [];
}

// Define jam slots (2 jam per slot)
$jam_slots_display = ['09:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00'];

$selected_psychologist = $_GET['psychologist_id'] ?? ($psychologists[0]['psychologist_id'] ?? '');

// Get saved schedules for this psychologist
$saved_schedules = [];
if ($selected_psychologist) {
    $sql = "SELECT schedule_date_id, tanggal, jam_mulai, jam_selesai, has_booking FROM psychologist_schedule_dates 
            WHERE psychologist_id = ?
            ORDER BY tanggal DESC";
    $result = $db->queryPrepare($sql, [$selected_psychologist]);
    if (is_array($result)) {
        $saved_schedules = $result;
    }
}

// Handle delete off days
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_off_day'])) {
    $off_id = intval($_POST['off_id']);
    $psychologist_id = intval($_POST['psychologist_id']);
    
    $sql = "DELETE FROM psychologist_off_days WHERE off_id = ? AND psychologist_id = ?";
    if ($db->executePrepare($sql, [$off_id, $psychologist_id])) {
        $success = 'Cuti berhasil dihapus!';
        
        // Refresh off days
        $sql = "SELECT * FROM psychologist_off_days WHERE psychologist_id = ? ORDER BY tanggal_mulai";
        $result = $db->queryPrepare($sql, [$psychologist_id]);
        $off_days = is_array($result) ? $result : [];
    } else {
        $error = 'Gagal menghapus cuti!';
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
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/schedule_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo $path; ?>assets/js/sidebar.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include $path . 'components/sidebar_admin.php'; ?>
        <?php include $path . 'components/header_admin.php'; ?>

        <main class="main-content">
            
            <!-- Modern Header -->
            <div class="title-section">
                <div class="title-section-content">
                    <div>
                        <h2>
                            <i class="fas fa-calendar-alt"></i>
                            Manajemen Jadwal Kerja Psikolog
                        </h2>
                        <p>Atur jadwal kerja psikolog dengan mudah</p>
                    </div>
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.3);">
                            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Admin" style="width: 30px; height: 30px; border-radius: 50%;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Messages -->
            <?php if ($error): ?>
                <div class="schedule-notification">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="schedule-notification-content">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="schedule-notification-success">
                    <i class="fas fa-check-circle"></i>
                    <div class="schedule-notification-content">
                        <strong>Sukses:</strong> <?php echo htmlspecialchars($success); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Psychologist Selector -->
            <div class="psychologist-selector">
                <div class="psychologist-selector-header">
                    <div class="psychologist-selector-title">
                        <h3>
                            <i class="fas fa-user-md"></i>
                            Pilih Psikolog
                        </h3>
                        <p>Pilih psikolog untuk mengatur jadwal kerja</p>
                    </div>
                    <div class="psychologist-selector-controls">
                        <div class="psychologist-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <select onchange="window.location.href='?psychologist_id=' + this.value" class="psychologist-select">
                            <option value="">-- Pilih Psikolog --</option>
                            <?php foreach ($psychologists as $psy): ?>
                                <option value="<?php echo $psy['psychologist_id']; ?>" <?php echo ($psy['psychologist_id'] == $selected_psychologist) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($psy['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

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

                        <div class="time-slot-grid" id="timeSlotGrid">
                            <?php foreach ($jam_slots_display as $index => $display): ?>
                                <div class="time-slot-item">
                                    <input type="checkbox" 
                                           id="jam_<?php echo $index; ?>" 
                                           class="time-slot-checkbox" 
                                           value="<?php echo $display; ?>">
                                    <label for="jam_<?php echo $index; ?>" class="time-slot-label">
                                        <i class="fas fa-clock"></i>
                                        <?php echo $display; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="time-slot-actions">
                            <button type="button" class="btn-clear-times" id="btnClearTimes">
                                <i class="fas fa-times"></i> Bersihkan
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Selector -->
                    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed;">
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

                        <div class="calendar-legend" style="display: flex;gap: 20px;justify-content: center;padding: 15px;background: #f8f9fa;border-radius: 10px;margin-bottom: 20px;">
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
                <!-- No Psychologist Selected -->
                <div style="background: white; border-radius: 20px; padding: 60px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e1e8ed; text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-user-md" style="color: #6c757d; font-size: 32px;"></i>
                    </div>
                    <h3 style="margin: 0 0 10px 0; color: var(--color-text); font-size: 20px; font-weight: 600;">Pilih Psikolog Terlebih Dahulu</h3>
                    <p style="margin: 0; color: #6c757d; font-size: 16px;">Silakan pilih psikolog dari dropdown di atas untuk mulai mengatur jadwal kerja</p>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Modal Notification -->
    <div id="scheduleNotification" class="schedule-notification" style="display: none; position: fixed; top: 20px; right: 20px; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 2000; min-width: 300px; border-left: 4px solid var(--color-primary);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="notification-icon" style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="notification-icon-inner fas fa-check" style="color: white; font-size: 18px;"></i>
            </div>
            <div style="flex: 1;">
                <div class="schedule-notification-title" id="notificationTitle" style="font-weight: 600; color: var(--color-text); margin-bottom: 4px;">Sukses</div>
                <div class="schedule-notification-message" id="notificationMessage" style="color: #6c757d; font-size: 14px;">Jadwal berhasil disimpan</div>
            </div>
        </div>
    </div>

    <!-- Modal Delete Schedules -->
    <div id="deleteScheduleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div style="background: white; border-radius: 20px; padding: 0; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); transform: scale(0.9); opacity: 0; transition: all 0.3s ease;">
            <div style="padding: 25px; border-bottom: 1px solid #e9ecef; background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border-radius: 20px 20px 0 0;">
                <h3 style="margin: 0; color: var(--color-text); display: flex; align-items: center; gap: 12px; font-size: 18px; font-weight: 700;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-trash" style="color: white; font-size: 18px;"></i>
                    </div>
                    Batalkan Jadwal
                </h3>
            </div>
            <div style="padding: 25px; max-height: 400px; overflow-y: auto;">
                <p style="margin-top: 0; color: #6c757d; font-size: 16px; margin-bottom: 20px;">Pilih tanggal yang ingin dikosongin (hapus semua jadwal):</p>
                <div id="deleteScheduleList" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Filled by JavaScript -->
                </div>
            </div>
            <div style="padding: 20px 25px; border-top: 1px solid #e9ecef; background: #f8f9fa; border-radius: 0 0 20px 20px; display: flex; gap: 15px; justify-content: flex-end;">
                <button type="button" onclick="closeDeleteModal()" style="padding: 12px 20px; background: #6c757d; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" onclick="confirmDeleteSchedules()" style="padding: 12px 20px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-check"></i> Hapus Jadwal
                </button>
            </div>
        </div>
    </div>

    <!-- Pass saved schedules to JavaScript -->
    <script>
        // Data from PHP backend (used by assets/js/schedule.js)
        window.selectedPsychologistId = <?php echo json_encode($selected_psychologist); ?>;
        window.savedSchedulesData = <?php echo json_encode($saved_schedules); ?>;
        window.apiPath = '<?php echo $path; ?>api/';
    </script>

    <script src="<?php echo $path; ?>assets/js/schedule.js"></script>
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
    
</body>
</html>
