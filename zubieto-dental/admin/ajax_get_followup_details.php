<?php
// FILE: admin/ajax_get_followup_details.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$followup_id_str = $_GET['id'];

// The ID from the calendar is in the format "followup-123". We need to extract the number.
if (sscanf($followup_id_str, 'followup-%d', $record_id) !== 1) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid follow-up ID format.']);
    exit;
}

// Query the treatment_records table to get details for the follow-up
$sql = "
    SELECT 
        tr.next_appt AS appointment_date,
        tr.procedure_done,
        tr.patient_id,
        p.first_name, 
        p.last_name, 
        u.full_name as dentist_name 
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    LEFT JOIN users u ON tr.dentist_id = u.user_id
    WHERE tr.record_id = ?
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare statement.']);
    exit;
}

$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    // Construct the patient name and service description for the response
    $data['patient_name'] = htmlspecialchars($data['last_name'] . ', ' . $data['first_name']);
    $data['service_description'] = 'Follow-up for: ' . htmlspecialchars($data['procedure_done']);
    
    // Assign a status and color for display consistency
    $data['status'] = 'Follow-up';
    $data['color'] = '#8e44ad'; // Purple, same as in the calendar
}

// Send the data back as a JSON object
echo json_encode($data);

$stmt->close();
$conn->close();
?>