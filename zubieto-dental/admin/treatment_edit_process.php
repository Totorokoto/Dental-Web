<?php
// FILE: admin/treatment_edit_process.php (FINAL CORRECTED VERSION)
session_start();
require '../includes/db_connect.php';

// Security check
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
header("Location: ../login.php");
exit();
}

// Get and sanitize all the data from the form
$record_id = intval($_POST['record_id']);
$patient_id = intval($_POST['patient_id']);
$procedure_date = $_POST['procedure_date'];
$procedure_done_select = trim($_POST['procedure_done']);
$procedure_done_custom = trim($_POST['procedure_done_custom']);
$tooth_no = trim($_POST['tooth_no']);
$amount_charged = floatval($_POST['amount_charged']);
$amount_paid = floatval($_POST['amount_paid']);
$balance = $amount_charged - $amount_paid;
$next_appt = !empty($_POST['next_appt']) ? $_POST['next_appt'] : NULL;

// --- CRITICAL FIX: Determine the final procedure description ---
$procedure_done = ($procedure_done_select === 'custom' && !empty($procedure_done_custom))
? $procedure_done_custom
: $procedure_done_select;

// Prepare the SQL UPDATE statement
// Note: We are NOT updating the dentist_id here to preserve the original record-keeper.
$sql = "UPDATE treatment_records SET procedure_date=?, procedure_done=?, tooth_no=?, amount_charged=?, amount_paid=?, balance=?, next_appt=? WHERE record_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssdddsi", $procedure_date, $procedure_done, $tooth_no, $amount_charged, $amount_paid, $balance, $next_appt, $record_id);

// Execute the statement and set feedback
if ($stmt->execute()) {
$_SESSION['message'] = "Treatment record updated successfully.";
$_SESSION['message_type'] = 'success';
} else {
$_SESSION['message'] = "Error updating treatment record: " . $stmt->error;
$_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

// Get the active tab from the form submission, default to 'profile'
$active_tab = isset($_POST['tab_redirect']) ? htmlspecialchars($_POST['tab_redirect']) : 'profile';

// Build the redirect URL
$redirect_url = "patient_view.php?id=" . $patient_id . "&tab=" . $active_tab;

// Redirect
header("Location: " . $redirect_url);
exit();