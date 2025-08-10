<?php
// FILE: admin/appointment_add_process.php
session_start();
require '../includes/db_connect.php';

// Set the header to return a JSON response, which the JavaScript expects
header('Content-Type: application/json');

// Security Check: Only allow POST requests from logged-in users
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    // Send a JSON error message and stop
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// --- Get and sanitize data from the form ---
$patient_id = intval($_POST['patient_id']);
$dentist_id = intval($_POST['dentist_id']);
$appointment_date = $_POST['appointment_date'];
$service_description = trim($_POST['service_description']);
$status = $_POST['status'];

// --- Server-side validation ---
if(empty($patient_id) || empty($dentist_id) || empty($appointment_date) || empty($service_description)){
    echo json_encode(['success' => false, 'message' => 'Patient, Dentist, Date, and Service are required fields.']);
    exit;
}

// --- Prepare and execute the SQL query to prevent SQL injection ---
$stmt = $conn->prepare("INSERT INTO appointments (patient_id, dentist_id, appointment_date, service_description, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $patient_id, $dentist_id, $appointment_date, $service_description, $status);

if ($stmt->execute()) {
    // Send a success message back to the browser
    echo json_encode(['success' => true, 'message' => 'Appointment created successfully!']);
} else {
    // Send a detailed error message back to the browser
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt->error]);
}

// --- Clean up ---
$stmt->close();
$conn->close();
?>