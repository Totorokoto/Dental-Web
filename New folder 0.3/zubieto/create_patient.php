<?php
session_start();
 
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_patient.php");
    exit();
}

$pdo->beginTransaction();

try {
    // --- 1. Insert into Patients Table (MODIFIED: Added ConsentTimestamp) ---
    $sql_patient = "INSERT INTO Patients (
                        FirstName, LastName, MiddleName, Address, Birthdate, Religion, CivilStatus, 
                        Nationality, MobileNumber, ParentGuardianName, Occupation, Age, Gender, 
                        Nickname, PatientNumber, Email, ConsentTimestamp
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt_patient = $pdo->prepare($sql_patient);
    
    $stmt_patient->execute([
        $_POST['firstName'] ?? null, $_POST['lastName'] ?? null, $_POST['middleName'] ?? null,
        $_POST['address'] ?? null, empty($_POST['birthdate']) ? null : $_POST['birthdate'], $_POST['religion'] ?? null,
        $_POST['civilStatus'] ?? null, $_POST['nationality'] ?? null, $_POST['mobileNumber'] ?? null,
        $_POST['parentGuardianName'] ?? null, $_POST['occupation'] ?? null, empty($_POST['age']) ? null : (int)$_POST['age'],
        $_POST['gender'] ?? null, $_POST['nickname'] ?? null, $_POST['patientNumber'] ?? null, $_POST['email'] ?? null
    ]);
    
    $newPatientID = $pdo->lastInsertId();

    // --- 2. Insert into MedicalHistories Table (HEAVILY MODIFIED) ---
    $sql_medical = "INSERT INTO MedicalHistories (
                        PatientID, NameOfPhysician, PhysicianAddress, PhysicianPhoneNumber, DateOfLastPhysicalExam, 
                        AreYouInGoodHealth, UnderMedicalTreatment, TreatmentDetails, HadSeriousIllness, IllnessDetails, 
                        EverHospitalized, HospitalizationDetails, DietaryRestrictions, UseAlcohol, UseTobacco, Allergies, 
                        AllergyToAnesthetics, AllergyToAspirin, AllergyToLatex, AllergyToPenicillin, BloodPressure, 
                        RespiratoryRate, PulseRate, Temperature, OtherConditionsDetails, HighBloodPressure, LowBloodPressure,
                        EpilepsyConvulsions, AIDSOrHIVInfection, StomachTroublesUlcer, HeartFailure, RapidWeightLoss, 
                        RadiationTherapy, JointReplacement, HeartSurgery, HeartAttack, HeartDisease, HeartMurmur, 
                        HepatitisOrLiverDisease, RheumaticFever, HayFeverAllergies, RespiratoryProblems, HepatitisJaundice, 
                        Tuberculosis, SwollenAnkles, KidneyDisease, Diabetes, JointInjuriesBloodDisease, ArthritisRheumatism, 
                        CancerTumor, Anemia, Angina, Asthma, ThyroidProblem, Emphysema, BleedingProblems, Stroke, ChestPain
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";
    
    $stmt_medical = $pdo->prepare($sql_medical);
    
    $medical_booleans = [
        'AreYouInGoodHealth', 'UnderMedicalTreatment', 'HadSeriousIllness', 'EverHospitalized', 'HighBloodPressure', 'LowBloodPressure',
        'EpilepsyConvulsions', 'AIDSOrHIVInfection', 'StomachTroublesUlcer', 'HeartFailure', 'RapidWeightLoss', 'RadiationTherapy', 'JointReplacement', 'HeartSurgery', 'HeartAttack',
        'HeartDisease', 'HeartMurmur', 'HepatitisOrLiverDisease', 'RheumaticFever', 'HayFeverAllergies', 'RespiratoryProblems', 'HepatitisJaundice', 'Tuberculosis',
        'SwollenAnkles', 'KidneyDisease', 'Diabetes', 'JointInjuriesBloodDisease', 'ArthritisRheumatism', 'CancerTumor', 'Anemia', 'Angina', 'Asthma', 'ThyroidProblem',
        'Emphysema', 'BleedingProblems', 'Stroke', 'ChestPain'
    ];

    $medical_values = [
        $newPatientID,
        $_POST['NameOfPhysician'] ?? null, $_POST['PhysicianAddress'] ?? null, $_POST['PhysicianPhoneNumber'] ?? null, empty($_POST['DateOfLastPhysicalExam']) ? null : $_POST['DateOfLastPhysicalExam'],
        isset($_POST['AreYouInGoodHealth']) ? 1 : 0, isset($_POST['UnderMedicalTreatment']) ? 1 : 0, $_POST['TreatmentDetails'] ?? null,
        isset($_POST['HadSeriousIllness']) ? 1 : 0, $_POST['IllnessDetails'] ?? null, isset($_POST['EverHospitalized']) ? 1 : 0, $_POST['HospitalizationDetails'] ?? null,
        $_POST['DietaryRestrictions'] ?? null, $_POST['UseAlcohol'] ?? null, $_POST['UseTobacco'] ?? null, $_POST['allergies'] ?? null,
        isset($_POST['AllergyToAnesthetics']) ? 1 : 0, isset($_POST['AllergyToAspirin']) ? 1 : 0, isset($_POST['AllergyToLatex']) ? 1 : 0, isset($_POST['AllergyToPenicillin']) ? 1 : 0,
        $_POST['BloodPressure'] ?? null, $_POST['RespiratoryRate'] ?? null, $_POST['PulseRate'] ?? null, $_POST['Temperature'] ?? null, $_POST['otherConditionsDetails'] ?? null
    ];
    
    // Dynamically add the boolean values for the remaining conditions
    foreach(array_slice($medical_booleans, 4) as $field) { // Starts after the first 4 which are handled manually with their text fields
        array_push($medical_values, isset($_POST[$field]) ? 1 : 0);
    }
    
    $stmt_medical->execute($medical_values);

    // --- 3. Insert into DentalHistories Table ---
    $sql_dental = "INSERT INTO DentalHistories (PatientID, ChiefComplaint, HistoryOfPresentIllness, PreviousDentist, LastDentalVisit, Complications) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_dental = $pdo->prepare($sql_dental);
    $stmt_dental->execute([
        $newPatientID, $_POST['chiefComplaint'] ?? null, $_POST['historyOfPresentIllness'] ?? null,
        $_POST['previousDentist'] ?? null, empty($_POST['lastDentalVisit']) ? null : $_POST['lastDentalVisit'], $_POST['complications'] ?? null
    ]);
    $newDentalHistoryID = $pdo->lastInsertId();

    // --- 4. NEW: Insert into PastDentalProcedures Table ---
    if (!empty($_POST['pastProcedures']) && is_array($_POST['pastProcedures'])) {
        $sql_past_proc = "INSERT INTO PastDentalProcedures (DentalHistoryID, ProcedureType) VALUES (?, ?)";
        $stmt_past_proc = $pdo->prepare($sql_past_proc);
        foreach ($_POST['pastProcedures'] as $procedure) {
            $stmt_past_proc->execute([$newDentalHistoryID, $procedure]);
        }
    }
    // Handle the 'Other' text field for past procedures
    if (!empty(trim($_POST['pastProceduresOther']))) {
        $sql_past_proc_other = "INSERT INTO PastDentalProcedures (DentalHistoryID, ProcedureType, Details) VALUES (?, ?, ?)";
        $stmt_past_proc_other = $pdo->prepare($sql_past_proc_other);
        $stmt_past_proc_other->execute([$newDentalHistoryID, 'Other', $_POST['pastProceduresOther']]);
    }

    // If all inserts were successful, commit the transaction
    $pdo->commit();

    header("Location: patients.php?patientID=" . $newPatientID . "&status=created");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Create Full Patient Record Error: " . $e->getMessage());
    header("Location: add_patient.php?error=" . urlencode("A database error occurred while creating the record. Details: " . $e->getMessage()));
    exit();
}