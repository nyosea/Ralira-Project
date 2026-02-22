// Psychologist Schedule Management Script
console.log('Schedule script loaded');

const path = '../../';
let selectedDates = new Set();
let selectedTimes = new Set();
let currentMonth = new Date();
let editingDate = null;
let editingMode = false;  // Track if we're in edit mode
let selectedPsychologist = null;
let scheduledDates = {};
let schedulesToDelete = new Set();  // Track schedules selected for deletion

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM Content Loaded - Initializing Schedule ===');
    
    // Get psychologist ID from window
    if (typeof window.selectedPsychologistId !== 'undefined') {
        selectedPsychologist = window.selectedPsychologistId;
    }
    console.log('Selected Psychologist:', selectedPsychologist);
    
    // Load saved schedules from PHP
    if (typeof window.savedSchedulesData !== 'undefined' && window.savedSchedulesData) {
        console.log('Loading saved schedules:', window.savedSchedulesData);
        
        // Process each saved schedule and add date to scheduledDates
        window.savedSchedulesData.forEach(schedule => {
            const dateStr = schedule.tanggal; // Format: YYYY-MM-DD
            if (!scheduledDates[dateStr]) {
                scheduledDates[dateStr] = [];
            }
            scheduledDates[dateStr].push({
                time: schedule.jam_mulai + '-' + schedule.jam_selesai,
                id: schedule.schedule_date_id
            });
        });
        
        console.log('Processed scheduledDates:', scheduledDates);
    } else {
        console.log('No saved schedules found (window.savedSchedulesData is undefined)');
    }
    
    // Render calendar
    renderCalendar();
    updateSelectedDatesList();
    
    // Attach event listeners
    attachEventListeners();
    
    console.log('=== Initialization Complete ===');
});

function renderCalendar() {
    console.log('Rendering calendar for:', currentMonth);
    
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    
    // Update header
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const headerEl = document.getElementById('calendarMonthYear');
    if (headerEl) {
        headerEl.textContent = `${monthNames[month]} ${year}`;
    }
    
    // Get first day and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) {
        console.error('Calendar grid not found');
        return;
    }
    
    calendarGrid.innerHTML = '';
    
    // Day names
    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    dayNames.forEach(day => {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day-name';
        dayElement.textContent = day;
        calendarGrid.appendChild(dayElement);
    });
    
    // Previous month days
    for (let i = firstDay - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day other-month';
        dayElement.textContent = day;
        calendarGrid.appendChild(dayElement);
    }
    
    // Current month days
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        dayElement.className = 'calendar-day';
        dayElement.innerHTML = day;
        
        // Check if today
        if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
            dayElement.classList.add('today');
        }
        
        // Check if has scheduled dates
        if (scheduledDates[dateStr]) {
            dayElement.classList.add('available');
        }
        
        // Check if selected
        if (selectedDates.has(dateStr)) {
            dayElement.classList.add('selected');
        }
        
        // Disable past dates
        const cellDate = new Date(year, month, day);
        if (cellDate < new Date(today.getFullYear(), today.getMonth(), today.getDate())) {
            dayElement.classList.remove('available');
            dayElement.style.cursor = 'not-allowed';
            dayElement.style.opacity = '0.5';
        } else {
            dayElement.style.cursor = 'pointer';
            dayElement.addEventListener('click', () => {
                // If date has schedules, enter edit mode
                if (scheduledDates[dateStr]) {
                    enterEditMode(dateStr);
                } else {
                    // Otherwise toggle normal selection
                    toggleDateSelection(dateStr, dayElement);
                }
            });
        }
        
        calendarGrid.appendChild(dayElement);
    }
    
    // Next month days
    const totalCells = calendarGrid.children.length;
    const emptyDays = 35 - totalCells + 7;
    for (let day = 1; day <= emptyDays; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day other-month';
        dayElement.textContent = day;
        calendarGrid.appendChild(dayElement);
    }
}

function toggleDateSelection(dateStr, element) {
    if (selectedDates.has(dateStr)) {
        selectedDates.delete(dateStr);
    } else {
        selectedDates.add(dateStr);
    }
    renderCalendar();
    updateSelectedDatesList();
}

function updateSelectedDatesList() {
    const list = document.getElementById('selectedDatesList');
    
    if (selectedDates.size === 0) {
        list.innerHTML = '<p style="color: var(--color-text-light); font-style: italic;">Tidak ada tanggal yang dipilih</p>';
    } else {
        list.innerHTML = '';
        const sortedDates = Array.from(selectedDates).sort();
        
        sortedDates.forEach(dateStr => {
            const date = new Date(dateStr + 'T00:00:00');
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const displayText = `${dayNames[date.getDay()]}, ${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
            const selectedTimesText = selectedTimes.size > 0 ? 
                `(${Array.from(selectedTimes).join(', ')})` : 
                '(Belum ada jam)';
            
            const item = document.createElement('div');
            item.className = 'selected-date-item';
            item.innerHTML = `
                <div>
                    <div class="date-text">${displayText}</div>
                    <div class="time-text">${selectedTimesText}</div>
                </div>
                <button type="button" class="btn-remove" onclick="removeSelectedDate('${dateStr}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            list.appendChild(item);
        });
    }
}

function removeSelectedDate(dateStr) {
    selectedDates.delete(dateStr);
    renderCalendar();
    updateSelectedDatesList();
}

function attachEventListeners() {
    console.log('Attaching event listeners...');
    
    // Time slot selection
    const timeSlotGrid = document.getElementById('timeSlotGrid');
    if (timeSlotGrid) {
        timeSlotGrid.addEventListener('change', function(e) {
            if (e.target.classList.contains('time-slot-checkbox')) {
                selectedTimes.clear();
                document.querySelectorAll('.time-slot-checkbox:checked').forEach(checkbox => {
                    selectedTimes.add(checkbox.value);
                });
                updateSelectedDatesList();
                console.log('Selected times:', Array.from(selectedTimes));
            }
        });
    }
    
    // Clear times
    const btnClearTimes = document.getElementById('btnClearTimes');
    if (btnClearTimes) {
        btnClearTimes.addEventListener('click', function() {
            document.querySelectorAll('.time-slot-checkbox').forEach(cb => cb.checked = false);
            selectedTimes.clear();
            updateSelectedDatesList();
        });
    }
    
    // Clear dates
    const btnClearDates = document.getElementById('btnClearDates');
    if (btnClearDates) {
        btnClearDates.addEventListener('click', function() {
            selectedDates.clear();
            renderCalendar();
            updateSelectedDatesList();
        });
    }
    
    // Calendar navigation
    const btnPrevMonth = document.getElementById('btnPrevMonth');
    if (btnPrevMonth) {
        btnPrevMonth.addEventListener('click', function() {
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            renderCalendar();
        });
    }
    
    const btnNextMonth = document.getElementById('btnNextMonth');
    if (btnNextMonth) {
        btnNextMonth.addEventListener('click', function() {
            currentMonth.setMonth(currentMonth.getMonth() + 1);
            renderCalendar();
        });
    }
    
    // Save schedule
    const btnSaveSchedule = document.getElementById('btnSaveSchedule');
    if (btnSaveSchedule) {
        console.log('Found save button, attaching listener');
        btnSaveSchedule.addEventListener('click', function() {
            console.log('=== SAVE SCHEDULE CLICKED ===');
            console.log('Edit Mode:', editingMode);
            console.log('Selected Dates:', Array.from(selectedDates));
            console.log('Selected Times:', Array.from(selectedTimes));
            
            if (selectedDates.size === 0) {
                showNotification('error', 'Error', 'Pilih setidaknya satu tanggal');
                return;
            }
            
            if (selectedTimes.size === 0) {
                showNotification('error', 'Error', 'Pilih setidaknya satu jam');
                return;
            }
            
            // Get psychologist ID
            const psyId = window.selectedPsychologistId || selectedPsychologist;
            console.log('Using psychologist ID:', psyId);
            
            if (!psyId) {
                showNotification('error', 'Error', 'Psychologist ID tidak ditemukan');
                return;
            }
            
            // If in edit mode, handle updates
            if (editingMode && editingDate) {
                const allSchedules = scheduledDates[editingDate] || [];
                const timesToDelete = [];
                const timesToAdd = [];
                
                // Find schedules that are NOT in selectedTimes (to delete)
                allSchedules.forEach(schedule => {
                    if (!selectedTimes.has(schedule.time)) {
                        // Hanya hapus jika schedule sudah ada sebelumnya dan tidak dipilih sekarang
                        if (schedule.id) {  // Pastikan schedule punya ID (sudah ada di database)
                            timesToDelete.push(schedule.id);
                        }
                    }
                });
                
                // Find new times that are not in existing schedules (to add)
                const existingTimes = new Set(allSchedules.map(s => s.time));
                selectedTimes.forEach(time => {
                    if (!existingTimes.has(time)) {
                        timesToAdd.push(time);
                    }
                });
                
                console.log('Times to delete:', timesToDelete);
                console.log('Times to add:', timesToAdd);
                
                // Show confirmation dialog
                if (timesToDelete.length > 0 || timesToAdd.length > 0) {
                    showEditConfirmation(timesToDelete, timesToAdd, psyId);
                } else {
                    // No changes, just exit edit mode
                    exitEditMode();
                    showNotification('success', 'Sukses', 'Tidak ada perubahan');
                }
                return;
            }
            
            // Normal save (create new schedules)
            const formData = new FormData();
            formData.append('action', 'save_schedule_dates');
            formData.append('psychologist_id', psyId);
            formData.append('dates', JSON.stringify(Array.from(selectedDates)));
            formData.append('times', JSON.stringify(Array.from(selectedTimes)));
            
            console.log('Sending fetch request...');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showNotification('success', 'Sukses', data.message);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showNotification('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('error', 'Error', 'Terjadi kesalahan: ' + error.message);
            });
        });
    } else {
        console.error('Save button NOT found!');
    }
}

function showNotification(type, title, message) {
    const notif = document.getElementById('scheduleNotification');
    if (!notif) return;
    
    notif.className = `schedule-notification ${type}`;
    document.getElementById('notificationTitle').textContent = title;
    document.getElementById('notificationMessage').textContent = message;
    notif.style.display = 'block';
    
    setTimeout(() => {
        notif.style.display = 'none';
    }, 4000);
}

// Edit Mode Functions
function enterEditMode(dateStr) {
    console.log('Entering edit mode for date:', dateStr);
    editingMode = true;
    editingDate = dateStr;
    selectedDates.clear();
    selectedDates.add(dateStr);
    
    // Show edit mode notification
    document.getElementById('editModeNotif').style.display = 'block';
    
    // Load existing schedules for this date and check them
    const psyId = window.selectedPsychologistId || selectedPsychologist;
    if (!psyId) {
        showNotification('error', 'Error', 'Psychologist ID tidak ditemukan');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'get_schedules_for_date');
    formData.append('psychologist_id', psyId);
    formData.append('tanggal', dateStr);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        console.log('Get schedules response:', data);
        if (data.success && data.schedules) {
            console.log('Loaded schedules for edit:', data.schedules);
            
            // Clear all checkboxes first
            document.querySelectorAll('.time-slot-checkbox').forEach(cb => cb.checked = false);
            selectedTimes.clear();
            
            // Check the existing times
            data.schedules.forEach(schedule => {
                const timeStr = schedule.jam_mulai.substring(0, 5) + '-' + schedule.jam_selesai.substring(0, 5);
                const checkbox = document.querySelector(`input.time-slot-checkbox[value="${timeStr}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    selectedTimes.add(timeStr);
                    // Store schedule ID for deletion tracking
                    checkbox.dataset.scheduleId = schedule.schedule_date_id;
                }
            });
            
            updateSelectedDatesList();
            
            // Scroll to time slots section
            document.querySelector('.time-slot-selector').scrollIntoView({behavior: 'smooth', block: 'start'});
        } else {
            console.error('Failed to load schedules:', data.message);
            showNotification('error', 'Error', data.message || 'Gagal memuat jadwal');
            exitEditMode();
        }
    })
    .catch(error => {
        console.error('Error loading schedules:', error);
        showNotification('error', 'Error', 'Gagal memuat jadwal: ' + error.message);
        exitEditMode();
    });
}

function showEditConfirmation(timesToDelete, timesToAdd, psychologistId) {
    console.log('showEditConfirmation called with:', { timesToDelete, timesToAdd, psychologistId, editingDate });
    
    // Create confirmation modal
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
        background: rgba(0,0,0,0.5); z-index: 1000; 
        display: flex; align-items: center; justify-content: center;
    `;
    
    let deleteHtml = '';
    let addHtml = '';
    
    if (timesToDelete.length > 0) {
        deleteHtml = `
            <div style="margin-bottom: 15px;">
                <strong style="color: #f44336;">Jam yang akan dihapus:</strong>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    ${timesToDelete.filter(id => {
                        const schedule = Object.values(scheduledDates[editingDate] || []).find(s => s.id === id);
                        // Cek apakah schedule ini ada booking (dari PHP data)
                        const hasBooking = schedule && schedule.has_booking;
                        // Jangan tampilkan jam yang ada booking di daftar hapus
                        return !hasBooking;
                    }).map(id => {
                        const schedule = Object.values(scheduledDates[editingDate] || []).find(s => s.id === id);
                        return `<li>${schedule ? schedule.time : 'Unknown'}</li>`;
                    }).join('')}
                </ul>
            </div>
        `;
    }
    
    if (timesToAdd.length > 0) {
        addHtml = `
            <div style="margin-bottom: 15px;">
                <strong style="color: #4caf50;">Jam yang akan ditambahkan:</strong>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    ${timesToAdd.map(time => `<li>${time}</li>`).join('')}
                </ul>
            </div>
        `;
    }
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 8px; padding: 0; width: 90%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            <div style="padding: 20px; border-bottom: 1px solid #e0e0e0;">
                <h3 style="margin: 0; color: var(--color-text);">
                    <i class="fas fa-edit"></i> Konfirmasi Perubahan Jadwal
                </h3>
            </div>
            <div style="padding: 20px;">
                <p style="margin-top: 0;">Anda akan mengubah jadwal untuk tanggal <strong>${editingDate}</strong>:</p>
                ${deleteHtml}
                ${addHtml}
                <p style="margin-bottom: 0; color: #666; font-size: 0.9em;">
                    <i class="fas fa-exclamation-triangle"></i> Perubahan akan mempengaruhi ketersediaan konsultasi.
                </p>
            </div>
            <div style="padding: 15px 20px; border-top: 1px solid #e0e0e0; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeEditConfirmation()" style="padding: 10px 20px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" onclick="confirmEditSchedule()" style="padding: 10px 20px; background: var(--color-primary); color: white; border: none; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-check"></i> Ya, Ubah Jadwal
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store data globally for confirmation
    window.editConfirmationData = {
        timesToDelete,
        timesToAdd,
        psychologistId,
        modal
    };
}

function closeEditConfirmation() {
    if (window.editConfirmationData && window.editConfirmationData.modal) {
        document.body.removeChild(window.editConfirmationData.modal);
        window.editConfirmationData = null;
    }
}

function confirmEditSchedule() {
    console.log('confirmEditSchedule called');
    const { timesToDelete, timesToAdd, psychologistId } = window.editConfirmationData;
    
    console.log('Edit data:', { timesToDelete, timesToAdd, psychologistId, editingDate });
    
    // Process in sequence: delete first, then add
    let deletePromise = Promise.resolve();
    
    if (timesToDelete.length > 0) {
        const deleteForm = new FormData();
        deleteForm.append('action', 'delete_multiple_schedules');
        deleteForm.append('schedule_ids', JSON.stringify(timesToDelete));
        
        deletePromise = fetch(window.location.href, {
            method: 'POST',
            body: deleteForm
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Gagal menghapus jadwal');
            }
            console.log('Successfully deleted schedules');
        });
    }
    
    deletePromise
        .then(() => {
            // Add new schedules if any
            if (timesToAdd.length > 0) {
                const formData = new FormData();
                formData.append('action', 'save_schedule_dates');
                formData.append('psychologist_id', psychologistId);
                formData.append('dates', JSON.stringify([editingDate]));
                formData.append('times', JSON.stringify(timesToAdd));
                
                return fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal menambah jadwal');
                    }
                    console.log('Successfully added schedules');
                });
            }
        })
        .then(() => {
            closeEditConfirmation();
            exitEditMode();
            showNotification('success', 'Sukses', 'Jadwal berhasil diperbarui!');
            setTimeout(() => location.reload(), 1500);
        })
        .catch(error => {
            console.error('Error updating schedule:', error);
            showNotification('error', 'Error', error.message);
            closeEditConfirmation();
        });
}

function exitEditMode() {
    console.log('Exiting edit mode');
    editingMode = false;
    editingDate = null;
    selectedDates.clear();
    selectedTimes.clear();
    schedulesToDelete.clear();
    document.getElementById('editModeNotif').style.display = 'none';
    renderCalendar();
    updateSelectedDatesList();
}

// Delete Schedule Functions
const btnDeleteSchedule = document.getElementById('btnDeleteSchedule');
if (btnDeleteSchedule) {
    btnDeleteSchedule.addEventListener('click', function() {
        const scheduledDatesList = Object.keys(scheduledDates);
        
        if (scheduledDatesList.length === 0) {
            showNotification('error', 'Error', 'Tidak ada jadwal untuk dibatalkan');
            return;
        }
        
        showDeleteModal(scheduledDatesList);
    });
}

function showDeleteModal(scheduledDatesList) {
    const list = document.getElementById('deleteScheduleList');
    list.innerHTML = '';
    schedulesToDelete.clear();
    
    scheduledDatesList.sort().forEach(dateStr => {
        const dateObj = new Date(dateStr + 'T00:00:00');
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const dateDisplay = `${dayNames[dateObj.getDay()]}, ${dateObj.getDate()} ${monthNames[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
        
        const schedules = scheduledDates[dateStr];
        const jamDisplay = schedules.map(s => s.time).join(', ');
        
        const item = document.createElement('label');
        item.style.cssText = 'display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer; background: #f9f9f9; margin-bottom: 8px;';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = dateStr;
        checkbox.style.width = '18px';
        checkbox.style.height = '18px';
        checkbox.style.cursor = 'pointer';
        checkbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                schedulesToDelete.add(dateStr);
            } else {
                schedulesToDelete.delete(dateStr);
            }
        });
        
        const textDiv = document.createElement('div');
        textDiv.style.flex = '1';
        
        const dateText = document.createElement('div');
        dateText.style.fontWeight = 'bold';
        dateText.style.marginBottom = '4px';
        dateText.textContent = dateDisplay;
        
        const jamText = document.createElement('div');
        jamText.style.fontSize = '0.9em';
        jamText.style.color = '#666';
        jamText.textContent = 'Jam: ' + jamDisplay;
        
        textDiv.appendChild(dateText);
        textDiv.appendChild(jamText);
        
        item.appendChild(checkbox);
        item.appendChild(textDiv);
        list.appendChild(item);
    });
    
    document.getElementById('deleteScheduleModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteScheduleModal').style.display = 'none';
    schedulesToDelete.clear();
}

function confirmDeleteSchedules() {
    if (schedulesToDelete.size === 0) {
        showNotification('error', 'Error', 'Pilih setidaknya satu tanggal');
        return;
    }
    
    const psyId = window.selectedPsychologistId || selectedPsychologist;
    if (!psyId) {
        showNotification('error', 'Error', 'Psychologist ID tidak ditemukan');
        closeDeleteModal();
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_multiple_schedules');
    formData.append('psychologist_id', psyId);
    formData.append('dates_to_delete', JSON.stringify(Array.from(schedulesToDelete)));
    
    console.log('Deleting schedules for psychologist:', psyId, 'Dates:', Array.from(schedulesToDelete));
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        console.log('Delete response:', data);
        if (data.success) {
            if (data.warning) {
                showNotification('warning', 'Peringatan', data.warning);
            } else {
                showNotification('success', 'Sukses', data.message);
            }
            // Force reload immediately to refresh scheduledDates from server
            setTimeout(() => location.reload(), 800);
        } else {
            showNotification('error', 'Error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error', 'Terjadi kesalahan');
    });
    
    closeDeleteModal();
}

console.log('Schedule script initialized');
