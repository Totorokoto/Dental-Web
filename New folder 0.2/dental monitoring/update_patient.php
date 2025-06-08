<?php
require_once 'config.php'; // Database connection

// --- 1. VERIFY THE REQUEST ---
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_GET['patientID'])) {
    header("Location: patient_list.php");
    exit();
}

$patientID = (int)$_GET['patientID'];

// --- 2. BEGIN DATABASE TRANSACTION ---
$pdo->beginTransaction();

try {
    // --- 3. UPDATE THE 'Patients' TABLE ---
    $sql_patient = "UPDATE Patients SET 
                        FirstName = ?, LastName = ?, MiddleName = ?, Address = ?, Birthdate = ?, 
                        Religion = ?, CivilStatus = ?, Nationality = ?, MobileNumber = ?, 
                        ParentGuardianName = ?, Occupation = ?, Age = ?, Gender = ?, 
                        Nickname = ?, PatientNumber = ?, Email = ?
                    WHERE PatientID = ?";
    
    $stmt_patient = $pdo->prepare($sql_patient);
    
    $stmt_patient->execute([
        $_POST['firstName'] ?? null, $_POST['lastName'] ?? null, $_POST['middleName'] ?? null,
        $_POST['address'] ?? null, empty($_POST['birthdate']) ? null : $_POST['birthdate'], $_POST['religion'] ?? null,
        $_POST['civilStatus'] ?? null, $_POST['nationality'] ?? null, $_POST['mobileNumber'] ?? null,
        $_POST['parentGuardianName'] ?? null, $_POST['occupation'] ?? null, empty($_POST['age']) ? null : (int)$_POST['age'],
        $_POST['gender'] ?? null, $_POST['nickname'] ?? null, $_POST['patientNumber'] ?? null, $_POST['email'] ?? null,
        $patientID
    ]);

    // --- 4. UPDATE THE 'MedicalHistories' TABLE ---
    $sql_medical = "UPDATE MedicalHistories SET 
                        NameOfPhysician = ?, PhysicianAddress = ?, PhysicianPhoneNumber = ?, DateOfLastPhysicalExam = ?, 
                        BloodPressure = ?, RespiratoryRate = ?, PulseRate = ?, Temperature = ?, Medications = ?, 
                        Allergies = ?, OtherConditionsDetails = ?, AreYouInGoodHealth = ?, UnderMedicalTreatment = ?, 
                        HadSeriousIllness = ?, EverHospitalized = ?, HighBloodPressure = ?, EpilepsyConvulsions = ?, 
                        AIDSOrHIVInfection = ?, StomachTroublesUlcer = ?, HeartFailure = ?, RapidWeightLoss = ?, 
                        RadiationTherapy = ?, JointReplacement = ?, HeartSurgery = ?, HeartAttack = ?, HeartDisease = ?, 
                        HeartMurmur = ?, HepatitisOrLiverDisease = ?, RheumaticFever = ?, HayFeverAllergies = ?, 
                        RespiratoryProblems = ?, HepatitisJaundice = ?, Tuberculosis = ?, SwollenAnkles = ?, 
                        KidneyDisease = ?, Diabetes = ?, JointInjuriesBloodDisease = ?, ArthritisRheumatism = ?, 
                        CancerTumor = ?, Anemia = ?, Angina = ?, Asthma = ?, ThyroidProblem = ?, Emphysema = ?, 
                        BleedingProblems = ?, Stroke = ?, ChestPain = ?
                    WHERE PatientID = ? ORDER BY DateTaken DESC LIMIT 1";

    $stmt_medical = $pdo->prepare($sql_medical);
    
    $stmt_medical->execute([
        $_POST['NameOfPhysician'] ?? null, $_POST['PhysicianAddress'] ?? null, $_POST['PhysicianPhoneNumber'] ?? null, empty($_POST['DateOfLastPhysicalExam']) ? null : $_POST['DateOfLastPhysicalExam'],
        $_POST['BloodPressure'] ?? null, $_POST['RespiratoryRate'] ?? null, $_POST['PulseRate'] ?? null, $_POST['Temperature'] ?? null,
        $_POST['medications'] ?? null, $_POST['allergies'] ?? null, $_POST['otherConditionsDetails'] ?? null,
        isset($_POST['AreYouInGoodHealth']) ? 1 : 0, isset($_POST['UnderMedicalTreatment']) ? 1 : 0, isset($_POST['HadSeriousIllness']) ? 1 : 0, isset($_POST['EverHospitalized']) ? 1 : 0,
        isset($_POST['HighBloodPressure']) ? 1 : 0, isset($_POST['EpilepsyConvulsions']) ? 1 : 0, isset($_POST['AIDSOrHIVInfection']) ? 1 : 0, isset($_POST['StomachTroublesUlcer']) ? 1 : 0,
        isset($_POST['HeartFailure']) ? 1 : 0, isset($_POST['RapidWeightLoss']) ? 1 : 0, isset($_POST['RadiationTherapy']) ? 1 : 0, isset($_POST['JointReplacement']) ? 1 : 0,
        isset($_POST['HeartSurgery']) ? 1 : 0, isset($_POST['HeartAttack']) ? 1 : 0, isset($_POST['HeartDisease']) ? 1 : 0, isset($_POST['HeartMurmur']) ? 1 : 0,
        isset($_POST['HepatitisOrLiverDisease']) ? 1 : 0, isset($_POST['RheumaticFever']) ? 1 : 0, isset($_POST['HayFeverAllergies']) ? 1 : 0, isset($_POST['RespiratoryProblems']) ? 1 : 0,
        isset($_POST['HepatitisJaundice']) ? 1 : 0, isset($_POST['Tuberculosis']) ? 1 : 0, isset($_POST['SwollenAnkles']) ? 1 : 0, isset($_POST['KidneyDisease']) ? 1 : 0,
        isset($_POST['Diabetes']) ? 1 : 0, isset($_POST['JointInjuriesBloodDisease']) ? 1 : 0, isset($_POST['ArthritisRheumatism']) ? 1 : 0, isset($_POST['CancerTumor']) ? 1 : 0,
        isset($_POST['Anemia']) ? 1 : 0, isset($_POST['Angina']) ? 1 : 0, isset($_POST['Asthma']) ? 1 : 0, isset($_POST['ThyroidProblem']) ? 1 : 0,
        isset($_POST['Emphysema']) ? 1 : 0, isset($_POST['BleedingProblems']) ? 1 : 0, isset($_POST['Stroke']) ? 1 : 0, isset($_POST['ChestPain']) ? 1 : 0,
        $patientID
    ]);

    // --- 5. UPDATE THE 'DentalHistories' TABLE ---
    $sql_dental = "UPDATE DentalHistories SET PreviousDentist = ?, LastDentalVisit = ?, ChiefComplaint = ?, HistoryOfPresentIllness = ?, Complications = ? WHERE PatientID = ? ORDER BY DateTaken DESC LIMIT 1";
    $stmt_dental = $pdo->prepare($sql_dental);
    $stmt_dental->execute([
        $_POST['previousDentist'] ?? null, empty($_POST['lastDentalVisit']) ? null : $_POST['lastDentalVisit'], $_POST['chiefComplaint'] ?? null, $_POST['historyOfPresentIllness'] ?? null, $_POST['complications'] ?? null,
        $patientID
    ]);

    // --- 6. UPDATE THE 'ClinicalFindings' TABLE (if any) ---
    if (isset($_POST['clinicalFindings']) && is_array($_POST['clinicalFindings'])) {
        $sql_finding = "UPDATE ClinicalFindings SET DateObserved = ?, ToothNumber = ?, Diagnosis = ?, ProposedTreatment = ?, Remarks = ? WHERE ClinicalFindingsID = ? AND PatientID = ?";
        $stmt_finding = $pdo->prepare($sql_finding);
        foreach ($_POST['clinicalFindings'] as $finding) {
            $stmt_finding->execute([
                empty($finding['DateObserved']) ? null : $finding['DateObserved'], $finding['ToothNumber'] ?? null, $finding['Diagnosis'] ?? null, $finding['ProposedTreatment'] ?? null, $finding['Remarks'] ?? null,
                $finding['ClinicalFindingsID'], $patientID
            ]);
        }
    }

    // --- 7. COMMIT THE TRANSACTION ---
    $pdo->commit();

    // --- 8. REDIRECT ON SUCCESS ---
    header("Location: patients.php?patientID=" . $patientID . "&status=success");
    exit();

} catch (PDOException $e) {
    // --- 9. HANDLE ERRORS ---
    $pdo->rollBack();
    error_log("Update Patient Error: " . $e->getMessage());
    header("Location: patients.php?patientID=" . $patientID . "&error=" . urlencode("Database error occurred while saving."));
    exit();
}
?>