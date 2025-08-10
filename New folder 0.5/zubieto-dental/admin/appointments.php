<?php
// FILE: admin/appointments.php (ENHANCED UI/UX VERSION)
require 'includes/header.php';
require '../includes/db_connect.php';

// Fetch patients and dentists for dropdown menus (unchanged)
$patients_result = $conn->query("SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name, first_name");
$patients = $patients_result->fetch_all(MYSQLI_ASSOC);
$dentists_result = $conn->query("SELECT user_id, full_name FROM users WHERE role IN ('Dentist', 'Admin') AND is_active = 1 ORDER BY full_name");
$dentists = $dentists_result->fetch_all(MYSQLI_ASSOC);
?>

<!-- =================================================================
     PAGE-SPECIFIC LIBRARIES AND STYLES
     ================================================================= -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<!-- NEW: Custom styles for the enhanced UI -->
<style>
    .fc-event { cursor: pointer; }
    .select2-container--open { z-index: 1060; /* Ensures Select2 dropdown appears above the modal */ }
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 1.5rem;
    }
    .legend-color-box {
        width: 15px;
        height: 15px;
        border-radius: 4px;
        margin-right: 8px;
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Appointment Calendar</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary" id="addAppointmentBtn">
            <i class="fas fa-plus me-2"></i>Add New Appointment
        </button>
    </div>
</div>

<!-- =================================================================
     NEW: CALENDAR LEGEND
     ================================================================= -->
<div class="card mb-3">
    <div class="card-body d-flex justify-content-center flex-wrap">
        <div class="legend-item"><span class="legend-color-box" style="background-color: #0d6efd;"></span> Scheduled</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #198754;"></span> Completed</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #6c757d;"></span> Cancelled</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #dc3545;"></span> No-Show</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #8e44ad;"></span> Follow-up</div>
    </div>
</div>


<!-- Main Calendar Card -->
<div class="card">
    <div class="card-body">
        <div id='calendar'></div>
    </div>
</div>

<!-- Toast Notification Container (unchanged) -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="responseToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto" id="toastTitle">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastBody"></div>
    </div>
</div>

<!-- =================================================================
     MODIFIED: ENHANCED APPOINTMENT MODAL
     ================================================================= -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Manage Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="appointmentForm">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="appointment_id">
                    
                    <div class="mb-3">
                        <label for="patient_id" class="form-label"><i class="fas fa-user me-2"></i>Patient <span class="text-danger">*</span></label>
                        <select class="form-select searchable-select" id="patient_id" name="patient_id" required style="width: 100%;">
                            <option value="">Search for a patient...</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['patient_id']; ?>"><?php echo htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dentist_id" class="form-label"><i class="fas fa-user-md me-2"></i>Dentist <span class="text-danger">*</span></label>
                        <select class="form-select" id="dentist_id" name="dentist_id" required>
                            <option value="">Select a Dentist...</option>
                            <?php foreach ($dentists as $dentist): ?>
                                <option value="<?php echo $dentist['user_id']; ?>"><?php echo htmlspecialchars($dentist['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="appointment_date" class="form-label"><i class="fas fa-clock me-2"></i>Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="service_description" class="form-label"><i class="fas fa-notes-medical me-2"></i>Service / Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="service_description" name="service_description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label"><i class="fas fa-info-circle me-2"></i>Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Scheduled">Scheduled (Blue)</option>
                            <option value="Completed">Completed (Green)</option>
                            <option value="Cancelled">Cancelled (Gray)</option>
                            <option value="No-Show">No-Show (Red)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Delete button pushed to the left -->
                    <button type="button" class="btn btn-danger me-auto" id="deleteAppointmentBtn"><i class="fas fa-trash me-2"></i>Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// We include footer.php to load jQuery and other core libraries
require 'includes/footer.php';
?>

<!-- =================================================================
     PAGE-SPECIFIC JAVASCRIPT LIBRARIES & CUSTOM SCRIPT
     ================================================================= -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // --- INITIALIZE VARIABLES AND PLUGINS ---
    var calendarEl = document.getElementById('calendar');
    var appointmentModal = new bootstrap.Modal(document.getElementById('appointmentModal'));
    var form = $('#appointmentForm');
    var toastEl = document.getElementById('responseToast');
    var responseToast = new bootstrap.Toast(toastEl);

    $('.searchable-select').select2({ theme: 'bootstrap-5', dropdownParent: $('#appointmentModal') });

    // --- HELPER FUNCTIONS ---
    function showToast(message, title = 'Success', type = 'success') {
        $('#toastBody').text(message);
        $('#toastTitle').text(title);
        var toastHeader = $('#responseToast .toast-header');
        toastHeader.removeClass('bg-success bg-danger').addClass(type === 'success' ? 'bg-success' : 'bg-danger');
        toastHeader.find('i').removeClass('fa-check-circle fa-exclamation-circle').addClass(type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle');
        responseToast.show();
    }

    // MODIFIED: This function now also handles the delete button visibility
    function prepareNewAppointmentModal(startDate = '') {
        form[0].reset();
        $('#patient_id').val('').trigger('change');
        $('#appointment_id').val('');
        $('#status').val('Scheduled');
        $('#appointment_date').val(startDate);
        $('#appointmentModalLabel').text('Add New Appointment');
        $('#deleteAppointmentBtn').hide(); // HIDE delete button for new appointments
        appointmentModal.show();
    }

    // --- CALENDAR INITIALIZATION ---
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
        events: 'ajax_get_appointments.php',
        selectable: true,
        editable: true,
        eventDidMount: function(info) {
            var tooltipContent = `<strong>Patient:</strong> ${info.event.extendedProps.patientName}<br><strong>Dentist:</strong> ${info.event.extendedProps.dentistName}<br><strong>Service:</strong> ${info.event.extendedProps.description}`;
            new bootstrap.Tooltip(info.el, { title: tooltipContent, placement: 'top', trigger: 'hover', container: 'body', html: true });
        },
        select: function(info) {
            prepareNewAppointmentModal(info.startStr.slice(0, 16));
        },
        eventClick: function(info) {
            if (info.event.extendedProps.isFollowUp) {
                // Optionally handle follow-up clicks differently, e.g., show a read-only modal
                alert('This is a read-only follow-up appointment generated from a treatment record.');
                return;
            }
            form[0].reset();
            $('#appointment_id').val(info.event.id);
            $.getJSON('ajax_get_appointment_details.php', { id: info.event.id }, function(data) {
                if(data) {
                    $('#patient_id').val(data.patient_id).trigger('change');
                    $('#dentist_id').val(data.dentist_id);
                    $('#appointment_date').val(data.appointment_date.slice(0, 16));
                    $('#service_description').val(data.service_description);
                    $('#status').val(data.status);
                    $('#appointmentModalLabel').text('Edit Appointment');
                    $('#deleteAppointmentBtn').show(); // SHOW delete button for existing appointments
                    appointmentModal.show();
                } else { showToast('Could not fetch appointment details.', 'Error', 'danger'); }
            });
        },
        eventDrop: function(info) {
            if (confirm("Are you sure you want to move this appointment?")) {
                 $.post('appointment_edit_process.php', {
                    appointment_id: info.event.id,
                    appointment_date: info.event.startStr.slice(0, 16),
                    drag: true
                }, function(response) {
                    showToast(response.message, response.success ? 'Success' : 'Error', response.success ? 'success' : 'danger');
                    if (!response.success) info.revert();
                    calendar.refetchEvents();
                }, 'json');
            } else {
                info.revert();
            }
        }
    });

    calendar.render();

    // --- EVENT LISTENERS (unchanged) ---
    $('#addAppointmentBtn').on('click', function() {
        prepareNewAppointmentModal();
    });

    form.on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var url = $('#appointment_id').val() ? 'appointment_edit_process.php' : 'appointment_add_process.php';

        $.post(url, formData, function(response) {
            showToast(response.message, response.success ? 'Success' : 'Error', response.success ? 'success' : 'danger');
            if(response.success) {
                appointmentModal.hide();
                calendar.refetchEvents();
            }
        }, 'json');
    });

    $('#deleteAppointmentBtn').on('click', function() {
        var appointmentId = $('#appointment_id').val();
        if(appointmentId && confirm('Are you sure you want to permanently delete this appointment?')) {
            $.post('appointment_delete_process.php', { appointment_id: appointmentId }, function(response) {
                showToast(response.message, response.success ? 'Success' : 'Error', response.success ? 'success' : 'danger');
                if(response.success) {
                    appointmentModal.hide();
                    calendar.refetchEvents();
                }
            }, 'json');
        }
    });
});
</script>

<?php
$conn->close();
?>