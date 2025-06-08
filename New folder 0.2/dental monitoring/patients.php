<?php

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
// We only need the config file loaded at the top.
// All data fetching will happen after the main layout is loaded.
require_once 'config.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content-area">
        <?php
        // Data fetching logic with error handling
        $patientID = isset($_GET['patientID']) ? (int)$_GET['patientID'] : 0;
        $patient = null; 
        $medical = null; 
        $dental = null; 
        $clinicalFindings = null; 
        $treatments = null;
        $error_message = '';
        $success_message = '';

        // Check for success or error messages in the URL from redirect
        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            $success_message = "Patient record updated successfully!";
        }
        if (isset($_GET['error'])) {
            $error_message = htmlspecialchars($_GET['error']);
        }

        if ($patientID > 0) {
            try {
                // Fetch all patient data...
                $stmt = $pdo->prepare("SELECT * FROM Patients WHERE PatientID = ?");
                $stmt->execute([$patientID]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($patient) {
                    $stmt2 = $pdo->prepare("SELECT * FROM MedicalHistories WHERE PatientID = ? ORDER BY DateTaken DESC LIMIT 1");
                    $stmt2->execute([$patientID]);
                    $medical = $stmt2->fetch(PDO::FETCH_ASSOC);

                    $stmt3 = $pdo->prepare("SELECT * FROM DentalHistories WHERE PatientID = ? ORDER BY DateTaken DESC LIMIT 1");
                    $stmt3->execute([$patientID]);
                    $dental = $stmt3->fetch(PDO::FETCH_ASSOC);

                    $stmt4 = $pdo->prepare("SELECT * FROM ClinicalFindings WHERE PatientID = ?");
                    $stmt4->execute([$patientID]);
                    $clinicalFindings = $stmt4->fetchAll(PDO::FETCH_ASSOC);

                    $stmt5 = $pdo->prepare("SELECT * FROM Treatments WHERE PatientID = ?");
                    $stmt5->execute([$patientID]);
                    $treatments = $stmt5->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    if (!$error_message) $error_message = "Patient with ID " . htmlspecialchars($patientID) . " not found.";
                }

            } catch (PDOException $e) {
                error_log("Database Error: " . $e->getMessage());
                if (!$error_message) $error_message = "A database error occurred. Please contact support.";
            }
        } else {
            if (!$error_message) $error_message = "No patient ID was provided.";
        }
        ?>

        <div class="patient-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="avatar.png" alt="Patient Avatar" class="patient-avatar me-3">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <?php echo $patient ? htmlspecialchars($patient['FirstName'] . ' ' . $patient['LastName']) : 'Patient Not Found'; ?>
                    </h4>
                    <a href="#" class="event-link">
                        <i class="far fa-calendar-alt me-1"></i>
                        Event......
                        <span class="edit-text ms-2">Edit</span>
                    </a>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button type="submit" id="editBtn" form="patientForm" class="btn btn-primary me-2" <?php if (!$patient) echo 'disabled'; ?>>Change Data</button>
                <button type="button" id="cancelEditBtn" class="btn btn-secondary me-2">Cancel</button>
                <div class="dropdown">
                    <button class="btn btn-light" type="button" id="moreActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" <?php if (!$patient) echo 'disabled'; ?>>
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="moreActionsDropdown">
                        <li><a class="dropdown-item" href="#">Delete Patient</a></li>
                        <li><a class="dropdown-item" href="#">Print Record</a></li>
                        <li><a class="dropdown-item" href="#">Send Message</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link active" href="#">Patient Information</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Appointment History</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Next Treatment</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Medical Record</a></li>
        </ul>
        
        <div class="patient-form-content">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php elseif ($patient): ?>
                <form id="patientForm" method="post" action="update_patient.php?patientID=<?php echo htmlspecialchars($patientID); ?>">
                    
                    <h2 class="section-header">PATIENT INFORMATION RECORD</h2>
                    <div class="data-grid">
                        <div class="data-item">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control-plaintext" id="firstName" name="firstName" value="<?php echo htmlspecialchars($patient['FirstName'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control-plaintext" id="lastName" name="lastName" value="<?php echo htmlspecialchars($patient['LastName'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="middleName">Middle Name</label>
                            <input type="text" class="form-control-plaintext" id="middleName" name="middleName" value="<?php echo htmlspecialchars($patient['MiddleName'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="address">Address</label>
                            <input type="text" class="form-control-plaintext" id="address" name="address" value="<?php echo htmlspecialchars($patient['Address'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="birthdate">Birthdate</label>
                            <input type="date" class="form-control-plaintext" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($patient['Birthdate'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="religion">Religion</label>
                            <input type="text" class="form-control-plaintext" id="religion" name="religion" value="<?php echo htmlspecialchars($patient['Religion'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="civilStatus">Civil Status</label>
                            <input type="text" class="form-control-plaintext" id="civilStatus" name="civilStatus" value="<?php echo htmlspecialchars($patient['CivilStatus'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="nationality">Nationality</label>
                            <input type="text" class="form-control-plaintext" id="nationality" name="nationality" value="<?php echo htmlspecialchars($patient['Nationality'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="mobileNumber">Mobile Number</label>
                            <input type="text" class="form-control-plaintext" id="mobileNumber" name="mobileNumber" value="<?php echo htmlspecialchars($patient['MobileNumber'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="parentGuardianName">Parent/Guardian</label>
                            <input type="text" class="form-control-plaintext" id="parentGuardianName" name="parentGuardianName" value="<?php echo htmlspecialchars($patient['ParentGuardianName'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="occupation">Occupation</label>
                            <input type="text" class="form-control-plaintext" id="occupation" name="occupation" value="<?php echo htmlspecialchars($patient['Occupation'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="age">Age</label>
                            <input type="number" class="form-control-plaintext" id="age" name="age" value="<?php echo htmlspecialchars($patient['Age'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-select" disabled>
                                <option value="M" <?php if (($patient['Gender'] ?? '') == 'M') echo 'selected'; ?>>Male</option>
                                <option value="F" <?php if (($patient['Gender'] ?? '') == 'F') echo 'selected'; ?>>Female</option>
                                <option value="Other" <?php if (($patient['Gender'] ?? '') == 'Other') echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                        <div class="data-item">
                            <label for="nickname">Nickname</label>
                            <input type="text" class="form-control-plaintext" id="nickname" name="nickname" value="<?php echo htmlspecialchars($patient['Nickname'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="patientNumber">Patient Number</label>
                            <input type="text" class="form-control-plaintext" id="patientNumber" name="patientNumber" value="<?php echo htmlspecialchars($patient['PatientNumber'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="email">Email</label>
                            <input type="email" class="form-control-plaintext" id="email" name="email" value="<?php echo htmlspecialchars($patient['Email'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                    
                    <h2 class="section-header">MEDICAL INFORMATION</h2>
                    <div class="data-grid">
                        <?php
                            $medical_fields = [
                                'NameOfPhysician' => 'Name of Physician', 'PhysicianAddress' => 'Physician Address', 'PhysicianPhoneNumber' => 'Physician Phone', 'DateOfLastPhysicalExam' => 'Last Exam Date', 'BloodPressure' => 'Blood Pressure', 'RespiratoryRate' => 'Respiratory Rate', 'PulseRate' => 'Pulse Rate', 'Temperature' => 'Temperature'
                            ];
                            $medical_booleans = [
                                'AreYouInGoodHealth' => 'In Good Health?', 'UnderMedicalTreatment' => 'Under Medical Treatment?', 'HadSeriousIllness' => 'Had Serious Illness?', 'EverHospitalized' => 'Ever Hospitalized?', 'HighBloodPressure' => 'High Blood Pressure', 'EpilepsyConvulsions' => 'Epilepsy/Convulsions', 'AIDSOrHIVInfection' => 'AIDS/HIV', 'StomachTroublesUlcer' => 'Stomach Troubles/Ulcer', 'HeartFailure' => 'Heart Failure', 'RapidWeightLoss' => 'Rapid Weight Loss', 'RadiationTherapy' => 'Radiation Therapy', 'JointReplacement' => 'Joint Replacement', 'HeartSurgery' => 'Heart Surgery', 'HeartAttack' => 'Heart Attack', 'HeartDisease' => 'Heart Disease', 'HeartMurmur' => 'Heart Murmur', 'HepatitisOrLiverDisease' => 'Hepatitis/Liver Disease', 'RheumaticFever' => 'Rheumatic Fever', 'HayFeverAllergies' => 'Hay Fever/Allergies', 'RespiratoryProblems' => 'Respiratory Problems', 'HepatitisJaundice' => 'Hepatitis/Jaundice', 'Tuberculosis' => 'Tuberculosis', 'SwollenAnkles' => 'Swollen Ankles', 'KidneyDisease' => 'Kidney Disease', 'Diabetes' => 'Diabetes', 'JointInjuriesBloodDisease' => 'Joint Injuries/Blood Disease', 'ArthritisRheumatism' => 'Arthritis/Rheumatism', 'CancerTumor' => 'Cancer/Tumor', 'Anemia' => 'Anemia', 'Angina' => 'Angina', 'Asthma' => 'Asthma', 'ThyroidProblem' => 'Thyroid Problem', 'Emphysema' => 'Emphysema', 'BleedingProblems' => 'Bleeding Problems', 'Stroke' => 'Stroke', 'ChestPain' => 'Chest Pain'
                            ];
                        ?>
                        <?php foreach ($medical_fields as $field_name => $label): ?>
                            <div class="data-item">
                                <label for="<?php echo $field_name; ?>"><?php echo $label; ?></label>
                                <input type="<?php echo $field_name == 'DateOfLastPhysicalExam' ? 'date' : 'text'; ?>" class="form-control-plaintext" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo htmlspecialchars($medical[$field_name] ?? ''); ?>" readonly>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="data-item">
                            <label for="medications">Medications</label>
                            <textarea id="medications" name="medications" class="form-control-plaintext" readonly><?php echo htmlspecialchars($medical['Medications'] ?? ''); ?></textarea>
                        </div>
                        <div class="data-item">
                            <label for="allergies">Allergies</label>
                            <textarea id="allergies" name="allergies" class="form-control-plaintext" readonly><?php echo htmlspecialchars($medical['Allergies'] ?? ''); ?></textarea>
                        </div>
                         <div class="data-item">
                            <label for="otherConditionsDetails">Other Conditions Details</label>
                            <textarea id="otherConditionsDetails" name="otherConditionsDetails" class="form-control-plaintext" readonly><?php echo htmlspecialchars($medical['OtherConditionsDetails'] ?? ''); ?></textarea>
                        </div>

                        <div class="data-item grid-span-all">
                            <label>Do you have any of the following? (Check which apply)</label>
                            <div class="checkbox-container">
                                <?php foreach ($medical_booleans as $field_name => $label): ?>
                                <div class="checkbox-option">
                                    <input type="checkbox" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="1" <?php if (!empty($medical[$field_name]) && $medical[$field_name] == 1) echo 'checked'; ?> disabled>
                                    <label for="<?php echo $field_name; ?>"><?php echo $label; ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="section-header">DENTAL HISTORY</h2>
                    <div class="data-grid">
                        <div class="data-item">
                            <label for="previousDentist">Previous Dentist</label>
                            <input type="text" id="previousDentist" name="previousDentist" class="form-control-plaintext" value="<?php echo htmlspecialchars($dental['PreviousDentist'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="lastDentalVisit">Last Dental Visit</label>
                            <input type="date" id="lastDentalVisit" name="lastDentalVisit" class="form-control-plaintext" value="<?php echo htmlspecialchars($dental['LastDentalVisit'] ?? ''); ?>" readonly>
                        </div>
                        <div class="data-item">
                            <label for="chiefComplaint">Chief Complaint</label>
                            <textarea id="chiefComplaint" name="chiefComplaint" class="form-control-plaintext" readonly><?php echo htmlspecialchars($dental['ChiefComplaint'] ?? ''); ?></textarea>
                        </div>
                        <div class="data-item">
                            <label for="historyOfPresentIllness">History of Present Illness</label>
                            <textarea id="historyOfPresentIllness" name="historyOfPresentIllness" class="form-control-plaintext" readonly><?php echo htmlspecialchars($dental['HistoryOfPresentIllness'] ?? ''); ?></textarea>
                        </div>
                        <div class="data-item">
                            <label for="complications">Complications</label>
                            <textarea id="complications" name="complications" class="form-control-plaintext" readonly><?php echo htmlspecialchars($dental['Complications'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <h2 class="section-header">CLINICAL FINDINGS</h2>
                    <?php if ($clinicalFindings): ?>
                        <?php foreach ($clinicalFindings as $index => $finding): ?>
                        <div class="data-grid">
                           <input type="hidden" name="clinicalFindings[<?php echo $index; ?>][ClinicalFindingsID]" value="<?php echo htmlspecialchars($finding['ClinicalFindingsID']); ?>">
                            <div class="data-item">
                                    <label for="dateObserved<?php echo $index; ?>">Date Observed</label>
                                    <input type="date" id="dateObserved<?php echo $index; ?>" name="clinicalFindings[<?php echo $index; ?>][DateObserved]" class="form-control-plaintext" value="<?php echo htmlspecialchars($finding['DateObserved']); ?>" readonly>
                                </div>
                                <div class="data-item">
                                    <label for="toothNumber<?php echo $index; ?>">Tooth Number</label>
                                    <input type="text" id="toothNumber<?php echo $index; ?>" name="clinicalFindings[<?php echo $index; ?>][ToothNumber]" class="form-control-plaintext" value="<?php echo htmlspecialchars($finding['ToothNumber']); ?>" readonly>
                                </div>
                                <div class="data-item">
                                    <label for="diagnosis<?php echo $index; ?>">Diagnosis</label>
                                    <input type="text" id="diagnosis<?php echo $index; ?>" name="clinicalFindings[<?php echo $index; ?>][Diagnosis]" class="form-control-plaintext" value="<?php echo htmlspecialchars($finding['Diagnosis']); ?>" readonly>
                                </div>
                                <div class="data-item">
                                    <label for="proposedTreatment<?php echo $index; ?>">Proposed Treatment</label>
                                    <input type="text" id="proposedTreatment<?php echo $index; ?>" name="clinicalFindings[<?php echo $index; ?>][ProposedTreatment]" class="form-control-plaintext" value="<?php echo htmlspecialchars($finding['ProposedTreatment']); ?>" readonly>
                                </div>
                                <div class="data-item grid-span-all">
                                    <label for="remarks<?php echo $index; ?>">Remarks</label>
                                    <textarea id="remarks<?php echo $index; ?>" name="clinicalFindings[<?php echo $index; ?>][Remarks]" class="form-control-plaintext" readonly><?php echo htmlspecialchars($finding['Remarks']); ?></textarea>
                                </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="ms-1">No clinical findings recorded.</p>
                    <?php endif; ?>
                </form>

                <h2 class="section-header">TREATMENT RECORD</h2>
                <?php if ($treatments): ?>
                    <table class="table table-bordered treatment-table">
                        <thead>
                            <tr><th>Date</th><th>Procedure Done</th><th>Tooth #</th><th>Amount Charged</th><th>Amount Paid</th><th>Balance</th><th>Next Appointment</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($treatments as $treatment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($treatment['Date']); ?></td>
                                <td><?php echo 'Procedure ' . htmlspecialchars($treatment['ProcedureID']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['ToothNumber']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['AmountCharged']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['AmountPaid']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['Balance']); ?></td>
                                <td><?php echo htmlspecialchars($treatment['NextAppointment']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="ms-1">No treatment records found.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

        <!-- ================================== -->
    <!-- CORRECTED JAVASCRIPT FOR TOGGLE    -->
    <!-- ================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.getElementById('editBtn');
            const cancelBtn = document.getElementById('cancelEditBtn');
            const form = document.getElementById('patientForm');

            // Do nothing if the form doesn't exist on the page
            if (!form) return;

            const formElements = form.querySelectorAll('input, select, textarea');

            editBtn.addEventListener('click', function(event) {
                // Check if we are currently in "edit mode"
                const isEditMode = form.dataset.isEditMode === 'true';

                if (!isEditMode) {
                    // --- This is the FIRST click: ENTERING EDIT MODE ---
                    
                    // Prevent the form from submitting on this first click
                    event.preventDefault();

                    // Enable all form elements
                    formElements.forEach(el => {
                        el.removeAttribute('readonly');
                        el.removeAttribute('disabled');
                        if (el.classList.contains('form-control-plaintext')) {
                            el.classList.remove('form-control-plaintext');
                            if (el.tagName === 'SELECT') {
                                el.classList.add('form-select');
                            } else {
                                el.classList.add('form-control');
                            }
                        }
                    });

                    // Update button text and appearance
                    this.textContent = 'Save Changes';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');

                    // Mark that we are now in edit mode
                    form.dataset.isEditMode = 'true';

                    // Show the cancel button
                    cancelBtn.style.display = 'inline-block';
                }
                // --- This is the SECOND click: SAVING CHANGES ---
                // If isEditMode is true, this function does nothing. The button's default
                // 'submit' action (from type="submit" in the HTML) will take over and submit the form.
            });

            cancelBtn.addEventListener('click', function() {
                // Simply reload the page to discard any changes
                window.location.reload();
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>