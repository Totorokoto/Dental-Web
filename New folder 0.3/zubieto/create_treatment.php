<?php
session_start();
require_once 'config.php';

// Gatekeeper: Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // We can't redirect with an error message if the session is dead, so just exit.
    exit('Unauthorized access.');
}

// Ensure this is a POST request and has the minimum required data
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['PatientID']) || empty($_POST['Date'])) {
    header("Location: patient_list.php?error=invalid_request");
    exit();
}

// This script creates a new treatment record in the database.
$sql = "INSERT INTO Treatments (
            PatientID, 
            Date, 
            ProcedureID, 
            ToothNumber, 
            AmountCharged, 
            AmountPaid, 
            Balance, 
            NextAppointment, 
            Notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $pdo->prepare($sql);
    
    // Execute the statement with data from the modal form
    $stmt->execute([
        $_POST['PatientID'],
        $_POST['Date'],
        empty($_POST['ProcedureID']) ? null : $_POST['ProcedureID'],
        $_POST['ToothNumber'] ?? null,
        empty($_POST['AmountCharged']) ? 0.00 : $_POST['AmountCharged'],
        empty($_POST['AmountPaid']) ? 0.00 : $_POST['AmountPaid'],
        empty($_POST['Balance']) ? 0.00 : $_POST['Balance'],
        empty($_POST['NextAppointment']) ? null : $_POST['NextAppointment'],
        $_POST['Notes'] ?? null
    ]);
    
    // --- Redirection Logic ---
    // Check if the form included a 'return_to' field. This tells us which page to go back to.
    if (isset($_POST['return_to']) && $_POST['return_to'] === 'treatments') {
        // If the form was submitted from treatments.php, go back there.
        $redirect_url = "treatments.php?status=treatment_added";
    } else {
        // Otherwise, go back to the specific patient's page.
        $redirect_url = "patients.php?patientID=" . $_POST['PatientID'] . "&status=treatment_added";
    }
    
    header("Location: " . $redirect_url);
    exit();

} catch (PDOException $e) {
    // Log the detailed error for debugging
    error_log("Create Treatment Error: " . $e->getMessage());

    // Redirect the user back with a generic error message
    $error_redirect_url = "patient_list.php?error=db_error"; // Default fallback
    if(isset($_POST['PatientID'])) {
         $error_redirect_url = "patients.php?patientID=" . $_POST['PatientID'] . "&error=db_error";
         if (isset($_POST['return_to']) && $_POST['return_to'] === 'treatments') {
             $error_redirect_url = "treatments.php?error=db_error";
         }
    }
    header("Location: " . $error_redirect_url);
    exit();
}
?>