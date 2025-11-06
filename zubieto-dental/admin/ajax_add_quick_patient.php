<?php
// FILE: admin/ajax_add_quick_patient.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$mobile_no = trim($_POST['mobile_no']);
$email = trim($_POST['email']); // Get the email
$branch = $_SESSION['branch']; // Assign patient to the current user's branch

if (empty($first_name) || empty($last_name) || empty($mobile_no)) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, and mobile number are required.']);
    exit;
}

// Use a transaction to ensure both inserts succeed or fail together
$conn->begin_transaction();

try {
    // Prepare SQL to insert a minimal patient record, now including email
    $sql_patient = "INSERT INTO patients (first_name, last_name, mobile_no, email, branch, birthdate, age, gender, civil_status, address, chief_complaint, history_of_present_illness) VALUES (?, ?, ?, ?, ?, '1900-01-01', 0, 'M', 'N/A', 'N/A', 'For Registration', 'For Registration')";
    $stmt_patient = $conn->prepare($sql_patient);
    $stmt_patient->bind_param("sssss", $first_name, $last_name, $mobile_no, $email, $branch);
    $stmt_patient->execute();
    
    $new_patient_id = $conn->insert_id;
    if ($new_patient_id == 0) throw new Exception("Failed to create patient record.");

    // Create a minimal medical history entry
    $sql_history = "INSERT INTO medical_history (patient_id, are_you_in_good_health, is_under_medical_treatment, had_serious_illness_or_operation, has_been_hospitalized, is_taking_medication, is_on_diet, drinks_alcoholic_beverages, uses_tobacco) VALUES (?, 1, 0, 0, 0, 0, 0, 0, 0)";
    $stmt_history = $conn->prepare($sql_history);
    $stmt_history->bind_param("i", $new_patient_id);
    $stmt_history->execute();

    // If all good, commit the transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'new_patient_id' => $new_patient_id,
        'full_name' => $last_name . ', ' . $first_name,
        'branch' => $branch
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}

$conn->close();
?>