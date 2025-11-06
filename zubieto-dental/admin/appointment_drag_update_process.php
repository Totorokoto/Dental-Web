<?php
// FILE: admin/appointment_drag_update_process.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Security: Ensure it's a POST request from a logged-in user
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get and validate the input from the AJAX call
$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$new_date = isset($_POST['new_date']) ? $_POST['new_date'] : null;

if ($appointment_id <= 0 || is_null($new_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

// Prepare the SQL statement to update the appointment date
$sql = "UPDATE appointments SET appointment_date = ? WHERE appointment_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

// Bind parameters and execute
$stmt->bind_param("si", $new_date, $appointment_id);

if ($stmt->execute()) {
    // Optional: Log the change
    // You can add a log entry here if you have an activity_logs table
    echo json_encode(['success' => true, 'message' => 'Appointment rescheduled successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database Error: Could not update the appointment. ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>