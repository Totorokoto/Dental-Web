<?php
require_once 'config.php'; // Database connection

// --- 1. VERIFY THE REQUEST ---

// Check if a patientID is provided in the URL
if (!isset($_GET['patientID']) || empty($_GET['patientID'])) {
    // Redirect with an error if no ID is given
    header("Location: patient_list.php?error=" . urlencode("No patient specified for deletion."));
    exit();
}

$patientID = (int)$_GET['patientID'];

// --- 2. BEGIN DATABASE TRANSACTION ---
// While not strictly necessary for a single delete, it's good practice
// in case you later add logic to delete related files, etc.
$pdo->beginTransaction();

try {
    // --- 3. DELETE THE PATIENT RECORD ---
    // Because of the ON DELETE CASCADE constraint in your database schema,
    // deleting a patient from the `patients` table will automatically delete all
    // their corresponding records in `medicalhistories`, `dentalhistories`,
    // `clinicalfindings`, and `treatments`. This is very efficient!

    $sql = "DELETE FROM Patients WHERE PatientID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patientID]);

    // Check if any row was actually deleted
    if ($stmt->rowCount() > 0) {
        // --- 4. COMMIT THE TRANSACTION ---
        // If the delete was successful, make it permanent.
        $pdo->commit();
        
        // --- 5. REDIRECT ON SUCCESS ---
        header("Location: patient_list.php?status=deleted");
        exit();
    } else {
        // If no rows were affected, it means the patientID didn't exist.
        $pdo->rollBack(); // Not strictly needed, but good practice.
        header("Location: patient_list.php?error=" . urlencode("Patient with that ID could not be found."));
        exit();
    }

} catch (PDOException $e) {
    // --- 6. HANDLE ERRORS ---
    // If the delete failed for any reason, roll back any potential changes.
    $pdo->rollBack();
    
    error_log("Delete Patient Error: " . $e->getMessage());
    // Redirect back to the list with a generic error message
    header("Location: patient_list.php?error=" . urlencode("A database error occurred. Could not delete patient."));
    exit();
}
?>