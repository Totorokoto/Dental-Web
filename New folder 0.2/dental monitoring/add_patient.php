<?php
// 1. START THE SESSION. This must be the very first line.
session_start();
 
// 2. GATEKEEPER. Check if the user is logged in.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 3. REQUIRE CONFIG - THIS IS THE FIX
// This makes the $pdo variable AND the generate_breadcrumbs() function available.
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'topbar.php'; ?>

<main class="main-content-area">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add New Patient Record</h2>
        <a href="patient_list.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Cancel and Go Back to List
        </a>
    </div>
    
    <div class="patient-form-content">
         <form id="addPatientForm" method="post" action="create_patient.php">
            
            <!-- ============================ -->
            <!-- PATIENT INFO SECTION         -->
            <!-- ============================ -->
            <h2 class="section-header">PATIENT INFORMATION</h2>
            <div class="data-grid">
                <div class="data-item">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                <div class="data-item">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
                <div class="data-item">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName">
                </div>
                 <div class="data-item">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address">
                </div>
                <div class="data-item">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate">
                </div>
                <div class="data-item">
                    <label for="religion">Religion</label>
                    <input type="text" id="religion" name="religion">
                </div>
                <div class="data-item">
                    <label for="civilStatus">Civil Status</label>
                    <input type="text" id="civilStatus" name="civilStatus">
                </div>
                <div class="data-item">
                    <label for="nationality">Nationality</label>
                    <input type="text" id="nationality" name="nationality">
                </div>
                <div class="data-item">
                    <label for="mobileNumber">Mobile Number</label>
                    <input type="tel" id="mobileNumber" name="mobileNumber">
                </div>
                <div class="data-item">
                    <label for="parentGuardianName">Parent/Guardian</label>
                    <input type="text" id="parentGuardianName" name="parentGuardianName">
                </div>
                <div class="data-item">
                    <label for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation">
                </div>
                <div class="data-item">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age">
                </div>
                <div class="data-item">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="data-item">
                    <label for="nickname">Nickname</label>
                    <input type="text" id="nickname" name="nickname">
                </div>
                <div class="data-item">
                    <label for="patientNumber">Patient Number</label>
                    <input type="text" id="patientNumber" name="patientNumber">
                </div>
                <div class="data-item">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
            </div>
            
            <!-- ============================ -->
            <!-- MEDICAL INFO SECTION         -->
            <!-- ============================ -->
            <h2 class="section-header">MEDICAL INFORMATION</h2>
            <div class="data-grid">
                <?php
                    $medical_fields = [
                        'NameOfPhysician' => 'Name of Physician', 'PhysicianAddress' => 'Physician Address', 'PhysicianPhoneNumber' => 'Physician Phone',
                        'DateOfLastPhysicalExam' => 'Last Exam Date', 'BloodPressure' => 'Blood Pressure', 'RespiratoryRate' => 'Respiratory Rate', 'PulseRate' => 'Pulse Rate', 'Temperature' => 'Temperature'
                    ];
                    $medical_booleans = [
                        'AreYouInGoodHealth' => 'In Good Health?', 'UnderMedicalTreatment' => 'Under Medical Treatment?', 'HadSeriousIllness' => 'Had Serious Illness?', 'EverHospitalized' => 'Ever Hospitalized?',
                        'HighBloodPressure' => 'High Blood Pressure', 'EpilepsyConvulsions' => 'Epilepsy/Convulsions', 'AIDSOrHIVInfection' => 'AIDS/HIV', 'StomachTroublesUlcer' => 'Stomach Troubles/Ulcer',
                        'HeartFailure' => 'Heart Failure', 'RapidWeightLoss' => 'Rapid Weight Loss', 'RadiationTherapy' => 'Radiation Therapy', 'JointReplacement' => 'Joint Replacement', 'HeartSurgery' => 'Heart Surgery',
                        'HeartAttack' => 'Heart Attack', 'HeartDisease' => 'Heart Disease', 'HeartMurmur' => 'Heart Murmur', 'HepatitisOrLiverDisease' => 'Hepatitis/Liver Disease', 'RheumaticFever' => 'Rheumatic Fever',
                        'HayFeverAllergies' => 'Hay Fever/Allergies', 'RespiratoryProblems' => 'Respiratory Problems', 'HepatitisJaundice' => 'Hepatitis/Jaundice', 'Tuberculosis' => 'Tuberculosis',
                        'SwollenAnkles' => 'Swollen Ankles', 'KidneyDisease' => 'Kidney Disease', 'Diabetes' => 'Diabetes', 'JointInjuriesBloodDisease' => 'Joint Injuries/Blood Disease',
                        'ArthritisRheumatism' => 'Arthritis/Rheumatism', 'CancerTumor' => 'Cancer/Tumor', 'Anemia' => 'Anemia', 'Angina' => 'Angina', 'Asthma' => 'Asthma',
                        'ThyroidProblem' => 'Thyroid Problem', 'Emphysema' => 'Emphysema', 'BleedingProblems' => 'Bleeding Problems', 'Stroke' => 'Stroke', 'ChestPain' => 'Chest Pain'
                    ];
                ?>
                <!-- Standard Text/Date Inputs -->
                <?php foreach ($medical_fields as $field_name => $label): ?>
                    <div class="data-item">
                        <label for="<?php echo $field_name; ?>"><?php echo $label; ?></label>
                        <input type="<?php echo $field_name == 'DateOfLastPhysicalExam' ? 'date' : 'text'; ?>" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>">
                    </div>
                <?php endforeach; ?>
                
                <!-- Textarea Inputs -->
                <div class="data-item">
                    <label for="medications">Medications</label>
                    <textarea id="medications" name="medications"></textarea>
                </div>
                <div class="data-item">
                    <label for="allergies">Allergies</label>
                    <textarea id="allergies" name="allergies"></textarea>
                </div>
                 <div class="data-item">
                    <label for="otherConditionsDetails">Other Conditions Details</label>
                    <textarea id="otherConditionsDetails" name="otherConditionsDetails"></textarea>
                </div>

                <!-- Convenient Checkbox Section -->
                <div class="data-item grid-span-all">
                    <label>Do you have any of the following? (Check which apply)</label>
                    <div class="checkbox-container">
                        <?php foreach ($medical_booleans as $field_name => $label): ?>
                        <div class="checkbox-option">
                            <input type="checkbox" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="1">
                            <label for="<?php echo $field_name; ?>"><?php echo $label; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ============================ -->
            <!-- DENTAL HISTORY SECTION       -->
            <!-- ============================ -->
            <h2 class="section-header">DENTAL HISTORY</h2>
            <div class="data-grid">
                <div class="data-item">
                    <label for="previousDentist">Previous Dentist</label>
                    <input type="text" id="previousDentist" name="previousDentist">
                </div>
                <div class="data-item">
                    <label for="lastDentalVisit">Last Dental Visit</label>
                    <input type="date" id="lastDentalVisit" name="lastDentalVisit">
                </div>
                <div class="data-item">
                    <label for="chiefComplaint">Chief Complaint</label>
                    <textarea id="chiefComplaint" name="chiefComplaint"></textarea>
                </div>
                <div class="data-item">
                    <label for="historyOfPresentIllness">History of Present Illness</label>
                    <textarea id="historyOfPresentIllness" name="historyOfPresentIllness"></textarea>
                </div>
                <div class="data-item">
                    <label for="complications">Complications from past treatment</label>
                    <textarea id="complications" name="complications"></textarea>
                </div>
            </div>

            <!-- Submission Button -->
            <div class="mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check me-2"></i>Create Full Patient Record
                </button>
            </div>
         </form>
    </div>
    
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>