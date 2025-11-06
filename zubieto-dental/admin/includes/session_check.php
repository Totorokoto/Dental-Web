<?php
// Start the session to access session variables. This MUST be the very first line.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is not logged in (the user_id session variable is not set).
if (!isset($_SESSION['user_id'])) {
    // If not logged in, create a message to display on the login page.
    $_SESSION['login_error'] = "You must be logged in to view this page.";
    
    // Redirect the user back to the login page.
    // The `../` is important because we are inside the `admin/includes/` directory
    // and need to go up two levels to get to the root where login.php is.
    header("Location: ../../index.php");
    
    // Stop the script from executing any further.
    exit();
}
?>