<?php
// FILE: admin/ajax_get_appointments.php

error_reporting(0); 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not authenticated.']);
    exit;
}

$user_branch = $_SESSION['branch'];
$user_role = $_SESSION['role'];

// --- MODIFIED SQL QUERY ---
// This now includes a special condition for Admins to see all pending approvals.
$sql = "(
    SELECT 
        a.appointment_id as id,
        a.appointment_date as start,
        DATE_ADD(a.appointment_date, INTERVAL 1 HOUR) as `end`,
        a.status,
        p.first_name,
        p.last_name,
        a.service_description as service_name,
        'standard' as event_type
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE 
        p.branch = ? ";

// If the user is an Admin, also show them any appointments pending approval from ANY branch.
if ($user_role === 'Admin') {
    $sql .= " OR a.status = 'Pending Approval' ";
}

$sql .= ")
UNION ALL
(
    SELECT 
        CONCAT('followup-', tr.record_id) as id,
        tr.next_appt as start,
        tr.next_appt as `end`,
        'Scheduled' as status,
        p.first_name,
        p.last_name,
        CONCAT('Follow-up for: ', tr.procedure_done) as service_name,
        'followup' as event_type
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    WHERE p.branch = ? AND tr.next_appt IS NOT NULL AND tr.next_appt > '1970-01-01'
)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed to prepare: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $user_branch, $user_branch);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $isFollowUp = ($row['event_type'] === 'followup');
    
    $title = htmlspecialchars($row['last_name'] . ', ' . $row['first_name']);
    
    if ($isFollowUp) {
        $events[] = [
            'id'        => $row['id'],
            'title'     => 'Follow-up: ' . htmlspecialchars($row['last_name']),
            'start'     => $row['start'],
            'allDay'    => true,
            'color'     => '#8e44ad',
            'extendedProps' => [
                'isFollowUp'    => true, 
                'description'   => htmlspecialchars($row['service_name'])
            ]
        ];
    } else {
        // --- MODIFIED COLOR LOGIC ---
        $color = '#0d6efd'; // Default Blue for Scheduled
        if ($row['status'] == 'Completed') $color = '#198754'; // Green
        if ($row['status'] == 'Cancelled') $color = '#6c757d'; // Gray
        if ($row['status'] == 'No-Show') $color = '#dc3545';   // Red
        if ($row['status'] == 'Pending Approval') $color = '#ffc107'; // Yellow for Pending

        $events[] = [
            'id'        => $row['id'],
            'title'     => $title,
            'start'     => $row['start'],
            'end'       => $row['end'],
            'backgroundColor' => $color,
            'borderColor'     => $color,
            'textColor'       => ($row['status'] == 'Pending Approval' ? '#000000' : '#ffffff'), // Black text on yellow for readability
            'extendedProps' => [
                'isFollowUp'    => false, 
                'description'   => htmlspecialchars($row['service_name'])
            ]
        ];
    }
}

echo json_encode($events);

$stmt->close();
$conn->close();
?>