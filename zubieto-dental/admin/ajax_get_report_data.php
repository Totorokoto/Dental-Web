<?php
// FILE: admin/ajax_get_report_data.php
session_start();
require '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// --- Get and Validate Inputs ---
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$branch = $_POST['branch'] ?? 'All';

if (!$start_date || !$end_date) {
    echo json_encode(['success' => false, 'message' => 'Start date and end date are required.']);
    exit;
}

$response_data = [];

try {
    // --- Build Dynamic WHERE clauses for branch filtering ---
    $branch_condition_treatments = "";
    $branch_condition_patients_where_clause = "";
    $branch_condition_appts = "";
    $params = [$start_date, $end_date];
    $types = "ss";

    if ($branch !== 'All') {
        $branch_condition_treatments = " AND p.branch = ?";
        $branch_condition_appts = " AND p.branch = ?";
        $params[] = $branch;
        $types .= "s";
    }

    // 1. Get Revenue Data
    $revenue_sql = "SELECT SUM(tr.amount_paid) as total_revenue FROM treatment_records tr JOIN patients p ON tr.patient_id = p.patient_id WHERE tr.procedure_date BETWEEN ? AND ? $branch_condition_treatments";
    $stmt = $conn->prepare($revenue_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $response_data['total_revenue'] = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

    // 2. Get Total Outstanding Balance
    $balance_sql = "SELECT SUM(tr.balance) as total_balance FROM treatment_records tr JOIN patients p ON tr.patient_id = p.patient_id";
    if ($branch !== 'All') {
        $balance_sql .= " WHERE p.branch = ?";
        $stmt = $conn->prepare($balance_sql);
        $stmt->bind_param("s", $branch);
    } else {
        $stmt = $conn->prepare($balance_sql);
    }
    $stmt->execute();
    $response_data['outstanding_balance'] = $stmt->get_result()->fetch_assoc()['total_balance'] ?? 0;

    // 3. Get New Patient Count
    $patients_sql = "SELECT COUNT(p.patient_id) as total_new FROM patients p";
    $patient_params = [];
    $patient_types = "";
    $patient_where_clauses = [];

    $patient_where_clauses[] = "DATE(p.registration_date) BETWEEN ? AND ?";
    $patient_params[] = $start_date;
    $patient_params[] = $end_date;
    $patient_types .= "ss";

    if ($branch !== 'All') {
        $patient_where_clauses[] = "p.branch = ?";
        $patient_params[] = $branch;
        $patient_types .= "s";
    }
    
    // =========================================================================
    // THE FIX IS HERE: The base SQL query was missing before the WHERE clause.
    // This now correctly constructs the full SQL statement.
    // =========================================================================
    $patients_sql .= " WHERE " . implode(" AND ", $patient_where_clauses);
    $stmt = $conn->prepare($patients_sql);
    $stmt->bind_param($patient_types, ...$patient_params);
    $stmt->execute();
    $response_data['new_patients'] = $stmt->get_result()->fetch_assoc()['total_new'] ?? 0;


    // 4. Get Appointment Status Breakdown
    $appts_sql = "SELECT a.status, COUNT(a.appointment_id) as count FROM appointments a JOIN patients p ON a.patient_id = p.patient_id WHERE DATE(a.appointment_date) BETWEEN ? AND ? $branch_condition_appts GROUP BY a.status";
    $stmt = $conn->prepare($appts_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $appts_result = $stmt->get_result();
    $appointment_statuses = ['Scheduled' => 0, 'Completed' => 0, 'Cancelled' => 0, 'No-Show' => 0];
    while ($row = $appts_result->fetch_assoc()) {
        if (array_key_exists($row['status'], $appointment_statuses)) {
            $appointment_statuses[$row['status']] = $row['count'];
        }
    }
    $response_data['appointment_statuses'] = $appointment_statuses;
    
    // 5. Get Top 5 Most Performed Procedures
    $procedures_sql = "SELECT tr.procedure_done, COUNT(tr.record_id) as count FROM treatment_records tr JOIN patients p ON tr.patient_id = p.patient_id WHERE tr.procedure_date BETWEEN ? AND ? $branch_condition_treatments GROUP BY tr.procedure_done ORDER BY count DESC LIMIT 5";
    $stmt = $conn->prepare($procedures_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $procedures_result = $stmt->get_result();
    $top_procedures = [];
    while ($row = $procedures_result->fetch_assoc()) {
        $top_procedures[] = $row;
    }
    $response_data['top_procedures'] = $top_procedures;


    // Send successful response
    echo json_encode(['success' => true, 'data' => $response_data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
}

$conn->close();
?>