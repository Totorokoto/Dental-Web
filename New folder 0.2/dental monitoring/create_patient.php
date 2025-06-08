<?php

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php'; // Database connection

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_patient.php");
    exit();
}

// --- Begin Database Transaction ---
// This ensures that all inserts succeed, or none of them do.
$pdo->beginTransaction();

try {
    // --- 1. Insert into Patients Table ---
    $sql_patient = "INSERT INTO Patients (FirstName, LastName, MiddleName, Address, Birthdate, Religion, CivilStatus, Nationality, MobileNumber, ParentGuardianName, Occupation, Age, Gender, Nickname, PatientNumber, Email) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_patient = $pdo->prepare($sql_patient);
    
    // Execute with an array of values from the POST data
    // The null coalescing operator (??) provides a default value if the key doesn't exist
    $stmt_patient->execute([
        $_POST['firstName'] ?? null,
        $_POST['lastName'] ?? null,
        $_POST['middleName'] ?? null,
        $_POST['address'] ?? null,
        empty($_POST['birthdate']) ? null : $_POST['birthdate'],
        $_POST['religion'] ?? null,
        $_POST['civilStatus'] ?? null,
        $_POST['nationality'] ?? null,
        $_POST['mobileNumber'] ?? null,
        $_POST['parentGuardianName'] ?? null,
        $_POST['occupation'] ?? null,
        empty($_POST['age']) ? null : (int)$_POST['age'],
        $_POST['gender'] ?? null,
        $_POST['nickname'] ?? null,
        $_POST['patientNumber'] ?? null,
        $_POST['email'] ?? null
    ]);
    
    // Get the ID of the newly created patient
    $newPatientID = $pdo->lastInsertId();

    // --- 2. Insert into MedicalHistories Table ---
    $sql_medical = "INSERT INTO MedicalHistories (
                        PatientID, NameOfPhysician, PhysicianAddress, PhysicianPhoneNumber, DateOfLastPhysicalExam, 
                        AreYouInGoodHealth, UnderMedicalTreatment, HadSeriousIllness, EverHospitalized, Medications, 
                        Allergies, OtherConditionsDetails, BloodPressure, RespiratoryRate, PulseRate, Temperature,
                        HighBloodPressure, EpilepsyConvulsions, AIDSOrHIVInfection, StomachTroublesUlcer, HeartFailure,
                        RapidWeightLoss, RadiationTherapy, JointReplacement, HeartSurgery, HeartAttack, HeartDisease,
                        HeartMurmur, HepatitisOrLiverDisease, RheumaticFever, HayFeverAllergies, RespiratoryProblems,
                        HepatitisJaundice, Tuberculosis, SwollenAnkles, KidneyDisease, Diabetes, JointInjuriesBloodDisease,
                        ArthritisRheumatism, CancerTumor, Anemia, Angina, Asthma, ThyroidProblem, Emphysema, BleedingProblems,
                        Stroke, ChestPain
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_medical = $pdo->prepare($sql_medical);

    // For checkboxes, if they are not submitted, their value is not set. We default to 0 (No).
    $medical_booleans = [
        'AreYouInGoodHealth', 'UnderMedicalTreatment', 'HadSeriousIllness', 'EverHospitalized', 'HighBloodPressure', 'EpilepsyConvulsions', 'AIDSOrHIVInfection', 'StomachTroublesUlcer', 'HeartFailure',
        'RapidWeightLoss', 'RadiationTherapy', 'JointReplacement', 'HeartSurgery', 'HeartAttack', 'HeartDisease', 'HeartMurmur', 'HepatitisOrLiverDisease', 'RheumaticFever', 'HayFeverAllergies',
        'RespiratoryProblems', 'HepatitisJaundice', 'Tuberculosis', 'SwollenAnkles', 'KidneyDisease', 'Diabetes', 'JointInjuriesBloodDisease', 'ArthritisRheumatism', 'CancerTumor', 'Anemia', 'Angina',
        'Asthma', 'ThyroidProblem', 'Emphysema', 'BleedingProblems', 'Stroke', 'ChestPain'
    ];
    
    $medical_values = [$newPatientID];
    // Add standard fields
    array_push($medical_values, 
        $_POST['NameOfPhysician'] ?? null, $_POST['PhysicianAddress'] ?? null, $_POST['PhysicianPhoneNumber'] ?? null, empty($_POST['DateOfLastPhysicalExam']) ? null : $_POST['DateOfLastPhysicalExam'],
        isset($_POST['AreYouInGoodHealth']) ? 1 : 0, isset($_POST['UnderMedicalTreatment']) ? 1 : 0, isset($_POST['HadSeriousIllness']) ? 1 : 0, isset($_POST['EverHospitalized']) ? 1 : 0,
        $_POST['medications'] ?? null, $_POST['allergies'] ?? null, $_POST['otherConditionsDetails'] ?? null, $_POST['BloodPressure'] ?? null, $_POST['RespiratoryRate'] ?? null, $_POST['PulseRate'] ?? null, $_POST['Temperature'] ?? null
    );
    // Add boolean checkbox values
    foreach(array_slice($medical_booleans, 4) as $field) { // Start after the first 4 booleans already handled
        array_push($medical_values, isset($_POST[$field]) ? 1 : 0);
    }

    $stmt_medical->execute($medical_values);

    // --- 3. Insert into DentalHistories Table ---
    $sql_dental = "INSERT INTO DentalHistories (PatientID, ChiefComplaint, HistoryOfPresentIllness, PreviousDentist, LastDentalVisit, Complications) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt_dental = $pdo->prepare($sql_dental);
    
    $stmt_dental->execute([
        $newPatientID,
        $_POST['chiefComplaint'] ?? null,
        $_POST['historyOfPresentIllness'] ?? null,
        $_POST['previousDentist'] ?? null,
        empty($_POST['lastDentalVisit']) ? null : $_POST['lastDentalVisit'],
        $_POST['complications'] ?? null
    ]);

    // If all inserts were successful, commit the transaction
    $pdo->commit();

    // --- Redirect on Success ---
    header("Location: patients.php?patientID=" . $newPatientID . "&status=created");
    exit();

} catch (PDOException $e) {
    // If any insert fails, roll back the entire transaction
    $pdo->rollBack();
    
    // --- Handle Database Error ---
    error_log("Create Full Patient Record Error: " . $e->getMessage());
    header("Location: add_patient.php?error=" . urlencode("A database error occurred while creating the record."));
    exit();
}