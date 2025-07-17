<?php
session_start();
require_once 'config.php';

// Gatekeeper: Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { 
    exit('Unauthorized'); 
}

// Ensure this is a POST request and has the required TreatmentID
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['TreatmentID'])) {
    
    // Get the IDs from the form submission
    $patientID = $_POST['patientID'];
    $treatmentID = $_POST['TreatmentID'];

    // SQL statement to update a record in the Treatments table
    $sql = "UPDATE Treatments SET 
                Date = ?, 
                ProcedureID = ?, 
                ToothNumber = ?, 
                AmountCharged = ?, 
                AmountPaid = ?, 
                Balance = ?, 
                NextAppointment = ?, 
                Notes = ? 
            WHERE TreatmentID = ? AND PatientID = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        
        // Execute the update with data from the modal form
        $stmt->execute([
            empty($_POST['Date']) ? null : $_POST['Date'],
            $_POST['ProcedureID'] ?? null,
            $_POST['ToothNumber'] ?? null,
            empty($_POST['AmountCharged']) ? 0.00 : $_POST['AmountCharged'],
            empty($_POST['AmountPaid']) ? 0.00 : $_POST['AmountPaid'],
            empty($_POST['Balance']) ? 0.00 : $_POST['Balance'],
            empty($_POST['NextAppointment']) ? null : $_POST['NextAppointment'],
            $_POST['Notes'] ?? null,
            $treatmentID,
            $patientID
        ]);
        
        // ===============================================
        // INTEGRATED REDIRECTION LOGIC
        // ===============================================
        // This checks if the form was submitted from the master treatments log.
        
        // Set the default redirection URL to the patient's detail page
        $redirect_url = "patients.php?patientID=" . $patientID . "&status=treatment_updated";

        // If the 'return_to' field was sent and its value is 'treatments'...
        if (isset($_POST['return_to']) && $_POST['return_to'] === 'daily_log') {
    $redirect_url = "daily_log.php?date=" . $_POST['return_date'] . "&status=treatment_updated";
} elseif (isset($_POST['return_to']) && $_POST['return_to'] === 'treatments') {
    $redirect_url = "treatments.php?status=treatment_updated";
}

        // Perform the final redirection.
        header("Location: " . $redirect_url);
        exit();

    } catch (PDOException $e) {
        // Log the error for debugging purposes
        error_log("Update Treatment Error: " . $e->getMessage());

        // Also redirect back to the correct page on error
        $error_redirect_url = "patients.php?patientID=" . $patientID . "&error=db_error";
        if (isset($_POST['return_to']) && $_POST['return_to'] === 'treatments') {
             $error_redirect_url = "treatments.php?error=db_error";
        }
        header("Location: " . $error_redirect_url);
        exit();
    }
    
} else {
    // If the request is invalid (not POST or no ID), redirect to the main list.
    header("Location: patient_list.php");
    exit();
}
?>