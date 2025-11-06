<?php
// FILE: admin/ajax_get_treatment.php
session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Security Check: Ensure the user is logged in and an ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized access or missing ID.']);
    exit;
}

$record_id = intval($_GET['id']);

if ($record_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid record ID.']);
    exit;
}

$sql = "SELECT * FROM treatment_records WHERE record_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode($data);
} else {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Treatment record not found.']);
}

$stmt->close();
$conn->close();
?>