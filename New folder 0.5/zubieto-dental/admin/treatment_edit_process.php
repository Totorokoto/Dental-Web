<?php
// FILE: admin/treatment_edit_process.php (CORRECTED NON-AJAX VERSION)
session_start();
require '../includes/db_connect.php';

// Security check: Only allow POST requests from logged-in users
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    // If not, redirect to the login page
    header("Location: ../login.php");
    exit();
}

// Get and sanitize all the data from the form
$record_id = intval($_POST['record_id']);
$patient_id = intval($_POST['patient_id']); // Essential for redirecting back to the correct patient
$procedure_date = $_POST['procedure_date'];
$procedure_done = trim($_POST['procedure_done']);
$tooth_no = trim($_POST['tooth_no']);
$amount_charged = floatval($_POST['amount_charged']);
$amount_paid = floatval($_POST['amount_paid']);
$balance = $amount_charged - $amount_paid; // Always recalculate on the server for accuracy
$next_appt = !empty($_POST['next_appt']) ? $_POST['next_appt'] : NULL;

// Prepare the SQL UPDATE statement to prevent SQL injection
$sql = "UPDATE treatment_records SET procedure_date=?, procedure_done=?, tooth_no=?, amount_charged=?, amount_paid=?, balance=?, next_appt=? WHERE record_id=?";
$stmt = $conn->prepare($sql);

// The type string must have 8 characters to match the 8 variables
$stmt->bind_param("sssdddsi", $procedure_date, $procedure_done, $tooth_no, $amount_charged, $amount_paid, $balance, $next_appt, $record_id);

// Execute the statement and set a feedback message in the session
if ($stmt->execute()) {
    $_SESSION['message'] = "Treatment record updated successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error updating treatment record: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

// Clean up
$stmt->close();
$conn->close();

// =========================================================================
// THE FIX IS HERE: Redirect the user back to the patient view page.
// The feedback message will be displayed at the top of that page.
// =========================================================================
header("Location: patient_view.php?id=" . $patient_id . "&tab=treatments");
exit();
?>