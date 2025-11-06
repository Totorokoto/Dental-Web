<?php
// FILE: admin/user_delete_process.php
session_start();
require '../includes/db_connect.php';

// =================================================================
//  LOGGING FUNCTION
// =================================================================
function create_log($conn, $user_id, $action_type, $details) {
    $sql_log = "INSERT INTO activity_logs (user_id, action_type, details) VALUES (?, ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    if ($stmt_log) {
        $stmt_log->bind_param("iss", $user_id, $action_type, $details);
        $stmt_log->execute();
        $stmt_log->close();
    }
}
// =================================================================

// RBAC Check
if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php"); exit();
}

$user_id_to_delete = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- VALIDATION ---
if ($user_id_to_delete <= 0) {
    $_SESSION['message'] = "Invalid user ID."; $_SESSION['message_type'] = 'danger';
    header("Location: users.php"); exit();
}

if ($user_id_to_delete == $_SESSION['user_id']) {
    $_SESSION['message'] = "Error: You cannot delete your own account.";
    $_SESSION['message_type'] = 'danger';
    header("Location: users.php"); exit();
}

// --- SAFETY CHECK: VERIFY USER IS NOT LINKED TO ANY RECORDS ---
$can_delete = true;
$error_message = "Cannot delete user. They are linked to existing records: ";
$links = [];

// Check appointments
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE dentist_id = ?");
$stmt_check->bind_param("i", $user_id_to_delete);
$stmt_check->execute();
if ($stmt_check->get_result()->fetch_assoc()['count'] > 0) {
    $can_delete = false;
    $links[] = "appointments";
}
$stmt_check->close();

// Check treatment records
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM treatment_records WHERE dentist_id = ?");
$stmt_check->bind_param("i", $user_id_to_delete);
$stmt_check->execute();
if ($stmt_check->get_result()->fetch_assoc()['count'] > 0) {
    $can_delete = false;
    $links[] = "treatment records";
}
$stmt_check->close();

// Check clinical findings
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM clinical_findings WHERE dentist_id = ?");
$stmt_check->bind_param("i", $user_id_to_delete);
$stmt_check->execute();
if ($stmt_check->get_result()->fetch_assoc()['count'] > 0) {
    $can_delete = false;
    $links[] = "clinical findings";
}
$stmt_check->close();

// If user cannot be deleted, set message and redirect
if (!$can_delete) {
    $_SESSION['message'] = $error_message . implode(', ', $links) . ". Please reassign their records or deactivate the account instead.";
    $_SESSION['message_type'] = 'danger';
    header("Location: users.php");
    exit();
}
// --- END SAFETY CHECK ---


// --- DELETION LOGIC (Only runs if safety check passes) ---

// First, get user details for logging BEFORE deletion
$sql_user_info = "SELECT full_name FROM users WHERE user_id = ?";
$stmt_info = $conn->prepare($sql_user_info);
$stmt_info->bind_param("i", $user_id_to_delete);
$stmt_info->execute();
$user_full_name = $stmt_info->get_result()->fetch_assoc()['full_name'];
$stmt_info->close();

// Now, proceed with deletion
$sql_delete = "DELETE FROM users WHERE user_id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $user_id_to_delete);

if ($stmt_delete->execute()) {
    $_SESSION['message'] = "User account for <strong>" . htmlspecialchars($user_full_name) . "</strong> has been permanently deleted.";
    $_SESSION['message_type'] = 'success';

    // Log the successful deletion
    $admin_user_id = $_SESSION['user_id'];
    $log_action = "User Deleted";
    $log_details = "Permanently deleted the user account for '" . htmlspecialchars($user_full_name) . "'.";
    create_log($conn, $admin_user_id, $log_action, $log_details);

} else {
    $_SESSION['message'] = "An unexpected error occurred while trying to delete the user. Error: " . $stmt_delete->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt_delete->close();
$conn->close();
header("Location: users.php");
exit();
?>