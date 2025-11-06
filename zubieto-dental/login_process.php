<?php
// FILE: login_process.php 

// Always start the session at the very beginning
session_start();

// Include the database connection file
require 'includes/db_connect.php';

// =================================================================
//  LOGGING FUNCTION
// =================================================================
/**
 * Creates an activity log entry.
 *
 * @param mysqli $conn The database connection object.
 * @param int $user_id The ID of the user performing the action.
 * @param string $action_type The type of action being logged.
 * @param string $details A description of the action.
 */
function create_log($conn, $user_id, $action_type, $details) {
    // This function will be called to insert logs into the database
    $sql_log = "INSERT INTO activity_logs (user_id, action_type, details) VALUES (?, ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    if ($stmt_log) {
        $stmt_log->bind_param("iss", $user_id, $action_type, $details);
        $stmt_log->execute();
        $stmt_log->close();
    }
}
// =================================================================

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
                // --- LOG SUCCESSFUL LOGIN ---
                create_log($conn, $user['user_id'], 'Login Success', "User '" . htmlspecialchars($user['full_name']) . "' logged in successfully.");
                
                session_regenerate_id(true); // Security best practice
                
                // Password is correct, user is active. Create all session variables.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['branch'] = $user['branch'];

                // ROLE-BASED REDIRECTION LOGIC
                switch ($user['role']) {
                    case 'Admin':
                        header("Location: admin/dashboard.php");
                        break;
                    case 'Dentist':
                        header("Location: admin/dentist_dashboard.php");
                        break;
                    case 'Assistant':
                        header("Location: admin/patients.php");
                        break;
                    default:
                        header("Location: index.php");
                        break;
                }
                exit();

            } else {
                // --- LOG FAILED LOGIN (DEACTIVATED ACCOUNT) ---
                create_log($conn, $user['user_id'], 'Login Failed', "Login attempt for deactivated account '" . htmlspecialchars($username) . "'.");
                
                $_SESSION['login_error'] = "Your account has been deactivated.";
                header("Location: index.php");
                exit();
            }

        } else {
            // --- LOG FAILED LOGIN (INCORRECT PASSWORD) ---
            create_log($conn, $user['user_id'], 'Login Failed', "Failed login attempt for username '" . htmlspecialchars($username) . "' (Incorrect password).");
            
            $_SESSION['login_error'] = "Invalid username or password.";
            header("Location: index.php");
            exit();
        }
    } else {
        // NOTE: We do not log "user not found" attempts here to prevent username enumeration attacks
        // and because there is no user_id to associate with the log entry.
        
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