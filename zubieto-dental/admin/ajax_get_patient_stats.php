<?php
// FILE: admin/ajax_get_patient_stats.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$patient_id = intval($_GET['id']);
$response_data = [];

try {
    // 1. Get outstanding balance
    $balance_sql = "SELECT SUM(balance) as total_balance FROM treatment_records WHERE patient_id = ?";
    $stmt = $conn->prepare($balance_sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $balance_result = $stmt->get_result()->fetch_assoc();
    $response_data['outstanding_balance'] = $balance_result['total_balance'] ?? 0;

    // 2. Get last visit date from completed appointments
    $visit_sql = "SELECT MAX(appointment_date) as last_visit FROM appointments WHERE patient_id = ? AND status = 'Completed'";
    $stmt = $conn->prepare($visit_sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $visit_result = $stmt->get_result()->fetch_assoc();
    $response_data['last_visit'] = $visit_result['last_visit'] ? date('M d, Y', strtotime($visit_result['last_visit'])) : null;

    echo json_encode(['success' => true, 'data' => $response_data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database query failed.']);
}

$conn->close();
?>