<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { exit('Unauthorized'); }

if (isset($_GET['id'])) {
    $treatmentID = $_GET['id'];
    
    // Determine where to redirect back to
    $patientID = $_GET['patientID'] ?? null;
    $return_url = "patient_list.php"; // Default fallback

    if (!empty($_GET['return_to']) && $_GET['return_to'] === 'daily_log') {
    $return_url = "daily_log.php?date=" . $_GET['date'] . "&status=treatment_deleted";
} elseif (!empty($_GET['return_to']) && $_GET['return_to'] === 'treatments') {
    $return_url = "treatments.php?status=treatment_deleted";
}

    try {
        $sql = "DELETE FROM Treatments WHERE TreatmentID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$treatmentID]);

        header("Location: " . $return_url);
        exit();

    } catch (PDOException $e) {
        // ... (error handling remains the same) ...
        // Redirect with an error message
        $error_redirect_url = $patientID ? "patients.php?patientID=$patientID&error=db_error" : "treatments.php?error=db_error";
        header("Location: " . $error_redirect_url);
        exit();
    }
} else {
    header("Location: patient_list.php");
    exit();
}
?>