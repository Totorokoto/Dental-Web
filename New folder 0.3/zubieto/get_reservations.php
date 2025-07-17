<?php
header('Content-Type: application/json');
session_start();
require_once 'config.php';

// Gatekeeper
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// FullCalendar sends start and end parameters for the date range it's viewing
$start_date = $_GET['start'] ?? date('Y-m-01');
$end_date = $_GET['end'] ?? date('Y-m-t');

try {
    // Select treatments that have a 'NextAppointment' date set
    $sql = "SELECT 
                t.TreatmentID, 
                t.NextAppointment, 
                t.Notes,
                t.PatientID,
                t.ProcedureID,
                p.FirstName, 
                p.LastName, 
                pr.ProcedureName
            FROM Treatments t
            JOIN Patients p ON t.PatientID = p.PatientID
            LEFT JOIN procedures pr ON t.ProcedureID = pr.ProcedureID
            WHERE t.NextAppointment IS NOT NULL 
            AND t.NextAppointment BETWEEN ? AND ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    
    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => $row['TreatmentID'],
            'title' => htmlspecialchars($row['LastName'] . ' - ' . ($row['ProcedureName'] ?? 'Appointment')),
            'start' => $row['NextAppointment'],
            // 'end' => $row['NextAppointment'], // Optional: define an end time if you have one
            'extendedProps' => [
                'patientID' => $row['PatientID'],
                'procedureID' => $row['ProcedureID'],
                'notes' => $row['Notes']
            ]
        ];
    }
    
    echo json_encode($events);
    
} catch (PDOException $e) {
    // Return a JSON error object
    echo json_encode(['error' => 'Database query failed', 'message' => $e->getMessage()]);
    error_log("Get Reservations Error: " . $e->getMessage());
}
?>