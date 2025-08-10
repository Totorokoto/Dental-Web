<?php
// FILE: admin/ajax_get_finding.php
session_start();
require '../includes/db_connect.php';

// Ensure the user is logged in and an ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$finding_id = intval($_GET['id']);
$sql = "SELECT * FROM clinical_findings WHERE finding_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $finding_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Set the content type to JSON and output the data
header('Content-Type: application/json');
echo json_encode($data);
?>