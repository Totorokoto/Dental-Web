<?php
require 'includes/db_connect.php';
header('Content-Type: application/json');

// --- Input Validation ---
$required_fields = ['first_name', 'last_name', 'mobile_no', 'email', 'branch', 'service_description', 'preferred_date', 'preferred_time'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

// --- Data Gathering ---
$branch = $_POST['branch'];
if ($branch !== 'Sta. Rosa' && $branch !== 'Lucban') {
    echo json_encode(['success' => false, 'message' => 'Invalid branch selected.']);
    exit;
}

$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$mobile_no = trim($_POST['mobile_no']);
$email = trim($_POST['email']);
$preferred_date = $_POST['preferred_date'];
$preferred_time = $_POST['preferred_time'];

// --- Logic for Reason for Visit ---
$service_select = trim($_POST['service_description']);
$service_other = trim($_POST['service_description_other']);

$service = "PATIENT REQUEST: ";
if ($service_select === 'Other' && !empty($service_other)) {
    $service .= $service_other; // Use the text from the 'other' box
} else {
    $service .= $service_select; // Use the value from the dropdown
}
// ---

$patient_id = null;

// --- DUPLICATE CHECK ---
$stmt_check = $conn->prepare("SELECT patient_id FROM patients WHERE email = ? LIMIT 1");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows > 0) {
    $existing_patient = $result_check->fetch_assoc();
    $patient_id = $existing_patient['patient_id'];
    
    // Also update the mobile number if it has been changed by an existing patient
    $stmt_update_mobile = $conn->prepare("UPDATE patients SET mobile_no = ? WHERE patient_id = ?");
    $stmt_update_mobile->bind_param("si", $mobile_no, $patient_id);
    $stmt_update_mobile->execute();
    $stmt_update_mobile->close();

}
$stmt_check->close();

$conn->begin_transaction();
try {
    if (is_null($patient_id)) {
        // Create new minimal patient record
        $sql_patient = "INSERT INTO patients (first_name, last_name, mobile_no, email, branch, birthdate, chief_complaint) VALUES (?, ?, ?, ?, ?, '1900-01-01', 'Online Request')";
        $stmt_patient = $conn->prepare($sql_patient);
        $stmt_patient->bind_param("sssss", $first_name, $last_name, $mobile_no, $email, $branch);
        $stmt_patient->execute();
        $patient_id = $conn->insert_id;
        if ($patient_id == 0) throw new Exception("Failed to create new patient record.");

        $sql_history = "INSERT INTO medical_history (patient_id, are_you_in_good_health) VALUES (?, 1)";
        $stmt_history = $conn->prepare($sql_history);
        $stmt_history->bind_param("i", $patient_id);
        $stmt_history->execute();
    }

    // --- Create PENDING appointment ---
    $triage_dentist_id = 1; // Default dentist for triaging requests
    $status = 'Pending Approval';
    $appointment_date = $preferred_date . ' ' . $preferred_time; 

    $sql_appt = "INSERT INTO appointments (patient_id, dentist_id, appointment_date, service_description, status) VALUES (?, ?, ?, ?, ?)";
    $stmt_appt = $conn->prepare($sql_appt);
    $stmt_appt->bind_param("iisss", $patient_id, $triage_dentist_id, $appointment_date, $service, $status);
    $stmt_appt->execute();
    if($stmt_appt->affected_rows == 0) throw new Exception("Failed to create appointment request.");

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Thank you! Your request has been submitted. Our staff will contact you shortly to confirm your appointment. The page will now reload.']);
} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage()); // Log the specific error to the server logs
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again.']);
}

$conn->close();
?>