<?php
// FILE: admin/finding_add_process.php
session_start();
require '../includes/db_connect.php';

// Security check: only process POST requests from logged-in users
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get data from the form
$patient_id = intval($_POST['patient_id']);
$dentist_id = intval($_POST['dentist_id']);
$finding_date = $_POST['finding_date'];
$clinical_findings = trim($_POST['clinical_findings']);
$diagnosis = trim($_POST['diagnosis']);
$proposed_treatment = trim($_POST['proposed_treatment']);
$remarks = trim($_POST['remarks']);

// Validate required fields
if (empty($patient_id) || empty($finding_date) || empty($clinical_findings) || empty($diagnosis)) {
    $_SESSION['message'] = "Date, Findings, and Diagnosis are required fields.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patient_view.php?id=" . $patient_id);
    exit();
}

// Prepare the SQL INSERT statement to prevent SQL injection
$sql = "INSERT INTO clinical_findings (patient_id, dentist_id, finding_date, clinical_findings, diagnosis, proposed_treatment, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisssss", $patient_id, $dentist_id, $finding_date, $clinical_findings, $diagnosis, $proposed_treatment, $remarks);

// Execute and provide feedback
if ($stmt->execute()) {
    $_SESSION['message'] = "New clinical finding added successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error adding clinical finding: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

// Redirect back to the patient view page
header("Location: patient_view.php?id=" . $patient_id);
exit();
?>