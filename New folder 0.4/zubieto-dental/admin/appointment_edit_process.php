<?php
// FILE: admin/appointment_edit_process.php
session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$appointment_id = intval($_POST['appointment_id']);

// Handle drag-and-drop update
if (isset($_POST['drag'])) {
    $appointment_date = $_POST['appointment_date'];
    $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ? WHERE appointment_id = ?");
    $stmt->bind_param("si", $appointment_date, $appointment_id);
} else {
    // Handle full form edit
    $patient_id = intval($_POST['patient_id']);
    $dentist_id = intval($_POST['dentist_id']);
    $appointment_date = $_POST['appointment_date'];
    $service_description = trim($_POST['service_description']);
    $status = $_POST['status'];

    if(empty($patient_id) || empty($dentist_id) || empty($appointment_date) || empty($service_description)){
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE appointments SET patient_id=?, dentist_id=?, appointment_date=?, service_description=?, status=? WHERE appointment_id=?");
    $stmt->bind_param("iisssi", $patient_id, $dentist_id, $appointment_date, $service_description, $status, $appointment_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}
$stmt->close();
?>