<?php
// FILE: admin/user_edit_process.php
session_start();
require '../includes/db_connect.php';

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

// 1. Check for duplicate username (excluding the current user)
$sql_check = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("si", $username, $user_id);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    $_SESSION['message'] = "Username '<strong>" . htmlspecialchars($username) . "</strong>' is already taken. Please choose another.";
    $_SESSION['message_type'] = 'danger';
    header("Location: user_edit.php?id=" . $user_id);
    exit();
}
$stmt_check->close();

// --- DYNAMIC SQL UPDATE ---
$sql_parts = [];
$params = [];
$types = "";

// Always update these fields
array_push($sql_parts, "full_name = ?", "username = ?", "role = ?", "branch = ?", "is_active = ?");
array_push($params, $full_name, $username, $role, $branch, $is_active);
$types .= "ssssi";

// 2. Conditionally update password if a new one is provided
if (!empty($password)) {
    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match. Please try again.";
        $_SESSION['message_type'] = 'danger';
        header("Location: user_edit.php?id=" . $user_id);
        exit();
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    array_push($sql_parts, "password = ?");
    array_push($params, $hashed_password);
    $types .= "s";
}

// 3. Construct the final query
$sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE user_id = ?";
array_push($params, $user_id);
$types .= "i";

// 4. Prepare and execute
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['message'] = "User account for <strong>" . htmlspecialchars($full_name) . "</strong> updated successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error updating user account: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

header("Location: users.php");
exit();
?>