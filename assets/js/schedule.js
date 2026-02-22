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

function updateTimeSlotsState() {
    if (editingMode) {
        setTimeSlotsEnabled(true);
        return;
    }
    setTimeSlotsEnabled(selectedDates.size > 0);
}

function setTimeSlotsEnabled(enabled) {
    document.querySelectorAll('.time-slot-checkbox').forEach(cb => {
        const label = cb.nextElementSibling;
        if (!enabled) {
            cb.disabled = true;
            cb.checked = false;
            cb.removeAttribute('data-locked');
            if (label) {
                label.classList.remove('booked');
                label.classList.remove('enabled');
                label.classList.remove('not-allowed');
                label.removeAttribute('tabindex');
                label.removeAttribute('role');
                const lockIcon = label.querySelector('.fa-lock');
                if (lockIcon) lockIcon.remove();
            }
        } else {
            // If this checkbox is marked as locked, leave it locked
            if (cb.dataset.locked === '1') {
                if (label) {
                    label.classList.add('not-allowed');
                    label.classList.remove('enabled');
                    label.setAttribute('aria-disabled', 'true');
                }
                cb.disabled = true;
                return;
            }
            cb.disabled = false;
            if (label) {
                label.classList.add('enabled');
                label.classList.remove('not-allowed');
                label.setAttribute('tabindex', '0');
                label.setAttribute('role', 'button');
            }
        }
    });
    if (!enabled) {
        selectedTimes.clear();
    }
}

function lockTimeSlot(checkbox) {
    checkbox.disabled = true;
    checkbox.checked = true;
    checkbox.dataset.locked = '1';
    const label = checkbox.nextElementSibling;
    if (label) {
        label.classList.add('booked');
        // Mark as not-allowed and remove enabled affordance
        label.classList.add('not-allowed');
        label.classList.remove('enabled');
        label.removeAttribute('tabindex');
        label.removeAttribute('role');

        if (!label.querySelector('.fa-lock')) {
            const lockIcon = document.createElement('i');
            lockIcon.className = 'fas fa-lock';
            lockIcon.style.marginLeft = '5px';
            lockIcon.style.fontSize = '0.8em';
            lockIcon.title = 'Sudah ada booking - tidak dapat diubah';
            label.appendChild(lockIcon);
        }
        console.log('Time slot locked:', checkbox.value);
    }
}

function unlockTimeSlot(checkbox) {
    checkbox.dataset.locked = '0';
    checkbox.disabled = false;
    const label = checkbox.nextElementSibling;
    if (label) {
        label.classList.remove('booked');
        label.classList.remove('not-allowed');
        label.classList.add('enabled');
        label.setAttribute('tabindex', '0');
        label.setAttribute('role', 'button');
        const lockIcon = label.querySelector('.fa-lock');
        if (lockIcon) lockIcon.remove();
    }
}

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
                time: schedule.jam_mulai.substring(0, 5) + '-' + schedule.jam_selesai.substring(0, 5),
                id: schedule.schedule_date_id,
                has_booking: schedule.has_booking ? parseInt(schedule.has_booking, 10) : 0
            });
        });
        
        console.log('Processed scheduledDates:', scheduledDates);
    } else {
        console.log('No saved schedules found (window.savedSchedulesData is undefined)');
    }
    
    // Render calendar
    renderCalendar();
    updateSelectedDatesList();

    // Initial state: in normal mode time slots require at least one selected date
    updateTimeSlotsState();
    
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
    } else {
        console.warn('Calendar header element not found');
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
    
    // Clear entire grid
    calendarGrid.innerHTML = '';
    
    // Day names
    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    dayNames.forEach(day => {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day-header';
        dayElement.textContent = day;
        calendarGrid.appendChild(dayElement);
    });
    
    // Previous month days
    for (let day = daysInPrevMonth - firstDay + 1; day <= daysInPrevMonth; day++) {
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
        dayElement.dataset.dateStr = dateStr; // Store date as data attribute for debugging
        
        // Check if today
        if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
            dayElement.classList.add('today');
        }
        
        // Check if has scheduled dates
        if (scheduledDates[dateStr] && scheduledDates[dateStr].length > 0) {
            dayElement.classList.add('has-schedule');
        }
        
        // Check if selected
        if (selectedDates.has(dateStr)) {
            dayElement.classList.add('selected');
        }
        
        // Disable past dates
        const cellDate = new Date(year, month, day);
        cellDate.setHours(0, 0, 0, 0);
        const todayCheck = new Date();
        todayCheck.setHours(0, 0, 0, 0);
        
        if (cellDate < todayCheck) {
            dayElement.classList.add('disabled');
        } else {
            // Add click event with proper closure
            dayElement.addEventListener('click', function(e) {
                e.stopPropagation();
                console.log('Calendar date clicked:', dateStr, 'Current selectedDates size:', selectedDates.size);
                toggleDateSelection(dateStr, this);
                console.log('After toggle selectedDates:', Array.from(selectedDates));
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
    // If user was editing an existing scheduled date, switch back to normal mode
    // before allowing selection of a new (unscheduled) date to avoid carrying over times.
    if (editingMode) {
        exitEditMode();
    }

    // Check if this date has existing schedules
    if (scheduledDates[dateStr] && scheduledDates[dateStr].length > 0) {
        // If clicking on a date with existing schedules, enter edit mode
        enterEditMode(dateStr);
        return;
    }
    
    // JIKA KLIK TANGGAL BARU (BELUM ADA JADWAL) - CLEAR SELECTED TIMES
    if (!selectedDates.has(dateStr)) {
        // Tanggal baru dipilih - clear jam yang mungkin dari tanggal sebelumnya
        selectedTimes.clear();
        document.querySelectorAll('.time-slot-checkbox').forEach(cb => {
            cb.checked = false;
            const label = cb.nextElementSibling;
            if (label) {
                label.style.animation = 'none';
                label.style.background = 'white';
                label.style.color = 'var(--color-text)';
                label.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                label.style.borderColor = '#e1e8ed';
            }
        });
    }
    
    // Create ripple effect
    const ripple = document.createElement('span');
    ripple.style.position = 'absolute';
    ripple.style.borderRadius = '50%';
    ripple.style.background = 'rgba(251, 186, 0, 0.5)';
    ripple.style.transform = 'scale(0)';
    ripple.style.animation = 'ripple 0.6s ease-out';
    ripple.style.pointerEvents = 'none';
    
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event ? event.clientX - rect.left : rect.width / 2;
    const y = event ? event.clientY - rect.top : rect.height / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (x - size / 2) + 'px';
    ripple.style.top = (y - size / 2) + 'px';
    
    element.style.position = 'relative';
    element.appendChild(ripple);
    
    // Remove ripple after animation
    setTimeout(() => ripple.remove(), 600);
    
    // Normal date selection for new schedules
    if (selectedDates.has(dateStr)) {
        selectedDates.delete(dateStr);
        element.classList.remove('selected');
    } else {
        selectedDates.add(dateStr);
        element.classList.add('selected');
    }
    
    // Re-render calendar
    renderCalendar();
    updateSelectedDatesList();
    updateTimeSlotsState();

    if (!editingMode) {
        // Don't change time slots state based on date selection in normal mode
        // Time slots should always be enabled for selection
    }
}

function updateSelectedDatesList() {
    const list = document.getElementById('selectedDatesList');
    
    if (!list) {
        console.warn('Selected dates list element not found');
        return;
    }
    
    if (selectedDates.size === 0) {
        list.innerHTML = '<p style="color: #6c757d; font-style: italic; text-align: center; margin: 0;">Tidak ada tanggal yang dipilih</p>';
    } else {
        list.innerHTML = '';
        const sortedDates = Array.from(selectedDates).sort();
        
        sortedDates.forEach(dateStr => {
            const date = new Date(dateStr + 'T00:00:00');
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const displayText = `${dayNames[date.getDay()]}, ${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
            
            // Determine times to show for this date: existing schedules + pending additions if this date is currently selected
            const existingTimes = (scheduledDates[dateStr] || []).map(s => s.time);
            const pendingTimes = Array.from(selectedTimes);
            let combinedSet = new Set(existingTimes);
            if (selectedDates.has(dateStr)) {
                pendingTimes.forEach(t => combinedSet.add(t));
            }
            const combinedTimes = Array.from(combinedSet);
            const selectedTimesText = combinedTimes.length > 0 ? `(${combinedTimes.join(', ')})` : '(Belum ada jam)';
            
            const item = document.createElement('div');
            item.className = 'selected-date-item';
            item.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: white; border: 1px solid #e1e8ed; border-radius: 8px; margin-bottom: 10px; transition: all 0.3s ease;';
            item.innerHTML = `
                <div>
                    <div class="date-text" style="font-weight: 600; color: var(--color-text); margin-bottom: 4px;">${displayText}</div>
                    <div class="time-text" style="color: #6c757d; font-size: 14px;">${selectedTimesText}</div>
                    ${combinedTimes.length > 0 ? '<div style="color: #28a745; font-size: 12px; margin-top: 4px;"><i class="fas fa-check-circle"></i> Siap disimpan</div>' : '<div style="color: #ffc107; font-size: 12px; margin-top: 4px;"><i class="fas fa-exclamation-triangle"></i> Pilih jam terlebih dahulu</div>'}
                </div>
                <button type="button" class="btn-remove" data-date="${dateStr}" style="background: #dc3545; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            list.appendChild(item);
        });
        
        // Add summary info
        if (selectedDates.size > 0 && selectedTimes.size > 0) {
            const summary = document.createElement('div');
            summary.style.cssText = 'margin-top: 15px; padding: 10px; background: #e8f5e9; border-radius: 8px; border-left: 4px solid #28a745;';
            summary.innerHTML = `
                <div style="font-weight: 600; color: #155724; font-size: 14px;">
                    <i class="fas fa-info-circle"></i> Siap disimpan: ${selectedDates.size} tanggal, ${selectedTimes.size} jam
                </div>
                <div style="font-size: 12px; color: #155724; margin-top: 4px;">
                    Total jadwal yang akan disimpan: ${selectedDates.size * selectedTimes.size} sesi
                </div>
            `;
            list.appendChild(summary);
        }
    }
    
    // Show calendar section if there are selected dates
    const calendarSection = document.querySelector('.calendar-selector');
    if (calendarSection) {
        calendarSection.style.display = 'block';
    }
}

function removeSelectedDate(dateStr) {
    selectedDates.delete(dateStr);
    renderCalendar();
    updateSelectedDatesList();

    updateTimeSlotsState();
}

function attachEventListeners() {
    console.log('Attaching event listeners...');
    
    // Time slot selection
    const timeSlotGrid = document.getElementById('timeSlotGrid');
    if (timeSlotGrid) {
        timeSlotGrid.addEventListener('change', function(e) {
            if (e.target.classList.contains('time-slot-checkbox')) {
                // Prevent interaction with locked slots
                if (e.target.dataset.locked === '1') {
                    e.preventDefault();
                    e.stopPropagation();
                    showNotification('warning', 'Peringatan', 'Time slot ini sudah ada booking dan tidak dapat diubah');
                    return false;
                }
                
                // If not in edit mode, ensure at least one date is selected before choosing times
                if (!editingMode && selectedDates.size === 0) {
                    // Revert checkbox
                    e.target.checked = false;
                    showNotification('error', 'Error', 'Pilih tanggal terlebih dahulu');
                    return;
                }

                const label = e.target.nextElementSibling;
                
                if (e.target.checked) {
                    // SAAT JAM DIPILIH - TAMBAHKAN ANIMASI
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s ease-out';
                    ripple.style.pointerEvents = 'none';
                    
                    const rect = label.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = (label.offsetWidth / 2 - size / 2) + 'px';
                    ripple.style.top = (label.offsetHeight / 2 - size / 2) + 'px';
                    
                    label.appendChild(ripple);
                    
                    // Remove ripple after animation
                    setTimeout(() => ripple.remove(), 600);
                    
                    // Add pulse animation
                    label.style.animation = 'bounce-check 0.4s ease-out, pulse-strong 2s infinite';
                } else {
                    // SAAT JAM DIBATALKAN - HILANGKAN ANIMASI
                    label.style.animation = 'none';
                    label.style.background = 'white';
                    label.style.color = 'var(--color-text)';
                    label.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    label.style.borderColor = '#e1e8ed';
                    
                    // Remove checkmark pseudo-element effect
                    label.style.overflow = 'visible';
                }

                selectedTimes.clear();
                document.querySelectorAll('.time-slot-checkbox:checked').forEach(checkbox => {
                    selectedTimes.add(checkbox.value);
                });

                // In normal (create) mode, selectedTimes are pending additions applied to all selectedDates.
                // In edit mode, selectedTimes represent the desired final set for the editingDate.
                updateSelectedDatesList();
                console.log('Selected times (pending):', Array.from(selectedTimes));
            }
        });
        
        // Prevent click on locked time slots
        timeSlotGrid.addEventListener('click', function(e) {
            const label = e.target.closest('.time-slot-label');
            if (label && label.classList.contains('booked')) {
                e.preventDefault();
                e.stopPropagation();
                showNotification('warning', 'Peringatan', 'Time slot ini sudah ada booking dan tidak dapat diubah');
                return false;
            }
        });
    }
    
    // Select dates button
    const btnSelectDates = document.getElementById('btnSelectDates');
    if (btnSelectDates) {
        console.log('Found Select Dates button, attaching listener');
        btnSelectDates.addEventListener('click', function() {
            console.log('Select Dates button clicked!');
            console.log('Selected times count:', selectedTimes.size);
            
            if (selectedTimes.size === 0) {
                showNotification('error', 'Error', 'Pilih setidaknya satu jam terlebih dahulu');
                return;
            }
            
            // Show calendar section
            const calendarSection = document.getElementById('calendarSection');
            if (calendarSection) {
                console.log('Showing calendar section');
                calendarSection.style.display = 'block';
                // Scroll to calendar
                calendarSection.scrollIntoView({behavior: 'smooth', block: 'start'});
            } else {
                console.error('Calendar section not found');
            }
        });
    } else {
        console.log('Select Dates button NOT found!');
    }
    
    // Clear times
    const btnClearTimes = document.getElementById('btnClearTimes');
    if (btnClearTimes) {
        btnClearTimes.addEventListener('click', function() {
            document.querySelectorAll('.time-slot-checkbox').forEach(cb => {
                if (cb.dataset.locked === '1') return;
                cb.checked = false;
            });
            selectedTimes.clear();
            document.querySelectorAll('.time-slot-checkbox:checked').forEach(checkbox => {
                selectedTimes.add(checkbox.value);
            });
            updateSelectedDatesList();
        });
    }

    // Delegate remove selected date buttons (event delegation)
    const selectedDatesListEl = document.getElementById('selectedDatesList');
    if (selectedDatesListEl) {
        selectedDatesListEl.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove');
            if (!btn) return;
            const date = btn.dataset.date;
            if (date) {
                removeSelectedDate(date);
            }
        });
    }
    
    // Clear dates
    const btnClearDates = document.getElementById('btnClearDates');
    if (btnClearDates) {
        btnClearDates.addEventListener('click', function() {
            selectedDates.clear();
            renderCalendar();
            updateSelectedDatesList();

            if (!editingMode) {
                // Normal mode: time slots depend on date selection
                updateTimeSlotsState();
            }
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
            const datesArray = Array.from(selectedDates);
            const timesArray = Array.from(selectedTimes);
            
            console.log('=== SAVING SCHEDULE ===');
            console.log('Psychologist ID:', psyId);
            console.log('Dates to save:', datesArray);
            console.log('Times to save:', timesArray);
            console.log('Total combinations:', datesArray.length * timesArray.length);
            
            // Validate before sending
            if (datesArray.length === 0) {
                showNotification('error', 'Error', 'Pilih setidaknya satu tanggal');
                return;
            }
            
            if (timesArray.length === 0) {
                showNotification('error', 'Error', 'Pilih setidaknya satu jam');
                return;
            }
            
            if (!psyId) {
                showNotification('error', 'Error', 'Psychologist ID tidak valid');
                return;
            }

            // CONFIRMATION DIALOG - Tampilkan apa yang akan disimpan
            const confirmMessage = `KONFIRMASI PENYIMPANAN JADWAL\n\n` +
                `Tanggal yang dipilih: ${datesArray.length}\n` +
                `${datesArray.map(d => {
                    const date = new Date(d + 'T00:00:00');
                    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    return `  • ${dayNames[date.getDay()]}, ${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
                }).join('\n')}\n\n` +
                `Jam yang dipilih: ${timesArray.length}\n` +
                `${timesArray.map(t => `  • ${t}`).join('\n')}\n\n` +
                `Total jadwal yang akan disimpan: ${datesArray.length * timesArray.length}\n\n` +
                `Lanjutkan penyimpanan?`;

            if (!confirm(confirmMessage)) {
                console.log('Penyimpanan dibatalkan oleh user');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'save_schedule_dates');
            formData.append('psychologist_id', psyId);
            formData.append('dates', JSON.stringify(datesArray));
            formData.append('times', JSON.stringify(timesArray));
            
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
                    // Clear selections
                    selectedDates.clear();
                    selectedTimes.clear();
                    
                    // Uncheck all time slots
                    document.querySelectorAll('.time-slot-checkbox').forEach(cb => {
                        cb.checked = false;
                        cb.style.animation = 'none';
                        const label = cb.nextElementSibling;
                        if (label) {
                            label.style.animation = 'none';
                            label.style.background = 'white';
                            label.style.color = 'var(--color-text)';
                            label.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                        }
                    });
                    
                    // Show detailed success info
                    console.log('✅ Schedule saved successfully!');
                    console.log('📊 Total saved:', datesArray.length * timesArray.length, 'combinations');
                    setTimeout(() => location.reload(), 1500); // Give more time to see the notification
                } else {
                    console.error('❌ Save failed:', data.message);
                    showNotification('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('❌ Network error:', error);
                showNotification('error', 'Error', 'Gagal menyimpan jadwal: ' + error.message);
            });
        });
    } else {
        console.error('Save button NOT found!');
    }
    
    // Delete schedule button
    const btnDeleteSchedule = document.getElementById('btnDeleteSchedule');
    if (btnDeleteSchedule) {
        console.log('Found delete schedule button, attaching listener');
        btnDeleteSchedule.addEventListener('click', function() {
            console.log('Delete schedule button clicked');
            const scheduledDatesList = Object.keys(scheduledDates);
            
            if (scheduledDatesList.length === 0) {
                showNotification('error', 'Error', 'Tidak ada jadwal untuk dibatalkan');
                return;
            }
            
            showDeleteModal(scheduledDatesList);
        });
    } else {
        console.error('Delete schedule button NOT found!');
    }
}

function showNotification(type, title, message) {
    const notif = document.getElementById('scheduleNotification');
    if (!notif) return;
    
    // Remove hide animation if exists
    notif.classList.remove('notification-hide');
    
    // Update notification class
    notif.className = `schedule-notification ${type}`;
    notif.classList.remove('notification-hide');
    
    // Update icon based on type
    const iconElement = notif.querySelector('.notification-icon-inner');
    const iconContainer = notif.querySelector('.notification-icon');
    
    if (iconElement && iconContainer) {
        // Remove all icon classes
        iconElement.className = 'notification-icon-inner fas';
        
        // Set appropriate icon and color based on type
        switch(type) {
            case 'success':
                iconElement.classList.add('fa-check');
                iconContainer.style.background = 'linear-gradient(135deg, #4caf50 0%, #45a049 100%)';
                notif.style.borderLeftColor = '#4caf50';
                notif.style.borderLeft = '4px solid #4caf50';
                break;
            case 'error':
                iconElement.classList.add('fa-times');
                iconContainer.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
                notif.style.borderLeft = '4px solid #dc3545';
                break;
            case 'warning':
                iconElement.classList.add('fa-exclamation-triangle');
                iconContainer.style.background = 'linear-gradient(135deg, #ffc107 0%, #ff9800 100%)';
                notif.style.borderLeft = '4px solid #ffc107';
                break;
            case 'info':
                iconElement.classList.add('fa-info-circle');
                iconContainer.style.background = 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)';
                notif.style.borderLeft = '4px solid #17a2b8';
                break;
            default:
                iconElement.classList.add('fa-check');
                iconContainer.style.background = 'linear-gradient(135deg, #4caf50 0%, #45a049 100%)';
                notif.style.borderLeft = '4px solid #4caf50';
        }
    }
    
    // Update content
    document.getElementById('notificationTitle').textContent = title;
    document.getElementById('notificationMessage').textContent = message;
    notif.style.display = 'block';
    
    // Auto hide after 4.5 seconds
    setTimeout(() => {
        closeNotification();
    }, 4500);
}

function closeNotification() {
    const notif = document.getElementById('scheduleNotification');
    if (!notif) return;
    
    notif.classList.add('notification-hide');
    setTimeout(() => {
        notif.style.display = 'none';
        notif.classList.remove('notification-hide');
    }, 300);
}

// Edit Mode Functions
function enterEditMode(dateStr) {
    console.log('Entering edit mode for date:', dateStr);
    console.log('Scheduled dates for this date:', scheduledDates[dateStr]);
    
    editingMode = true;
    editingDate = dateStr;
    selectedDates.clear();
    selectedDates.add(dateStr);
    
    // Show edit mode notification
    document.getElementById('editModeNotif').style.display = 'block';

    // In edit mode, time slots must be enabled (except locked ones)
    setTimeSlotsEnabled(true);
    
    // Load existing schedules for this date and check them
    const psyId = window.selectedPsychologistId || selectedPsychologist;
    if (!psyId) {
        showNotification('error', 'Error', 'Psychologist ID tidak ditemukan');
        return;
    }
    
    console.log('Loading schedules for psychologist:', psyId, 'date:', dateStr);
    
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
            
            // Reset all checkboxes first (enabled + no lock)
            document.querySelectorAll('.time-slot-checkbox').forEach(cb => {
                cb.checked = false;
                cb.removeAttribute('data-schedule-id');
                unlockTimeSlot(cb);
            });
            selectedTimes.clear();
            
            // Check the existing times
            data.schedules.forEach(schedule => {
                const timeStr = schedule.jam_mulai.substring(0, 5) + '-' + schedule.jam_selesai.substring(0, 5);
                console.log('Processing schedule:', timeStr, 'has_booking:', schedule.has_booking);
                
                const checkbox = document.querySelector(`input.time-slot-checkbox[value="${timeStr}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    selectedTimes.add(timeStr);
                    // Store schedule ID for deletion tracking
                    checkbox.dataset.scheduleId = schedule.schedule_date_id;

                    // If booked, lock it so it can't be unchecked/edited
                    if (parseInt(schedule.has_booking, 10) === 1) {
                        console.log('Locking time slot:', timeStr);
                        lockTimeSlot(checkbox);
                    }
                } else {
                    console.warn('Checkbox not found for time:', timeStr);
                }
            });
            
            updateSelectedDatesList();
            
            // Scroll to time slots section - with validation
            const timeSlotSection = document.querySelector('.time-slot-selector');
            if (timeSlotSection) {
                timeSlotSection.scrollIntoView({behavior: 'smooth', block: 'start'});
            } else {
                console.warn('Time slot section not found, skipping scroll');
            }
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

function exitEditMode() {
    console.log('Exiting edit mode');
    editingMode = false;
    editingDate = null;
    selectedDates.clear();
    selectedTimes.clear();
    
    // Hide edit mode notification
    const editNotif = document.getElementById('editModeNotif');
    if (editNotif) {
        editNotif.style.display = 'none';
    }
    
    // Reset all checkboxes
    document.querySelectorAll('.time-slot-checkbox').forEach(cb => {
        cb.checked = false;
        cb.removeAttribute('data-schedule-id');
        unlockTimeSlot(cb);
    });

    // Normal mode: time slots depend on date selection
    updateTimeSlotsState();
    
    // Update UI
    renderCalendar();
    updateSelectedDatesList();
}

// Expose some functions globally for inline onclick handlers (keeps backward compatibility)
window.exitEditMode = exitEditMode;
window.removeSelectedDate = removeSelectedDate;
window.updateSelectedDatesList = updateSelectedDatesList;
window.closeEditConfirmation = closeEditConfirmation;
window.confirmEditSchedule = confirmEditSchedule;

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
    
    // Hide edit mode notification (guard if element missing)
    const editNotif = document.getElementById('editModeNotif');
    if (editNotif) {
        editNotif.style.display = 'none';
    }
    
    // Reset all time slot checkboxes + visual/lock state so it won't "stick" to next clicked date
    document.querySelectorAll('.time-slot-checkbox').forEach(cb => {
        cb.checked = false;
        cb.removeAttribute('data-schedule-id');
        cb.removeAttribute('data-locked');
        unlockTimeSlot(cb);
        const label = cb.nextElementSibling;
        if (label) {
            label.style.animation = 'none';
            label.style.background = 'white';
            label.style.color = 'var(--color-text)';
            label.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            label.style.borderColor = '#e1e8ed';
        }
    });
    
    // Normal mode: time slots depend on date selection
    updateTimeSlotsState();
    
    // Update UI
    renderCalendar();
    updateSelectedDatesList();
}

// Delete Schedule Functions
function showDeleteModal(scheduledDatesList) {
    const list = document.getElementById('deleteScheduleList');
    list.innerHTML = '';
    schedulesToDelete.clear();
    
    scheduledDatesList.sort().forEach(dateStr => {
        const dateObj = new Date(dateStr + 'T00:00:00');
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const dateDisplay = `${dayNames[dateObj.getDay()]}, ${dateObj.getDate()} ${monthNames[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
        
        const schedules = scheduledDates[dateStr] || [];
        const jamDisplay = schedules.map(s => s.time).join(', ');
        // Collect schedule IDs for this date
        const scheduleIdsForDate = schedules.map(s => s.id).filter(Boolean);
        
        const item = document.createElement('label');
        item.style.cssText = 'display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer; background: #f9f9f9; margin-bottom: 8px;';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        // store schedule IDs as JSON string in value
        checkbox.value = JSON.stringify(scheduleIdsForDate);
        checkbox.style.width = '18px';
        checkbox.style.height = '18px';
        checkbox.style.cursor = 'pointer';
        // If there are no schedule IDs, disable the checkbox
        if (scheduleIdsForDate.length === 0) {
            checkbox.disabled = true;
            checkbox.style.cursor = 'not-allowed';
            checkbox.title = 'Tidak ada jadwal untuk tanggal ini';
        }
        checkbox.addEventListener('change', (e) => {
            try {
                const ids = JSON.parse(e.target.value || '[]');
                if (e.target.checked) {
                    ids.forEach(id => schedulesToDelete.add(Number(id)));
                } else {
                    ids.forEach(id => schedulesToDelete.delete(Number(id)));
                }
            } catch (err) {
                console.error('Failed to parse schedule ids from checkbox value', err);
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
    
    const modal = document.getElementById('deleteScheduleModal');
    if (!modal) {
        console.error('Delete schedule modal not found!');
        showNotification('error', 'Error', 'Modal tidak ditemukan');
        return;
    }
    
    // Find modal content div (the white box inside modal)
    const modalContent = modal.querySelector('div > div');
    if (!modalContent) {
        console.error('Modal content not found!');
        return;
    }
    
    console.log('Showing delete modal with', scheduledDatesList.length, 'dates');
    
    // Show modal
    modal.style.display = 'flex';
    
    // Reset and animate modal content
    modalContent.style.transform = 'scale(0.9)';
    modalContent.style.opacity = '0';
    
    // Animate in
    requestAnimationFrame(() => {
        setTimeout(() => {
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 10);
    });
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteScheduleModal');
    if (!modal) return;
    
    const modalContent = modal.querySelector('div > div');
    if (modalContent) {
        // Animate out
        modalContent.style.transform = 'scale(0.9)';
        modalContent.style.opacity = '0';
        
        // Hide modal after animation
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    } else {
        modal.style.display = 'none';
    }
    
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
    // send actual schedule ids (not dates)
    formData.append('schedule_ids', JSON.stringify(Array.from(schedulesToDelete)));

    console.log('Deleting schedules for psychologist:', psyId, 'Schedule IDs:', Array.from(schedulesToDelete));

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(async r => {
        const text = await r.text();
        try {
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            console.error('Non-JSON response from server:', text);
            // Show server response for debugging
            showNotification('error', 'Error', 'Server error saat menghapus jadwal. Periksa server logs.');
            throw new Error('Invalid JSON response');
        }
    })
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
        // Do not swallow server HTML; already logged via console
    });
    
    closeDeleteModal();
}

console.log('Schedule script initialized');

// Make functions globally accessible
window.closeNotification = closeNotification;
