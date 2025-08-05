<?php
// FILE: admin/finding_edit_process.php
session_start();
require '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get and sanitize data from the form
$finding_id = intval($_POST['finding_id']);
$patient_id = intval($_POST['patient_id']); // For redirecting back
$finding_date = $_POST['finding_date'];
$clinical_findings = trim($_POST['clinical_findings']);
$diagnosis = trim($_POST['diagnosis']);
$proposed_treatment = trim($_POST['proposed_treatment']);
$remarks = trim($_POST['remarks']);

// Prepare the SQL UPDATE statement
$sql = "UPDATE clinical_findings SET finding_date=?, clinical_findings=?, diagnosis=?, proposed_treatment=?, remarks=? WHERE finding_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $finding_date, $clinical_findings, $diagnosis, $proposed_treatment, $remarks, $finding_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Clinical finding updated successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error updating finding: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

header("Location: patient_view.php?id=" . $patient_id);
exit();
?>