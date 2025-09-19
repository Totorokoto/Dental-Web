<?php
// FILE: admin/ajax_get_appointments.php 

// This is a failsafe to prevent any stray PHP notices from breaking the JSON output.
error_reporting(0); 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not authenticated.']);
    exit;
}

// Get the logged-in user's branch
$user_branch = $_SESSION['branch'];

// --- SQL QUERY USING UNION ALL ---
// This query combines standard appointments with follow-ups from treatment records.
$sql = "(
    SELECT 
        a.appointment_id as id,
        a.appointment_date as start,
        -- All standard appointments are 1 hour long
        DATE_ADD(a.appointment_date, INTERVAL 1 HOUR) as `end`,
        a.status,
        p.first_name,
        p.last_name,
        a.service_description as service_name,
        'standard' as event_type
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE p.branch = ?
)
UNION ALL
(
    -- Part 2: Get all follow-up dates from treatment records for the same branch
    SELECT 
        CONCAT('followup-', tr.record_id) as id,
        tr.next_appt as start,
        -- Follow-ups are given a 30min duration for calendar display
        DATE_ADD(tr.next_appt, INTERVAL 30 MINUTE) as `end`, 
        'Scheduled' as status,
        p.first_name,
        p.last_name,
        CONCAT('Follow-up for: ', tr.procedure_done) as service_name,
        'followup' as event_type
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    WHERE p.branch = ? AND tr.next_appt IS NOT NULL
)";

$stmt = $conn->prepare($sql);

// **CRITICAL FIX**: Check if the SQL statement failed to prepare.
// If it fails, send back a valid JSON error instead of letting PHP crash.
if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database query failed to prepare: ' . $conn->error]);
    exit;
}

// We need to bind the branch parameter twice, once for each part of the UNION
$stmt->bind_param("ss", $user_branch, $user_branch);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $isFollowUp = ($row['event_type'] === 'followup');
    
    // Default color for standard "Scheduled" appointments
    $color = '#0d6efd'; // Blue

    if ($isFollowUp) {
        $color = '#8e44ad'; // Purple for Follow-ups
        $title = 'Follow-up: ' . htmlspecialchars($row['last_name']);
    } else {
        // Color-coding for standard appointments based on status
        if ($row['status'] == 'Completed') $color = '#198754'; // Green
        if ($row['status'] == 'Cancelled') $color = '#6c757d'; // Gray
        if ($row['status'] == 'No-Show') $color = '#dc3545';   // Red
        $title = htmlspecialchars($row['last_name'] . ', ' . $row['first_name']);
    }
    
    $events[] = [
        'id'        => $row['id'],
        'title'     => $title,
        'start'     => $row['start'],
        'end'       => $row['end'],
        'color'     => $color,
        'extendedProps' => [
            'isFollowUp'    => $isFollowUp, 
            'description'   => htmlspecialchars($row['service_name'])
        ]
    ];
}

echo json_encode($events);

$stmt->close();
$conn->close();
?>