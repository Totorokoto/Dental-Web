<?php
// FILE: admin/ajax_get_report_data.php
session_start();
require '../includes/db_connect.php';

// Set header to return JSON
header('Content-Type: application/json');

// Security Check: Only allow POST requests from logged-in Admins
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// --- Get and Validate Dates ---
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;

if (!$start_date || !$end_date) {
    echo json_encode(['success' => false, 'message' => 'Start date and end date are required.']);
    exit;
}

$response_data = [];

try {
    // 1. Get Revenue Data
    $revenue_sql = "SELECT SUM(amount_paid) as total_revenue FROM treatment_records WHERE procedure_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($revenue_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $revenue_result = $stmt->get_result()->fetch_assoc();
    $response_data['total_revenue'] = $revenue_result['total_revenue'] ?? 0;

    // 2. Get New Patient Count
    $patients_sql = "SELECT COUNT(patient_id) as total_new FROM patients WHERE DATE(registration_date) BETWEEN ? AND ?";
    $stmt = $conn->prepare($patients_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $patients_result = $stmt->get_result()->fetch_assoc();
    $response_data['new_patients'] = $patients_result['total_new'] ?? 0;

    // 3. Get Appointment Status Breakdown
    $appts_sql = "SELECT status, COUNT(appointment_id) as count FROM appointments WHERE DATE(appointment_date) BETWEEN ? AND ? GROUP BY status";
    $stmt = $conn->prepare($appts_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $appts_result = $stmt->get_result();
    
    $appointment_statuses = [
        'Scheduled' => 0,
        'Completed' => 0,
        'Cancelled' => 0,
        'No-Show' => 0
    ];
    while ($row = $appts_result->fetch_assoc()) {
        if (array_key_exists($row['status'], $appointment_statuses)) {
            $appointment_statuses[$row['status']] = $row['count'];
        }
    }
    $response_data['appointment_statuses'] = $appointment_statuses;

    // Send successful response
    echo json_encode(['success' => true, 'data' => $response_data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>