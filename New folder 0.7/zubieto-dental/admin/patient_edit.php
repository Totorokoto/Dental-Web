<?php
// FILE: admin/patient_edit.php 

// STEP 1: ALL PHP LOGIC, VALIDATION, AND DATA FETCHING
session_start();
require '../includes/db_connect.php';
require 'includes/header.php'; 

// Get URL parameters
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validation 1: Check for a valid ID.
if ($patient_id === 0) {
    $_SESSION['message'] = "Invalid patient ID."; 
    $_SESSION['message_type'] = 'danger';
    // Use JavaScript redirect to ensure header is shown before redirecting
    echo "<script>window.location.href='patients.php';</script>";
    exit();
}

// Prepare and execute the database query to find the patient and their medical history.
$sql = "SELECT p.*, mh.* FROM patients p LEFT JOIN medical_history mh ON p.patient_id = mh.patient_id WHERE p.patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

// Validation 2: Check if a patient was found.
if (!$result || $result->num_rows === 0) {
    $_SESSION['message'] = "Patient not found."; 
    $_SESSION['message_type'] = 'danger';
    echo "<script>window.location.href='patients.php';</script>";
    exit();
}

$patient = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Helper functions for populating the form efficiently
function check_radio($value, $expected) {
    if (isset($value) && $value == $expected) {
        echo 'checked';
    }
}

function check_box($value) {
    if (!empty($value) && $value == 1) {
        echo 'checked';
    }
}

// STEP 2: HTML OUTPUT BEGINS
?>

<!-- Custom styles for the accordion form -->
<style>
    .accordion-button { font-size: 1.1rem; font-weight: 500; }
    .accordion-button:not(.collapsed) { color: #0d6efd; background-color: #e7f1ff; }
    .accordion-button:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
    .form-label i { margin-right: 0.5rem; color: #6c757d; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Record: <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h1>
    <a href="patient_view.php?id=<?php echo $patient_id; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Cancel & Go Back</a>
</div>

<!-- Form tag wraps the entire accordion -->
<form action="patient_edit_process.php" method="POST" id="patientForm">
    <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">

    <div class="accordion" id="patientEditAccordion">
    
      <!-- ACCORDION ITEM 1: PATIENT INFORMATION -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"><i class="fas fa-user-circle me-2"></i> Section 1: Patient Information</button></h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#patientEditAccordion">
          <div class="accordion-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($patient['last_name']); ?>" required></div>
                <div class="col-md-4"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required></div>
                <div class="col-md-4"><label class="form-label">Middle Name</label><input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($patient['middle_name']); ?>"></div>
                <div class="col-12"><label class="form-label">Address <span class="text-danger">*</span></label><input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($patient['address']); ?>" required></div>
                <div class="col-md-3"><label class="form-label">Birthdate <span class="text-danger">*</span></label><input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo $patient['birthdate']; ?>" required> <span id="ageDisplay" class="text-muted ms-2"></span></div>
                <div class="col-md-2"><label class="form-label">Gender <span class="text-danger">*</span></label><select name="gender" class="form-select" required><option value="M" <?php if($patient['gender']=='M') echo 'selected';?>>Male</option><option value="F" <?php if($patient['gender']=='F') echo 'selected';?>>Female</option></select></div>
                <div class="col-md-2"><label class="form-label">Nickname</label><input type="text" class="form-control" name="nickname" value="<?php echo htmlspecialchars($patient['nickname']); ?>"></div>
                <div class="col-md-3"><label class="form-label">Civil Status <span class="text-danger">*</span></label><input type="text" class="form-control" name="civil_status" value="<?php echo htmlspecialchars($patient['civil_status']); ?>" required></div>
                <div class="col-md-2"><label class="form-label">Nationality</label><input type="text" class="form-control" name="nationality" value="<?php echo htmlspecialchars($patient['nationality']); ?>"></div>
                <div class="col-md-4"><label class="form-label">Occupation</label><input type="text" class="form-control" name="occupation" value="<?php echo htmlspecialchars($patient['occupation']); ?>"></div>
                <div class="col-md-4"><label class="form-label">Religion</label><input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($patient['religion']); ?>"></div>
                <div class="col-md-4"><label class="form-label">Mobile # <span class="text-danger">*</span></label><input type="tel" class="form-control" name="mobile_no" value="<?php echo htmlspecialchars($patient['mobile_no']); ?>" required></div>
                <div class="col-md-6"><label class="form-label">Parent/Guardian's Name</label><input type="text" class="form-control" name="parent_guardian_name" value="<?php echo htmlspecialchars($patient['parent_guardian_name']); ?>"></div>
                <div class="col-md-6"><label class="form-label">Parent/Guardian's Occupation</label><input type="text" class="form-control" name="parent_guardian_occupation" value="<?php echo htmlspecialchars($patient['parent_guardian_occupation']); ?>"></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ACCORDION ITEM 2: DENTAL INFORMATION -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"><i class="fas fa-tooth me-2"></i> Section 2: Dental Information</button></h2>
        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#patientEditAccordion">
          <div class="accordion-body">
             <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Chief Complaint <span class="text-danger">*</span></label><textarea class="form-control" name="chief_complaint" rows="2" required><?php echo htmlspecialchars($patient['chief_complaint']); ?></textarea></div>
                <div class="col-md-6"><label class="form-label">History of Present Illness</label><textarea class="form-control" name="history_of_present_illness" rows="2"><?php echo htmlspecialchars($patient['history_of_present_illness']); ?></textarea></div>
                <div class="col-md-6"><label class="form-label">Previous Dentist</label><input type="text" class="form-control" name="previous_dentist" value="<?php echo htmlspecialchars($patient['previous_dentist']); ?>"></div>
                <div class="col-md-6"><label class="form-label">Last Dental Visit</label><input type="date" class="form-control" name="last_dental_visit" value="<?php echo $patient['last_dental_visit']; ?>"></div>
                <div class="col-12 mt-3"><label class="form-label fw-bold">Procedures Done (Previous)</label><div class="d-flex flex-wrap"><div class="form-check me-3"><input class="form-check-input" type="checkbox" name="procedures_done_perio" value="1" <?php check_box($patient['procedures_done_perio']); ?>><label class="form-check-label">Perio</label></div><div class="form-check me-3"><input class="form-check-input" type="checkbox" name="procedures_done_resto" value="1" <?php check_box($patient['procedures_done_resto']); ?>><label class="form-check-label">Resto</label></div><div class="form-check me-3"><input class="form-check-input" type="checkbox" name="procedures_done_os" value="1" <?php check_box($patient['procedures_done_os']); ?>><label class="form-check-label">O.S.</label></div><div class="form-check me-3"><input class="form-check-input" type="checkbox" name="procedures_done_prostho" value="1" <?php check_box($patient['procedures_done_prostho']); ?>><label class="form-check-label">Prostho</label></div><div class="form-check me-3"><input class="form-check-input" type="checkbox" name="procedures_done_endo" value="1" <?php check_box($patient['procedures_done_endo']); ?>><label class="form-check-label">Endo</label></div><div class="form-check me-3"><input class="form-check-input" type="checkbox" name="procedures_done_ortho" value="1" <?php check_box($patient['procedures_done_ortho']); ?>><label class="form-check-label">Ortho</label></div></div></div>
                <div class="col-md-6"><label class="form-label">Others, Specify:</label><input type="text" class="form-control" name="procedures_done_others_specify" value="<?php echo htmlspecialchars($patient['procedures_done_others_specify']); ?>"></div>
                <div class="col-md-6"><label class="form-label">Complications, if any:</label><input type="text" class="form-control" name="complications" value="<?php echo htmlspecialchars($patient['complications']); ?>"></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ACCORDION ITEM 3: MEDICAL INFORMATION  -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingThree"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><i class="fas fa-heartbeat me-2"></i> Section 3: Medical Information</button></h2>
        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#patientEditAccordion">
          <div class="accordion-body">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Name of Physician</label><input type="text" class="form-control" name="physician_name" value="<?php echo htmlspecialchars($patient['physician_name']); ?>"></div>
                <div class="col-md-4"><label class="form-label">Address</label><input type="text" class="form-control" name="physician_address" value="<?php echo htmlspecialchars($patient['physician_address']); ?>"></div>
                <div class="col-md-3"><label class="form-label">Phone Number</label><input type="tel" class="form-control" name="physician_phone" value="<?php echo htmlspecialchars($patient['physician_phone']); ?>"></div>
                <div class="col-md-12"><label class="form-label">Date of Last Physical Examination</label><input type="date" class="form-control" name="last_physical_exam" value="<?php echo $patient['last_physical_exam']; ?>"></div>
                <hr class="my-4">
                <div class="col-md-6"><p class="mb-1">Are you in good health? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="are_you_in_good_health" value="1" required <?php check_radio($patient['are_you_in_good_health'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="are_you_in_good_health" value="0" <?php check_radio($patient['are_you_in_good_health'], '0'); ?>><label class="form-check-label">No</label></div></div>
                <div class="col-md-6"><p class="mb-1">Are you under medical treatment now? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_under_medical_treatment" value="1" required <?php check_radio($patient['is_under_medical_treatment'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_under_medical_treatment" value="0" <?php check_radio($patient['is_under_medical_treatment'], '0'); ?>><label class="form-check-label">No</label></div><label class="form-label mt-1">If so, what is the condition being treated?</label><input type="text" class="form-control form-control-sm" name="medical_treatment_details" value="<?php echo htmlspecialchars($patient['medical_treatment_details']); ?>"></div>
                <div class="col-md-6 mt-3"><p class="mb-1">Had serious illness or surgical operation? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="had_serious_illness_or_operation" value="1" required <?php check_radio($patient['had_serious_illness_or_operation'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="had_serious_illness_or_operation" value="0" <?php check_radio($patient['had_serious_illness_or_operation'], '0'); ?>><label class="form-check-label">No</label></div><label class="form-label mt-1">If so, what illness or operation?</label><input type="text" class="form-control form-control-sm" name="illness_operation_details" value="<?php echo htmlspecialchars($patient['illness_operation_details']); ?>"></div>
                <div class="col-md-6 mt-3"><p class="mb-1">Have you ever been hospitalized? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="has_been_hospitalized" value="1" required <?php check_radio($patient['has_been_hospitalized'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="has_been_hospitalized" value="0" <?php check_radio($patient['has_been_hospitalized'], '0'); ?>><label class="form-check-label">No</label></div><label class="form-label mt-1">If so, when and why?</label><input type="text" class="form-control form-control-sm" name="hospitalization_details" value="<?php echo htmlspecialchars($patient['hospitalization_details']); ?>"></div>
                <div class="col-md-6 mt-3"><p class="mb-1">Taking any prescription/non-prescription medication? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_taking_medication" value="1" required <?php check_radio($patient['is_taking_medication'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_taking_medication" value="0" <?php check_radio($patient['is_taking_medication'], '0'); ?>><label class="form-check-label">No</label></div><label class="form-label mt-1">If so, please specify:</label><input type="text" class="form-control form-control-sm" name="medication_details" value="<?php echo htmlspecialchars($patient['medication_details']); ?>"></div>
                <div class="col-md-6 mt-3"><p class="mb-1">Are you taking any diet drugs? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_on_diet" value="1" required <?php check_radio($patient['is_on_diet'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_on_diet" value="0" <?php check_radio($patient['is_on_diet'], '0'); ?>><label class="form-check-label">No</label></div><label class="form-label mt-1">If yes, what diet drugs are you taking?</label><input type="text" class="form-control form-control-sm" name="diet_details" value="<?php echo htmlspecialchars($patient['diet_details']); ?>"></div>
                <hr class="my-4">
                <div class="col-md-6"><p class="mb-1">Do you drink alcoholic beverages? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="drinks_alcoholic_beverages" value="1" required <?php check_radio($patient['drinks_alcoholic_beverages'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="drinks_alcoholic_beverages" value="0" <?php check_radio($patient['drinks_alcoholic_beverages'], '0'); ?>><label class="form-check-label">No</label></div><label class="form-label mt-1">If yes, how much alcohol in the last 24 hours?</label><input type="text" class="form-control form-control-sm" name="alcohol_frequency" value="<?php echo htmlspecialchars($patient['alcohol_frequency']); ?>"></div>
                <div class="col-md-6"><p class="mb-1">Do you use tobacco? <span class="text-danger">*</span></p><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="uses_tobacco" value="1" required <?php check_radio($patient['uses_tobacco'], '1'); ?>><label class="form-check-label">Yes</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="uses_tobacco" value="0" <?php check_radio($patient['uses_tobacco'], '0'); ?>><label class="form-check-label">No</label></div><label class="form-label mt-1">If yes, what type?</label><input type="text" class="form-control form-control-sm" name="tobacco_details" value="<?php echo htmlspecialchars($patient['tobacco_details']); ?>"></div>
                <hr class="my-4">
                <div class="col-12"><strong>Are you allergic to any of the following?</strong></div>
                <div class="row"><div class="col-md-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="allergic_anesthetics" value="1" <?php check_box($patient['allergic_anesthetics']); ?>><label class="form-check-label">Anesthetics</label></div></div><div class="col-md-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="allergic_penicillin" value="1" <?php check_box($patient['allergic_penicillin']); ?>><label class="form-check-label">Penicillin</label></div></div><div class="col-md-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="allergic_aspirin" value="1" <?php check_box($patient['allergic_aspirin']); ?>><label class="form-check-label">Aspirin</label></div></div><div class="col-md-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="allergic_latex" value="1" <?php check_box($patient['allergic_latex']); ?>><label class="form-check-label">Latex</label></div></div></div>
                <div class="col-md-6 mt-2"><label class="form-label">Other allergies, please specify:</label><input type="text" class="form-control" name="allergic_others_details" value="<?php echo htmlspecialchars($patient['allergic_others_details']); ?>"></div>
                <div class="col-md-6 mt-2"><label class="form-label">Type of reaction:</label><input type="text" class="form-control" name="allergy_reaction_details" value="<?php echo htmlspecialchars($patient['allergy_reaction_details']); ?>"></div>
                <hr class="my-4">
                <div class="col-12"><strong>Do you have or have you had any of the following? (Check which apply):</strong></div>
                <div class="row"><div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="has_high_blood_pressure" value="1" <?php check_box($patient['has_high_blood_pressure']); ?>><label class="form-check-label">High Blood Pressure</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_low_blood_pressure" value="1" <?php check_box($patient['has_low_blood_pressure']); ?>><label class="form-check-label">Low Blood Pressure</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_epilepsy_convulsions" value="1" <?php check_box($patient['has_epilepsy_convulsions']); ?>><label class="form-check-label">Epilepsy</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_aids_hiv" value="1" <?php check_box($patient['has_aids_hiv']); ?>><label class="form-check-label">AIDS or HIV</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_stomach_troubles_ulcer" value="1" <?php check_box($patient['has_stomach_troubles_ulcer']); ?>><label class="form-check-label">Stomach Troubles</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_fainting_seizure" value="1" <?php check_box($patient['has_fainting_seizure']); ?>><label class="form-check-label">Fainting Seizure</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_rapid_weight_loss" value="1" <?php check_box($patient['has_rapid_weight_loss']); ?>><label class="form-check-label">Rapid Weight Loss</label></div></div><div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="has_hepatitis_jaundice" value="1" <?php check_box($patient['has_hepatitis_jaundice']); ?>><label class="form-check-label">Hepatitis/Jaundice</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_tuberculosis" value="1" <?php check_box($patient['has_tuberculosis']); ?>><label class="form-check-label">Tuberculosis</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_swollen_ankles" value="1" <?php check_box($patient['has_swollen_ankles']); ?>><label class="form-check-label">Swollen Ankles</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_kidney_disease" value="1" <?php check_box($patient['has_kidney_disease']); ?>><label class="form-check-label">Kidney Disease</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_diabetes" value="1" <?php check_box($patient['has_diabetes']); ?>><label class="form-check-label">Diabetes</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_bleeding_blood_disease" value="1" <?php check_box($patient['has_bleeding_blood_disease']); ?>><label class="form-check-label">Bleeding/Blood Disease</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_cancer_tumor" value="1" <?php check_box($patient['has_cancer_tumor']); ?>><label class="form-check-label">Cancer/Tumor</label></div></div><div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="has_rheumatic_fever_disease" value="1" <?php check_box($patient['has_rheumatic_fever_disease']); ?>><label class="form-check-label">Rheumatic Fever</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_hay_fever_allergies" value="1" <?php check_box($patient['has_hay_fever_allergies']); ?>><label class="form-check-label">Hay Fever</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_respiratory_problems" value="1" <?php check_box($patient['has_respiratory_problems']); ?>><label class="form-check-label">Respiratory Problems</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="had_stroke" value="1" <?php check_box($patient['had_stroke']); ?>><label class="form-check-label">Stroke</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_chest_pain" value="1" <?php check_box($patient['has_chest_pain']); ?>><label class="form-check-label">Chest Pain</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="has_asthma" value="1" <?php check_box($patient['has_asthma']); ?>><label class="form-check-label">Asthma</label></div></div></div>
                <div class="col-12 mt-3"><label class="form-label">Others, please explain:</label><textarea class="form-control" name="other_diseases_details" rows="2"><?php echo htmlspecialchars($patient['other_diseases_details']); ?></textarea></div>
                <hr class="my-4">
                <div class="col-12"><label class="form-label">Any other disease, condition, or problem not listed above?</label><textarea class="form-control" name="other_conditions_to_know" rows="3"><?php echo htmlspecialchars($patient['other_conditions_to_know']); ?></textarea></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ACCORDION ITEM 4: VITAL SIGNS -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingFour"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour"><i class="fas fa-stethoscope me-2"></i> Section 4: Vital Signs</button></h2>
        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#patientEditAccordion">
          <div class="accordion-body">
              <div class="row">
                <div class="col-md-3"><label class="form-label">Blood Pressure:</label><input type="text" class="form-control" name="blood_pressure" value="<?php echo htmlspecialchars($patient['blood_pressure']); ?>"></div>
                <div class="col-md-3"><label class="form-label">Respiratory Rate:</label><input type="text" class="form-control" name="respiratory_rate" value="<?php echo htmlspecialchars($patient['respiratory_rate']); ?>"></div>
                <div class="col-md-3"><label class="form-label">Pulse Rate:</label><input type="text" class="form-control" name="pulse_rate" value="<?php echo htmlspecialchars($patient['pulse_rate']); ?>"></div>
                <div class="col-md-3"><label class="form-label">Temperature:</label><input type="text" class="form-control" name="temperature" value="<?php echo htmlspecialchars($patient['temperature']); ?>"></div>
              </div>
          </div>
        </div>
      </div>

    </div> <!-- End Accordion -->

    <div class="col-12 mt-4 text-center">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Update Patient Record</button>
    </div>

</form> <!-- End Form -->


<script>
document.addEventListener('DOMContentLoaded', function() {
    function calculateAge() {
        const birthdateInput = document.getElementById('birthdate');
        const ageDisplay = document.getElementById('ageDisplay');
        if (birthdateInput.value) {
            const dob = new Date(birthdateInput.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) { age--; }
            ageDisplay.textContent = age > 0 ? `(${age} years old)` : '';
        } else { ageDisplay.textContent = ''; }
    }
    calculateAge(); // Calculate on page load
    document.getElementById('birthdate').addEventListener('change', calculateAge);
});
</script>

<?php require 'includes/footer.php'; ?>