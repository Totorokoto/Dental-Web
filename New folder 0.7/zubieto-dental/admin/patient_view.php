<?php
// FILE: admin/patient_view.php 

require 'includes/header.php';
require '../includes/db_connect.php';

$return_branch = isset($_GET['branch']) ? htmlspecialchars($_GET['branch']) : 'All';

// 1. VALIDATE AND SANITIZE INPUT
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id === 0) {
    $_SESSION['message'] = "Invalid patient ID provided.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit();
}

// =========================================================================
// (PART 1): GET THE ACTIVE TAB FROM URL OR SET A DEFAULT
// =========================================================================
$active_tab = isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'profile';


// 2. FETCH ALL RELATED DATA FOR THE PATIENT
$sql_patient = "SELECT p.*, mh.* FROM patients p LEFT JOIN medical_history mh ON p.patient_id = mh.patient_id WHERE p.patient_id = ?";
$stmt_patient = $conn->prepare($sql_patient);
$stmt_patient->bind_param("i", $patient_id);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
if ($result_patient->num_rows === 0) {
    $_SESSION['message'] = "No patient found with the specified ID.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit();
}
$patient = $result_patient->fetch_assoc();
$stmt_patient->close();

// Fetch Treatments
$sql_treatments = "SELECT tr.*, u.full_name as dentist_name FROM treatment_records tr JOIN users u ON tr.dentist_id = u.user_id WHERE tr.patient_id = ? ORDER BY tr.procedure_date DESC, tr.record_id DESC";
$stmt_treatments = $conn->prepare($sql_treatments);
$stmt_treatments->bind_param("i", $patient_id);
$stmt_treatments->execute();
$treatments = $stmt_treatments->get_result();
$stmt_treatments->close();

// Fetch Findings
$sql_findings = "SELECT cf.*, u.full_name as dentist_name FROM clinical_findings cf JOIN users u ON cf.dentist_id = u.user_id WHERE cf.patient_id = ? ORDER BY cf.finding_date DESC, cf.finding_id DESC";
$stmt_findings = $conn->prepare($sql_findings);
$stmt_findings->bind_param("i", $patient_id);
$stmt_findings->execute();
$findings = $stmt_findings->get_result();
$stmt_findings->close();

// HELPER FUNCTION
function display_yes_no($value, $details = null) {
    $details_html = !empty($details) ? '<em class="ms-2 text-muted">(' . htmlspecialchars($details) . ')</em>' : '';
    return ($value == 1 ? '<span class="fw-bold text-success">Yes</span>' : '<span class="text-muted">No</span>') . $details_html;
}
?>

<!-- Custom Styles -->
<style>
    .patient-header { background-color: #eef1f5; padding: 1.5rem; border-radius: .5rem; margin-bottom: 1.5rem; }
    .info-card { border: none; border-radius: .5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .info-card .card-header { background-color: #f8f9fa; font-weight: bold; border-bottom: 1px solid #dee2e6; display: flex; align-items: center; }
    .info-card .card-header i { color: #0d6efd; margin-right: 0.75rem; }
    .detail-item { margin-bottom: 1rem; }
    .detail-label { color: #6c757d; font-size: 0.9rem; display: block; }
    .detail-value { font-size: 1rem; font-weight: 500; }
    .critical-alert { border-left: 5px solid #dc3545; }
    .warning-alert { border-left: 5px solid #ffc107; }
    .nav-tabs .nav-link { font-weight: 500; }
    .tab-content.card { border-top-left-radius: 0; border-top: none; padding: 1.5rem; }
</style>

<!-- PAGE HEADER -->
<div class="patient-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h2 mb-1"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h1>
        <p class="text-muted mb-0">Patient ID: <?php echo $patient['patient_id']; ?> • Branch: <?php echo htmlspecialchars($patient['branch']); ?> • Registration: <?php echo date('F j, Y', strtotime($patient['registration_date'])); ?></p>
    </div>
    <div>
        <a href="patient_edit.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Edit Full Record</a>
        <a href="patients.php?branch=<?php echo urlencode($return_branch); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
    </div>
</div>

<?php
if (isset($_SESSION['message'])) {
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
    echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">' . $_SESSION['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']); unset($_SESSION['message_type']);
}
?>

<!-- =========================================================================
     (PART 2): DYNAMICALLY ADD 'active' CLASS TO TABS
     ========================================================================= -->
<ul class="nav nav-tabs" id="patientTab" role="tablist">
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'profile') echo 'active'; ?>" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-selected="true"><i class="fas fa-user-circle me-2"></i>Patient Profile</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'medical-history') echo 'active'; ?>" id="medical-history-tab" data-bs-toggle="tab" data-bs-target="#medical-history" type="button" role="tab" aria-selected="false"><i class="fas fa-heartbeat me-2"></i>Medical History</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'findings') echo 'active'; ?>" id="findings-tab" data-bs-toggle="tab" data-bs-target="#findings" type="button" role="tab" aria-selected="false"><i class="fas fa-search-plus me-2"></i>Clinical Findings</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'treatments') echo 'active'; ?>" id="treatments-tab" data-bs-toggle="tab" data-bs-target="#treatments" type="button" role="tab" aria-selected="false"><i class="fas fa-notes-medical me-2"></i>Treatment Record</button></li>
</ul>

<!-- =========================================================================
     (PART 3): DYNAMICALLY ADD 'show active' TO TAB PANES
     ========================================================================= -->
<div class="tab-content card" id="patientTabContent">
  
  <div class="tab-pane fade <?php if($active_tab == 'profile') echo 'show active'; ?>" id="profile" role="tabpanel" aria-labelledby="profile-tab">
    <!-- ... Patient Profile Content ... -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-7">
            <!-- Personal Details Card -->
            <div class="card info-card">
                <div class="card-header"><i class="fas fa-user-circle"></i>Personal Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 detail-item"><span class="detail-label">Last Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['last_name']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">First Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['first_name']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Middle Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['middle_name'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Nickname</span><span class="detail-value"><?php echo htmlspecialchars($patient['nickname'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Birthdate</span><span class="detail-value"><?php echo date('F j, Y', strtotime($patient['birthdate'])); ?> (<?php echo $patient['age']; ?> years old)</span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Gender</span><span class="detail-value"><?php echo $patient['gender'] == 'M' ? 'Male' : 'Female'; ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Civil Status</span><span class="detail-value"><?php echo htmlspecialchars($patient['civil_status']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Occupation</span><span class="detail-value"><?php echo htmlspecialchars($patient['occupation'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Nationality</span><span class="detail-value"><?php echo htmlspecialchars($patient['nationality'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Religion</span><span class="detail-value"><?php echo htmlspecialchars($patient['religion'] ?: 'N/A'); ?></span></div>
                    </div>
                </div>
            </div>
            <!-- Contact Card -->
            <div class="card info-card">
                <div class="card-header"><i class="fas fa-address-book"></i>Contact & Address</div>
                <div class="card-body">
                     <div class="row">
                        <div class="col-md-6 detail-item"><span class="detail-label">Mobile Number</span><span class="detail-value"><?php echo htmlspecialchars($patient['mobile_no']); ?></span></div>
                        <div class="col-md-12 detail-item"><span class="detail-label">Address</span><span class="detail-value"><?php echo htmlspecialchars($patient['address']); ?></span></div>
                    </div>
                </div>
            </div>
             <!-- Guardian Info Card -->
            <?php if (!empty($patient['parent_guardian_name'])): ?>
            <div class="card info-card">
                <div class="card-header"><i class="fas fa-users"></i>Guardian Information</div>
                <div class="card-body">
                     <div class="row">
                        <div class="col-md-6 detail-item"><span class="detail-label">Guardian's Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['parent_guardian_name']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Guardian's Occupation</span><span class="detail-value"><?php echo htmlspecialchars($patient['parent_guardian_occupation']); ?></span></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- Right Column -->
        <div class="col-lg-5">
             <!-- Medical Provider & Vitals Card -->
            <div class="card info-card">
                <div class="card-header"><i class="fas fa-clinic-medical"></i>Medical Provider & Vitals</div>
                <div class="card-body">
                    <div class="detail-item"><span class="detail-label">Physician's Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['physician_name'] ?: 'N/A'); ?></span></div>
                    <div class="detail-item"><span class="detail-label">Physician's Address</span><span class="detail-value"><?php echo htmlspecialchars($patient['physician_address'] ?: 'N/A'); ?></span></div>
                    <div class="detail-item"><span class="detail-label">Physician's Phone</span><span class="detail-value"><?php echo htmlspecialchars($patient['physician_phone'] ?: 'N/A'); ?></span></div>
                    <div class="detail-item"><span class="detail-label">Last Physical Exam</span><span class="detail-value"><?php echo !empty($patient['last_physical_exam']) ? date('F j, Y', strtotime($patient['last_physical_exam'])) : 'N/A'; ?></span></div>
                    <hr>
                    <h6 class="mb-3">Latest Vitals</h6>
                    <div class="row">
                        <div class="col-6 detail-item"><span class="detail-label">Blood Pressure</span><span class="detail-value"><?php echo htmlspecialchars($patient['blood_pressure'] ?: 'N/A'); ?></span></div>
                        <div class="col-6 detail-item"><span class="detail-label">Respiratory Rate</span><span class="detail-value"><?php echo htmlspecialchars($patient['respiratory_rate'] ?: 'N/A'); ?></span></div>
                        <div class="col-6 detail-item"><span class="detail-label">Pulse Rate</span><span class="detail-value"><?php echo htmlspecialchars($patient['pulse_rate'] ?: 'N/A'); ?></span></div>
                        <div class="col-6 detail-item"><span class="detail-label">Temperature</span><span class="detail-value"><?php echo htmlspecialchars($patient['temperature'] ?: 'N/A'); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Full Width Card for Dental History -->
        <div class="col-lg-12">
            <div class="card info-card">
                <div class="card-header"><i class="fas fa-tooth"></i>Initial Dental Assessment</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 detail-item"><span class="detail-label">Chief Complaint</span><span class="detail-value"><?php echo nl2br(htmlspecialchars($patient['chief_complaint'])); ?></span></div>
                        <div class="col-md-12 detail-item"><span class="detail-label">History of Present Illness</span><span class="detail-value"><?php echo nl2br(htmlspecialchars($patient['history_of_present_illness'] ?: 'N/A')); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Previous Dentist</span><span class="detail-value"><?php echo htmlspecialchars($patient['previous_dentist'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Last Dental Visit</span><span class="detail-value"><?php echo !empty($patient['last_dental_visit']) ? date('F j, Y', strtotime($patient['last_dental_visit'])) : 'N/A'; ?></span></div>
                        <div class="col-md-12 detail-item">
                            <span class="detail-label">Previous Procedures Done</span>
                            <span class="detail-value">
                                <?php 
                                    $procedures = ['procedures_done_perio' => 'Perio','procedures_done_resto' => 'Resto','procedures_done_os' => 'O.S.','procedures_done_prostho' => 'Prostho','procedures_done_endo' => 'Endo','procedures_done_ortho' => 'Ortho'];
                                    $done_procedures = [];
                                    foreach ($procedures as $field => $label) { if (!empty($patient[$field])) { $done_procedures[] = $label; } }
                                    echo !empty($done_procedures) ? implode(' • ', $done_procedures) : '<span class="text-muted">None indicated.</span>';
                                ?>
                            </span>
                        </div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Other Procedures Specified</span><span class="detail-value"><?php echo htmlspecialchars($patient['procedures_done_others_specify'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Complications Noted from Previous Treatments</span><span class="detail-value"><?php echo htmlspecialchars($patient['complications'] ?: 'N/A'); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <div class="tab-pane fade <?php if($active_tab == 'medical-history') echo 'show active'; ?>" id="medical-history" role="tabpanel" aria-labelledby="medical-history-tab">
    <!-- ... Medical History Content... -->
        <div class="alert alert-danger critical-alert" role="alert">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Reported Allergies</h4>
            <hr>
            <?php 
                $allergies = [ 'allergic_anesthetics' => 'Anesthetics (Lidocaine)', 'allergic_penicillin' => 'Penicillin/Antibiotics', 'allergic_aspirin' => 'Aspirin', 'allergic_latex' => 'Latex' ];
                $yes_allergies = [];
                foreach ($allergies as $field => $label) { if (!empty($patient[$field])) { $yes_allergies[] = $label; } }
                if (!empty($yes_allergies)) {
                    echo '<ul>';
                    foreach ($yes_allergies as $allergy) { echo '<li>' . htmlspecialchars($allergy) . '</li>'; }
                    echo '</ul>';
                } else { echo '<p class="mb-0">No major allergies reported.</p>'; }
                if (!empty($patient['allergic_others_details'])) { echo "<p class='mb-1'><strong>Other Specified Allergies: </strong>" . htmlspecialchars($patient['allergic_others_details']) . "</p>"; }
                if (!empty($patient['allergy_reaction_details'])) { echo "<p class='mb-0'><strong>Reaction Type: </strong>" . htmlspecialchars($patient['allergy_reaction_details']) . "</p>"; }
            ?>
        </div>
        
        <div class="alert alert-warning warning-alert" role="alert">
            <h4 class="alert-heading"><i class="fas fa-first-aid me-2"></i>Reported Medical Conditions</h4>
            <hr>
            <?php 
                $conditions = [ 'has_high_blood_pressure' => 'High Blood Pressure', 'has_low_blood_pressure' => 'Low Blood Pressure', 'has_epilepsy_convulsions' => 'Epilepsy/Convulsions', 'has_aids_hiv' => 'AIDS/HIV', 'has_stomach_troubles_ulcer' => 'Stomach Troubles/Ulcer', 'has_fainting_seizure' => 'Fainting/Seizure', 'has_rapid_weight_loss' => 'Rapid Weight Loss', 'had_radiation_therapy' => 'Radiation Therapy', 'has_joint_replacement_implant' => 'Joint Replacement/Implant', 'had_heart_surgery' => 'Heart Surgery', 'had_heart_attack' => 'Heart Attack', 'has_heart_disease' => 'Heart Disease', 'has_heart_murmur' => 'Heart Murmur', 'has_hepatitis_jaundice' => 'Hepatitis/Jaundice', 'has_tuberculosis' => 'Tuberculosis', 'has_swollen_ankles' => 'Swollen Ankles', 'has_kidney_disease' => 'Kidney Disease', 'has_diabetes' => 'Diabetes', 'has_bleeding_blood_disease' => 'Bleeding/Blood Disease', 'has_arthritis_rheumatism' => 'Arthritis/Rheumatism', 'has_cancer_tumor' => 'Cancer/Tumor', 'has_anemia' => 'Anemia', 'has_angina' => 'Angina', 'has_asthma' => 'Asthma', 'has_thyroid_problem' => 'Thyroid Problem', 'has_rheumatic_fever_disease' => 'Rheumatic Fever', 'has_hay_fever_allergies' => 'Hay Fever/Allergies', 'has_respiratory_problems' => 'Respiratory Problems', 'has_emphysema' => 'Emphysema', 'has_breathing_problems' => 'Breathing Problems', 'had_stroke' => 'Stroke', 'has_chest_pain' => 'Chest Pain' ];
                $yes_conditions = [];
                foreach ($conditions as $field => $label) { if (!empty($patient[$field])) { $yes_conditions[] = $label; } }
                if (!empty($yes_conditions)) {
                    echo '<ul class="mb-0">';
                    foreach ($yes_conditions as $condition) {
                        echo '<li>' . htmlspecialchars($condition) . '</li>';
                    }
                    echo '</ul>';
                } else { 
                    echo '<p class="mb-0">No major conditions reported.</p>'; 
                }
                if (!empty($patient['other_diseases_details'])) { echo "<hr><p class='mb-0'><strong>Other Specified Conditions: </strong>" . htmlspecialchars($patient['other_diseases_details']) . "</p>"; }
            ?>
        </div>

      <div class="card info-card">
          <div class="card-header"><i class="fas fa-file-medical-alt"></i>General Health Questionnaire</div>
          <div class="card-body">
              <div class="row">
                  <div class="col-md-6 detail-item"><span class="detail-label">In good health?</span><span class="detail-value"><?php echo display_yes_no($patient['are_you_in_good_health']); ?></span></div>
                  <div class="col-md-6 detail-item"><span class="detail-label">Under medical treatment?</span><span class="detail-value"><?php echo display_yes_no($patient['is_under_medical_treatment'], $patient['medical_treatment_details']); ?></span></div>
                  <div class="col-md-6 detail-item"><span class="detail-label">Had serious illness/operation?</span><span class="detail-value"><?php echo display_yes_no($patient['had_serious_illness_or_operation'], $patient['illness_operation_details']); ?></span></div>
                  <div class="col-md-6 detail-item"><span class="detail-label">Ever hospitalized?</span><span class="detail-value"><?php echo display_yes_no($patient['has_been_hospitalized'], $patient['hospitalization_details']); ?></span></div>
                  <div class="col-md-6 detail-item"><span class="detail-label">Taking medication?</span><span class="detail-value"><?php echo display_yes_no($patient['is_taking_medication'], $patient['medication_details']); ?></span></div>
                  <div class="col-md-6 detail-item"><span class="detail-label">Taking diet drugs?</span><span class="detail-value"><?php echo display_yes_no($patient['is_on_diet'], $patient['diet_details']); ?></span></div>
                  <div class="col-md-6 detail-item"><span class="detail-label">Drinks alcohol?</span><span class="detail-value"><?php echo display_yes_no($patient['drinks_alcoholic_beverages'], $patient['alcohol_frequency']); ?></span></div>
                  <div class="col-md-6 detail-item"><span class="detail-label">Uses tobacco?</span><span class="detail-value"><?php echo display_yes_no($patient['uses_tobacco'], $patient['tobacco_details']); ?></span></div>
              </div>
              <hr>
              <div class="detail-item">
                <span class="detail-label">Other conditions or problems the dentist should know about?</span>
                <span class="detail-value"><?php echo !empty($patient['other_conditions_to_know']) ? nl2br(htmlspecialchars($patient['other_conditions_to_know'])) : '<span class="text-muted">None reported.</span>'; ?></span>
              </div>
          </div>
      </div>
  </div>

  <div class="tab-pane fade <?php if($active_tab == 'findings') echo 'show active'; ?>" id="findings" role="tabpanel" aria-labelledby="findings-tab">
    <!-- ... Clinical Findings Content... -->
      <div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Clinical Findings</h4><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFindingModal"><i class="fas fa-plus me-2"></i>Add New Finding</button></div>
      <div class="table-responsive">
          <table id="findingsTable" class="table table-striped table-hover" style="width:100%">
              <thead class="table-light"><tr><th>Date</th><th>Findings</th><th>Diagnosis</th><th>Proposed Treatment</th><th>Remarks</th><th>Dentist</th><th class="text-center">Actions</th></tr></thead>
              <tbody>
                  <?php if ($findings->num_rows > 0): $findings->data_seek(0); while($row = $findings->fetch_assoc()): ?>
                  <tr>
                      <td><?php echo date('M d, Y', strtotime($row['finding_date'])); ?></td>
                      <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($row['clinical_findings']); ?></td>
                      <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                      <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($row['proposed_treatment'] ?: '-'); ?></td>
                      <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($row['remarks'] ?: '-'); ?></td>
                      <td><?php echo htmlspecialchars($row['dentist_name']); ?></td>
                      <td class="text-center">
                          <button class="btn btn-warning btn-sm editFindingBtn" data-id="<?php echo $row['finding_id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                          <button class="btn btn-danger btn-sm deleteRecordBtn" data-id="<?php echo $row['finding_id']; ?>" data-type="Finding" data-url="finding_delete_process.php" title="Delete"><i class="fas fa-trash"></i></button>
                      </td>
                  </tr>
                  <?php endwhile; endif; ?>
              </tbody>
          </table>
      </div>
  </div>

  <div class="tab-pane fade <?php if($active_tab == 'treatments') echo 'show active'; ?>" id="treatments" role="tabpanel" aria-labelledby="treatments-tab">
    <!-- ... Treatment Record Content ... -->
    <div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Treatment Record</h4><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTreatmentModal"><i class="fas fa-plus me-2"></i>Add New Treatment</button></div>
    <div class="table-responsive">
        <table id="treatmentsTable" class="table table-striped table-hover" style="width:100%">
          <thead class="table-light"><tr><th>Date</th><th>Procedure</th><th>Tooth #</th><th>Charged</th><th>Paid</th><th>Balance</th><th>Next Appt.</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
          <tbody>
            <?php if ($treatments->num_rows > 0): $treatments->data_seek(0); while($row = $treatments->fetch_assoc()): ?>
            <tr>
                <td><?php echo date('M d, Y', strtotime($row['procedure_date'])); ?></td>
                <td><?php echo htmlspecialchars($row['procedure_done']); ?></td>
                <td><?php echo htmlspecialchars($row['tooth_no'] ?: '-'); ?></td>
                <td><?php echo number_format($row['amount_charged'], 2); ?></td>
                <td><?php echo number_format($row['amount_paid'], 2); ?></td>
                <td class="fw-bold <?php echo ($row['balance'] > 0) ? 'text-danger' : 'text-success'; ?>"><?php echo number_format($row['balance'], 2); ?></td>
                <td><?php echo !empty($row['next_appt']) ? date('M d, Y', strtotime($row['next_appt'])) : '-'; ?></td>
                <td class="text-center"><?php if ($row['balance'] <= 0): ?><span class="badge bg-success">Paid</span><?php else: ?><span class="badge bg-warning text-dark">Balance</span><?php endif; ?></td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm editTreatmentBtn" data-id="<?php echo $row['record_id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-danger btn-sm deleteRecordBtn" data-id="<?php echo $row['record_id']; ?>" data-type="Treatment" data-url="treatment_delete_process.php" title="Delete"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
    </div>
  </div>
</div>


<!-- ADD FINDING MODAL --><div class="modal fade" id="addFindingModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Clinical Finding</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="finding_add_process.php" method="POST"><div class="modal-body"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><input type="hidden" name="dentist_id" value="<?php echo $_SESSION['user_id']; ?>"><div class="mb-3"><label class="form-label">Date</label><input type="date" name="finding_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div><div class="mb-3"><label class="form-label">Clinical Findings</label><textarea name="clinical_findings" class="form-control" rows="3" required></textarea></div><div class="mb-3"><label class="form-label">Diagnosis</label><textarea name="diagnosis" class="form-control" rows="2" required></textarea></div><div class="mb-3"><label class="form-label">Proposed Treatment</label><textarea name="proposed_treatment" class="form-control" rows="2"></textarea></div><div class="mb-3"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Finding</button></div></form></div></div></div>
<!-- ADD TREATMENT MODAL --><div class="modal fade" id="addTreatmentModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="treatment_add_process.php" method="POST"><div class="modal-body"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><input type="hidden" name="dentist_id" value="<?php echo $_SESSION['user_id']; ?>"><div class="row g-3"><div class="col-md-6"><label class="form-label">Procedure Date</label><input type="date" name="procedure_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div><div class="col-md-6"><label class="form-label">Tooth Number(s)</label><input type="text" name="tooth_no" class="form-control" placeholder="e.g., 18, 21, 35"></div><div class="col-12"><label class="form-label">Procedure Done</label><input type="text" name="procedure_done" class="form-control" required></div><div class="col-md-4"><label class="form-label">Amount Charged (PHP)</label><input type="number" step="0.01" name="amount_charged" id="add_amount_charged" class="form-control" required></div><div class="col-md-4"><label class="form-label">Amount Paid (PHP)</label><input type="number" step="0.01" name="amount_paid" id="add_amount_paid" class="form-control" required></div><div class="col-md-4"><label class="form-label">Balance</label><input type="number" step="0.01" name="balance" id="add_balance" class="form-control" readonly required></div><div class="col-md-6"><label class="form-label">Next Appointment Date</label><input type="date" name="next_appt" class="form-control"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Treatment</button></div></form></div></div></div>
<!-- EDIT FINDING MODAL --><div class="modal fade" id="editFindingModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Clinical Finding</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="finding_edit_process.php" method="POST"><div class="modal-body"><input type="hidden" name="finding_id" id="edit_finding_id"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><div class="mb-3"><label>Date</label><input type="date" name="finding_date" id="edit_finding_date" class="form-control" required></div><div class="mb-3"><label>Clinical Findings</label><textarea name="clinical_findings" id="edit_clinical_findings" class="form-control" rows="3" required></textarea></div><div class="mb-3"><label>Diagnosis</label><textarea name="diagnosis" id="edit_diagnosis" class="form-control" rows="2" required></textarea></div><div class="mb-3"><label>Proposed Treatment</label><textarea name="proposed_treatment" id="edit_proposed_treatment" class="form-control" rows="2"></textarea></div><div class="mb-3"><label>Remarks</label><textarea name="remarks" id="edit_finding_remarks" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>
<!-- EDIT TREATMENT MODAL --><div class="modal fade" id="editTreatmentModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="treatment_edit_process.php" method="POST"><div class="modal-body"><input type="hidden" name="record_id" id="edit_record_id"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><div class="row g-3"><div class="col-md-6"><label>Procedure Date</label><input type="date" name="procedure_date" id="edit_procedure_date" class="form-control" required></div><div class="col-md-6"><label>Tooth #</label><input type="text" name="tooth_no" id="edit_tooth_no" class="form-control"></div><div class="col-12"><label>Procedure Done</label><input type="text" name="procedure_done" id="edit_procedure_done" class="form-control" required></div><div class="col-md-4"><label>Amount Charged</label><input type="number" step="0.01" name="amount_charged" id="edit_amount_charged" class="form-control" required></div><div class="col-md-4"><label>Amount Paid</label><input type="number" step="0.01" name="amount_paid" id="edit_amount_paid" class="form-control" required></div><div class="col-md-4"><label>Balance</label><input type="number" step="0.01" name="balance" id="edit_balance" class="form-control" readonly required></div><div class="col-md-6"><label>Next Appointment</label><input type="date" name="next_appt" id="edit_next_appt" class="form-control"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>
<!-- GENERIC DELETE RECORD MODAL --><div class="modal fade" id="deleteRecordModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Are you sure you want to delete this <strong id="recordTypeToDelete"></strong>? This action cannot be undone.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><a id="confirmRecordDeleteLink" href="#" class="btn btn-danger">Yes, Delete</a></div></div></div></div>


<?php require 'includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#findingsTable, #treatmentsTable').DataTable({"order": [[0, "desc"]]});
    function calculateBalance(chargedSelector, paidSelector, balanceSelector) { var charged = parseFloat($(chargedSelector).val()) || 0; var paid = parseFloat($(paidSelector).val()) || 0; $(balanceSelector).val((charged - paid).toFixed(2)); }
    $('#add_amount_charged, #add_amount_paid').on('input', function() { calculateBalance('#add_amount_charged', '#add_amount_paid', '#add_balance'); });
    $('#edit_amount_charged, #edit_amount_paid').on('input', function() { calculateBalance('#edit_amount_charged', '#edit_amount_paid', '#edit_balance'); });
    $(document).on('click', '.editFindingBtn', function() { var findId = $(this).data('id'); $.getJSON('ajax_get_finding.php', { id: findId }, function(data) { if(data) { $('#edit_finding_id').val(data.finding_id); $('#edit_finding_date').val(data.finding_date); $('#edit_clinical_findings').val(data.clinical_findings); $('#edit_diagnosis').val(data.diagnosis); $('#edit_proposed_treatment').val(data.proposed_treatment); $('#edit_finding_remarks').val(data.remarks); new bootstrap.Modal(document.getElementById('editFindingModal')).show(); } }); });
    $(document).on('click', '.editTreatmentBtn', function() { var recId = $(this).data('id'); $.getJSON('ajax_get_treatment.php', { id: recId }, function(data) { if(data) { $('#edit_record_id').val(data.record_id); $('#edit_procedure_date').val(data.procedure_date); $('#edit_procedure_done').val(data.procedure_done); $('#edit_tooth_no').val(data.tooth_no); $('#edit_amount_charged').val(parseFloat(data.amount_charged).toFixed(2)); $('#edit_amount_paid').val(parseFloat(data.amount_paid).toFixed(2)); $('#edit_balance').val(parseFloat(data.balance).toFixed(2)); $('#edit_next_appt').val(data.next_appt); new bootstrap.Modal(document.getElementById('editTreatmentModal')).show(); } }); });
    $(document).on('click', '.deleteRecordBtn', function() { var recordId = $(this).data('id'); var recordType = $(this).data('type'); var deleteUrl = $(this).data('url') + '?id=' + recordId + '&patient_id=' + <?php echo $patient_id; ?>; $('#recordTypeToDelete').text(recordType); $('#confirmRecordDeleteLink').attr('href', deleteUrl); new bootstrap.Modal(document.getElementById('deleteRecordModal')).show(); });
});
</script>

<?php $conn->close(); ?>