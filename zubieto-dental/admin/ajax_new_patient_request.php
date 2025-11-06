<?php
require 'includes/db_connect.php';
header('Content-Type: application/json');

// --- Basic Input Validation ---
if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['mobile_no']) || empty($_POST['email']) || empty($_POST['service_description'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$mobile_no = trim($_POST['mobile_no']);
$email = trim($_POST['email']);
$service = trim($_POST['service_description']);

// --- Prevent Duplicate Patients ---
$stmt_check = $conn->prepare("SELECT patient_id FROM patients WHERE email = ? OR mobile_no = ?");
$stmt_check->bind_param("ss", $email, $mobile_no);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'An account with this email or mobile number already exists. Please use the "Existing Patient" option to log in.']);
    exit;
}
$stmt_check->close();

$conn->begin_transaction();

try {
    // --- Create a MINIMAL patient record ---
    // We assume a default branch or you can add a dropdown in the form
    $branch = 'Sta. Rosa'; // Or 'Lucban' or make it selectable
    $sql_patient = "INSERT INTO patients (first_name, last_name, mobile_no, email, branch, birthdate, chief_complaint) VALUES (?, ?, ?, ?, ?, '1900-01-01', 'For Triage')";
    $stmt_patient = $conn->prepare($sql_patient);
    $stmt_patient->bind_param("sssss", $first_name, $last_name, $mobile_no, $email, $branch);
    $stmt_patient->execute();
    
    $new_patient_id = $conn->insert_id;
    if ($new_patient_id == 0) throw new Exception("Failed to create patient record.");

    // --- Create a PENDING appointment for them ---
    // We assign it to a default dentist/admin (e.g., user_id 1) for triage
    $triage_dentist_id = 1; 
    $status = 'Pending Approval';
    $appointment_date = date('Y-m-d H:i:s'); // Set to now, staff will reschedule

    $sql_appt = "INSERT INTO appointments (patient_id, dentist_id, appointment_date, service_description, status) VALUES (?, ?, ?, ?, ?)";
    $stmt_appt = $conn->prepare($sql_appt);
    $stmt_appt->bind_param("iisss", $new_patient_id, $triage_dentist_id, $appointment_date, $service, $status);
    $stmt_appt->execute();
    
    // --- Create a minimal medical history to prevent errors in patient_view ---
    $sql_history = "INSERT INTO medical_history (patient_id, are_you_in_good_health) VALUES (?, 1)";
    $stmt_history = $conn->prepare($sql_history);
    $stmt_history->bind_param("i", $new_patient_id);
    $stmt_history->execute();

    // If all good, commit the transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Thank you! Your request has been submitted. Our staff will contact you shortly to confirm your appointment time and details.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}

$conn->close();
?>