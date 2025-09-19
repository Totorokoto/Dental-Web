<?php
session_start(); // Access the existing session.

session_unset(); // Remove all session variables.

session_destroy(); // Destroy the session itself.

// Redirect to the login page with a success message (optional).
header("Location: index.php");
exit();
?>