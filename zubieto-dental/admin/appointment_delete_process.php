<?php
// FILE: admin/appointment_delete_process.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;

if ($appointment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Appointment ID.']);
    exit;
}

$conn->begin_transaction();

try {
    // --- STEP 1: Fetch appointment details for logging BEFORE deleting ---
    $sql_info = "
        SELECT 
            p.first_name, 
            p.last_name,
            a.appointment_date,
            a.service_description
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.appointment_id = ?
    ";
    $stmt_info = $conn->prepare($sql_info);
    $stmt_info->bind_param("i", $appointment_id);
    $stmt_info->execute();
    $info_result = $stmt_info->get_result()->fetch_assoc();
    $stmt_info->close();

    if (!$info_result) {
        throw new Exception("Appointment not found. It may have already been deleted.");
    }
    
    // --- STEP 2: Delete the appointment record ---
    $stmt_delete = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt_delete->bind_param("i", $appointment_id);
    $stmt_delete->execute();
    
    // Check if the deletion was successful
    if ($stmt_delete->affected_rows > 0) {
        // --- STEP 3: Create the activity log entry ---
        $user_id = $_SESSION['user_id'];
        $action_type = 'Appointment Deleted';
        
        $patient_name = $info_result['first_name'] . ' ' . $info_result['last_name'];
        $appt_date = date('M d, Y @ h:i A', strtotime($info_result['appointment_date']));
        $service = $info_result['service_description'];

        $details = "Deleted appointment for patient '" . htmlspecialchars($patient_name) . "' (scheduled for " . $appt_date . " - Service: " . htmlspecialchars($service) . ").";

        $stmt_log = $conn->prepare("INSERT INTO activity_logs (user_id, action_type, details) VALUES (?, ?, ?)");
        $stmt_log->bind_param("iss", $user_id, $action_type, $details);
        $stmt_log->execute();
        $stmt_log->close();
        
        $message = 'Appointment deleted successfully.';
    } else {
        // This case handles if the appointment_id was valid but nothing was deleted (rare).
        $message = 'Appointment not found or already deleted.';
    }

    $stmt_delete->close();
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database operation failed: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>