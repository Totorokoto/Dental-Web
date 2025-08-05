<?php
// FILE: admin/patient_delete_process.php

// Start the session to use session-based feedback messages
session_start();

// Include the database connection and session check to ensure security
require '../includes/db_connect.php';
// The path to session_check.php must be relative to this file's location.
// Since this file is in /admin/, and session_check is in /admin/includes/, the path is correct.
require 'includes/session_check.php';

// --- 1. VALIDATE THE INCOMING REQUEST ---

// Get the patient ID from the URL and sanitize it to ensure it's an integer.
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If the ID is not a valid positive number, the request is invalid.
if ($patient_id <= 0) {
    // Set an error message and redirect the user back to the patient list.
    $_SESSION['message'] = "Invalid request. A valid patient ID is required for deletion.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit(); // Stop the script immediately.
}

// --- 2. PREPARE AND EXECUTE THE SECURE DELETION ---

// The SQL query to delete a record from the `patients` table.
// Because your foreign keys are set with `ON DELETE CASCADE`, the database
// will automatically delete all corresponding records in `medical_history`,
// `appointments`, `clinical_findings`, and `treatment_records`.
$sql = "DELETE FROM patients WHERE patient_id = ?";

// Prepare the SQL statement to prevent SQL injection.
$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful.
if ($stmt === false) {
    // This indicates a syntax error in the SQL, a server-side problem.
    $_SESSION['message'] = "Error preparing the delete statement: " . $conn->error;
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php");
    exit();
}

// Bind the sanitized integer patient ID to the placeholder in the SQL statement.
// "i" means the variable is an integer.
$stmt->bind_param("i", $patient_id);

// --- 3. PROVIDE FEEDBACK BASED ON EXECUTION RESULT ---

// Execute the prepared statement.
if ($stmt->execute()) {
    // The query ran successfully. Now check if a record was actually deleted.
    if ($stmt->affected_rows > 0) {
        // If one or more rows were affected, the deletion was successful.
        $_SESSION['message'] = "The patient record and all associated data have been permanently deleted.";
        $_SESSION['message_type'] = 'success';
    } else {
        // If zero rows were affected, it means no patient with that ID was found.
        $_SESSION['message'] = "Warning: No patient found with the specified ID. No records were deleted.";
        $_SESSION['message_type'] = 'warning';
    }
} else {
    // If execute() returns false, a database error occurred (e.g., a permissions issue).
    $_SESSION['message'] = "Error executing deletion: " . $stmt->error;
    $_SESSION['message_type'] = 'danger';
}

// --- 4. CLEAN UP AND REDIRECT ---

// Close the prepared statement to free up server resources.
$stmt->close();
// Close the database connection.
$conn->close();

// Redirect the user back to the main patient list page.
// The feedback message will be displayed there.
header("Location: patients.php");
exit();
?>