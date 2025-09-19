<?php
// FILE: admin/appointment_add_process.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$patient_id = intval($_POST['patient_id']);
$dentist_id = intval($_POST['dentist_id']);
$appointment_date = $_POST['appointment_date']; // This is the start time
$service_description = trim($_POST['service_description']);
$status = $_POST['status'];

if(empty($patient_id) || empty($dentist_id) || empty($appointment_date) || empty($service_description)){
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}


// **CHANGED**: The SQL query no longer includes the `appointment_end_time` column.
$stmt = $conn->prepare("INSERT INTO appointments (patient_id, dentist_id, appointment_date, service_description, status) VALUES (?, ?, ?, ?, ?)");
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

// **CHANGED**: The bind_param now only has 5 parameters ("iisss")
$stmt->bind_param("iisss", $patient_id, $dentist_id, $appointment_date, $service_description, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment created successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>