<?php
// FILE: admin/appointment_status_process.php

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';
$valid_statuses = ['Completed', 'Scheduled', 'Cancelled', 'No-Show'];

if ($appointment_id <= 0 || !in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input provided.']);
    exit;
}

$conn->begin_transaction();

try {
    // Step 1: Get the old status and patient ID
    $sql_get_info = "SELECT status, patient_id FROM appointments WHERE appointment_id = ?";
    $stmt_get_info = $conn->prepare($sql_get_info);
    if ($stmt_get_info === false) throw new Exception("Failed to prepare statement to get old status.");
    $stmt_get_info->bind_param("i", $appointment_id);
    $stmt_get_info->execute();
    $result = $stmt_get_info->get_result();
    $old_data = $result->fetch_assoc();
    $stmt_get_info->close(); 

    if (!$old_data) {
        throw new Exception("Appointment with ID $appointment_id not found.");
    }
    $old_status = $old_data['status'];
    $patient_id = $old_data['patient_id'];

    // Step 2: Update the appointment status
    $sql_update = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update === false) throw new Exception("Failed to prepare update statement.");
    $stmt_update->bind_param("si", $new_status, $appointment_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Step 3: Conditionally create the log entry
    if ($old_status !== 'Completed' && $new_status === 'Completed') {
        // Get patient details safely for the log message
        $patient_name = "Patient ID: " . $patient_id; // Default name
        $sql_patient = "SELECT first_name, last_name FROM patients WHERE patient_id = ?";
        $stmt_patient = $conn->prepare($sql_patient);
        if ($stmt_patient) {
            $stmt_patient->bind_param("i", $patient_id);
            $stmt_patient->execute();
            $patient_result = $stmt_patient->get_result();
            if ($patient_data = $patient_result->fetch_assoc()) {
                $patient_name = $patient_data['first_name'] . ' ' . $patient_data['last_name'];
            }
            $stmt_patient->close();
        }

        // Prepare and insert the log
        $user_id = $_SESSION['user_id'];
        $action_type = 'Appointment Completed';
        
        $details = "Marked appointment (Appt ID: $appointment_id) for patient '" . htmlspecialchars($patient_name) . "' (Patient ID: $patient_id) as Completed.";

        $sql_log = "INSERT INTO activity_logs (user_id, action_type, details) VALUES (?, ?, ?)";
        $stmt_log = $conn->prepare($sql_log);
        if ($stmt_log === false) throw new Exception("Failed to prepare log statement.");
        $stmt_log->bind_param("iss", $user_id, $action_type, $details);
        $stmt_log->execute();
        $stmt_log->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    // Send a proper server error code and the specific error message for debugging
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Database operation failed: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>