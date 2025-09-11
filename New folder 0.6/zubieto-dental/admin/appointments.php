<?php
// FILE: admin/appointments.php 

require 'includes/header.php';
require '../includes/db_connect.php';

// --- 1. FETCH DATA STRICTLY FOR THE USER'S BRANCH ---
$user_branch = $_SESSION['branch'];
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch patients ONLY from the user's branch
$stmt_patients = $conn->prepare("SELECT patient_id, first_name, last_name, branch FROM patients WHERE branch = ? ORDER BY last_name, first_name");
$stmt_patients->bind_param("s", $user_branch);
$stmt_patients->execute();
$patients_result = $stmt_patients->get_result();
$patients = $patients_result->fetch_all(MYSQLI_ASSOC);
$stmt_patients->close();

// Fetch active dentists and admins.
$dentists_result = $conn->query("SELECT user_id, full_name, role, branch FROM users WHERE role IN ('Dentist', 'Admin') AND is_active = 1 ORDER BY full_name");
$dentists = $dentists_result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Page-specific styles and libraries -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<!-- STYLES FOR RESPONSIVE SIDE-PANEL LAYOUT -->
<style>
    #calendar-wrapper { display: flex; height: calc(100vh - 210px); position: relative; overflow: hidden; }
    #calendar-container { flex-grow: 1; transition: padding-right 0.35s cubic-bezier(0.4, 0, 0.2, 1); position: relative; }
    #calendar { height: 100%; }
    #calendar-container.panel-is-open { padding-right: 450px; }
    #appointment-side-panel { width: 450px; position: absolute; right: 0; top: 0; bottom: 0; border-left: 1px solid #dee2e6; padding: 1.5rem; background-color: #ffffff; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto; box-shadow: -4px 0 15px rgba(0,0,0,0.05); z-index: 1000; }
    #appointment-side-panel.is-open { transform: translateX(0); }
    .side-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .detail-group { margin-bottom: 1.25rem; }
    .detail-label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .detail-value { font-size: 1rem; font-weight: 500; }
    .legend-item { display: inline-flex; align-items: center; margin-right: 1.5rem; }
    .legend-color-box { width: 15px; height: 15px; border-radius: 4px; margin-right: 8px; }
    
    /* **CSS FOR FORM LAYOUT** */
    .form-section {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        padding: 1rem;
        margin-bottom: 1.25rem;
    }
    .form-section-header {
        font-size: 0.9rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 0.5rem;
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Appointment Calendar</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary" id="addAppointmentBtn"><i class="fas fa-plus me-2"></i>Add New Appointment</button>
    </div>
</div>

<!-- Calendar Legend -->
<div class="card mb-3"><div class="card-body d-flex justify-content-center flex-wrap">
    <div class="legend-item"><span class="legend-color-box" style="background-color: #0d6efd;"></span> Scheduled</div>
    <div class="legend-item"><span class="legend-color-box" style="background-color: #198754;"></span> Completed</div>
    <div class="legend-item"><span class="legend-color-box" style="background-color: #6c757d;"></span> Cancelled</div>
    <div class="legend-item"><span class="legend-color-box" style="background-color: #dc3545;"></span> No-Show</div>
    <div class="legend-item"><span class="legend-color-box" style="background-color: #8e44ad;"></span> Follow-up</div>
</div></div>

<!-- Main Calendar Area -->
<div class="card"><div class="card-body" id="calendar-wrapper">
    <div id="calendar-container"><div id='calendar'></div></div>
    <!-- The Side Panel -->
    <div id="appointment-side-panel">
        <!-- View Mode Content -->
        <div id="side-panel-view-content">
            <div class="side-panel-header"><h4 id="view-title">Appointment Details</h4><div><button class="btn btn-outline-secondary btn-sm" id="close-panel-btn-view"><i class="fas fa-times"></i></button></div></div>
            <div class="detail-group"><div class="detail-label">Patient</div><div class="detail-value" id="view-patient-name"></div></div>
            <div class="detail-group"><div class="detail-label">Date & Time</div><div class="detail-value" id="view-datetime"></div></div>
            <div class="detail-group"><div class="detail-label">Assigned Dentist</div><div class="detail-value" id="view-dentist-name"></div></div>
            <div class="detail-group"><div class="detail-label">Service / Reason</div><div class="detail-value" id="view-service" style="white-space: pre-wrap;"></div></div>
            <div class="detail-group"><div class="detail-label">Status</div><div class="detail-value" id="view-status"></div></div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <a href="#" id="view-patient-btn" class="btn btn-info"><i class="fas fa-user me-2"></i>View Patient</a>
                <div><button class="btn btn-danger" id="delete-panel-btn" title="Delete Appointment"><i class="fas fa-trash"></i></button><button class="btn btn-warning" id="edit-panel-btn" title="Edit Appointment"><i class="fas fa-edit"></i></button></div>
            </div>
            <div class="d-grid gap-2 mt-3"><button class="btn btn-success" id="mark-completed-btn"><i class="fas fa-check-circle me-2"></i>Mark as Completed</button></div>
        </div>
        
        <!--Form (Add/Edit) Mode Content with improved layout -->
        <div id="side-panel-form-content" style="display: none;">
            <div class="side-panel-header"><h4 id="form-title">New Appointment</h4><button class="btn btn-outline-secondary btn-sm" id="close-panel-btn-form"><i class="fas fa-times"></i></button></div>
            <form id="appointmentForm">
                <input type="hidden" name="appointment_id" id="appointment_id">
                
                <!-- Section 1: Patient & Provider -->
                <div class="form-section">
                    <h6 class="form-section-header">Patient & Provider</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="showNewPatientModalBtn"><i class="fas fa-plus me-1"></i> Quick Add</button>
                        </div>
                        <select class="form-select searchable-select" id="patient_id" name="patient_id" required style="width: 100%;">
                            <option value="">Search for a patient...</option>
                            <?php foreach ($patients as $patient): ?><option value="<?php echo $patient['patient_id']; ?>" data-branch="<?php echo htmlspecialchars($patient['branch']); ?>"><?php echo htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="dentist_id" class="form-label">Assigned Dentist <span class="text-danger">*</span></label>
                        <select class="form-select" id="dentist_id" name="dentist_id" required>
                            <option value="">Select a Dentist...</option>
                            <?php foreach ($dentists as $dentist): ?><option value="<?php echo $dentist['user_id']; ?>" data-branch="<?php echo htmlspecialchars($dentist['branch']); ?>" data-role="<?php echo htmlspecialchars($dentist['role']); ?>"><?php echo htmlspecialchars($dentist['full_name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Section 2: Appointment Details -->
                <div class="form-section">
                    <h6 class="form-section-header">Appointment Details</h6>
                     <div class="mb-3">
                        <label for="service_description" class="form-label">Service / Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="service_description" name="service_description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="appointment_date" class="form-label">Appointment Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required>
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="No-Show">No-Show</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" id="cancel-edit-btn">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div></div>

<!-- Quick Add Patient Modal -->
<div class="modal fade" id="newPatientModal" tabindex="-1" style="z-index: 1061;"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="newPatientModalLabel">Quick Patient Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="newPatientForm"><div class="modal-body"><p class="text-muted small">Patient assigned to branch: <strong><?php echo htmlspecialchars($user_branch); ?></strong>.</p><div class="mb-3"><label for="new_first_name" class="form-label">First Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="new_first_name" required></div><div class="mb-3"><label for="new_last_name" class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="new_last_name" required></div><div class="mb-3"><label for="new_mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label><input type="tel" class="form-control" id="new_mobile_no" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Patient</button></div></form></div></div></div>

<?php 
$conn->close();
require 'includes/footer.php'; 
?>

<!-- Page-Specific JavaScript -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    var calendarEl = document.getElementById('calendar');
    var sidePanel = $('#appointment-side-panel');
    var calendarContainer = $('#calendar-container');
    var form = $('#appointmentForm');
    var newPatientModal = new bootstrap.Modal(document.getElementById('newPatientModal'));
    var userBranch = '<?php echo $user_branch; ?>';
    var userRole = '<?php echo $user_role; ?>';
    
    $('.searchable-select').select2({ theme: 'bootstrap-5', dropdownParent: sidePanel });

    function openSidePanel() { if (!sidePanel.hasClass('is-open')) { sidePanel.addClass('is-open'); calendarContainer.addClass('panel-is-open'); setTimeout(() => { calendar.updateSize(); }, 350); } }
    function closeSidePanel() { if (sidePanel.hasClass('is-open')) { sidePanel.removeClass('is-open'); calendarContainer.removeClass('panel-is-open'); setTimeout(() => { calendar.updateSize(); }, 350); } }
    function showViewMode() { $('#side-panel-view-content').show(); $('#side-panel-form-content').hide(); }
    function showFormMode() { $('#side-panel-view-content').hide(); $('#side-panel-form-content').show(); }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        events: 'ajax_get_appointments.php',
        selectable: true,
        editable: (info) => !info.event.extendedProps.isFollowUp,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.extendedProps.isFollowUp) { alert('This is a read-only follow-up. To change it, edit the patient\'s treatment record.'); return; }
            populateAndShowViewPanel(info.event.id);
        },
        select: function(info) {
            const startDate = new Date(info.startStr);
            startDate.setHours(9, 0, 0);
            const localISOString = new Date(startDate.getTime() - (startDate.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
            prepareAndShowNewForm(localISOString);
        },
    });
    calendar.render();

    function populateAndShowViewPanel(eventId) {
        $.getJSON('ajax_get_appointment_details.php', { id: eventId }, function(data) {
            if(data) {
                $('#view-patient-name').text(data.patient_name || 'N/A');
                $('#view-datetime').text(formatEventDateTime(data.appointment_date));
                $('#view-dentist-name').text(data.dentist_name || 'N/A');
                $('#view-service').text(data.service_description || 'N/A');
                $('#view-status').html(`<span class="badge" style="background-color:${data.color}; color: white; font-size: 1rem;">${data.status}</span>`);
                $('#edit-panel-btn').data('id', eventId);
                $('#delete-panel-btn').data('id', eventId);
                $('#view-patient-btn').attr('href', `patient_view.php?id=${data.patient_id}`);
                $('#mark-completed-btn').data('id', eventId);
                if (data.status === 'Completed') { $('#mark-completed-btn').hide(); } else { $('#mark-completed-btn').show(); }
                showViewMode(); openSidePanel();
            }
        });
    }

    function prepareAndShowNewForm(start) {
        form[0].reset();
        $('#appointment_id').val('');
        $('#patient_id').val('').trigger('change');
        $('#appointment_date').val(start);
        $('#status').val('Scheduled');
        $('#form-title').text('New Appointment');
        $('#cancel-edit-btn').hide();
        filterControlsByBranch();
        $('#showNewPatientModalBtn').show();
        showFormMode();
        openSidePanel();
    }

    function prepareAndShowEditForm(eventId) {
        $.getJSON('ajax_get_appointment_details.php', { id: eventId }, function(data) {
            if(data) {
                form[0].reset();
                $('#appointment_id').val(data.appointment_id);
                filterControlsByBranch();
                $('#patient_id').val(data.patient_id).trigger('change');
                $('#dentist_id').val(data.dentist_id);
                $('#service_description').val(data.service_description);
                $('#appointment_date').val(data.appointment_date ? data.appointment_date.slice(0, 16) : '');
                $('#status').val(data.status);
                $('#form-title').text('Edit Appointment');
                $('#cancel-edit-btn').show();
                $('#showNewPatientModalBtn').hide();
                showFormMode();
            }
        });
    }
    
    function filterControlsByBranch() { $('#dentist_id option').each(function() { var dentistBranch = $(this).data('branch'); if (dentistBranch === userBranch || $(this).data('role') === 'Admin' || !$(this).val()) { $(this).prop('disabled', false); } else { $(this).prop('disabled', true); } }); }
    function formatEventDateTime(start) { const date = new Date(start); return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' at ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }); }

    $('#addAppointmentBtn').on('click', () => { const now = new Date(); const localISOString = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 16); prepareAndShowNewForm(localISOString); });
    $('#close-panel-btn-view, #close-panel-btn-form').on('click', closeSidePanel);
    $('#cancel-edit-btn').on('click', () => { if ($('#appointment_id').val()) { populateAndShowViewPanel($('#appointment_id').val()); } else { closeSidePanel(); } });
    $('#edit-panel-btn').on('click', function() { prepareAndShowEditForm($(this).data('id')); });
    $('#showNewPatientModalBtn').on('click', () => { $('#newPatientForm')[0].reset(); newPatientModal.show(); });
    $('#newPatientForm').on('submit', function(e) { e.preventDefault(); $.ajax({ url: 'ajax_add_quick_patient.php', type: 'POST', data: { first_name: $('#new_first_name').val(), last_name: $('#new_last_name').val(), mobile_no: $('#new_mobile_no').val() }, dataType: 'json', success: function(response) { if (response.success) { var newOption = new Option(response.full_name, response.new_patient_id, true, true); $(newOption).data('branch', response.branch); $('#patient_id').append(newOption).trigger('change'); newPatientModal.hide(); } else { alert('Error: ' + response.message); } } }); });
    form.on('submit', function(e) { e.preventDefault(); var url = $('#appointment_id').val() ? 'appointment_edit_process.php' : 'appointment_add_process.php'; $.ajax({ url: url, type: 'POST', data: $(this).serialize(), dataType: 'json', success: function(response) { if (response.success) { closeSidePanel(); calendar.refetchEvents(); } else { alert('Error: ' + response.message); } }, error: function() { alert('A critical server error occurred.'); } }); });
    $('#delete-panel-btn').on('click', function() { deleteAppointment($(this).data('id')); });
    function deleteAppointment(id) { if(id && confirm('Are you sure you want to delete this appointment?')) { $.post('appointment_delete_process.php', { appointment_id: id }, (response) => { if(response.success) { closeSidePanel(); calendar.refetchEvents(); } else { alert("Error: " + response.message); } }, 'json'); } }
    $('#mark-completed-btn').on('click', function() { var appointmentId = $(this).data('id'); if (!appointmentId) return; if (confirm('Are you sure you want to mark this appointment as completed?')) { $.ajax({ url: 'appointment_status_process.php', type: 'POST', data: { appointment_id: appointmentId, status: 'Completed' }, dataType: 'json', success: function(response) { if (response.success) { closeSidePanel(); calendar.refetchEvents(); } else { alert('Error: ' + response.message); } }, error: function() { alert('A server error occurred while updating the status.'); } }); } });
    filterControlsByBranch();
});
</script>