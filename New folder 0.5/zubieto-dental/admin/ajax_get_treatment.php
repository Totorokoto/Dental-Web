<?php
// FILE: admin/ajax_get_treatment.php
session_start();
require '../includes/db_connect.php';

// Ensure the user is logged in and an ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$record_id = intval($_GET['id']);
$sql = "SELECT * FROM treatment_records WHERE record_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Set the content type to JSON and output the data
header('Content-Type: application/json');
echo json_encode($data);
?>