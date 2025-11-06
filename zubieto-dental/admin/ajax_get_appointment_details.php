<?php
// FILE: admin/ajax_get_appointment_details.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$appointment_id = intval($_GET['id']);

//: Query now joins tables to get patient and dentist names
$sql = "
    SELECT 
        a.*, 
        p.first_name, 
        p.last_name, 
        u.full_name as dentist_name 
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN users u ON a.dentist_id = u.user_id
    WHERE a.appointment_id = ?
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare statement.']);
    exit;
}

$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    //: Construct full name for the response
    $data['patient_name'] = htmlspecialchars($data['last_name'] . ', ' . $data['first_name']);
    
    // Assign color based on status for consistency
    switch ($data['status']) {
        case 'Completed': $data['color'] = '#198754'; break;
        case 'Cancelled': $data['color'] = '#6c757d'; break;
        case 'No-Show': $data['color'] = '#dc3545'; break;
        default: $data['color'] = '#0d6efd'; break;
    }
}

// Send the data back as a JSON object
echo json_encode($data);

$stmt->close();
$conn->close();
?>