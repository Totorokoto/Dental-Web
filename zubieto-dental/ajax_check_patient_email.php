<?php
// FILE: ajax_check_patient_email.php

require 'includes/db_connect.php';
header('Content-Type: application/json');

// Ensure an email was provided
if (empty($_GET['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

$email = trim($_GET['email']);

// Prepare to query the database for the patient, now including the branch
$sql = "SELECT first_name, last_name, mobile_no, branch FROM patients WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Patient was found, return their data
    $patient = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'found' => true,
        'patient' => $patient
    ]);
} else {
    // Patient was not found
    echo json_encode([
        'success' => true,
        'found' => false
    ]);
}

$stmt->close();
$conn->close();
?>