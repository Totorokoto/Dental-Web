<?php
// FILE: login_process.php (CORRECTED WITH ROLE-BASED REDIRECTION)

// Always start the session at the very beginning
session_start();

// Include the database connection file
require 'includes/db_connect.php';

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get and sanitize input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 2. Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required.";
        header("Location: login.php");
        exit();
    }

    // 3. Prepare SQL statement to prevent SQL injection
    $sql = "SELECT user_id, password, full_name, role, is_active FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        // Use a generic error message for security
        $_SESSION['login_error'] = "An unexpected error occurred. Please try again later.";
        header("Location: login.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // 4. Check if a user was found
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // 5. Verify the password
        if (password_verify($password, $user['password'])) {
            
            // Check if the account is active
            if ($user['is_active']) {
                session_regenerate_id(true); // Security best practice
                
                // Password is correct, user is active. Create session variables.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // ====================================================================
                // THE FIX IS HERE: ROLE-BASED REDIRECTION LOGIC
                // Check the user's role and redirect them to the appropriate page.
                // ====================================================================
                if ($user['role'] == 'Admin') {
                    // If the user is an Admin, send them to the dashboard.
                    header("Location: admin/dashboard.php");
                } else {
                    // For all other roles (Dentist, Assistant), send them to the calendar.
                    header("Location: admin/appointments.php");
                }
                exit(); // Important to stop the script after redirection

            } else {
                // Account is deactivated
                $_SESSION['login_error'] = "Your account has been deactivated.";
                header("Location: login.php");
                exit();
            }

        } else {
            // Incorrect password
            $_SESSION['login_error'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // No user found with that username
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: login.php");
        exit();
    }

} else {
    // If the page was accessed directly without POST, redirect to login
    header("Location: login.php");
    exit();
}
?>