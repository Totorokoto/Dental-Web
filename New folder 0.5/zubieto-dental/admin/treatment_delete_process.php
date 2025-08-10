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

header("Location: patient_view.php?id=" . $patient_id . "&tab=treatments");
exit();
?>