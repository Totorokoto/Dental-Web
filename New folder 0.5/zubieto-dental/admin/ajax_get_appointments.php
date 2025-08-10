<?php
// FILE: admin/ajax_get_appointments.php (ENHANCED WITH BRANCH FILTERING)
session_start();
require '../includes/db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    exit;
}

// --- KEY CHANGE: BUILD A DYNAMIC WHERE CLAUSE FOR BRANCH FILTERING ---
$where_clause = "";
$params = [];
$types = "";

if ($_SESSION['role'] !== 'Admin') {
    // If user is not an Admin, they can only see patients from their branch
    $where_clause = " WHERE p.branch = ? ";
    $params[] = $_SESSION['branch'];
    $types .= "s";
}

// Construct the final SQL query using the dynamic WHERE clause
// The first part of the UNION gets standard appointments
$sql = "(
    SELECT 
        a.appointment_id as id,
        a.appointment_date as start,
        a.service_description as description,
        a.status,
        p.first_name,
        p.last_name,
        u.full_name AS dentist_name,
        'standard' as event_type
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN users u ON a.dentist_id = u.user_id
    $where_clause 
)
UNION ALL
(
    SELECT 
        CONCAT('followup-', tr.record_id) as id,
        tr.next_appt as start,
        CONCAT('Follow-up for: ', tr.procedure_done) as description,
        'Scheduled' as status,
        p.first_name,
        p.last_name,
        u.full_name AS dentist_name,
        'followup' as event_type
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    LEFT JOIN users u ON tr.dentist_id = u.user_id
    " . (empty($where_clause) ? "WHERE tr.next_appt IS NOT NULL AND tr.next_appt >= CURDATE()" : $where_clause . " AND tr.next_appt IS NOT NULL AND tr.next_appt >= CURDATE()") . "
)";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    // We need to bind the branch parameter for BOTH parts of the UNION query
    $final_params = array_merge($params, $params);
    $final_types = $types . $types;
    $stmt->bind_param($final_types, ...$final_params);
}
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $dentistName = !empty($row['dentist_name']) ? htmlspecialchars($row['dentist_name']) : 'Unassigned';
    $dentistFirstName = !empty($row['dentist_name']) ? strtok($row['dentist_name'], ' ') : 'N/A';
    
    $event = [];

    // Configure the event based on its type (standard vs. followup)
    if ($row['event_type'] == 'standard') {
        $color = '#0d6efd'; // Default Blue
        if ($row['status'] == 'Completed') $color = '#198754'; // Green
        if ($row['status'] == 'Cancelled') $color = '#6c757d'; // Gray
        if ($row['status'] == 'No-Show') $color = '#dc3545';   // Red
        
        $event = [
            'id'        => $row['id'],
            'title'     => htmlspecialchars($row['last_name'] . ' w/ ' . $dentistFirstName),
            'start'     => $row['start'],
            'color'     => $color,
            'editable'  => true
        ];

    } else { // 'followup' event
        $event = [
            'id'        => $row['id'],
            'title'     => 'Follow-up: ' . htmlspecialchars($row['last_name']),
            'start'     => $row['start'],
            'color'     => '#8e44ad', // Purple
            'editable'  => false
        ];
    }

    // Add extended properties for the tooltip, works for both types
    $event['extendedProps'] = [
        'patientName'   => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        'dentistName'   => $dentistName,
        'description'   => htmlspecialchars($row['description']),
        'status'        => htmlspecialchars($row['status'])
    ];

    $events[] = $event;
}

// Set the header and output the JSON data
header('Content-Type: application/json');
echo json_encode($events);

$stmt->close();
$conn->close();
?>