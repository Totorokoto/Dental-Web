<?php
// FILE: admin/appointments.php (FINAL CORRECTED VERSION)

require 'includes/header.php';
// The database connection is now open from header.php

// --- 1. FETCH DATA ---
$user_branch = $_SESSION['branch'];
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch patients. Admins can see all patients, others only see their branch.
if ($user_role === 'Admin') {
    $stmt_patients = $conn->prepare("SELECT patient_id, first_name, last_name, branch FROM patients ORDER BY last_name, first_name");
} else {
    $stmt_patients = $conn->prepare("SELECT patient_id, first_name, last_name, branch FROM patients WHERE branch = ? ORDER BY last_name, first_name");
    $stmt_patients->bind_param("s", $user_branch);
}
$stmt_patients->execute();
$patients_result = $stmt_patients->get_result();
$patients = $patients_result->fetch_all(MYSQLI_ASSOC);
$stmt_patients->close();


// Fetch active dentists and admins, including availability status
$dentists_result = $conn->query("SELECT user_id, full_name, role, branch, availability_status FROM users WHERE role IN ('Dentist', 'Admin') AND is_active = 1 ORDER BY full_name");
$dentists = $dentists_result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Page-specific styles and libraries -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


<!-- STYLES TO MATCH THE NEW DESIGN LANGUAGE -->
<style>
    :root {
        --primary-color: #00796B; 
        --secondary-color: #B2DFDB;
        --background-color: #f4f7f6;
        --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        --success-color: #198754;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #0dcaf0;
    }
    .page-header { border-bottom: none !important; }
    .btn { border-radius: 0.5rem; padding: 0.75rem 1.25rem; font-weight: 500; transition: all 0.2s ease-in-out; }
    .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
    .btn-primary:hover { background-color: #00695C; border-color: #00695C; }
    .btn-outline-secondary { border-radius: 0.5rem; }
    .form-control, .form-select { border-radius: 0.5rem; padding: 0.75rem 1rem; }
    .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25); }
    .form-label { font-weight: 500; color: #343a40; }
    .modal-content { border-radius: 1rem; border: none; box-shadow: var(--card-shadow); }
    .modal-header { background-color: #f8f9fa; border-bottom: none; padding: 1.5rem; }
    .modal-header .modal-title { font-weight: 600; }
    .modal-footer { border-top: none; padding: 1rem 1.5rem; }
    .modal-body { padding: 1.5rem; }
    .alert { border-radius: 0.75rem; border-width: 0; color: #fff; }
    .main-content-card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); background-color: #ffffff; overflow: hidden; }
    .legend-card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); background-color: #ffffff; }
    .legend-item { display: inline-flex; align-items: center; margin: 0.25rem 0.75rem; }
    .legend-color-box { width: 15px; height: 15px; border-radius: 4px; margin-right: 8px; }
    #calendar-wrapper { display: flex; height: calc(100vh - 220px); position: relative; overflow: hidden; }
    #calendar-container { flex-grow: 1; transition: padding-right 0.35s cubic-bezier(0.4, 0, 0.2, 1); position: relative; }
    #calendar { height: 100%; }
    #calendar-container.panel-is-open { padding-right: 450px; }
    #appointment-side-panel { width: 450px; position: absolute; right: 0; top: 0; bottom: 0; border-left: 1px solid #dee2e6; padding: 1.5rem; background-color: #ffffff; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto; z-index: 1000; }
    #appointment-side-panel.is-open { transform: translateX(0); }
    .side-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .detail-group { margin-bottom: 1.25rem; }
    .detail-label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; }
    .form-section { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: .75rem; padding: 1.25rem; margin-bottom: 1.25rem; }
    .form-section-header { font-size: 0.9rem; font-weight: 600; color: var(--primary-color); margin-bottom: 1rem; border-bottom: 1px solid #dee2e6; padding-bottom: 0.5rem; }
    
    option.dentist-unavailable { color: #999; font-style: italic; background-color: #f8f9fa; }
    option:disabled { color: #aeaeae !important; background-color: #f1f1f1 !important; }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 page-header">
    <h1 class="h2">Appointment Calendar</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary" id="addAppointmentBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="Create a new appointment for a patient.">
            <i class="fas fa-plus me-2"></i>Add New Appointment
        </button>
    </div>
</div>

<!-- Calendar Legend -->
<div class="card mb-4 legend-card">
    <div class="card-body d-flex justify-content-center flex-wrap py-2">
        <div class="legend-item"><span class="legend-color-box" style="background-color: #ffc107;"></span> Pending Approval</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #0d6efd;"></span> Scheduled</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #198754;"></span> Completed</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #6c757d;"></span> Cancelled</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #dc3545;"></span> No-Show</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #8e44ad;"></span> Follow-up</div>
    </div>
</div>

<!-- Main Calendar Area -->
<div class="main-content-card">
    <div id="calendar-wrapper">
        <div id="calendar-container">
            <div id='calendar' class="p-3"></div>
        </div>
        <!-- The Side Panel -->
        <div id="appointment-side-panel">
            <!-- View Mode Content -->
            <div id="side-panel-view-content">
                <div class="side-panel-header">
                    <h4 id="view-title">Appointment Details</h4>
                    <button class="btn btn-sm btn-outline-secondary" id="close-panel-btn-view" data-bs-toggle="tooltip" data-bs-placement="left" title="Close Panel"><i class="fas fa-times"></i></button>
                </div>
                <div class="detail-group"><div class="detail-label">Patient</div><div class="detail-value" id="view-patient-name"></div></div>
                <div class="detail-group"><div class="detail-label">Date & Time</div><div class="detail-value" id="view-datetime"></div></div>
                <div class="detail-group"><div class="detail-label">Assigned Dentist</div><div class="detail-value" id="view-dentist-name"></div></div>
                <div class="detail-group"><div class="detail-label">Service / Reason</div><div class="detail-value" id="view-service" style="white-space: pre-wrap;"></div></div>
                <div class="detail-group"><div class="detail-label">Status</div><div class="detail-value" id="view-status"></div></div>
                <hr class="my-4">
                
                <div id="standard-actions" class="d-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="#" id="view-patient-btn" class="btn btn-info text-white" data-bs-toggle="tooltip" data-bs-placement="top" title="View this patient's full record"><i class="fas fa-user me-2"></i>View Patient</a>
                        <div>
                            <button class="btn btn-danger" id="delete-panel-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Appointment"><i class="fas fa-trash"></i></button>
                            <button class="btn btn-warning" id="edit-panel-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Appointment"><i class="fas fa-edit"></i></button>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-success d-none" id="mark-completed-btn"><i class="fas fa-check-circle me-2"></i>Mark as Completed</button>
                    </div>
                </div>

                <div id="approval-actions" class="d-grid gap-2 mt-3 d-none">
                    <button class="btn btn-success" id="approve-btn"><i class="fas fa-check me-2"></i>Approve & Confirm</button>
                    <button class="btn btn-warning" id="reschedule-btn" data-bs-toggle="modal" data-bs-target="#declineModal"><i class="fas fa-calendar-alt me-2"></i>Decline / Reschedule</button>
                </div>
            </div>
            
            <!--Form (Add/Edit) Mode Content -->
            <div id="side-panel-form-content" style="display: none;">
                <div class="side-panel-header">
                    <h4 id="form-title">New Appointment</h4>
                    <button class="btn btn-sm btn-outline-secondary" id="close-panel-btn-form" data-bs-toggle="tooltip" data-bs-placement="left" title="Close Panel"><i class="fas fa-times"></i></button>
                </div>
                <form id="appointmentForm">
                    <input type="hidden" name="appointment_id" id="appointment_id">
                    <div class="form-section">
                        <h6 class="form-section-header">Patient & Provider</h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="showNewPatientModalBtn"><i class="fas fa-plus me-1"></i> Quick Add</button>
                            </div>
                            <select class="form-select searchable-select" id="patient_id" name="patient_id" required style="width: 100%;">
                                <option value="">Search for a patient...</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['patient_id']; ?>" data-branch="<?php echo htmlspecialchars($patient['branch']); ?>">
                                        <?php echo htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']); ?> (<?php echo htmlspecialchars($patient['branch']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="dentist_id" class="form-label">Assigned Dentist <span class="text-danger">*</span></label>
                            <select class="form-select" id="dentist_id" name="dentist_id" required>
                                <option value="">Select a Dentist...</option>
                                <?php foreach ($dentists as $dentist): ?>
                                    <?php 
                                        $is_available = ($dentist['availability_status'] === 'Available');
                                        $status_text = $is_available ? '' : ' - ' . htmlspecialchars($dentist['availability_status']);
                                        $option_class = $is_available ? '' : 'class="dentist-unavailable"';
                                        $disabled_attr = !$is_available ? 'disabled' : '';
                                    ?>
                                    <option value="<?php echo $dentist['user_id']; ?>" 
                                            data-branch="<?php echo htmlspecialchars($dentist['branch']); ?>" 
                                            data-role="<?php echo htmlspecialchars($dentist['role']); ?>"
                                            <?php echo $option_class; ?>
                                            <?php echo $disabled_attr; ?>>
                                        <?php echo htmlspecialchars($dentist['full_name']) . $status_text; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-header">Appointment Details</h6>
                         <div class="mb-3">
                            <label for="service_description" class="form-label">Service / Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="service_description" name="service_description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Appointment Time <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="appointment_date" name="appointment_date" required placeholder="Select date and time...">
                        </div>
                        <div>
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Pending Approval">Pending Approval</option>
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
    </div>
</div>

<!-- All Modals (Quick Add, Decline, etc.) -->
<div class="modal fade" id="newPatientModal" tabindex="-1" style="z-index: 1061;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Patient Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newPatientForm">
                <div class="modal-body">
                    <p class="text-muted small">This patient will be assigned to your branch: <strong><?php echo htmlspecialchars($user_branch); ?></strong>.</p>
                    <div class="mb-3">
                        <label for="new_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="new_mobile_no" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="new_email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="declineModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Decline & Suggest New Times</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="declineForm">
        <div class="modal-body">
          <input type="hidden" id="decline_appointment_id" name="appointment_id">
          <div class="mb-3">
              <label for="decline_reason_select" class="form-label">Reason for Declining <span class="text-danger">*</span></label>
              <select class="form-select" id="decline_reason_select" name="reason_select" required>
                  <option value="" selected disabled>Choose a reason...</option>
                  <option value="The requested time slot is fully booked.">The requested time slot is fully booked.</option>
                  <option value="The selected doctor is unavailable at that time.">The selected doctor is unavailable at that time.</option>
                  <option value="The clinic is closed for a holiday/event on the requested date.">The clinic is closed for a holiday/event on the requested date.</option>
                  <option value="Other">Other (please specify below)</option>
              </select>
          </div>
          <div class="mb-3" id="decline_custom_reason_wrapper" style="display: none;">
              <label for="decline_reason_custom" class="form-label">Custom Reason</label>
              <textarea class="form-control" id="decline_reason_custom" name="reason_custom" rows="2"></textarea>
          </div>
          <hr>
          <h6>Suggested Alternative Slots</h6>
          <p class="text-muted small">The system will find the next available slots in the patient's branch. These will be included in the email.</p>
          <div id="suggested-slots-container"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Send Reschedule Email</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign Dentist & Confirm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="approveForm">
        <div class="modal-body">
          <input type="hidden" id="approve_appointment_id" name="appointment_id">
          <p>Please assign an available dentist for the following appointment:</p>
          <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Patient:</strong> <span id="approve_patient_name"></span></li>
              <li class="list-group-item"><strong>Time:</strong> <span id="approve_appointment_time"></span></li>
          </ul>
          <div class="mb-3">
              <label for="approve_dentist_id" class="form-label">Assign Dentist <span class="text-danger">*</span></label>
              <select class="form-select" id="approve_dentist_id" name="dentist_id" required>
              </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm and Send Email</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php 
// The connection will be closed by footer.php
require 'includes/footer.php'; 
?>

<!-- Pass PHP variables to a global JS object -->
<script>
    window.userConfig = {
        branch: '<?php echo $user_branch; ?>',
        role: '<?php echo $user_role; ?>'
    };
</script>

<!-- FullCalendar JS Library -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>

<!-- Load the external JavaScript file -->
<script src="assets/js/appointments.js"></script>

<!-- SCRIPT TO INITIALIZE BOOTSTRAP TOOLTIPS -->
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>