<?php
// create_user.php
session_start();

// Gatekeeper: Only Admins can create users
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'Admin') {
    header("location: login.php?error=Unauthorized action.");
    exit;
}

require_once 'config.php';

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: user_management.php");
    exit();
}

// --- Data Validation and Sanitization ---
$firstName = trim($_POST['firstName']);
$lastName = trim($_POST['lastName']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$role = trim($_POST['role']);

// Basic validation
if (empty($firstName) || empty($username) || empty($password) || empty($role)) {
    header("Location: user_management.php?error=" . urlencode("All fields except Last Name are required."));
    exit();
}

// Check if username already exists
try {
    $sql_check = "SELECT UserID FROM users WHERE Username = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$username]);
    if ($stmt_check->rowCount() > 0) {
        header("Location: user_management.php?error=" . urlencode("Username already taken. Please choose another."));
        exit();
    }
} catch (PDOException $e) {
    error_log("Create User (Check) Error: " . $e->getMessage());
    header("Location: user_management.php?error=" . urlencode("A database error occurred."));
    exit();
}


// --- Database Insertion ---
try {
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql_insert = "INSERT INTO users (Username, Password, FirstName, LastName, Role) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    
    // Execute the statement
    $stmt_insert->execute([$username, $hashed_password, $firstName, $lastName, $role]);
    
    // --- Redirect on Success ---
    header("Location: user_management.php?status=success");
    exit();
    
} catch (PDOException $e) {
    error_log("Create User (Insert) Error: " . $e->getMessage());
    header("Location: user_management.php?error=" . urlencode("A database error occurred while creating the user."));
    exit();
}