<?php
// FILE: admin/ajax_get_appointment_details.php
session_start();
require '../includes/db_connect.php';

// Set the header to return a JSON response
header('Content-Type: application/json');

// Security Check: Ensure user is logged in and an ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$appointment_id = intval($_GET['id']);

// Prepare and execute the query to fetch full details for ONE appointment
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Send the data back as a JSON object
echo json_encode($data);

// Clean up
$stmt->close();
$conn->close();
?>