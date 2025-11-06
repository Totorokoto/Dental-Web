<?php
// FILE: admin/user_edit_process.php
session_start();
require '../includes/db_connect.php';

// Logging Function
function create_log($conn, $user_id, $action_type, $details) {
    $sql_log = "INSERT INTO activity_logs (user_id, action_type, details) VALUES (?, ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    if ($stmt_log) {
        $stmt_log->bind_param("iss", $user_id, $action_type, $details);
        $stmt_log->execute();
        $stmt_log->close();
    }
}

// RBAC Check and POST method validation
if ($_SESSION['role'] !== 'Admin' || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: dashboard.php"); exit();
}

// --- DATA VALIDATION ---
$user_id = intval($_POST['user_id']);
$full_name = trim($_POST['full_name']);
$username = trim($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = $_POST['role'];
$branch = $_POST['branch'];
$is_active = $_POST['is_active'];
// New field
$availability_status = ($role === 'Assistant') ? 'Available' : $_POST['availability_status']; // Assistants are always available


$sql_check = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("si", $username, $user_id);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    $_SESSION['message'] = "Username '<strong>" . htmlspecialchars($username) . "</strong>' is already taken.";
    $_SESSION['message_type'] = 'danger';
    header("Location: user_edit.php?id=" . $user_id);
    exit();
}
$stmt_check->close();

// --- DYNAMIC SQL UPDATE ---
$sql_parts = [];
$params = [];
$types = "";

array_push($sql_parts, "full_name = ?", "username = ?", "role = ?", "branch = ?", "is_active = ?", "availability_status = ?");
array_push($params, $full_name, $username, $role, $branch, $is_active, $availability_status);
$types .= "ssssis";

$password_changed_message = "No"; 
if (!empty($password)) {
    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['message_type'] = 'danger';
        header("Location: user_edit.php?id=" . $user_id);
        exit();
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    array_push($sql_parts, "password = ?");
    array_push($params, $hashed_password);
    $types .= "s";
    $password_changed_message = "Yes"; 
}

$sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE user_id = ?";
array_push($params, $user_id);
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['message'] = "User account for <strong>" . htmlspecialchars($full_name) . "</strong> updated successfully.";
    $_SESSION['message_type'] = 'success';

    $admin_user_id = $_SESSION['user_id'];
    $log_action = "User Edited";
    $log_details = "Updated profile for user '" . htmlspecialchars($full_name) . "'. Details changed (Password updated: " . $password_changed_message . ").";
    create_log($conn, $admin_user_id, $log_action, $log_details);

} else {
    $_SESSION['message'] = "Error updating user account: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

header("Location: users.php");
exit();
?>