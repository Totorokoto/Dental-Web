<?php
// FILE: admin/appointment_delete_process.php
session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$appointment_id = intval($_POST['appointment_id']);

$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}
$stmt->close();
?>