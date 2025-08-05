<?php
// FILE: admin/treatment_add_process.php
session_start();
require '../includes/db_connect.php';

// Security check
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get data from the form
$patient_id = intval($_POST['patient_id']);
$dentist_id = intval($_POST['dentist_id']);
$procedure_date = $_POST['procedure_date'];
$procedure_done = trim($_POST['procedure_done']);
$tooth_no = trim($_POST['tooth_no']);
$amount_charged = floatval($_POST['amount_charged']);
$amount_paid = floatval($_POST['amount_paid']);
// Always recalculate balance on the server for data integrity
$balance = $amount_charged - $amount_paid;
$next_appt = !empty($_POST['next_appt']) ? $_POST['next_appt'] : NULL;

// Validate required fields
if (empty($patient_id) || empty($procedure_date) || empty($procedure_done)) {
    $_SESSION['message'] = "Date and Procedure Done are required fields.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patient_view.php?id=" . $patient_id);
    exit();
}

// Prepare the SQL INSERT statement
$sql = "INSERT INTO treatment_records (patient_id, dentist_id, procedure_date, procedure_done, tooth_no, amount_charged, amount_paid, balance, next_appt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
// "d" is for double/float types, "s" for string, "i" for integer
$stmt->bind_param("iisssddds", $patient_id, $dentist_id, $procedure_date, $procedure_done, $tooth_no, $amount_charged, $amount_paid, $balance, $next_appt);

// Execute and provide feedback
if ($stmt->execute()) {
    $_SESSION['message'] = "New treatment record added successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error adding treatment record: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

// Redirect back to the patient view page
header("Location: patient_view.php?id=" . $patient_id);
exit();
?>