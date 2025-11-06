<?php
// FILE: admin/patient_view.php (FULLY INTEGRATED AND CORRECTED)

require 'includes/header.php';

$return_branch = isset($_GET['branch']) ? htmlspecialchars($_GET['branch']) : 'All';

// 1. VALIDATE AND SANITIZE INPUT
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id === 0) {
    $_SESSION['message'] = "Invalid patient ID provided.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit();
}

// GET THE ACTIVE TAB FROM URL OR SET A DEFAULT
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

// Fetch Lookup Data for Modals
$lookup_findings_result = $conn->query("SELECT id, name FROM lookup_findings WHERE is_active = 1 ORDER BY name ASC");
$lookup_treatments_result = $conn->query("SELECT id, name FROM lookup_treatments WHERE is_active = 1 ORDER BY name ASC");
$lookup_procedures_result = $conn->query("SELECT name FROM lookup_procedures WHERE is_active = 1 ORDER BY name ASC");


// HELPER FUNCTION
function display_yes_no($value, $details = null) {
    $details_html = !empty($details) ? '<em class="ms-2 text-muted">(' . htmlspecialchars($details) . ')</em>' : '';
    return ($value == 1 ? '<span class="fw-bold text-success">Yes</span>' : '<span class="text-secondary">No</span>') . $details_html;
}
?>

<!-- Custom Styles for Patient View Page -->
<style>
    .patient-header-card { background-color: #ffffff; padding: 1.5rem 2rem; margin-bottom: 2rem; }
    .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
    .btn-primary:hover { background-color: #00695C; border-color: #00695C; }
    .alert-custom { border-radius: 0.75rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: none; padding: 1rem 1.5rem; }
    .nav-tabs { border-bottom: 0; margin-bottom: -1px; }
    .nav-tabs .nav-link { border: none; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; font-weight: 600; color: #6c757d; background-color: transparent; padding: 0.75rem 1.5rem; }
    .nav-tabs .nav-link.active { color: var(--primary-color); background-color: #ffffff; border-bottom: 3px solid var(--primary-color); }
    .tab-content.card { border-top-left-radius: 0; }
    .detail-item { margin-bottom: 1.25rem; }
    .detail-label { color: #6c757d; font-size: 0.85rem; display: block; margin-bottom: 0.1rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #343a40; }
    .info-sub-card { border: 1px solid #e9ecef; border-radius: 0.75rem; margin-bottom: 1.5rem; }
    .info-sub-card .card-header { background-color: #f8f9fa; font-weight: 600; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; }
    .info-sub-card .card-header i { color: var(--primary-color); margin-right: 0.75rem; }
    .critical-alert-card, .warning-alert-card { border-radius: 1rem; box-shadow: var(--card-shadow); margin-bottom: 1.5rem; }
    .critical-alert-card { border-left: 5px solid #dc3545; }
    .warning-alert-card { border-left: 5px solid #ffc107; }
    .finding-card { border-left: 4px solid var(--primary-color); }
    .finding-header { background-color: #f8f9fa; padding: 0.75rem 1.25rem; border-bottom: 1px solid #dee2e6; }
    .modal-content { border-radius: 1rem; border: none; }
    .modal-header { background-color: #f4f7f6; }
    .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25); }
    .attachment-thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; cursor: pointer; border: 1px solid #dee2e6; }
    .attachment-video-icon { width: 80px; height: 80px; background-color: #343a40; color: white; border-radius: 5px; display: flex; align-items: center; justify-content: center; }
    .attachment-thumbnail-edit { position: relative; width: 80px; height: 80px; border-radius: .375rem; overflow: hidden; border: 1px solid #dee2e6; }
    .attachment-thumbnail-edit img, .attachment-thumbnail-edit .file-icon { width: 100%; height: 100%; object-fit: cover; }
    .attachment-thumbnail-edit .file-icon { display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; font-size: 2rem; color: #6c757d; }
    .delete-attachment-btn-overlay { position: absolute; top: -5px; right: -5px; width: 24px; height: 24px; border-radius: 50%; background-color: #dc3545; color: white; border: 2px solid white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; cursor: pointer; opacity: 0; transition: opacity 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .attachment-thumbnail-edit:hover .delete-attachment-btn-overlay { opacity: 1; }
    .lightbox-close-button { z-index: 1060; }
</style>

<!-- PAGE HEADER -->
<div class="card patient-header-card">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2 mb-1"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h1>
            <p class="text-muted mb-0"> Patient ID: <?php echo $patient['patient_id']; ?> • Branch: <?php echo htmlspecialchars($patient['branch']); ?> • Registered: <?php echo date('F j, Y', strtotime($patient['registration_date'])); ?> </p>
        </div>
        <div>
            <a href="patient_edit.php?id=<?php echo $patient['patient_id']; ?>" id="editPatientBtn" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Edit Full Record</a>
            <a href="patients.php?branch=<?php echo urlencode($return_branch); ?>" id="backToListBtn" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
        </div>
    </div>
</div>

<!-- Session Message -->
<?php
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message_type'] ?? 'success';
    $icon = ($message_type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';
    echo '<div class="alert alert-' . $message_type . ' d-flex align-items-center alert-custom alert-dismissible fade show" role="alert"><i class="fas ' . $icon . ' me-3 fa-lg"></i><div>' . $_SESSION['message'] . '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']); unset($_SESSION['message_type']);
}
?>

<!-- TABS NAVIGATION -->
<ul class="nav nav-tabs" id="patientTab" role="tablist">
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'profile') echo 'active'; ?>" data-bs-toggle="tab" data-bs-target="#profile" type="button"><i class="fas fa-user-circle me-2"></i>Patient Profile</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'medical-history') echo 'active'; ?>" data-bs-toggle="tab" data-bs-target="#medical-history" type="button"><i class="fas fa-heartbeat me-2"></i>Medical History</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'findings') echo 'active'; ?>" data-bs-toggle="tab" data-bs-target="#findings" type="button"><i class="fas fa-search-plus me-2"></i>Clinical Findings</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'treatments') echo 'active'; ?>" data-bs-toggle="tab" data-bs-target="#treatments" type="button"><i class="fas fa-notes-medical me-2"></i>Treatment Record</button></li>
</ul>

<!-- TABS CONTENT -->
<div class="tab-content card" id="patientTabContent">
  
  <!-- PROFILE TAB -->
  <div class="tab-pane fade p-4 <?php if($active_tab == 'profile') echo 'show active'; ?>" id="profile" role="tabpanel">
    <div class="row">
        <div class="col-lg-8">
            <div class="card info-sub-card">
                <div class="card-header"><i class="fas fa-user-circle"></i>Personal Information</div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4 detail-item"><span class="detail-label">Last Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['last_name']); ?></span></div>
                        <div class="col-md-4 detail-item"><span class="detail-label">First Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['first_name']); ?></span></div>
                        <div class="col-md-4 detail-item"><span class="detail-label">Middle Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['middle_name'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-4 detail-item"><span class="detail-label">Nickname</span><span class="detail-value"><?php echo htmlspecialchars($patient['nickname'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-8 detail-item"><span class="detail-label">Birthdate & Age</span><span class="detail-value"><?php echo date('F j, Y', strtotime($patient['birthdate'])); ?> (<?php echo $patient['age']; ?> years old)</span></div>
                        <div class="col-md-4 detail-item"><span class="detail-label">Gender</span><span class="detail-value"><?php echo $patient['gender'] == 'M' ? 'Male' : 'Female'; ?></span></div>
                        <div class="col-md-4 detail-item"><span class="detail-label">Civil Status</span><span class="detail-value"><?php echo htmlspecialchars($patient['civil_status']); ?></span></div>
                        <div class="col-md-4 detail-item"><span class="detail-label">Nationality</span><span class="detail-value"><?php echo htmlspecialchars($patient['nationality'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-4 detail-item"><span class="detail-label">Religion</span><span class="detail-value"><?php echo htmlspecialchars($patient['religion'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-12 detail-item"><span class="detail-label">Occupation</span><span class="detail-value"><?php echo htmlspecialchars($patient['occupation'] ?: 'N/A'); ?></span></div>
                    </div>
                </div>
            </div>
            <div class="card info-sub-card">
                <div class="card-header"><i class="fas fa-address-book"></i>Contact & Address</div>
                <div class="card-body p-4">
                     <div class="row">
                        <div class="col-md-6 detail-item"><span class="detail-label">Mobile Number</span><span class="detail-value"><?php echo htmlspecialchars($patient['mobile_no']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Email Address</span><span class="detail-value"><?php echo htmlspecialchars($patient['email'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-12 detail-item mb-0"><span class="detail-label">Address</span><span class="detail-value"><?php echo htmlspecialchars($patient['address']); ?></span></div>
                    </div>
                </div>
            </div>
             <div class="card info-sub-card">
                <div class="card-header"><i class="fas fa-users"></i>Guardian Information (For Minors)</div>
                <div class="card-body p-4">
                     <div class="row">
                        <div class="col-md-6 detail-item"><span class="detail-label">Parent/Guardian's Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['parent_guardian_name'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Parent/Guardian's Occupation</span><span class="detail-value"><?php echo htmlspecialchars($patient['parent_guardian_occupation'] ?: 'N/A'); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card info-sub-card">
                <div class="card-header"><i class="fas fa-clinic-medical"></i>Vitals & Provider</div>
                <div class="card-body p-4">
                    <h6 class="mb-3">Latest Vitals</h6>
                    <div class="row">
                        <div class="col-6 detail-item"><span class="detail-label">Blood Pressure</span><span class="detail-value"><?php echo htmlspecialchars($patient['blood_pressure'] ?: 'N/A'); ?></span></div>
                        <div class="col-6 detail-item"><span class="detail-label">Resp. Rate</span><span class="detail-value"><?php echo htmlspecialchars($patient['respiratory_rate'] ?: 'N/A'); ?></span></div>
                        <div class="col-6 detail-item"><span class="detail-label">Pulse Rate</span><span class="detail-value"><?php echo htmlspecialchars($patient['pulse_rate'] ?: 'N/A'); ?></span></div>
                        <div class="col-6 detail-item"><span class="detail-label">Temperature</span><span class="detail-value"><?php echo htmlspecialchars($patient['temperature'] ?: 'N/A'); ?></span></div>
                    </div>
                    <hr>
                    <div class="detail-item"><span class="detail-label">Physician's Name</span><span class="detail-value"><?php echo htmlspecialchars($patient['physician_name'] ?: 'N/A'); ?></span></div>
                    <div class="detail-item"><span class="detail-label">Physician's Phone</span><span class="detail-value"><?php echo htmlspecialchars($patient['physician_phone'] ?: 'N/A'); ?></span></div>
                    <div class="detail-item"><span class="detail-label">Physician's Address</span><span class="detail-value"><?php echo htmlspecialchars($patient['physician_address'] ?: 'N/A'); ?></span></div>
                    <div class="detail-item mb-0"><span class="detail-label">Last Physical Exam</span><span class="detail-value"><?php echo !empty($patient['last_physical_exam']) ? date('F j, Y', strtotime($patient['last_physical_exam'])) : 'N/A'; ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card info-sub-card">
                <div class="card-header"><i class="fas fa-tooth"></i>Initial Dental Assessment</div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 detail-item"><span class="detail-label">Chief Complaint</span><span class="detail-value"><?php echo nl2br(htmlspecialchars($patient['chief_complaint'])); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">History of Present Illness</span><span class="detail-value"><?php echo nl2br(htmlspecialchars($patient['history_of_present_illness'] ?: 'N/A')); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Previous Dentist</span><span class="detail-value"><?php echo htmlspecialchars($patient['previous_dentist'] ?: 'N/A'); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Last Dental Visit</span><span class="detail-value"><?php echo !empty($patient['last_dental_visit']) ? date('F j, Y', strtotime($patient['last_dental_visit'])) : 'N/A'; ?></span></div>
                        <div class="col-md-12 detail-item">
                            <span class="detail-label">Previous Procedures Done</span>
                            <div class="detail-value">
                                <?php
                                    $procedures = [];
                                    if($patient['procedures_done_perio']) $procedures[] = 'Perio';
                                    if($patient['procedures_done_resto']) $procedures[] = 'Resto';
                                    if($patient['procedures_done_os']) $procedures[] = 'O.S.';
                                    if($patient['procedures_done_prostho']) $procedures[] = 'Prostho';
                                    if($patient['procedures_done_endo']) $procedures[] = 'Endo';
                                    if($patient['procedures_done_ortho']) $procedures[] = 'Ortho';
                                    if(!empty($patient['procedures_done_others_specify'])) $procedures[] = htmlspecialchars($patient['procedures_done_others_specify']);
                                    echo !empty($procedures) ? implode(', ', $procedures) : 'N/A';
                                ?>
                            </div>
                        </div>
                        <div class="col-md-12 detail-item mb-0"><span class="detail-label">Complications from Previous Treatments</span><span class="detail-value"><?php echo htmlspecialchars($patient['complications'] ?: 'N/A'); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- MEDICAL HISTORY TAB -->
  <div class="tab-pane fade p-4 <?php if($active_tab == 'medical-history') echo 'show active'; ?>" id="medical-history" role="tabpanel">
    <div class="row">
        <div class="col-lg-6">
            <div class="card critical-alert-card">
                <div class="card-body p-4">
                    <h4 class="card-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Reported Allergies</h4><hr>
                    <?php 
                        $allergies = [ 'allergic_anesthetics' => 'Anesthetics (Lidocaine)', 'allergic_penicillin' => 'Penicillin/Antibiotics', 'allergic_aspirin' => 'Aspirin', 'allergic_latex' => 'Latex' ];
                        $yes_allergies = [];
                        foreach ($allergies as $field => $label) { if (!empty($patient[$field])) { $yes_allergies[] = $label; } }
                        
                        if (!empty($yes_allergies)) { echo '<ul class="mb-0">'; foreach ($yes_allergies as $allergy) { echo '<li>' . htmlspecialchars($allergy) . '</li>'; } echo '</ul>'; } 
                        else { echo '<p class="mb-0 text-muted">No major allergies reported.</p>'; }

                        if (!empty($patient['allergic_others_details'])) { echo "<p class='mb-1 mt-2'><strong>Other: </strong>" . htmlspecialchars($patient['allergic_others_details']) . "</p>"; }
                        if (!empty($patient['allergy_reaction_details'])) { echo "<p class='mb-0'><strong>Reaction: </strong>" . htmlspecialchars($patient['allergy_reaction_details']) . "</p>"; }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
             <div class="card warning-alert-card">
                <div class="card-body p-4">
                    <h4 class="card-title text-warning"><i class="fas fa-first-aid me-2"></i>Reported Medical Conditions</h4><hr>
                    <?php 
                        $conditions = [ 'has_high_blood_pressure' => 'High Blood Pressure', 'has_low_blood_pressure' => 'Low Blood Pressure', 'has_epilepsy_convulsions' => 'Epilepsy/Convulsions', 'has_aids_hiv' => 'AIDS/HIV', 'has_stomach_troubles_ulcer' => 'Stomach Troubles/Ulcer', 'has_fainting_seizure' => 'Fainting/Seizure', 'has_rapid_weight_loss' => 'Rapid Weight Loss', 'had_radiation_therapy' => 'Radiation Therapy', 'has_joint_replacement_implant' => 'Joint Replacement/Implant', 'had_heart_surgery' => 'Heart Surgery', 'had_heart_attack' => 'Heart Attack', 'has_heart_disease' => 'Heart Disease', 'has_heart_murmur' => 'Heart Murmur', 'has_hepatitis_jaundice' => 'Hepatitis/Jaundice', 'has_tuberculosis' => 'Tuberculosis', 'has_swollen_ankles' => 'Swollen Ankles', 'has_kidney_disease' => 'Kidney Disease', 'has_diabetes' => 'Diabetes', 'has_bleeding_blood_disease' => 'Bleeding/Blood Disease', 'has_arthritis_rheumatism' => 'Arthritis/Rheumatism', 'has_cancer_tumor' => 'Cancer/Tumor', 'has_anemia' => 'Anemia', 'has_angina' => 'Angina', 'has_asthma' => 'Asthma', 'has_thyroid_problem' => 'Thyroid Problem', 'has_rheumatic_fever_disease' => 'Rheumatic Fever', 'has_hay_fever_allergies' => 'Hay Fever/Allergies', 'has_respiratory_problems' => 'Respiratory Problems', 'has_emphysema' => 'Emphysema', 'has_breathing_problems' => 'Breathing Problems', 'had_stroke' => 'Stroke', 'has_chest_pain' => 'Chest Pain' ];
                        $yes_conditions = [];
                        foreach ($conditions as $field => $label) { if (!empty($patient[$field])) { $yes_conditions[] = $label; } }
                        if (!empty($yes_conditions)) { echo '<ul class="mb-0" style="columns: 2;">'; foreach ($yes_conditions as $condition) { echo '<li>' . htmlspecialchars($condition) . '</li>'; } echo '</ul>'; } 
                        else { echo '<p class="mb-0 text-muted">No major conditions reported.</p>'; }
                        if (!empty($patient['other_diseases_details'])) { echo "<hr><p class='mb-0'><strong>Other Conditions: </strong>" . htmlspecialchars($patient['other_diseases_details']) . "</p>"; }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card info-sub-card">
                <div class="card-header"><i class="fas fa-file-medical-alt"></i>General Health Questionnaire</div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 detail-item"><span class="detail-label">In good health?</span><span class="detail-value"><?php echo display_yes_no($patient['are_you_in_good_health']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Under medical treatment?</span><span class="detail-value"><?php echo display_yes_no($patient['is_under_medical_treatment'], $patient['medical_treatment_details']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Had serious illness/operation?</span><span class="detail-value"><?php echo display_yes_no($patient['had_serious_illness_or_operation'], $patient['illness_operation_details']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Ever hospitalized?</span><span class="detail-value"><?php echo display_yes_no($patient['has_been_hospitalized'], $patient['hospitalization_details']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Taking medication?</span><span class="detail-value"><?php echo display_yes_no($patient['is_taking_medication'], $patient['medication_details']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">On a special diet / diet drugs?</span><span class="detail-value"><?php echo display_yes_no($patient['is_on_diet'], $patient['diet_details']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Uses tobacco?</span><span class="detail-value"><?php echo display_yes_no($patient['uses_tobacco'], $patient['tobacco_details']); ?></span></div>
                        <div class="col-md-6 detail-item"><span class="detail-label">Drinks alcoholic beverages?</span><span class="detail-value"><?php echo display_yes_no($patient['drinks_alcoholic_beverages'], $patient['alcohol_frequency']); ?></span></div>
                    </div>
                </div>
            </div>
            <div class="card info-sub-card">
                <div class="card-header"><i class="fas fa-notes-medical"></i>Other Conditions or Problems</div>
                 <div class="card-body p-4">
                    <div class="detail-item mb-0">
                        <span class="detail-label">Any other disease, condition, or problem not listed above that the dentist should know about?</span>
                        <span class="detail-value"><?php echo nl2br(htmlspecialchars($patient['other_conditions_to_know'] ?: 'N/A')); ?></span>
                    </div>
                 </div>
            </div>
        </div>
    </div>
  </div>

  <!-- CLINICAL FINDINGS TAB -->
  <div class="tab-pane fade p-4 <?php if($active_tab == 'findings') echo 'show active'; ?>" id="findings" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Clinical Findings History</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFindingModal"><i class="fas fa-plus me-2"></i>Add New Finding</button>
    </div>
    <?php if ($findings->num_rows > 0): $findings->data_seek(0); while($row = $findings->fetch_assoc()): ?>
        <div class="card finding-card mb-4">
            <div class="card-header finding-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-5 fw-bold text-dark"><?php echo date('F j, Y', strtotime($row['finding_date'])); ?></div>
                    <div class="small text-muted">Recorded by: <?php echo htmlspecialchars($row['dentist_name']); ?></div>
                </div>
                <div>
                    <button class="btn btn-outline-warning btn-sm editFindingBtn" data-id="<?php echo $row['finding_id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-outline-danger btn-sm deleteRecordBtn" data-id="<?php echo $row['finding_id']; ?>" data-type="Finding" data-url="finding_delete_process.php"><i class="fas fa-trash"></i> Delete</button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-8">
                         <div class="detail-item">
                            <span class="detail-label">Clinical Findings</span>
                            <div class="detail-value">
                                <?php
                                $links_stmt = $conn->prepare("SELECT lf.name FROM clinical_finding_links cfl JOIN lookup_findings lf ON cfl.lookup_finding_id = lf.id WHERE cfl.finding_id = ?");
                                $links_stmt->bind_param("i", $row['finding_id']);
                                $links_stmt->execute();
                                $links_result = $links_stmt->get_result();
                                if ($links_result->num_rows > 0) {
                                    while($link_row = $links_result->fetch_assoc()) { echo '<span class="badge bg-primary me-1 mb-1">' . htmlspecialchars($link_row['name']) . '</span>'; }
                                }
                                if(!empty($row['custom_findings_notes'])) { echo '<div class="mt-2 text-muted small" style="white-space: pre-wrap;">' . htmlspecialchars($row['custom_findings_notes']) . '</div>'; }
                                elseif ($links_result->num_rows == 0 && empty($row['custom_findings_notes'])) { echo '<span class="text-muted fst-italic">No details provided.</span>'; }
                                ?>
                            </div>
                         </div>
                         <div class="detail-item">
                            <span class="detail-label">Proposed Treatments</span>
                            <div class="detail-value">
                                <?php
                                $links_stmt = $conn->prepare("SELECT lt.name FROM clinical_treatment_links ctl JOIN lookup_treatments lt ON ctl.lookup_treatment_id = lt.id WHERE ctl.finding_id = ?");
                                $links_stmt->bind_param("i", $row['finding_id']);
                                $links_stmt->execute();
                                $links_result = $links_stmt->get_result();
                                if ($links_result->num_rows > 0) {
                                    while($link_row = $links_result->fetch_assoc()) { echo '<span class="badge bg-success me-1 mb-1">' . htmlspecialchars($link_row['name']) . '</span>'; }
                                }
                                if(!empty($row['custom_treatment_notes'])) { echo '<div class="mt-2 text-muted small" style="white-space: pre-wrap;">' . htmlspecialchars($row['custom_treatment_notes']) . '</div>'; }
                                elseif ($links_result->num_rows == 0 && empty($row['custom_treatment_notes'])) { echo '<span class="text-muted fst-italic">No details provided.</span>'; }
                                ?>
                            </div>
                         </div>
                         <div class="detail-item mb-0">
                            <span class="detail-label">Diagnosis</span>
                            <div class="detail-value"><?php echo htmlspecialchars($row['diagnosis'] ?: 'N/A'); ?></div>
                         </div>
                    </div>
                    <div class="col-md-4 border-start">
                        <?php
                        $sql_attachments = "SELECT attachment_id, file_path, file_type, file_name FROM finding_attachments WHERE finding_id = ? AND is_deleted = 0";
                        $stmt_attachments = $conn->prepare($sql_attachments);
                        $stmt_attachments->bind_param("i", $row['finding_id']);
                        $stmt_attachments->execute();
                        $attachments_result = $stmt_attachments->get_result();
                        ?>
                        <h6 class="text-muted small text-uppercase">Attachments (<?php echo $attachments_result->num_rows; ?>)</h6>
                        <div class="d-flex flex-wrap gap-2">
                        <?php
                        if ($attachments_result->num_rows > 0) {
                            while($attach = $attachments_result->fetch_assoc()) {
                                $file_url = '../' . htmlspecialchars($attach['file_path']);
                                if (strpos($attach['file_type'], 'image/') === 0) {
                                    echo '<a href="' . $file_url . '" data-bs-toggle="lightbox" data-gallery="finding-'.$row['finding_id'].'"><img src="' . $file_url . '" class="attachment-thumbnail" title="'.htmlspecialchars($attach['file_name']).'"></a>';
                                } else {
                                    echo '<a href="' . $file_url . '" target="_blank" class="text-decoration-none" title="'.htmlspecialchars($attach['file_name']).'"><div class="attachment-video-icon"><i class="fas fa-file-alt fa-2x"></i></div></a>';
                                }
                            }
                        } else {
                            echo '<span class="text-muted small fst-italic">No attachments.</span>';
                        }
                        $stmt_attachments->close();
                        ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center p-5 bg-light rounded">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <p class="mb-0 text-muted">No clinical findings have been recorded for this patient yet.</p>
        </div>
    <?php endif; ?>
  </div>

  <!-- TREATMENTS TAB -->
  <div class="tab-pane fade p-4 <?php if($active_tab == 'treatments') echo 'show active'; ?>" id="treatments" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Treatment Record</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTreatmentModal"><i class="fas fa-plus me-2"></i>Add New Treatment</button>
    </div>
    <div class="table-responsive">
        <table id="treatmentsTable" class="table table-hover" style="width:100%">
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
                <td><?php echo !empty($row['next_appt']) && $row['next_appt'] != '0000-00-00' ? date('M d, Y', strtotime($row['next_appt'])) : '-'; ?></td>
                <td class="text-center"><?php if ($row['balance'] <= 0): ?><span class="badge bg-success-subtle text-success-emphasis rounded-pill">Paid</span><?php else: ?><span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Balance</span><?php endif; ?></td>
                <td class="text-center">
                    <button class="btn btn-outline-warning btn-sm editTreatmentBtn" data-id="<?php echo $row['record_id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-sm deleteRecordBtn" data-id="<?php echo $row['record_id']; ?>" data-type="Treatment" data-url="treatment_delete_process.php" title="Delete"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
    </div>
  </div>
</div>

<!-- ALL MODALS -->
<div class="modal fade" id="addFindingModal" tabindex="-1"> <div class="modal-dialog modal-xl modal-dialog-centered"> <div class="modal-content"> <form action="finding_add_process.php" method="POST" enctype="multipart/form-data"> <div class="modal-header"> <h5 class="modal-title"><i class="fas fa-search-plus me-2"></i>Add New Clinical Finding</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body p-4"> <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"> <div class="row g-4"> <div class="col-md-7"> <div class="mb-3"> <label class="form-label">Clinical Findings (Select one or more)</label> <select class="form-select multi-select-add" name="lookup_findings[]" multiple="multiple" style="width: 100%;"> <?php while($item = $lookup_findings_result->fetch_assoc()): ?><option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option><?php endwhile; $lookup_findings_result->data_seek(0); ?> </select> </div> <div class="mb-3"><label class="form-label">Additional Findings Notes</label><textarea name="custom_findings_notes" class="form-control" rows="2"></textarea></div> <div class="mb-3"><label class="form-label">Diagnosis <span class="text-danger">*</span></label><textarea name="diagnosis" class="form-control" rows="2" required></textarea></div> <hr> <div class="mb-3"> <label class="form-label">Proposed Treatments (Select one or more)</label> <select class="form-select multi-select-add" name="lookup_treatments[]" multiple="multiple" style="width: 100%;"> <?php while($item = $lookup_treatments_result->fetch_assoc()): ?><option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option><?php endwhile; $lookup_treatments_result->data_seek(0); ?> </select> </div> <div><label class="form-label">Additional Treatment Notes</label><textarea name="custom_treatment_notes" class="form-control" rows="2"></textarea></div> </div> <div class="col-md-5 bg-light p-4 rounded"> <div class="mb-3"><label class="form-label">Date</label><input type="text" name="finding_date" class="form-control date-picker-present-future" value="<?php echo date('Y-m-d'); ?>" placeholder="YYYY-MM-DD" required></div> <div class="mb-3"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3"></textarea></div> <div class="mt-4"> <label for="formFileMultiple" class="form-label fw-bold"><i class="fas fa-paperclip me-2"></i>Add Attachments</label> <input class="form-control" type="file" id="formFileMultiple" name="attachments[]" multiple accept="image/*,video/*"> </div> </div> </div> </div> <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Finding</button></div> </form> </div> </div> </div>
<div class="modal fade" id="editFindingModal" tabindex="-1"> <div class="modal-dialog modal-xl modal-dialog-centered"> <div class="modal-content"> <form id="editFindingForm" action="finding_edit_process.php" method="POST" enctype="multipart/form-data"> <div class="modal-header"> <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Clinical Finding</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body p-4"> <input type="hidden" name="finding_id" id="edit_finding_id"> <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"> <div class="row g-4"> <div class="col-md-7"> <div class="mb-3"><label class="form-label">Clinical Findings</label><select class="form-select multi-select-edit" id="edit_lookup_findings" name="lookup_findings[]" multiple="multiple" style="width: 100%;"><?php while($item = $lookup_findings_result->fetch_assoc()): ?><option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option><?php endwhile; $lookup_findings_result->data_seek(0); ?></select></div> <div class="mb-3"><label>Additional Findings Notes</label><textarea name="custom_findings_notes" id="edit_custom_findings_notes" class="form-control" rows="2"></textarea></div> <div class="mb-3"><label>Diagnosis <span class="text-danger">*</span></label><textarea name="diagnosis" id="edit_diagnosis" class="form-control" rows="2" required></textarea></div> <hr> <div class="mb-3"><label class="form-label">Proposed Treatments</label><select class="form-select multi-select-edit" id="edit_lookup_treatments" name="lookup_treatments[]" multiple="multiple" style="width: 100%;"><?php while($item = $lookup_treatments_result->fetch_assoc()): ?><option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option><?php endwhile; $lookup_treatments_result->data_seek(0); ?></select></div> <div><label>Additional Treatment Notes</label><textarea name="custom_treatment_notes" id="edit_custom_treatment_notes" class="form-control" rows="2"></textarea></div> </div> <div class="col-md-5 bg-light p-4 rounded"> <div class="mb-3"><label>Date</label><input type="text" name="finding_date" id="edit_finding_date" class="form-control date-picker-present-future" placeholder="YYYY-MM-DD" required></div> <div class="mb-3"><label>Remarks</label><textarea name="remarks" id="edit_finding_remarks" class="form-control" rows="3"></textarea></div> <div class="mt-4"> <h6 class="fw-bold"><i class="fas fa-paperclip me-2"></i>Attachments</h6> <div id="existing-attachments-container" class="mb-3 d-flex flex-wrap gap-3"></div> <label class="form-label small">Add More Attachments</label> <input class="form-control" type="file" name="attachments[]" multiple accept="image/*,video/*"> </div> </div> </div> </div> <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div> </form> </div> </div> </div>
<div class="modal fade" id="addTreatmentModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-notes-medical me-2"></i>Add New Treatment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="treatment_add_process.php" method="POST"><div class="modal-body p-4"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><input type="hidden" name="dentist_id" value="<?php echo $_SESSION['user_id']; ?>"><div class="row g-3"><div class="col-md-6"><label class="form-label">Procedure Date</label><input type="text" name="procedure_date" class="form-control date-picker-present-future" value="<?php echo date('Y-m-d'); ?>" placeholder="YYYY-MM-DD" required></div><div class="col-md-6"><label class="form-label">Tooth Number(s)</label><input type="text" name="tooth_no" class="form-control" placeholder="e.g., 18, 21, 35"></div><div class="col-12"><label class="form-label">Procedure Done</label><select name="procedure_done" class="form-select" required><option value="" selected disabled>Select a procedure...</option><?php while($proc = $lookup_procedures_result->fetch_assoc()): ?><option value="<?php echo htmlspecialchars($proc['name']); ?>"><?php echo htmlspecialchars($proc['name']); ?></option><?php endwhile; $lookup_procedures_result->data_seek(0); ?><option value="custom">-- Other (Specify Below) --</option></select></div><div class="col-12" id="custom_procedure_wrapper" style="display: none;"><label class="form-label">Specify Custom Procedure</label><input type="text" name="procedure_done_custom" class="form-control"></div><div class="col-md-4"><label class="form-label">Amount Charged (₱)</label><input type="number" step="0.01" name="amount_charged" id="add_amount_charged" class="form-control" required></div><div class="col-md-4"><label class="form-label">Amount Paid (₱)</label><input type="number" step="0.01" name="amount_paid" id="add_amount_paid" class="form-control" required></div><div class="col-md-4"><label class="form-label">Balance</label><input type="number" step="0.01" name="balance" id="add_balance" class="form-control" readonly required></div><div class="col-md-6"><label class="form-label">Next Appointment Date</label><input type="text" name="next_appt" class="form-control date-picker-present-future" placeholder="YYYY-MM-DD"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Treatment</button></div></form></div></div></div>
<div class="modal fade" id="editTreatmentModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="treatment_edit_process.php" method="POST"><div class="modal-body p-4"><input type="hidden" name="record_id" id="edit_record_id"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><div class="row g-3"><div class="col-md-6"><label>Procedure Date</label><input type="text" name="procedure_date" id="edit_procedure_date" class="form-control date-picker-present-future" placeholder="YYYY-MM-DD" required></div><div class="col-md-6"><label>Tooth #</label><input type="text" name="tooth_no" id="edit_tooth_no" class="form-control"></div><div class="col-12"><label class="form-label">Procedure Done</label><select name="procedure_done" id="edit_procedure_done" class="form-select" required><option value="" disabled>Select...</option><?php while($proc = $lookup_procedures_result->fetch_assoc()): ?><option value="<?php echo htmlspecialchars($proc['name']); ?>"><?php echo htmlspecialchars($proc['name']); ?></option><?php endwhile; $lookup_procedures_result->data_seek(0); ?><option value="custom">-- Other (Specify) --</option></select></div><div class="col-12" id="edit_custom_procedure_wrapper" style="display: none;"><label class="form-label">Specify Custom Procedure</label><input type="text" name="procedure_done_custom" id="edit_procedure_done_custom" class="form-control"></div><div class="col-md-4"><label>Amount Charged (₱)</label><input type="number" step="0.01" name="amount_charged" id="edit_amount_charged" class="form-control" required></div><div class="col-md-4"><label>Amount Paid (₱)</label><input type="number" step="0.01" name="amount_paid" id="edit_amount_paid" class="form-control" required></div><div class="col-md-4"><label>Balance</label><input type="number" step="0.01" name="balance" id="edit_balance" class="form-control" readonly required></div><div class="col-md-6"><label>Next Appointment</label><input type="text" name="next_appt" id="edit_next_appt" class="form-control date-picker-present-future" placeholder="YYYY-MM-DD"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>
<div class="modal fade" id="deleteRecordModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Are you sure you want to delete this <strong id="recordTypeToDelete"></strong>? This action cannot be undone.</p></div><div class="modal-footer border-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><a id="confirmRecordDeleteLink" href="#" class="btn btn-danger">Yes, Delete</a></div></div></div></div>
<div class="modal fade" id="deleteAttachmentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>This attachment will be marked for deletion and permanently removed after you save your changes to the finding.</p><p>Are you sure you want to proceed?</p></div><div class="modal-footer border-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" id="confirmAttachmentDeleteBtn">Yes, Proceed</button></div></div></div></div>

<?php require 'includes/footer.php'; ?>

<!-- Lightbox for viewing image attachments -->
<script src="https://cdn.jsdelivr.net/npm/bs5-lightbox@1.8.3/dist/index.bundle.min.js"></script>

<!-- JAVASCRIPT LOGIC -->
<script>
$(document).ready(function() {
    var patientId = <?php echo $patient_id; ?>;
    var currentTab = '<?php echo $active_tab; ?>';

    // NEW: Initialize flatpickr for modal date pickers that should only allow present and future dates
    flatpickr(".date-picker-present-future", {
        dateFormat: "Y-m-d",
        allowInput: true,
        minDate: "today" // This is the key change for request #4
    });

    // --- TAB PERSISTENCE LOGIC ---
    function updateUrlParameter(url, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = url.indexOf('?') !== -1 ? "&" : "?";
        if (url.match(re)) { return url.replace(re, '$1' + key + "=" + value + '$2'); } 
        else { return url + separator + key + "=" + value; }
    }

    function updatePersistentElements(activeTabId) {
        $('form').each(function() {
            var hiddenInput = $(this).find('input[name="tab_redirect"]');
            if (hiddenInput.length) { hiddenInput.val(activeTabId); } 
            else { $(this).append('<input type="hidden" name="tab_redirect" value="' + activeTabId + '">'); }
        });
        $('a#editPatientBtn, a#backToListBtn, a#confirmRecordDeleteLink').each(function() {
            var link = $(this);
            var href = link.attr('href');
            if (href && href !== '#') {
                var newHref = updateUrlParameter(href.split('&tab=')[0], 'tab', activeTabId);
                link.attr('href', newHref);
            }
        });
    }

    $('#patientTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        currentTab = $(e.target).data('bs-target').substring(1);
        var url = new URL(window.location);
        url.searchParams.set('tab', currentTab);
        window.history.replaceState({}, '', url);
        updatePersistentElements(currentTab);
    });
    updatePersistentElements(currentTab);

    // Initialize remaining components
    $('#treatmentsTable').DataTable({"order": [[0, "desc"]]});
    $('.multi-select-add').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Select options...", dropdownParent: $('#addFindingModal') });
    $('.multi-select-edit').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Select options...", dropdownParent: $('#editFindingModal') });
    
    // The rest of your existing JavaScript for this page...
    $('select[name="procedure_done"]').on('change', function() { if ($(this).val() === 'custom') { $('#custom_procedure_wrapper').slideDown(); $('input[name="procedure_done_custom"]').prop('required', true); } else { $('#custom_procedure_wrapper').slideUp(); $('input[name="procedure_done_custom"]').prop('required', false); } });
    $('#edit_procedure_done').on('change', function() { if ($(this).val() === 'custom') { $('#edit_custom_procedure_wrapper').slideDown(); $('#edit_procedure_done_custom').prop('required', true); } else { $('#edit_custom_procedure_wrapper').slideUp(); $('#edit_procedure_done_custom').prop('required', false); } });
    function calculateBalance(charged, paid, balance) { var c = parseFloat($(charged).val())||0; var p = parseFloat($(paid).val())||0; $(balance).val((c - p).toFixed(2)); }
    $('#add_amount_charged, #add_amount_paid').on('input', function() { calculateBalance('#add_amount_charged', '#add_amount_paid', '#add_balance'); });
    $('#edit_amount_charged, #edit_amount_paid').on('input', function() { calculateBalance('#edit_amount_charged', '#edit_amount_paid', '#edit_balance'); });
    $(document).on('click', '.editFindingBtn', function() {
        var findId = $(this).data('id');
        $.getJSON('ajax_get_finding_details.php', { id: findId }, function(response) {
            if(response.details) {
                $('#edit_finding_id').val(response.details.finding_id);
                $('#edit_finding_date').val(response.details.finding_date);
                $('#edit_custom_findings_notes').val(response.details.custom_findings_notes);
                $('#edit_diagnosis').val(response.details.diagnosis);
                $('#edit_custom_treatment_notes').val(response.details.custom_treatment_notes);
                $('#edit_finding_remarks').val(response.details.remarks);
                $('#edit_lookup_findings').val(response.selected_findings).trigger('change');
                $('#edit_lookup_treatments').val(response.selected_treatments).trigger('change');
                var attachmentsHtml = '';
                if (response.attachments && response.attachments.length > 0) {
                    response.attachments.forEach(function(attach) {
                        var fileUrl = '../' + attach.file_path;
                        var thumbHtml = `<a href="${fileUrl}" target="_blank"><div class="file-icon"><i class="fas fa-file-alt"></i></div></a>`;
                        if (attach.file_type.startsWith('image/')) { thumbHtml = `<a href="${fileUrl}" data-bs-toggle="lightbox" data-gallery="finding-edit-${findId}"><img src="${fileUrl}" alt="${attach.file_name}"></a>`; }
                        else if (attach.file_type.startsWith('video/')) { thumbHtml = `<a href="${fileUrl}" target="_blank"><div class="file-icon"><i class="fas fa-video"></i></div></a>`; }
                        attachmentsHtml += `<div class="attachment-thumbnail-edit" id="attachment-item-${attach.attachment_id}">${thumbHtml}<div class="delete-attachment-btn-overlay" data-id="${attach.attachment_id}" title="Delete Attachment"><i class="fas fa-times"></i></div></div>`;
                    });
                } else { attachmentsHtml = '<p class="text-muted small fst-italic">No existing attachments.</p>'; }
                $('#existing-attachments-container').html(attachmentsHtml);
                new bootstrap.Modal(document.getElementById('editFindingModal')).show();
            } else { alert('Error: Could not retrieve finding details.'); }
        });
    });
    var attachmentToDeleteId = null;
    const deleteAttachmentModal = new bootstrap.Modal(document.getElementById('deleteAttachmentModal'));
    $(document).on('click', '.delete-attachment-btn-overlay', function() { attachmentToDeleteId = $(this).data('id'); deleteAttachmentModal.show(); });
    $('#confirmAttachmentDeleteBtn').on('click', function() { if (attachmentToDeleteId) { $.ajax({ url: 'ajax_delete_attachment.php', type: 'POST', data: { attachment_id: attachmentToDeleteId }, dataType: 'json', success: function(response) { if (response.success) { $(`#attachment-item-${attachmentToDeleteId}`).fadeOut(300, function() { $(this).remove(); }); } else { alert('Error: ' + response.message); } }, error: function() { alert('A server error occurred.'); }, complete: function() { attachmentToDeleteId = null; deleteAttachmentModal.hide(); } }); } });
    $(document).on('click', '.editTreatmentBtn', function() {
        var recId = $(this).data('id');
        $.getJSON('ajax_get_treatment.php', { id: recId }, function(data) {
            if(data) {
                $('#edit_record_id').val(data.record_id);
                $('#edit_procedure_date').val(data.procedure_date);
                $('#edit_tooth_no').val(data.tooth_no);
                $('#edit_amount_charged').val(parseFloat(data.amount_charged).toFixed(2));
                $('#edit_amount_paid').val(parseFloat(data.amount_paid).toFixed(2));
                $('#edit_balance').val(parseFloat(data.balance).toFixed(2));
                $('#edit_next_appt').val(data.next_appt);
                var procedureInOptions = $('#edit_procedure_done option').filter(function() { return $(this).val() == data.procedure_done; }).length > 0;
                if (procedureInOptions) { $('#edit_procedure_done').val(data.procedure_done).trigger('change'); } 
                else { $('#edit_procedure_done').val('custom').trigger('change'); $('#edit_procedure_done_custom').val(data.procedure_done); }
                new bootstrap.Modal(document.getElementById('editTreatmentModal')).show();
            } else { alert('Error: Could not retrieve treatment details.'); }
        });
    });
    $(document).on('click', '.deleteRecordBtn', function() {
        var recordId = $(this).data('id');
        var recordType = $(this).data('type');
        var deleteUrl = $(this).data('url');
        $('#recordTypeToDelete').text(recordType);
        var finalUrl = deleteUrl + '?id=' + recordId + '&patient_id=' + patientId;
        finalUrl = updateUrlParameter(finalUrl, 'tab', currentTab);
        $('#confirmRecordDeleteLink').attr('href', finalUrl);
        new bootstrap.Modal(document.getElementById('deleteRecordModal')).show();
    });
});
</script>