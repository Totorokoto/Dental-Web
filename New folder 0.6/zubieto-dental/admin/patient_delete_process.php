<?php
// FILE: admin/patient_delete_process.php 

session_start();
require '../includes/db_connect.php';

// --- 1. SECURITY CHECK: Only Admins can delete records ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "Access Denied: You do not have permission to delete patient records.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit();
}

// --- 2. VALIDATE THE INCOMING REQUEST ---
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id <= 0) {
    $_SESSION['message'] = "Invalid request. A valid patient ID is required.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit();
}

// --- 3. PREPARE AND EXECUTE THE DELETION ---
$sql = "DELETE FROM patients WHERE patient_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $_SESSION['message'] = "Error preparing the delete statement: " . $conn->error;
    $_SESSION['message_type'] = 'danger';
} else {
    $stmt->bind_param("i", $patient_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "The patient record has been permanently deleted.";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Warning: No patient found with the specified ID.";
            $_SESSION['message_type'] = 'warning';
        }
    } else {
        $_SESSION['message'] = "Error: Could not delete the patient. They may have linked records (appointments, etc.). Error: " . $stmt->error;
        $_SESSION['message_type'] = 'danger';
    }
    $stmt->close();
}

$conn->close();

// --- 4. REDIRECT BACK WITH A CACHE-BUSTING PARAMETER ---
// This ensures the browser always fetches a fresh copy of the patient list.
$redirect_branch = isset($_GET['branch']) ? $_GET['branch'] : 'All';
header("Location: patients.php?branch=" . urlencode($redirect_branch) . "&v=" . time());
exit();
?>