<?php
// FILE: admin/appointment_edit_process.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get and sanitize new data from the form
$appointment_id = intval($_POST['appointment_id']);
$new_patient_id = intval($_POST['patient_id']);
$new_dentist_id = intval($_POST['dentist_id']);
$new_appointment_date = $_POST['appointment_date'];
$new_service_description = trim($_POST['service_description']);
$new_status = $_POST['status'];

if(empty($new_patient_id) || empty($new_dentist_id) || empty($new_appointment_date) || empty($new_service_description)){
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

$conn->begin_transaction();

try {
    // --- STEP 1: Fetch the original appointment data ---
    $sql_get_old = "
        SELECT 
            a.patient_id, a.dentist_id, a.appointment_date, a.service_description, a.status,
            p.first_name as p_fname, p.last_name as p_lname,
            u.full_name as dentist_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        LEFT JOIN users u ON a.dentist_id = u.user_id
        WHERE a.appointment_id = ?
    ";
    $stmt_get_old = $conn->prepare($sql_get_old);
    $stmt_get_old->bind_param("i", $appointment_id);
    $stmt_get_old->execute();
    $old_data = $stmt_get_old->get_result()->fetch_assoc();
    $stmt_get_old->close();

    if (!$old_data) {
        throw new Exception("Original appointment not found.");
    }

    $log_patient_name = $old_data['p_fname'] . ' ' . $old_data['p_lname'];


    // --- STEP 2: Update the appointment record ---
    $sql_update = "UPDATE appointments SET patient_id=?, dentist_id=?, appointment_date=?, service_description=?, status=? WHERE appointment_id=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("iisssi", $new_patient_id, $new_dentist_id, $new_appointment_date, $new_service_description, $new_status, $appointment_id);
    $stmt_update->execute();
    $stmt_update->close();

    // --- STEP 3: Compare old and new data to build a log of changes ---
    $changes = [];

    // Compare Status
    if ($old_data['status'] !== $new_status) {
        $changes[] = "Status changed from '" . htmlspecialchars($old_data['status']) . "' to '" . htmlspecialchars($new_status) . "'";
    }

    // Compare Patient
    if ($old_data['patient_id'] != $new_patient_id) {
        $old_patient_name = $old_data['p_fname'] . ' ' . $old_data['p_lname'];
        $stmt_new_p = $conn->prepare("SELECT first_name, last_name FROM patients WHERE patient_id = ?");
        $stmt_new_p->bind_param("i", $new_patient_id);
        $stmt_new_p->execute();
        $new_p_data = $stmt_new_p->get_result()->fetch_assoc();
        $new_patient_name = $new_p_data['first_name'] . ' ' . $new_p_data['last_name'];
        $stmt_new_p->close();
        $changes[] = "Patient changed from '" . htmlspecialchars($old_patient_name) . "' to '" . htmlspecialchars($new_patient_name) . "'";
        $log_patient_name = $new_patient_name; 
    }

    // Compare Dentist
    if ($old_data['dentist_id'] != $new_dentist_id) {
        $old_dentist_name = $old_data['dentist_name'];
        $stmt_new_d = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt_new_d->bind_param("i", $new_dentist_id);
        $stmt_new_d->execute();
        $new_d_data = $stmt_new_d->get_result()->fetch_assoc();
        $new_dentist_name = $new_d_data['full_name'];
        $stmt_new_d->close();
        $changes[] = "Dentist changed from '" . htmlspecialchars($old_dentist_name) . "' to '" . htmlspecialchars($new_dentist_name) . "'";
    }
    
    // Compare Time
    if (strtotime($old_data['appointment_date']) != strtotime($new_appointment_date)) {
        $old_date_formatted = date('M d, Y @ h:i A', strtotime($old_data['appointment_date']));
        $new_date_formatted = date('M d, Y @ h:i A', strtotime($new_appointment_date));
        $changes[] = "Time changed from " . $old_date_formatted . " to " . $new_date_formatted;
    }

    // Compare Service Description
    if ($old_data['service_description'] !== $new_service_description) {
        $changes[] = "Service/Reason was updated.";
    }

    // --- STEP 4: If there were any changes, insert the log entry ---
    if (!empty($changes)) {
        $user_id = $_SESSION['user_id'];
        $action_type = 'Appointment Edited';
        

        $details = "Edited appointment for patient '" . htmlspecialchars($log_patient_name) . "' (Appt ID: $appointment_id). Changes: " . implode('; ', $changes) . ".";

        $sql_log = "INSERT INTO activity_logs (user_id, action_type, details) VALUES (?, ?, ?)";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("iss", $user_id, $action_type, $details);
        $stmt_log->execute();
        $stmt_log->close();
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Appointment updated successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database operation failed: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>