<?php
// FILE: admin/patient_view.php (COMPLETE, FINAL VERSION WITH ALL TABLE COLUMNS)

require 'includes/header.php';
require '../includes/db_connect.php';

// 1. VALIDATE AND SANITIZE INPUT
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id === 0) {
    $_SESSION['message'] = "Invalid patient ID provided.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit();
}

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
function display_yes_no($value) {
    return $value == 1 ? '<span class="fw-bold text-success">Yes</span>' : '<span class="text-muted">No</span>';
}
?>

<!-- PAGE HEADER AND ACTION BUTTONS -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dental Record: <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h1>
    <div>
        <a href="patient_edit.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Edit Full Record</a>
        <a href="patients.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Patient List</a>
    </div>
</div>

<?php
// Display feedback messages
if (isset($_SESSION['message'])) {
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
    echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">' . $_SESSION['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']); unset($_SESSION['message_type']);
}
?>

<!-- TABBED INTERFACE -->
<ul class="nav nav-tabs" id="patientTab" role="tablist">
  <li class="nav-item" role="presentation"><button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-selected="true">Patient Profile</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" id="medical-history-tab" data-bs-toggle="tab" data-bs-target="#medical-history" type="button" role="tab" aria-selected="false">Medical History</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" id="findings-tab" data-bs-toggle="tab" data-bs-target="#findings" type="button" role="tab" aria-selected="false">Clinical Findings</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" id="treatments-tab" data-bs-toggle="tab" data-bs-target="#treatments" type="button" role="tab" aria-selected="false">Treatment Record</button></li>
</ul>

<div class="tab-content card" id="patientTabContent">
  
  <!-- TAB PANE 1: PATIENT PROFILE -->
  <div class="tab-pane fade show active p-4" id="profile" role="tabpanel" aria-labelledby="profile-tab">
    <h4 class="form-section-title">Patient Information Record</h4>
    <div class="row display-section">
        <div class="col-md-6"><p><strong>Last Name:</strong> <?php echo htmlspecialchars($patient['last_name']); ?></p></div>
        <div class="col-md-6"><p><strong>First Name:</strong> <?php echo htmlspecialchars($patient['first_name']); ?></p></div>
        <div class="col-md-6"><p><strong>Middle Name:</strong> <?php echo htmlspecialchars($patient['middle_name'] ?: 'N/A'); ?></p></div>
        <div class="col-md-6"><p><strong>Nickname:</strong> <?php echo htmlspecialchars($patient['nickname'] ?: 'N/A'); ?></p></div>
        <div class="col-12"><p><strong>Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p></div>
        <div class="col-md-6"><p><strong>Birthdate:</strong> <?php echo date('F j, Y', strtotime($patient['birthdate'])); ?> (Age: <?php echo $patient['age']; ?>)</p></div>
        <div class="col-md-6"><p><strong>Gender:</strong> <?php echo $patient['gender'] == 'M' ? 'Male' : 'Female'; ?></p></div>
        <div class="col-md-6"><p><strong>Civil Status:</strong> <?php echo htmlspecialchars($patient['civil_status']); ?></p></div>
        <div class="col-md-6"><p><strong>Mobile #:</strong> <?php echo htmlspecialchars($patient['mobile_no']); ?></p></div>
        <div class="col-md-6"><p><strong>Occupation:</strong> <?php echo htmlspecialchars($patient['occupation'] ?: 'N/A'); ?></p></div>
        <div class="col-md-6"><p><strong>Religion:</strong> <?php echo htmlspecialchars($patient['religion'] ?: 'N/A'); ?></p></div>
        <div class="col-md-6"><p><strong>Parent/Guardian:</strong> <?php echo htmlspecialchars($patient['parent_guardian_name'] ?: 'N/A'); ?></p></div>
        <div class="col-md-6"><p><strong>Parent/Guardian Occupation:</strong> <?php echo htmlspecialchars($patient['parent_guardian_occupation'] ?: 'N/A'); ?></p></div>
    </div>
    <h4 class="form-section-title mt-4">Dental Information</h4>
    <div class="row display-section">
        <div class="col-12"><p><strong>Chief Complaint:</strong> <?php echo nl2br(htmlspecialchars($patient['chief_complaint'])); ?></p></div>
        <div class="col-12"><p><strong>History of Present Illness:</strong> <?php echo nl2br(htmlspecialchars($patient['history_of_present_illness'] ?: 'N/A')); ?></p></div>
        <div class="col-md-6"><p><strong>Previous Dentist:</strong> <?php echo htmlspecialchars($patient['previous_dentist'] ?: 'N/A'); ?></p></div>
        <div class="col-md-6"><p><strong>Last Dental Visit:</strong> <?php echo !empty($patient['last_dental_visit']) ? date('F j, Y', strtotime($patient['last_dental_visit'])) : 'N/A'; ?></p></div>
        <div class="col-12 mt-3"><p><strong>Previous Procedures Done:</strong>
                <?php $procedures = ['procedures_done_perio' => 'Perio','procedures_done_resto' => 'Resto','procedures_done_os' => 'O.S.','procedures_done_prostho' => 'Prostho','procedures_done_endo' => 'Endo','procedures_done_ortho' => 'Ortho']; $done_procedures = []; foreach ($procedures as $field => $label) { if (!empty($patient[$field])) { $done_procedures[] = $label; } } echo !empty($done_procedures) ? '<span class="fw-bold">'.implode(' • ', $done_procedures).'</span>' : '<span class="text-muted">None indicated.</span>'; ?>
        </p></div>
        <div class="col-md-6"><p><strong>Other Procedures Specified:</strong> <?php echo htmlspecialchars($patient['procedures_done_others_specify'] ?: 'N/A'); ?></p></div>
        <div class="col-md-6"><p><strong>Complications Noted:</strong> <?php echo htmlspecialchars($patient['complications'] ?: 'N/A'); ?></p></div>
    </div>
    <h4 class="form-section-title mt-4">Vital Signs</h4>
    <div class="row display-section">
        <div class="col-md-3"><p><strong>Blood Pressure:</strong> <?php echo htmlspecialchars($patient['blood_pressure'] ?: 'N/A'); ?></p></div>
        <div class="col-md-3"><p><strong>Respiratory Rate:</strong> <?php echo htmlspecialchars($patient['respiratory_rate'] ?: 'N/A'); ?></p></div>
        <div class="col-md-3"><p><strong>Pulse Rate:</strong> <?php echo htmlspecialchars($patient['pulse_rate'] ?: 'N/A'); ?></p></div>
        <div class="col-md-3"><p><strong>Temperature:</strong> <?php echo htmlspecialchars($patient['temperature'] ?: 'N/A'); ?></p></div>
    </div>
  </div>
  
  <!-- TAB PANE 2: MEDICAL HISTORY -->
  <div class="tab-pane fade p-4" id="medical-history" role="tabpanel" aria-labelledby="medical-history-tab">
    <h4 class="form-section-title">Medical Information</h4>
    <div class="row display-section">
        <p><strong>Physician:</strong> <?php echo htmlspecialchars($patient['physician_name'] ?: 'N/A'); ?> | <strong>Address:</strong> <?php echo htmlspecialchars($patient['physician_address'] ?: 'N/A'); ?> | <strong>Phone:</strong> <?php echo htmlspecialchars($patient['physician_phone'] ?: 'N/A'); ?></p>
        <div class="col-md-6">
            <p><strong>In good health?</strong> <?php echo display_yes_no($patient['are_you_in_good_health']); ?></p>
            <p><strong>Under medical treatment?</strong> <?php echo display_yes_no($patient['is_under_medical_treatment']); ?><?php if($patient['is_under_medical_treatment']) echo "<em class='ms-2 text-muted'>(" . htmlspecialchars($patient['medical_treatment_details']) . ")</em>"; ?></p>
            <p><strong>Serious illness/operation?</strong> <?php echo display_yes_no($patient['had_serious_illness_or_operation']); ?><?php if($patient['had_serious_illness_or_operation']) echo "<em class='ms-2 text-muted'>(" . htmlspecialchars($patient['illness_operation_details']) . ")</em>"; ?></p>
            <p><strong>Hospitalized?</strong> <?php echo display_yes_no($patient['has_been_hospitalized']); ?><?php if($patient['has_been_hospitalized']) echo "<em class='ms-2 text-muted'>(" . htmlspecialchars($patient['hospitalization_details']) . ")</em>"; ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Taking medication?</strong> <?php echo display_yes_no($patient['is_taking_medication']); ?><?php if($patient['is_taking_medication']) echo "<em class='ms-2 text-muted'>(" . htmlspecialchars($patient['medication_details']) . ")</em>"; ?></p>
            <p><strong>Taking diet drugs?</strong> <?php echo display_yes_no($patient['is_on_diet']); ?><?php if($patient['is_on_diet']) echo "<em class='ms-2 text-muted'>(" . htmlspecialchars($patient['diet_details']) . ")</em>"; ?></p>
            <p><strong>Drinks alcohol?</strong> <?php echo display_yes_no($patient['drinks_alcoholic_beverages']); ?><?php if($patient['drinks_alcoholic_beverages']) echo "<em class='ms-2 text-muted'>(Last 24hrs: " . htmlspecialchars($patient['alcohol_frequency']) . ")</em>"; ?></p>
            <p><strong>Uses tobacco?</strong> <?php echo display_yes_no($patient['uses_tobacco']); ?><?php if($patient['uses_tobacco']) echo "<em class='ms-2 text-muted'>(" . htmlspecialchars($patient['tobacco_details']) . ")</em>"; ?></p>
        </div>
    </div>
    <h5 class="mt-3">Reported Medical Conditions:</h5>
    <div class="alert alert-secondary">
        <?php $conditions = [ 'has_high_blood_pressure' => 'High Blood Pressure', 'has_low_blood_pressure' => 'Low Blood Pressure', 'has_epilepsy_convulsions' => 'Epilepsy/Convulsions', 'has_aids_hiv' => 'AIDS/HIV', 'has_stomach_troubles_ulcer' => 'Stomach Troubles/Ulcer', 'has_fainting_seizure' => 'Fainting/Seizure', 'has_rapid_weight_loss' => 'Rapid Weight Loss', 'had_radiation_therapy' => 'Radiation Therapy', 'has_joint_replacement_implant' => 'Joint Replacement/Implant', 'had_heart_surgery' => 'Heart Surgery', 'had_heart_attack' => 'Heart Attack', 'has_heart_disease' => 'Heart Disease', 'has_heart_murmur' => 'Heart Murmur', 'has_hepatitis_jaundice' => 'Hepatitis/Jaundice', 'has_tuberculosis' => 'Tuberculosis', 'has_swollen_ankles' => 'Swollen Ankles', 'has_kidney_disease' => 'Kidney Disease', 'has_diabetes' => 'Diabetes', 'has_bleeding_blood_disease' => 'Bleeding/Blood Disease', 'has_arthritis_rheumatism' => 'Arthritis/Rheumatism', 'has_cancer_tumor' => 'Cancer/Tumor', 'has_anemia' => 'Anemia', 'has_angina' => 'Angina', 'has_asthma' => 'Asthma', 'has_thyroid_problem' => 'Thyroid Problem', 'has_rheumatic_fever_disease' => 'Rheumatic Fever', 'has_hay_fever_allergies' => 'Hay Fever/Allergies', 'has_respiratory_problems' => 'Respiratory Problems', 'has_emphysema' => 'Emphysema', 'has_breathing_problems' => 'Breathing Problems', 'had_stroke' => 'Stroke', 'has_chest_pain' => 'Chest Pain' ]; $yes_conditions = []; foreach ($conditions as $field => $label) { if (!empty($patient[$field])) { $yes_conditions[] = $label; } } if (!empty($yes_conditions)) { echo '<div>' . implode(' • ', $yes_conditions) . '</div>'; } else { echo '<span class="text-muted">None reported.</span>'; } if (!empty($patient['other_diseases_details'])) { echo "<div class='mt-2'><strong>Other Specified Conditions: </strong>" . htmlspecialchars($patient['other_diseases_details']) . "</div>"; } ?>
    </div>
    <h5 class="mt-3">Reported Allergies:</h5>
    <div class="alert alert-secondary">
         <?php $allergies = [ 'allergic_anesthetics' => 'Anesthetics (Lidocaine)', 'allergic_penicillin' => 'Penicillin/Antibiotics', 'allergic_aspirin' => 'Aspirin', 'allergic_latex' => 'Latex' ]; $yes_allergies = []; foreach ($allergies as $field => $label) { if (!empty($patient[$field])) { $yes_allergies[] = $label; } } if (!empty($yes_allergies)) { echo '<div>' . implode(' • ', $yes_allergies) . '</div>'; } else { echo '<span class="text-muted">None reported.</span>'; } if (!empty($patient['allergic_others_details'])) { echo "<div class='mt-2'><strong>Other Specified Allergies: </strong>" . htmlspecialchars($patient['allergic_others_details']) . "</div>"; } if (!empty($patient['allergy_reaction_details'])) { echo "<div class='mt-2'><strong>Reaction Type: </strong>" . htmlspecialchars($patient['allergy_reaction_details']) . "</div>"; } ?>
    </div>
    <h5 class="mt-3">Other Conditions for Dentist to Know:</h5>
    <div class="alert alert-info">
        <p class="mb-0"><?php echo !empty($patient['other_conditions_to_know']) ? nl2br(htmlspecialchars($patient['other_conditions_to_know'])) : '<span class="text-muted">None reported.</span>'; ?></p>
    </div>
  </div>

  <!-- TAB PANE 3: CLINICAL FINDINGS (TABLE CORRECTED) -->
  <div class="tab-pane fade p-4" id="findings" role="tabpanel">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="form-section-title mb-0">Clinical Findings</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFindingModal"><i class="fas fa-plus me-2"></i>Add New Finding</button>
      </div>
      <div class="table-responsive">
          <table id="findingsTable" class="table table-striped table-hover" style="width:100%">
              <thead class="table-light">
                  <tr>
                      <th>Date</th>
                      <th>Findings</th>
                      <th>Diagnosis</th>
                      <th>Proposed Treatment</th>
                      <th>Remarks</th>
                      <th>Dentist</th>
                      <th class="text-center">Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if ($findings->num_rows > 0): $findings->data_seek(0); ?>
                      <?php while($row = $findings->fetch_assoc()): ?>
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
                      <?php endwhile; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
  </div>

  <!-- TAB PANE 4: TREATMENT RECORD (TABLE CORRECTED) -->
  <div class="tab-pane fade p-4" id="treatments" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="form-section-title mb-0">Treatment Record</h4>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTreatmentModal"><i class="fas fa-plus me-2"></i>Add New Treatment</button>
    </div>
    <div class="table-responsive">
        <table id="treatmentsTable" class="table table-striped table-hover" style="width:100%">
          <thead class="table-light">
              <tr>
                  <th>Date</th>
                  <th>Procedure</th>
                  <th>Tooth #</th>
                  <th>Charged</th>
                  <th>Paid</th>
                  <th>Balance</th>
                  <th>Next Appt.</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Actions</th>
              </tr>
          </thead>
          <tbody>
            <?php if ($treatments->num_rows > 0): $treatments->data_seek(0); ?>
                <?php while($row = $treatments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['procedure_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['procedure_done']); ?></td>
                        <td><?php echo htmlspecialchars($row['tooth_no'] ?: '-'); ?></td>
                        <td><?php echo number_format($row['amount_charged'], 2); ?></td>
                        <td><?php echo number_format($row['amount_paid'], 2); ?></td>
                        <td><?php echo number_format($row['balance'], 2); ?></td>
                        <td><?php echo !empty($row['next_appt']) ? date('M d, Y', strtotime($row['next_appt'])) : '-'; ?></td>
                        <td class="text-center">
                            <?php if ($row['balance'] <= 0): ?><span class="badge bg-success">Paid</span><?php else: ?><span class="badge bg-warning text-dark">Balance</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm editTreatmentBtn" data-id="<?php echo $row['record_id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm deleteRecordBtn" data-id="<?php echo $row['record_id']; ?>" data-type="Treatment" data-url="treatment_delete_process.php" title="Delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
    </div>
  </div>
</div>

<!-- ALL MODALS -->
<!-- ADD FINDING MODAL --><div class="modal fade" id="addFindingModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Clinical Finding</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="finding_add_process.php" method="POST"><div class="modal-body"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><input type="hidden" name="dentist_id" value="<?php echo $_SESSION['user_id']; ?>"><div class="mb-3"><label class="form-label">Date</label><input type="date" name="finding_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div><div class="mb-3"><label class="form-label">Clinical Findings</label><textarea name="clinical_findings" class="form-control" rows="3" required></textarea></div><div class="mb-3"><label class="form-label">Diagnosis</label><textarea name="diagnosis" class="form-control" rows="2" required></textarea></div><div class="mb-3"><label class="form-label">Proposed Treatment</label><textarea name="proposed_treatment" class="form-control" rows="2"></textarea></div><div class="mb-3"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Finding</button></div></form></div></div></div>
<!-- ADD TREATMENT MODAL --><div class="modal fade" id="addTreatmentModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="treatment_add_process.php" method="POST"><div class="modal-body"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><input type="hidden" name="dentist_id" value="<?php echo $_SESSION['user_id']; ?>"><div class="row g-3"><div class="col-md-6"><label class="form-label">Procedure Date</label><input type="date" name="procedure_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div><div class="col-md-6"><label class="form-label">Tooth Number(s)</label><input type="text" name="tooth_no" class="form-control" placeholder="e.g., 18, 21, 35"></div><div class="col-12"><label class="form-label">Procedure Done</label><input type="text" name="procedure_done" class="form-control" required></div><div class="col-md-4"><label class="form-label">Amount Charged (PHP)</label><input type="number" step="0.01" name="amount_charged" id="add_amount_charged" class="form-control" required></div><div class="col-md-4"><label class="form-label">Amount Paid (PHP)</label><input type="number" step="0.01" name="amount_paid" id="add_amount_paid" class="form-control" required></div><div class="col-md-4"><label class="form-label">Balance</label><input type="number" step="0.01" name="balance" id="add_balance" class="form-control" readonly required></div><div class="col-md-6"><label class="form-label">Next Appointment Date</label><input type="date" name="next_appt" class="form-control"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Treatment</button></div></form></div></div></div>
<!-- EDIT FINDING MODAL --><div class="modal fade" id="editFindingModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Clinical Finding</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="finding_edit_process.php" method="POST"><div class="modal-body"><input type="hidden" name="finding_id" id="edit_finding_id"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><div class="mb-3"><label>Date</label><input type="date" name="finding_date" id="edit_finding_date" class="form-control" required></div><div class="mb-3"><label>Clinical Findings</label><textarea name="clinical_findings" id="edit_clinical_findings" class="form-control" rows="3" required></textarea></div><div class="mb-3"><label>Diagnosis</label><textarea name="diagnosis" id="edit_diagnosis" class="form-control" rows="2" required></textarea></div><div class="mb-3"><label>Proposed Treatment</label><textarea name="proposed_treatment" id="edit_proposed_treatment" class="form-control" rows="2"></textarea></div><div class="mb-3"><label>Remarks</label><textarea name="remarks" id="edit_finding_remarks" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>
<!-- EDIT TREATMENT MODAL --><div class="modal fade" id="editTreatmentModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="treatment_edit_process.php" method="POST"><div class="modal-body"><input type="hidden" name="record_id" id="edit_record_id"><input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>"><div class="row g-3"><div class="col-md-6"><label>Procedure Date</label><input type="date" name="procedure_date" id="edit_procedure_date" class="form-control" required></div><div class="col-md-6"><label>Tooth #</label><input type="text" name="tooth_no" id="edit_tooth_no" class="form-control"></div><div class="col-12"><label>Procedure Done</label><input type="text" name="procedure_done" id="edit_procedure_done" class="form-control" required></div><div class="col-md-4"><label>Amount Charged</label><input type="number" step="0.01" name="amount_charged" id="edit_amount_charged" class="form-control" required></div><div class="col-md-4"><label>Amount Paid</label><input type="number" step="0.01" name="amount_paid" id="edit_amount_paid" class="form-control" required></div><div class="col-md-4"><label>Balance</label><input type="number" step="0.01" name="balance" id="edit_balance" class="form-control" readonly required></div><div class="col-md-6"><label>Next Appointment</label><input type="date" name="next_appt" id="edit_next_appt" class="form-control"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>
<!-- GENERIC DELETE RECORD MODAL --><div class="modal fade" id="deleteRecordModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Are you sure you want to delete this <strong id="recordTypeToDelete"></strong>? This action cannot be undone.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><a id="confirmRecordDeleteLink" href="#" class="btn btn-danger">Yes, Delete</a></div></div></div></div>

<!-- STYLES AND SCRIPTS -->
<style>.display-section p{margin-bottom:.75rem;font-size:1.05rem}.display-section p strong{color:#343a40;min-width:160px;display:inline-block}.tab-content.card{border-top-left-radius:0; border-top: none;}</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#findingsTable, #treatmentsTable').DataTable({"order": [[0, "desc"]]});
    function calculateBalance(chargedSelector, paidSelector, balanceSelector) { var charged = parseFloat($(chargedSelector).val()) || 0; var paid = parseFloat($(paidSelector).val()) || 0; $(balanceSelector).val((charged - paid).toFixed(2)); }
    $('#add_amount_charged, #add_amount_paid').on('input', function() { calculateBalance('#add_amount_charged', '#add_amount_paid', '#add_balance'); });
    $('#edit_amount_charged, #edit_amount_paid').on('input', function() { calculateBalance('#edit_amount_charged', '#edit_amount_paid', '#edit_balance'); });
    $(document).on('click', '.editFindingBtn', function() { var findingId = $(this).data('id'); $.getJSON('ajax_get_finding.php', { id: findingId }, function(data) { if(data) { $('#edit_finding_id').val(data.finding_id); $('#edit_finding_date').val(data.finding_date); $('#edit_clinical_findings').val(data.clinical_findings); $('#edit_diagnosis').val(data.diagnosis); $('#edit_proposed_treatment').val(data.proposed_treatment); $('#edit_finding_remarks').val(data.remarks); new bootstrap.Modal(document.getElementById('editFindingModal')).show(); } }); });
    $(document).on('click', '.editTreatmentBtn', function() { var recordId = $(this).data('id'); $.getJSON('ajax_get_treatment.php', { id: recordId }, function(data) { if(data) { $('#edit_record_id').val(data.record_id); $('#edit_procedure_date').val(data.procedure_date); $('#edit_procedure_done').val(data.procedure_done); $('#edit_tooth_no').val(data.tooth_no); $('#edit_amount_charged').val(parseFloat(data.amount_charged).toFixed(2)); $('#edit_amount_paid').val(parseFloat(data.amount_paid).toFixed(2)); $('#edit_balance').val(parseFloat(data.balance).toFixed(2)); $('#edit_next_appt').val(data.next_appt); new bootstrap.Modal(document.getElementById('editTreatmentModal')).show(); } }); });
    $(document).on('click', '.deleteRecordBtn', function() { var recordId = $(this).data('id'); var recordType = $(this).data('type'); var deleteUrl = $(this).data('url') + '?id=' + recordId + '&patient_id=' + <?php echo $patient_id; ?>; $('#recordTypeToDelete').text(recordType); $('#confirmRecordDeleteLink').attr('href', deleteUrl); new bootstrap.Modal(document.getElementById('deleteRecordModal')).show(); });
});
</script>

<?php
$conn->close();
// require 'includes/footer.php'; // Not needed since scripts are self-contained
?>