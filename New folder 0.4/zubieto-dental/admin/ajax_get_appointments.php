<?php
// FILE: admin/ajax_get_appointments.php (ENHANCED TO INCLUDE FOLLOW-UPS)
session_start();
require '../includes/db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    exit;
}

// =========================================================================
// THE CORE CHANGE IS HERE: We use UNION ALL to combine two separate queries.
// Query 1: Fetches all regular appointments from the 'appointments' table.
// Query 2: Fetches all follow-up dates from the 'treatment_records' table.
// =========================================================================
$sql = "(
    SELECT 
        a.appointment_id as id,
        a.appointment_date as start,
        a.service_description as description,
        a.status,
        p.first_name,
        p.last_name,
        u.full_name AS dentist_name,
        'standard' as event_type -- Flag for standard appointments
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN users u ON a.dentist_id = u.user_id
)
UNION ALL
(
    SELECT 
        CONCAT('followup-', tr.record_id) as id, -- Create a unique string ID to prevent clashes
        tr.next_appt as start,
        CONCAT('Follow-up for: ', tr.procedure_done) as description,
        'Scheduled' as status, -- Assume all future follow-ups are 'Scheduled'
        p.first_name,
        p.last_name,
        u.full_name AS dentist_name,
        'followup' as event_type -- Flag for follow-up appointments
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    LEFT JOIN users u ON tr.dentist_id = u.user_id
    WHERE tr.next_appt IS NOT NULL AND tr.next_appt >= CURDATE() -- Only show upcoming follow-ups
)";

$result = $conn->query($sql);
$events = [];

while ($row = $result->fetch_assoc()) {
    $dentistName = !empty($row['dentist_name']) ? htmlspecialchars($row['dentist_name']) : 'Unassigned';
    $dentistFirstName = !empty($row['dentist_name']) ? strtok($row['dentist_name'], ' ') : 'N/A';
    
    $event = [];

    // --- Configure the event based on its type (standard vs. followup) ---
    if ($row['event_type'] == 'standard') {
        $color = '#0d6efd'; // Default Blue for Scheduled
        if ($row['status'] == 'Completed') $color = '#198754'; // Green
        if ($row['status'] == 'Cancelled') $color = '#6c757d'; // Gray
        if ($row['status'] == 'No-Show') $color = '#dc3545';   // Red
        
        $event = [
            'id'        => $row['id'],
            'title'     => htmlspecialchars($row['last_name'] . ' w/ ' . $dentistFirstName),
            'start'     => $row['start'],
            'color'     => $color,
            'editable'  => true // Standard appointments are editable
        ];

    } else { // This is a 'followup' event
        $event = [
            'id'        => $row['id'],
            'title'     => 'Follow-up: ' . htmlspecialchars($row['last_name']),
            'start'     => $row['start'],
            'color'     => '#8e44ad', // A distinct purple color
            'editable'  => false // Follow-ups are READ-ONLY on the calendar
        ];
    }

    // Add extended properties for the tooltip, which works for both types
    $event['extendedProps'] = [
        'patientName'   => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        'dentistName'   => $dentistName,
        'description'   => htmlspecialchars($row['description']),
        'status'        => htmlspecialchars($row['status'])
    ];

    $events[] = $event;
}

// Set the header to return a JSON response and output the data
header('Content-Type: application/json');
echo json_encode($events);

$conn->close();
?>