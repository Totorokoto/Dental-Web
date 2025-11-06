<?php
// FILE: admin/treatment_add_process.php (FINAL CORRECTED VERSION)
session_start();
require '../includes/db_connect.php';

// Security check: Ensure user is logged in and the request is a POST.
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// --- DATA GATHERING AND VALIDATION ---

$patient_id = intval($_POST['patient_id']);
$dentist_id = intval($_SESSION['user_id']); 
$procedure_date = $_POST['procedure_date'];
$procedure_done_select = trim($_POST['procedure_done']);
$procedure_done_custom = trim($_POST['procedure_done_custom']);
$tooth_no = trim($_POST['tooth_no']);
$amount_charged = floatval($_POST['amount_charged']);
$amount_paid = floatval($_POST['amount_paid']);
$balance = $amount_charged - $amount_paid;
$next_appt = !empty($_POST['next_appt']) ? $_POST['next_appt'] : NULL;

// Correctly determine the final procedure description
$procedure_done = ($procedure_done_select === 'custom' && !empty($procedure_done_custom))
    ? $procedure_done_custom
    : $procedure_done_select;

// Final validation to ensure no invalid data is saved
if (empty($patient_id) || empty($dentist_id) || empty($procedure_date) || empty($procedure_done)) {
    $_SESSION['message'] = "Error: Required fields were missing (Patient, Dentist, Date, Procedure). Please try again.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patient_view.php?id=" . $patient_id . "&tab=treatments");
    exit();
}

// --- DATABASE TRANSACTION ---
$conn->begin_transaction();

try {
    // STEP 1: Insert the new treatment record
    $sql_insert_treatment = "INSERT INTO treatment_records (patient_id, dentist_id, procedure_date, procedure_done, tooth_no, amount_charged, amount_paid, balance, next_appt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert_treatment);
    if ($stmt_insert === false) {
        throw new Exception("Database error: Could not prepare the statement for treatment insertion.");
    }
    $stmt_insert->bind_param("iisssddds", $patient_id, $dentist_id, $procedure_date, $procedure_done, $tooth_no, $amount_charged, $amount_paid, $balance, $next_appt);
    $stmt_insert->execute();
    $stmt_insert->close();

    // --- NEW LOGIC FOR AUTOMATIC COMPLETION ---
    // STEP 2: If a follow-up date was set, automatically complete the last scheduled appointment.
    if (!is_null($next_appt)) {
        // This query finds the single most recent appointment for this patient with a status of 'Scheduled'
        // that occurred on or before the date of the procedure, and updates it.
        $sql_update_appointment = "
            UPDATE appointments 
            SET status = 'Completed' 
            WHERE patient_id = ? 
              AND status = 'Scheduled' 
              AND DATE(appointment_date) <= ?
            ORDER BY appointment_date DESC 
            LIMIT 1
        ";
        
        $stmt_update = $conn->prepare($sql_update_appointment);
        if ($stmt_update === false) {
            // Log this error but don't fail the whole transaction, as the treatment record is more critical.
            error_log("Failed to prepare statement for auto-completing appointment for patient ID: " . $patient_id);
        } else {
            $stmt_update->bind_param("is", $patient_id, $procedure_date);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
    // --- END OF NEW LOGIC ---

    // If everything succeeded, commit the transaction
    $conn->commit();
    $_SESSION['message'] = "New treatment record added successfully. The follow-up will now appear on the calendar.";
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    // If any part failed, roll back all database changes
    $conn->rollback();
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

$conn->close();

// Redirect back to the patient's treatment tab
$active_tab = isset($_POST['tab_redirect']) ? htmlspecialchars($_POST['tab_redirect']) : 'treatments';
$redirect_url = "patient_view.php?id=" . $patient_id . "&tab=" . $active_tab;
header("Location: " . $redirect_url);
exit();
?>