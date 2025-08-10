<?php
// FILE: admin/finding_delete_process.php
session_start();
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../login.php");
    exit();
}

$finding_id = intval($_GET['id']);
$patient_id = intval($_GET['patient_id']); // For redirecting back

$sql = "DELETE FROM clinical_findings WHERE finding_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $finding_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Clinical finding deleted successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error deleting finding: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

header("Location: patient_view.php?id=" . $patient_id . "&tab=findings");
exit();
?>