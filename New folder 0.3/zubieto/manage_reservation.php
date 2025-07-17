<?php
header('Content-Type: application/json');
session_start();
require_once 'config.php';

// Gatekeeper
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check for the action parameter
$action = $_POST['action'] ?? '';

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit();
}

try {
    switch ($action) {
        case 'create':
            // Validation
            if (empty($_POST['patientID']) || empty($_POST['procedureID']) || empty($_POST['nextAppointment'])) {
                echo json_encode(['success' => false, 'message' => 'Patient, Procedure, and Date are required.']);
                exit;
            }

            $sql = "INSERT INTO Treatments (PatientID, ProcedureID, NextAppointment, Notes, Date, AmountCharged, AmountPaid, Balance) VALUES (?, ?, ?, ?, ?, 0, 0, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['patientID'],
                $_POST['procedureID'],
                $_POST['nextAppointment'],
                $_POST['notes'] ?? null,
                $_POST['nextAppointment'] // Set the main 'Date' to the appointment date
            ]);
            echo json_encode(['success' => true, 'message' => 'Appointment created successfully.']);
            break;

        case 'update':
            // Validation
            if (empty($_POST['treatmentID']) || empty($_POST['patientID']) || empty($_POST['procedureID']) || empty($_POST['nextAppointment'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required data for update.']);
                exit;
            }

            $sql = "UPDATE Treatments SET PatientID = ?, ProcedureID = ?, NextAppointment = ?, Notes = ?, Date = ? WHERE TreatmentID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['patientID'],
                $_POST['procedureID'],
                $_POST['nextAppointment'],
                $_POST['notes'] ?? null,
                $_POST['nextAppointment'],
                $_POST['treatmentID']
            ]);
            echo json_encode(['success' => true, 'message' => 'Appointment updated successfully.']);
            break;
            
        case 'delete':
            // Validation
            if (empty($_POST['treatmentID'])) {
                echo json_encode(['success' => false, 'message' => 'No appointment ID provided for deletion.']);
                exit;
            }

            $sql = "DELETE FROM Treatments WHERE TreatmentID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['treatmentID']]);
            echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully.']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
            break;
    }
} catch (PDOException $e) {
    error_log("Manage Reservation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>