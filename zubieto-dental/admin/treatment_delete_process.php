<?php
// FILE: admin/treatment_delete_process.php
session_start();
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../login.php");
    exit();
}

$record_id = intval($_GET['id']);
$patient_id = intval($_GET['patient_id']); // For redirecting

$sql = "DELETE FROM treatment_records WHERE record_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $record_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Treatment record deleted successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error deleting treatment record: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();
// Get the active tab from the form submission, default to 'profile'
$active_tab = isset($_POST['tab_redirect']) ? htmlspecialchars($_POST['tab_redirect']) : 'profile';

// Build the redirect URL
$redirect_url = "patient_view.php?id=" . $patient_id . "&tab=" . $active_tab;

// Redirect
$_SESSION['message'] = "Clinical finding deleted successfully.";
$_SESSION['message_type'] = 'success';

// Get the patient ID from the URL
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Get the tab to redirect back to from the URL, default to 'findings'
$active_tab = isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'findings';

// Build the final redirect URL
$redirect_url = "patient_view.php?id=" . $patient_id . "&tab=" . $active_tab;

// Redirect
header("Location: " . $redirect_url);
exit();
?>