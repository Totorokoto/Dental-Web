<?php
// FILE: admin/user_add_process.php
session_start();
require '../includes/db_connect.php';

// RBAC Check and POST method validation
if ($_SESSION['role'] !== 'Admin' || $_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['message'] = "Unauthorized access."; $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit();
}

// --- DATA VALIDATION ---
$full_name = trim($_POST['full_name']);
$username = trim($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = $_POST['role'];
$branch = $_POST['branch'];
$is_active = $_POST['is_active'];

// 1. Check if passwords match
if ($password !== $confirm_password) {
    $_SESSION['message'] = "Passwords do not match. Please try again.";
    $_SESSION['message_type'] = 'danger';
    header("Location: user_add.php");
    exit();
}

// 2. Check for duplicate username
$sql_check = "SELECT user_id FROM users WHERE username = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    $_SESSION['message'] = "Username '<strong>" . htmlspecialchars($username) . "</strong>' already exists. Please choose another.";
    $_SESSION['message_type'] = 'danger';
    header("Location: user_add.php");
    exit();
}
$stmt_check->close();

// --- DATA INSERTION ---

// 3. Hash the password for secure storage
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 4. Prepare the INSERT statement
$sql = "INSERT INTO users (full_name, username, password, role, branch, is_active) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $full_name, $username, $hashed_password, $role, $branch, $is_active);

// 5. Execute and provide feedback
if ($stmt->execute()) {
    $_SESSION['message'] = "User account for <strong>" . htmlspecialchars($full_name) . "</strong> created successfully.";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error creating user account: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

$stmt->close();
$conn->close();

header("Location: users.php");
exit();
?>