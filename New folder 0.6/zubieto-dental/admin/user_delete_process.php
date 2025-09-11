<?php
// FILE: admin/user_delete_process.php
session_start();
require '../includes/db_connect.php';

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

// 1. Critical Check: Prevent self-deletion
if ($user_id_to_delete == $_SESSION['user_id']) {
    $_SESSION['message'] = "Error: You cannot delete your own account.";
    $_SESSION['message_type'] = 'danger';
    header("Location: users.php"); exit();
}

// --- DELETION ---
$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id_to_delete);

if ($stmt->execute()) {
    $_SESSION['message'] = "User account has been deleted successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    // This might fail if, for example, the user is linked to appointments and there's no ON DELETE rule.
    $_SESSION['message'] = "Error deleting user. They may be linked to other records in the system. Error: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();
header("Location: users.php");
exit();
?>