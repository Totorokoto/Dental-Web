<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { exit('Unauthorized'); }

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ClinicalFindingsID'])) {
    $patientID = $_POST['patientID'];
    $findingID = $_POST['ClinicalFindingsID'];

    $sql = "UPDATE ClinicalFindings SET DateObserved = ?, ToothNumber = ?, Diagnosis = ?, ProposedTreatment = ?, Remarks = ? WHERE ClinicalFindingsID = ? AND PatientID = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            empty($_POST['DateObserved']) ? null : $_POST['DateObserved'],
            $_POST['ToothNumber'] ?? null,
            $_POST['Diagnosis'] ?? null,
            $_POST['ProposedTreatment'] ?? null,
            $_POST['Remarks'] ?? null,
            $findingID,
            $patientID
        ]);
        
        header("Location: patients.php?patientID=" . $patientID . "&status=finding_updated");
        exit();

    } catch (PDOException $e) {
        error_log("Update Finding Error: " . $e->getMessage());
        header("Location: patients.php?patientID=" . $patientID . "&error=db_error");
        exit();
    }
} else {
    header("Location: patient_list.php");
    exit();
}
?>