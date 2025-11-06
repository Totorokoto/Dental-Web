<?php
// FILE: admin/patient_edit_process.php 

session_start();
require '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
if ($patient_id === 0) {
    $_SESSION['message'] = "Invalid Patient ID for update."; $_SESSION['message_type'] = 'danger';
    header("Location: patients.php"); exit();
}

$conn->autocommit(FALSE);

try {
    function calculateAge($birthdate) { 
        if(empty($birthdate)) return 0;
        try { return (new DateTime())->diff(new DateTime($birthdate))->y; } 
        catch (Exception $e) { return 0; }
    }
    function sanitize_bool($name) { return isset($_POST[$name]) && $_POST[$name] == '1' ? 1 : 0; }

    // Sanitize 'patients' table data
    $last_name = $conn->real_escape_string(trim($_POST['last_name']));
    $first_name = $conn->real_escape_string(trim($_POST['first_name']));
    $middle_name = $conn->real_escape_string(trim($_POST['middle_name']));
    $nickname = $conn->real_escape_string(trim($_POST['nickname']));
    $birthdate = $conn->real_escape_string($_POST['birthdate']);
    $age = calculateAge($_POST['birthdate']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $civil_status = $conn->real_escape_string($_POST['civil_status']);
    $nationality = $conn->real_escape_string($_POST['nationality']);
    $religion = $conn->real_escape_string($_POST['religion']);
    $occupation = $conn->real_escape_string($_POST['occupation']);
    $address = $conn->real_escape_string($_POST['address']);
    $mobile_no = $conn->real_escape_string($_POST['mobile_no']);
    $email = $conn->real_escape_string(trim($_POST['email'])); // Capture the email
    $parent_guardian_name = $conn->real_escape_string($_POST['parent_guardian_name']);
    $parent_guardian_occupation = $conn->real_escape_string($_POST['parent_guardian_occupation']);
    $chief_complaint = $conn->real_escape_string($_POST['chief_complaint']);
    $history_of_present_illness = $conn->real_escape_string($_POST['history_of_present_illness']);
    $previous_dentist = $conn->real_escape_string($_POST['previous_dentist']);
    $last_dental_visit = !empty($_POST['last_dental_visit']) ? "'" . $conn->real_escape_string($_POST['last_dental_visit']) . "'" : "NULL";
    $procedures_done_others_specify = $conn->real_escape_string($_POST['procedures_done_others_specify']);
    $complications = $conn->real_escape_string($_POST['complications']);
    $physician_name = $conn->real_escape_string($_POST['physician_name']);
    $physician_address = $conn->real_escape_string($_POST['physician_address']);
    $physician_phone = $conn->real_escape_string($_POST['physician_phone']);
    $last_physical_exam = !empty($_POST['last_physical_exam']) ? "'" . $conn->real_escape_string($_POST['last_physical_exam']) . "'" : "NULL";
    $blood_pressure = $conn->real_escape_string($_POST['blood_pressure']);
    $respiratory_rate = $conn->real_escape_string($_POST['respiratory_rate']);
    $pulse_rate = $conn->real_escape_string($_POST['pulse_rate']);
    $temperature = $conn->real_escape_string($_POST['temperature']);

    // Updated SQL query to include the email field
    $sql_patient = "UPDATE patients SET 
        last_name='$last_name', first_name='$first_name', middle_name='$middle_name', nickname='$nickname', 
        birthdate='$birthdate', age=$age, gender='$gender', civil_status='$civil_status', 
        nationality='$nationality', religion='$religion', occupation='$occupation', address='$address', 
        mobile_no='$mobile_no', email='$email', parent_guardian_name='$parent_guardian_name', parent_guardian_occupation='$parent_guardian_occupation',
        chief_complaint='$chief_complaint', history_of_present_illness='$history_of_present_illness', 
        previous_dentist='$previous_dentist', last_dental_visit=$last_dental_visit, 
        procedures_done_perio=".sanitize_bool('procedures_done_perio').", procedures_done_resto=".sanitize_bool('procedures_done_resto').",
        procedures_done_os=".sanitize_bool('procedures_done_os').", procedures_done_prostho=".sanitize_bool('procedures_done_prostho').",
        procedures_done_endo=".sanitize_bool('procedures_done_endo').", procedures_done_ortho=".sanitize_bool('procedures_done_ortho').",
        procedures_done_others_specify='$procedures_done_others_specify', complications='$complications',
        physician_name='$physician_name', physician_address='$physician_address', 
        physician_phone='$physician_phone', last_physical_exam=$last_physical_exam,
        blood_pressure='$blood_pressure', respiratory_rate='$respiratory_rate', pulse_rate='$pulse_rate', temperature='$temperature'
        WHERE patient_id=$patient_id";
    if (!$conn->query($sql_patient)) { throw new Exception("Error updating patient record: " . $conn->error); }

    // Sanitize 'medical_history' table data
    $medical_treatment_details = $conn->real_escape_string($_POST['medical_treatment_details']);
    $illness_operation_details = $conn->real_escape_string($_POST['illness_operation_details']);
    $hospitalization_details = $conn->real_escape_string($_POST['hospitalization_details']);
    $medication_details = $conn->real_escape_string($_POST['medication_details']);
    $diet_details = $conn->real_escape_string($_POST['diet_details']);
    $alcohol_frequency = $conn->real_escape_string($_POST['alcohol_frequency']);
    $tobacco_details = $conn->real_escape_string($_POST['tobacco_details']);
    $allergic_others_details = $conn->real_escape_string($_POST['allergic_others_details']);
    $allergy_reaction_details = $conn->real_escape_string($_POST['allergy_reaction_details']);
    $other_diseases_details = $conn->real_escape_string($_POST['other_diseases_details']);
    $other_conditions_to_know = $conn->real_escape_string($_POST['other_conditions_to_know']); 

    $sql_history = "UPDATE medical_history SET 
        are_you_in_good_health=" . intval($_POST['are_you_in_good_health']) . ", is_under_medical_treatment=" . intval($_POST['is_under_medical_treatment']) . ", medical_treatment_details='$medical_treatment_details',
        had_serious_illness_or_operation=" . intval($_POST['had_serious_illness_or_operation']) . ", illness_operation_details='$illness_operation_details',
        has_been_hospitalized=" . intval($_POST['has_been_hospitalized']) . ", hospitalization_details='$hospitalization_details',
        is_taking_medication=" . intval($_POST['is_taking_medication']) . ", medication_details='$medication_details',
        is_on_diet=" . intval($_POST['is_on_diet']) . ", diet_details='$diet_details',
        drinks_alcoholic_beverages=" . intval($_POST['drinks_alcoholic_beverages']) . ", alcohol_frequency='$alcohol_frequency',
        uses_tobacco=" . intval($_POST['uses_tobacco']) . ", tobacco_details='$tobacco_details',
        allergic_anesthetics=" . sanitize_bool('allergic_anesthetics') . ", allergic_penicillin=" . sanitize_bool('allergic_penicillin') . ",
        allergic_latex=" . sanitize_bool('allergic_latex') . ", allergic_aspirin=" . sanitize_bool('allergic_aspirin') . ", allergic_others_details='$allergic_others_details', allergy_reaction_details='$allergy_reaction_details',
        has_high_blood_pressure=" . sanitize_bool('has_high_blood_pressure') . ", has_low_blood_pressure=" . sanitize_bool('has_low_blood_pressure') . ",
        has_epilepsy_convulsions=" . sanitize_bool('has_epilepsy_convulsions') . ", has_aids_hiv=" . sanitize_bool('has_aids_hiv') . ",
        has_stomach_troubles_ulcer=" . sanitize_bool('has_stomach_troubles_ulcer') . ", has_fainting_seizure=" . sanitize_bool('has_fainting_seizure') . ",
        has_rapid_weight_loss=" . sanitize_bool('has_rapid_weight_loss') . ", had_radiation_therapy=" . sanitize_bool('had_radiation_therapy') . ",
        has_joint_replacement_implant=" . sanitize_bool('has_joint_replacement_implant') . ", had_heart_surgery=" . sanitize_bool('had_heart_surgery') . ",
        had_heart_attack=" . sanitize_bool('had_heart_attack') . ", has_heart_disease=" . sanitize_bool('has_heart_disease') . ", has_heart_murmur=" . sanitize_bool('has_heart_murmur') . ",
        has_rheumatic_fever_disease=" . sanitize_bool('has_rheumatic_fever_disease') . ", has_hay_fever_allergies=" . sanitize_bool('has_hay_fever_allergies') . ",
        has_respiratory_problems=" . sanitize_bool('has_respiratory_problems') . ", has_hepatitis_jaundice=" . sanitize_bool('has_hepatitis_jaundice') . ",
        has_tuberculosis=" . sanitize_bool('has_tuberculosis') . ", has_swollen_ankles=" . sanitize_bool('has_swollen_ankles') . ", has_kidney_disease=" . sanitize_bool('has_kidney_disease') . ",
        has_diabetes=" . sanitize_bool('has_diabetes') . ", has_bleeding_blood_disease=" . sanitize_bool('has_bleeding_blood_disease') . ",
        has_arthritis_rheumatism=" . sanitize_bool('has_arthritis_rheumatism') . ", has_cancer_tumor=" . sanitize_bool('has_cancer_tumor') . ", has_anemia=" . sanitize_bool('has_anemia') . ",
        has_angina=" . sanitize_bool('has_angina') . ", has_asthma=" . sanitize_bool('has_asthma') . ", has_thyroid_problem=" . sanitize_bool('has_thyroid_problem') . ",
        has_emphysema=" . sanitize_bool('has_emphysema') . ", has_breathing_problems=" . sanitize_bool('has_breathing_problems') . ",
        had_stroke=" . sanitize_bool('had_stroke') . ", has_chest_pain=" . sanitize_bool('has_chest_pain') . ", 
        other_diseases_details='$other_diseases_details', other_conditions_to_know='$other_conditions_to_know'
        WHERE patient_id=$patient_id";
    if (!$conn->query($sql_history)) { throw new Exception("Error updating medical history: " . $conn->error); }
    
    $conn->commit();
    $_SESSION['message'] = "Success! Patient record has been updated.";
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = "Update Failed! An error occurred. <br><strong>Error:</strong> " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
} finally {
    $conn->autocommit(TRUE);
    $conn->close();
    header("Location: patient_view.php?id=" . $patient_id);
    exit();
}
?>
