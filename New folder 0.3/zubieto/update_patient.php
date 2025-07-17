<?php
session_start();
require_once 'config.php'; 

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_GET['patientID'])) {
    header("Location: patient_list.php");
    exit();
}

$patientID = (int)$_GET['patientID'];

$pdo->beginTransaction();

try {
    // --- 1. UPDATE 'Patients' TABLE ---
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

    // --- 2. UPDATE THE 'MedicalHistories' TABLE (HEAVILY MODIFIED) ---
    // This assumes one medical history record per patient for simplicity of update.
    $sql_medical = "UPDATE MedicalHistories SET 
                        NameOfPhysician = ?, PhysicianAddress = ?, PhysicianPhoneNumber = ?, DateOfLastPhysicalExam = ?, 
                        AreYouInGoodHealth = ?, UnderMedicalTreatment = ?, TreatmentDetails = ?, HadSeriousIllness = ?, IllnessDetails = ?, 
                        EverHospitalized = ?, HospitalizationDetails = ?, DietaryRestrictions = ?, UseAlcohol = ?, UseTobacco = ?, Allergies = ?, 
                        AllergyToAnesthetics = ?, AllergyToAspirin = ?, AllergyToLatex = ?, AllergyToPenicillin = ?, BloodPressure = ?, 
                        RespiratoryRate = ?, PulseRate = ?, Temperature = ?, OtherConditionsDetails = ?, HighBloodPressure = ?, LowBloodPressure = ?,
                        EpilepsyConvulsions = ?, AIDSOrHIVInfection = ?, StomachTroublesUlcer = ?, HeartFailure = ?, RapidWeightLoss = ?, 
                        RadiationTherapy = ?, JointReplacement = ?, HeartSurgery = ?, HeartAttack = ?, HeartDisease = ?, HeartMurmur = ?, 
                        HepatitisOrLiverDisease = ?, RheumaticFever = ?, HayFeverAllergies = ?, RespiratoryProblems = ?, HepatitisJaundice = ?, 
                        Tuberculosis = ?, SwollenAnkles = ?, KidneyDisease = ?, Diabetes = ?, JointInjuriesBloodDisease = ?, ArthritisRheumatism = ?, 
                        CancerTumor = ?, Anemia = ?, Angina = ?, Asthma = ?, ThyroidProblem = ?, Emphysema = ?, BleedingProblems = ?, Stroke = ?, ChestPain = ?
                    WHERE PatientID = ?";
    
    $stmt_medical = $pdo->prepare($sql_medical);
    
    // Build the execute array in order
    $medical_values = [
        $_POST['NameOfPhysician'] ?? null, $_POST['PhysicianAddress'] ?? null, $_POST['PhysicianPhoneNumber'] ?? null, empty($_POST['DateOfLastPhysicalExam']) ? null : $_POST['DateOfLastPhysicalExam'],
        isset($_POST['AreYouInGoodHealth']) ? 1 : 0, isset($_POST['UnderMedicalTreatment']) ? 1 : 0, $_POST['TreatmentDetails'] ?? null,
        isset($_POST['HadSeriousIllness']) ? 1 : 0, $_POST['IllnessDetails'] ?? null, isset($_POST['EverHospitalized']) ? 1 : 0, $_POST['HospitalizationDetails'] ?? null,
        $_POST['DietaryRestrictions'] ?? null, $_POST['UseAlcohol'] ?? null, $_POST['UseTobacco'] ?? null, $_POST['allergies'] ?? null,
        isset($_POST['AllergyToAnesthetics']) ? 1 : 0, isset($_POST['AllergyToAspirin']) ? 1 : 0, isset($_POST['AllergyToLatex']) ? 1 : 0, isset($_POST['AllergyToPenicillin']) ? 1 : 0,
        $_POST['BloodPressure'] ?? null, $_POST['RespiratoryRate'] ?? null, $_POST['PulseRate'] ?? null, $_POST['Temperature'] ?? null, $_POST['otherConditionsDetails'] ?? null,
        // Boolean values
        isset($_POST['HighBloodPressure']) ? 1 : 0, isset($_POST['LowBloodPressure']) ? 1 : 0, isset($_POST['EpilepsyConvulsions']) ? 1 : 0, isset($_POST['AIDSOrHIVInfection']) ? 1 : 0,
        isset($_POST['StomachTroublesUlcer']) ? 1 : 0, isset($_POST['HeartFailure']) ? 1 : 0, isset($_POST['RapidWeightLoss']) ? 1 : 0, isset($_POST['RadiationTherapy']) ? 1 : 0,
        isset($_POST['JointReplacement']) ? 1 : 0, isset($_POST['HeartSurgery']) ? 1 : 0, isset($_POST['HeartAttack']) ? 1 : 0, isset($_POST['HeartDisease']) ? 1 : 0,
        isset($_POST['HeartMurmur']) ? 1 : 0, isset($_POST['HepatitisOrLiverDisease']) ? 1 : 0, isset($_POST['RheumaticFever']) ? 1 : 0, isset($_POST['HayFeverAllergies']) ? 1 : 0,
        isset($_POST['RespiratoryProblems']) ? 1 : 0, isset($_POST['HepatitisJaundice']) ? 1 : 0, isset($_POST['Tuberculosis']) ? 1 : 0, isset($_POST['SwollenAnkles']) ? 1 : 0,
        isset($_POST['KidneyDisease']) ? 1 : 0, isset($_POST['Diabetes']) ? 1 : 0, isset($_POST['JointInjuriesBloodDisease']) ? 1 : 0, isset($_POST['ArthritisRheumatism']) ? 1 : 0,
        isset($_POST['CancerTumor']) ? 1 : 0, isset($_POST['Anemia']) ? 1 : 0, isset($_POST['Angina']) ? 1 : 0, isset($_POST['Asthma']) ? 1 : 0,
        isset($_POST['ThyroidProblem']) ? 1 : 0, isset($_POST['Emphysema']) ? 1 : 0, isset($_POST['BleedingProblems']) ? 1 : 0, isset($_POST['Stroke']) ? 1 : 0, isset($_POST['ChestPain']) ? 1 : 0,
        // WHERE clause
        $patientID
    ];

    $stmt_medical->execute($medical_values);

    // --- 3. UPDATE 'DentalHistories' TABLE ---
    $sql_dental = "UPDATE DentalHistories SET PreviousDentist = ?, LastDentalVisit = ?, ChiefComplaint = ?, HistoryOfPresentIllness = ?, Complications = ? WHERE PatientID = ?";
    $stmt_dental = $pdo->prepare($sql_dental);
    $stmt_dental->execute([
        $_POST['previousDentist'] ?? null, empty($_POST['lastDentalVisit']) ? null : $_POST['lastDentalVisit'], $_POST['chiefComplaint'] ?? null, $_POST['historyOfPresentIllness'] ?? null, $_POST['complications'] ?? null,
        $patientID
    ]);
    // Get the DentalHistoryID to update past procedures
    $stmt_get_dh_id = $pdo->prepare("SELECT DentalHistoryID FROM DentalHistories WHERE PatientID = ? LIMIT 1");
    $stmt_get_dh_id->execute([$patientID]);
    $dentalHistoryID = $stmt_get_dh_id->fetchColumn();


    // --- 4. UPDATE 'PastDentalProcedures' (DELETE AND RE-INSERT) ---
    if ($dentalHistoryID) {
        $stmt_delete_proc = $pdo->prepare("DELETE FROM PastDentalProcedures WHERE DentalHistoryID = ?");
        $stmt_delete_proc->execute([$dentalHistoryID]);

        if (!empty($_POST['pastProcedures']) && is_array($_POST['pastProcedures'])) {
            $sql_past_proc = "INSERT INTO PastDentalProcedures (DentalHistoryID, ProcedureType) VALUES (?, ?)";
            $stmt_past_proc = $pdo->prepare($sql_past_proc);
            foreach ($_POST['pastProcedures'] as $procedure) {
                $stmt_past_proc->execute([$dentalHistoryID, $procedure]);
            }
        }
        if (!empty(trim($_POST['pastProceduresOther']))) {
            $sql_past_proc_other = "INSERT INTO PastDentalProcedures (DentalHistoryID, ProcedureType, Details) VALUES (?, ?, ?)";
            $stmt_past_proc_other = $pdo->prepare($sql_past_proc_other);
            $stmt_past_proc_other->execute([$dentalHistoryID, 'Other', $_POST['pastProceduresOther']]);
        }
    }


    $pdo->commit();

    header("Location: patients.php?patientID=" . $patientID . "&status=success");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Update Patient Error: " . $e->getMessage());
    header("Location: patients.php?patientID=" . $patientID . "&error=" . urlencode("Database error occurred while saving. Details: " . $e->getMessage()));
    exit();
}