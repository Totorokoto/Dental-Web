<?php
// FILE: login_process.php (FINAL, CORRECTED VERSION)

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
        header("Location: index.php");
        exit();
    }

    // 3. Prepare SQL statement to fetch all necessary user data
    $sql = "SELECT user_id, password, full_name, role, branch, is_active FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        // Use a generic error message for security
        $_SESSION['login_error'] = "An unexpected error occurred. Please try again later.";
        header("Location: index.php");
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
                
                // Password is correct, user is active. Create all session variables.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['branch'] = $user['branch']; // CRITICAL: This sets the branch for filtering

                // ROLE-BASED REDIRECTION LOGIC
                switch ($user['role']) {
                    case 'Admin':
                        header("Location: admin/dashboard.php");
                        break;
                    case 'Dentist':
                        // Correctly redirects dentists to their specific dashboard
                        header("Location: admin/dentist_dashboard.php");
                        break;
                    case 'Assistant':
                        // Assistants go to the patient list
                        header("Location: admin/patients.php");
                        break;
                    default:
                        // Fallback to the login page
                        header("LLocation: index.php");
                        break;
                }
                exit(); // Important to stop the script after redirection

            } else {
                // Account is deactivated
                $_SESSION['login_error'] = "Your account has been deactivated.";
                header("Location: index.php");
                exit();
            }

        } else {
            // Incorrect password
            $_SESSION['login_error'] = "Invalid username or password.";
            header("Location: index.php");
            exit();
        }
    } else {
        // No user found with that username
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: index.php");
        exit();
    }

} else {
    // If the page was accessed directly without POST, redirect to login
    header("Location: index.php");
    exit();
}
?>