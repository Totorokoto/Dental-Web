<?php
session_start();
require '../includes/db_connect.php';
require '../includes/send_email.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$appointment_id = intval($_POST['appointment_id']);
$action = $_POST['action'];

// Get Appointment and Patient Details
$sql = "SELECT a.appointment_date, p.first_name, p.last_name, p.email 
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.appointment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$details = $stmt->get_result()->fetch_assoc();

if (!$details) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
    exit;
}

$patientName = $details['first_name'] . ' ' . $details['last_name'];
$patientEmail = $details['email'];
$apptDate = date('F j, Y \a\t g:i A', strtotime($details['appointment_date']));

if ($action === 'approve') {
    $dentist_id = intval($_POST['dentist_id']);
    if ($dentist_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid dentist selected.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE appointments SET status = 'Scheduled', dentist_id = ? WHERE appointment_id = ?");
    $stmt->bind_param("ii", $dentist_id, $appointment_id);
    $stmt->execute();

    $stmt_dentist = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $stmt_dentist->bind_param("i", $dentist_id);
    $stmt_dentist->execute();
    $dentist_details = $stmt_dentist->get_result()->fetch_assoc();
    $dentistName = $dentist_details ? $dentist_details['full_name'] : 'one of our dentists';

    $subject = "Appointment Confirmed - Zubieto Dental Clinic";
    $body = "Dear {$patientName},<br><br>Your appointment for <strong>{$apptDate}</strong> has been confirmed.<br><br>You will be seen by <strong>{$dentistName}</strong>.<br><br>We look forward to seeing you!<br><br>Sincerely,<br>Zubieto Dental Clinic";
    sendAppointmentEmail($patientEmail, $patientName, $subject, $body);

    echo json_encode(['success' => true, 'message' => 'Appointment approved and confirmation email sent.']);

} elseif ($action === 'decline') {
    // Mark appointment as Cancelled to remove it from the pending queue
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    // Determine the final reason to show in the email
    $reason_select = $_POST['reason_select'] ?? 'Our clinic had to reschedule your appointment.';
    $reason_custom = trim($_POST['reason_custom'] ?? '');
    
    $final_reason = ($reason_select === 'Other' && !empty($reason_custom)) 
        ? $reason_custom 
        : $reason_select;

    // The suggestions now come with HTML, so we don't need to re-format them
    $suggestions = $_POST['suggestions'];

    // Send the professional reschedule email
    $subject = "Update Regarding Your Appointment Request - Zubieto Dental Clinic";
    $body = "Dear {$patientName},<br><br>Thank you for your interest in Zubieto Dental Clinic. Regarding your appointment request for <strong>{$apptDate}</strong>, we need to reschedule due to the following reason:<br><br><em>\"" . htmlspecialchars($final_reason) . "\"</em><br><br>We apologize for any inconvenience. To help you find a new time, here are some of our next available openings:<br><br>{$suggestions}<br><br>Please call our clinic or simply reply to this email to book one of these times, or to request a different schedule. We'll be happy to assist you.<br><br>Sincerely,<br>Zubieto Dental Clinic";
    sendAppointmentEmail($patientEmail, $patientName, $subject, $body);

    echo json_encode(['success' => true, 'message' => 'Appointment declined and reschedule email sent.']);
}
?>