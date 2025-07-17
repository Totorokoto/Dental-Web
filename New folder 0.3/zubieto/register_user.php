<?php
require_once "config.php";

$username = "SuperAdmin";
$password = "admin"; // Change this
$firstName = "Admin";
$role = "Admin";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (Username, Password, FirstName, Role) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $hashed_password, $firstName, $role]);

echo "User registered successfully!";
?>