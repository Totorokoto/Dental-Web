<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
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
        $patient = $medical = $dental = $clinicalFindings = $treatments = $allProcedures = null;
        $pastProcedures = [];
        $error_message = '';
        $success_message = '';

        // Handle status messages from redirects
        $status_messages = [
            'success' => 'Patient record updated successfully!',
            'created' => 'New patient record created successfully!',
            'finding_added' => 'New clinical finding added successfully!',
            'treatment_added' => 'New treatment record added successfully!',
            'finding_updated' => 'Clinical finding updated successfully!',
            'treatment_updated' => 'Treatment record updated successfully!',
            'finding_deleted' => 'Clinical finding deleted successfully!',
            'treatment_deleted' => 'Treatment record deleted successfully!',
        ];
        if (isset($_GET['status']) && array_key_exists($_GET['status'], $status_messages)) {
            $success_message = $status_messages[$_GET['status']];
        }
        if (isset($_GET['error'])) {
            $error_message = htmlspecialchars($_GET['error']);
        }

        if ($patientID > 0) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM Patients WHERE PatientID = ?");
                $stmt->execute([$patientID]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($patient) {
                    $stmt2 = $pdo->prepare("SELECT * FROM MedicalHistories WHERE PatientID = ? ORDER BY MedicalHistoryID DESC LIMIT 1");
                    $stmt2->execute([$patientID]);
                    $medical = $stmt2->fetch(PDO::FETCH_ASSOC);

                    $stmt3 = $pdo->prepare("SELECT * FROM DentalHistories WHERE PatientID = ? ORDER BY DentalHistoryID DESC LIMIT 1");
                    $stmt3->execute([$patientID]);
                    $dental = $stmt3->fetch(PDO::FETCH_ASSOC);

                    if ($dental) {
                        $stmt_past_proc = $pdo->prepare("SELECT * FROM PastDentalProcedures WHERE DentalHistoryID = ?");
                        $stmt_past_proc->execute([$dental['DentalHistoryID']]);
                        $pastProcedures = $stmt_past_proc->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $stmt4 = $pdo->prepare("SELECT * FROM ClinicalFindings WHERE PatientID = ? ORDER BY DateObserved DESC");
                    $stmt4->execute([$patientID]);
                    $clinicalFindings = $stmt4->fetchAll(PDO::FETCH_ASSOC);

                    $allProcedures = $pdo->query("SELECT ProcedureID, ProcedureName FROM procedures ORDER BY ProcedureName")->fetchAll(PDO::FETCH_ASSOC);

                    $stmt5 = $pdo->prepare("
                        SELECT t.*, p.ProcedureName 
                        FROM Treatments t
                        LEFT JOIN procedures p ON t.ProcedureID = p.ProcedureID
                        WHERE t.PatientID = ?
                        ORDER BY t.Date DESC
                    ");
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
                    <h4 class="mb-1 fw-bold"><?php echo $patient ? htmlspecialchars($patient['FirstName'] . ' ' . $patient['LastName']) : 'Patient Not Found'; ?></h4>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" id="editBtn" class="btn btn-primary me-2" <?php if (!$patient) echo 'disabled'; ?>>Change Data</button>
                <button type="submit" form="patientForm" id="saveBtn" class="btn btn-success me-2" style="display:none;">Save Changes</button>
                <button type="button" id="cancelBtn" class="btn btn-secondary me-2" style="display:none;">Cancel</button>
            </div>
        </div>
        
        <div class="patient-form-content">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
            <?php elseif ($patient): ?>
                <form id="patientForm" method="post" action="update_patient.php?patientID=<?php echo htmlspecialchars($patientID); ?>">
                    
                    <!-- ============================================== -->
                    <!-- PATIENT INFORMATION SECTION - FULL HTML RESTORED -->
                    <!-- ============================================== -->
                    <h2 class="section-header">PATIENT INFORMATION</h2>
                    <fieldset id="patientInfoSection" disabled>
                        <div class="data-grid">
                            <div class="data-item"><label>First Name</label><input type="text" class="form-control" name="firstName" value="<?php echo htmlspecialchars($patient['FirstName'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Last Name</label><input type="text" class="form-control" name="lastName" value="<?php echo htmlspecialchars($patient['LastName'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Middle Name</label><input type="text" class="form-control" name="middleName" value="<?php echo htmlspecialchars($patient['MiddleName'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Address</label><input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($patient['Address'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Birthdate</label><input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($patient['Birthdate'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Religion</label><input type="text" class="form-control" name="religion" value="<?php echo htmlspecialchars($patient['Religion'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Civil Status</label><input type="text" class="form-control" name="civilStatus" value="<?php echo htmlspecialchars($patient['CivilStatus'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Nationality</label><input type="text" class="form-control" name="nationality" value="<?php echo htmlspecialchars($patient['Nationality'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Mobile Number</label><input type="text" class="form-control" name="mobileNumber" value="<?php echo htmlspecialchars($patient['MobileNumber'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Parent/Guardian</label><input type="text" class="form-control" name="parentGuardianName" value="<?php echo htmlspecialchars($patient['ParentGuardianName'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Occupation</label><input type="text" class="form-control" name="occupation" value="<?php echo htmlspecialchars($patient['Occupation'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Age</label><input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($patient['Age'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Gender</label><select name="gender" class="form-select"><option value="M" <?php if (($patient['Gender'] ?? '') == 'M') echo 'selected'; ?>>Male</option><option value="F" <?php if (($patient['Gender'] ?? '') == 'F') echo 'selected'; ?>>Female</option><option value="Other" <?php if (($patient['Gender'] ?? '') == 'Other') echo 'selected'; ?>>Other</option></select></div>
                            <div class="data-item"><label>Nickname</label><input type="text" class="form-control" name="nickname" value="<?php echo htmlspecialchars($patient['Nickname'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Patient Number</label><input type="text" class="form-control" name="patientNumber" value="<?php echo htmlspecialchars($patient['PatientNumber'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Email</label><input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($patient['Email'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Consent Given On</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($patient['ConsentTimestamp'] ?? 'N/A'); ?>" readonly></div>
                        </div>
                    </fieldset>

                    <!-- ============================================== -->
                    <!-- MEDICAL INFORMATION SECTION - FULL HTML RESTORED -->
                    <!-- ============================================== -->
                    <h2 class="section-header">MEDICAL INFORMATION</h2>
                    <fieldset id="medicalInfoSection" disabled>
                       <div class="data-grid">
                            <div class="data-item"><label>Name of Physician</label><input type="text" class="form-control" name="NameOfPhysician" value="<?php echo htmlspecialchars($medical['NameOfPhysician'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Physician Address</label><input type="text" class="form-control" name="PhysicianAddress" value="<?php echo htmlspecialchars($medical['PhysicianAddress'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Physician Phone</label><input type="text" class="form-control" name="PhysicianPhoneNumber" value="<?php echo htmlspecialchars($medical['PhysicianPhoneNumber'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Last Exam Date</label><input type="date" class="form-control" name="DateOfLastPhysicalExam" value="<?php echo htmlspecialchars($medical['DateOfLastPhysicalExam'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Blood Pressure</label><input type="text" class="form-control" name="BloodPressure" value="<?php echo htmlspecialchars($medical['BloodPressure'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Respiratory Rate</label><input type="text" class="form-control" name="RespiratoryRate" value="<?php echo htmlspecialchars($medical['RespiratoryRate'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Pulse Rate</label><input type="text" class="form-control" name="PulseRate" value="<?php echo htmlspecialchars($medical['PulseRate'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Temperature</label><input type="text" class="form-control" name="Temperature" value="<?php echo htmlspecialchars($medical['Temperature'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Medical Treatment Details</label><textarea class="form-control" name="TreatmentDetails"><?php echo htmlspecialchars($medical['TreatmentDetails'] ?? ''); ?></textarea></div>
                            <div class="data-item"><label>Serious Illness/Operation Details</label><textarea class="form-control" name="IllnessDetails"><?php echo htmlspecialchars($medical['IllnessDetails'] ?? ''); ?></textarea></div>
                            <div class="data-item"><label>Hospitalization Details</label><textarea class="form-control" name="HospitalizationDetails"><?php echo htmlspecialchars($medical['HospitalizationDetails'] ?? ''); ?></textarea></div>
                            <div class="data-item"><label>Diet Drugs/Pills</label><textarea class="form-control" name="DietaryRestrictions"><?php echo htmlspecialchars($medical['DietaryRestrictions'] ?? ''); ?></textarea></div>
                            <div class="data-item"><label>Alcohol Use</label><input type="text" class="form-control" name="UseAlcohol" value="<?php echo htmlspecialchars($medical['UseAlcohol'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Tobacco Use</label><input type="text" class="form-control" name="UseTobacco" value="<?php echo htmlspecialchars($medical['UseTobacco'] ?? ''); ?>"></div>
                        </div>
                        <div class="data-item grid-span-all"><label>Allergies</label><div class="checkbox-container"><div class="checkbox-option"><input type="checkbox" name="AllergyToAnesthetics" value="1" <?php if(!empty($medical['AllergyToAnesthetics'])) echo 'checked';?>><label>Local Anesthetics</label></div><div class="checkbox-option"><input type="checkbox" name="AllergyToAspirin" value="1" <?php if(!empty($medical['AllergyToAspirin'])) echo 'checked';?>><label>Aspirin</label></div><div class="checkbox-option"><input type="checkbox" name="AllergyToLatex" value="1" <?php if(!empty($medical['AllergyToLatex'])) echo 'checked';?>><label>Latex</label></div><div class="checkbox-option"><input type="checkbox" name="AllergyToPenicillin" value="1" <?php if(!empty($medical['AllergyToPenicillin'])) echo 'checked';?>><label>Penicillin/Antibiotics</label></div></div><label for="allergies" class="mt-2">Other Allergies:</label><textarea id="allergies" name="allergies" class="form-control"><?php echo htmlspecialchars($medical['Allergies'] ?? ''); ?></textarea></div>
                        <div class="data-item grid-span-all"><label>Do you have any of the following? (Check which apply)</label><div class="checkbox-container"><?php $medical_booleans = [ 'AreYouInGoodHealth' => 'In Good Health?', 'UnderMedicalTreatment' => 'Under Medical Treatment?', 'HadSeriousIllness' => 'Had Serious Illness?', 'EverHospitalized' => 'Ever Hospitalized?', 'HighBloodPressure' => 'High Blood Pressure', 'LowBloodPressure' => 'Low Blood Pressure', 'EpilepsyConvulsions' => 'Epilepsy/Convulsions', 'AIDSOrHIVInfection' => 'AIDS/HIV', 'StomachTroublesUlcer' => 'Stomach Troubles/Ulcer', 'HeartFailure' => 'Heart Failure', 'RapidWeightLoss' => 'Rapid Weight Loss', 'RadiationTherapy' => 'Radiation Therapy', 'JointReplacement' => 'Joint Replacement', 'HeartSurgery' => 'Heart Surgery', 'HeartAttack' => 'Heart Attack', 'HeartDisease' => 'Heart Disease', 'HeartMurmur' => 'Heart Murmur', 'HepatitisOrLiverDisease' => 'Hepatitis/Liver Disease', 'RheumaticFever' => 'Rheumatic Fever', 'HayFeverAllergies' => 'Hay Fever/Allergies', 'RespiratoryProblems' => 'Respiratory Problems', 'HepatitisJaundice' => 'Hepatitis/Jaundice', 'Tuberculosis' => 'Tuberculosis', 'SwollenAnkles' => 'Swollen Ankles', 'KidneyDisease' => 'Kidney Disease', 'Diabetes' => 'Diabetes', 'JointInjuriesBloodDisease' => 'Joint Injuries/Blood Disease', 'ArthritisRheumatism' => 'Arthritis/Rheumatism', 'CancerTumor' => 'Cancer/Tumor', 'Anemia' => 'Anemia', 'Angina' => 'Angina', 'Asthma' => 'Asthma', 'ThyroidProblem' => 'Thyroid Problem', 'Emphysema' => 'Emphysema', 'BleedingProblems' => 'Bleeding Problems', 'Stroke' => 'Stroke', 'ChestPain' => 'Chest Pain' ]; foreach ($medical_booleans as $field_name => $label): ?><div class="checkbox-option"><input type="checkbox" name="<?php echo $field_name; ?>" value="1" <?php if (!empty($medical[$field_name])) echo 'checked'; ?>><label><?php echo $label; ?></label></div><?php endforeach; ?></div></div>
                    </fieldset>

                    <!-- ============================================== -->
                    <!-- DENTAL HISTORY SECTION - FULL HTML RESTORED   -->
                    <!-- ============================================== -->
                    <h2 class="section-header">DENTAL HISTORY</h2>
                    <fieldset id="dentalInfoSection" disabled>
                        <div class="data-grid">
                            <div class="data-item"><label>Previous Dentist</label><input type="text" name="previousDentist" class="form-control" value="<?php echo htmlspecialchars($dental['PreviousDentist'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Last Dental Visit</label><input type="date" name="lastDentalVisit" class="form-control" value="<?php echo htmlspecialchars($dental['LastDentalVisit'] ?? ''); ?>"></div>
                            <div class="data-item"><label>Chief Complaint</label><textarea name="chiefComplaint" class="form-control"><?php echo htmlspecialchars($dental['ChiefComplaint'] ?? ''); ?></textarea></div>
                            <div class="data-item"><label>History of Present Illness</label><textarea name="historyOfPresentIllness" class="form-control"><?php echo htmlspecialchars($dental['HistoryOfPresentIllness'] ?? ''); ?></textarea></div>
                            <div class="data-item"><label>Complications from Past Treatment</label><textarea name="complications" class="form-control"><?php echo htmlspecialchars($dental['Complications'] ?? ''); ?></textarea></div>
                            <div class="data-item grid-span-all"><label>Procedures Done in the Past</label><div class="checkbox-container"><?php $past_procedures_options = ['Perio', 'Resto', 'O.S.', 'Prostho', 'Endo', 'Ortho']; $checked_procedures = array_column($pastProcedures, 'ProcedureType'); $other_details = ''; foreach($pastProcedures as $proc) { if ($proc['ProcedureType'] === 'Other') { $other_details = $proc['Details']; } } foreach($past_procedures_options as $proc_opt): ?><div class="checkbox-option"><input type="checkbox" name="pastProcedures[]" value="<?php echo $proc_opt; ?>" <?php if(in_array($proc_opt, $checked_procedures)) echo 'checked';?>><label><?php echo $proc_opt; ?></label></div><?php endforeach; ?></div><label class="mt-2">Other past procedures:</label><input type="text" name="pastProceduresOther" class="form-control" value="<?php echo htmlspecialchars($other_details); ?>"></div>
                        </div>
                    </fieldset>
                </form>

                <!-- Clinical Findings Section -->
                <h2 class="section-header mt-4">CLINICAL FINDINGS</h2>
                <div class="text-end mb-2"><button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addFindingModal"><i class="fas fa-plus"></i> Add New Finding</button></div>
                <div class="table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>Date</th><th>Tooth#</th><th>Diagnosis</th><th>Proposed Tx</th><th>Remarks</th><th>Actions</th></tr></thead><tbody>
                <?php if ($clinicalFindings): foreach ($clinicalFindings as $finding): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($finding['DateObserved']); ?></td>
                        <td><?php echo htmlspecialchars($finding['ToothNumber']); ?></td>
                        <td><?php echo htmlspecialchars($finding['Diagnosis']); ?></td>
                        <td><?php echo htmlspecialchars($finding['ProposedTreatment']); ?></td>
                        <td><?php echo htmlspecialchars($finding['Remarks']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-finding-btn" data-bs-toggle="modal" data-bs-target="#editFindingModal" data-finding='<?php echo json_encode($finding); ?>'><i class="fas fa-edit"></i></button>
                            <a href="delete_finding.php?id=<?php echo $finding['ClinicalFindingsID']; ?>&patientID=<?php echo $patientID; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this finding? This cannot be undone.')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center">No clinical findings recorded.</td></tr>
                <?php endif; ?>
                </tbody></table></div>

                <!-- Treatment Record Section -->
                <h2 class="section-header mt-4">TREATMENT RECORD</h2>
                <div class="text-end mb-2"><button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addTreatmentModal"><i class="fas fa-plus"></i> Add Treatment Record</button></div>
                <div class="table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>Date</th><th>Procedure</th><th>Tooth#</th><th>Charged</th><th>Paid</th><th>Balance</th><th>Next Appt.</th><th>Actions</th></tr></thead><tbody>
                <?php if ($treatments): foreach ($treatments as $treatment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($treatment['Date']); ?></td>
                        <td><?php echo htmlspecialchars($treatment['ProcedureName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($treatment['ToothNumber']); ?></td>
                        <td><?php echo number_format((float)$treatment['AmountCharged'], 2); ?></td>
                        <td><?php echo number_format((float)$treatment['AmountPaid'], 2); ?></td>
                        <td><?php echo number_format((float)$treatment['Balance'], 2); ?></td>
                        <td><?php echo htmlspecialchars($treatment['NextAppointment']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-treatment-btn" data-bs-toggle="modal" data-bs-target="#editTreatmentModal" data-treatment='<?php echo json_encode($treatment); ?>'><i class="fas fa-edit"></i></button>
                            <a href="delete_treatment.php?id=<?php echo $treatment['TreatmentID']; ?>&patientID=<?php echo $patientID; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this treatment record? This cannot be undone.')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center">No treatment records found.</td></tr>
                <?php endif; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>

        <!-- ================================== -->
        <!-- ALL MODALS FOR THIS PAGE           -->
        <!-- ================================== -->
        <div class="modal fade" id="addFindingModal" tabindex="-1" aria-labelledby="addFindingModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="addFindingModalLabel">Add New Clinical Finding</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><form action="create_finding.php" method="POST"><input type="hidden" name="patientID" value="<?php echo $patientID; ?>"><div class="mb-3"><label class="form-label">Date Observed</label><input type="date" name="DateObserved" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div><div class="mb-3"><label class="form-label">Tooth Number</label><input type="text" name="ToothNumber" class="form-control"></div><div class="mb-3"><label class="form-label">Diagnosis</label><input type="text" name="Diagnosis" class="form-control"></div><div class="mb-3"><label class="form-label">Proposed Treatment</label><input type="text" name="ProposedTreatment" class="form-control"></div><div class="mb-3"><label class="form-label">Remarks</label><textarea name="Remarks" class="form-control"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Finding</button></div></form></div></div></div></div>
        <div class="modal fade" id="editFindingModal" tabindex="-1" aria-labelledby="editFindingModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="editFindingModalLabel">Edit Clinical Finding</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><form id="editFindingForm" action="update_finding.php" method="POST"><input type="hidden" name="patientID" value="<?php echo $patientID; ?>"><input type="hidden" name="ClinicalFindingsID" id="editFindingId"><div class="mb-3"><label class="form-label">Date Observed</label><input type="date" name="DateObserved" id="editFindingDate" class="form-control" required></div><div class="mb-3"><label class="form-label">Tooth Number</label><input type="text" name="ToothNumber" id="editFindingTooth" class="form-control"></div><div class="mb-3"><label class="form-label">Diagnosis</label><input type="text" name="Diagnosis" id="editFindingDiagnosis" class="form-control"></div><div class="mb-3"><label class="form-label">Proposed Treatment</label><input type="text" name="ProposedTreatment" id="editFindingProposed" class="form-control"></div><div class="mb-3"><label class="form-label">Remarks</label><textarea name="Remarks" id="editFindingRemarks" class="form-control"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Update Finding</button></div></form></div></div></div></div>
        <div class="modal fade" id="addTreatmentModal" tabindex="-1" aria-labelledby="addTreatmentModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="addTreatmentModalLabel">Add New Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><form action="create_treatment.php" method="POST"><input type="hidden" name="patientID" value="<?php echo $patientID; ?>"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Date</label><input type="date" name="Date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div><div class="col-md-6 mb-3"><label class="form-label">Procedure Done</label><select name="ProcedureID" class="form-select" required><option value="">-- Select --</option><?php if($allProcedures) foreach ($allProcedures as $proc): ?><option value="<?php echo $proc['ProcedureID']; ?>"><?php echo htmlspecialchars($proc['ProcedureName']); ?></option><?php endforeach; ?></select></div><div class="col-md-6 mb-3"><label class="form-label">Tooth Number</label><input type="text" name="ToothNumber" class="form-control"></div><div class="col-md-6 mb-3"><label class="form-label">Next Appointment</label><input type="date" name="NextAppointment" class="form-control"></div><div class="col-md-4 mb-3"><label class="form-label">Amount Charged</label><input type="number" step="0.01" name="AmountCharged" class="form-control balance-calc-add"></div><div class="col-md-4 mb-3"><label class="form-label">Amount Paid</label><input type="number" step="0.01" name="AmountPaid" class="form-control balance-calc-add"></div><div class="col-md-4 mb-3"><label class="form-label">Balance</label><input type="number" step="0.01" name="Balance" class="form-control balance-calc-add" readonly></div><div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="Notes" class="form-control"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Treatment</button></div></form></div></div></div></div>
        <div class="modal fade" id="editTreatmentModal" tabindex="-1" aria-labelledby="editTreatmentModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="editTreatmentModalLabel">Edit Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><form id="editTreatmentForm" action="update_treatment.php" method="POST"><input type="hidden" name="patientID" value="<?php echo $patientID; ?>"><input type="hidden" name="TreatmentID" id="editTreatmentId"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Date</label><input type="date" name="Date" id="editTreatmentDate" class="form-control" required></div><div class="col-md-6 mb-3"><label class="form-label">Procedure Done</label><select name="ProcedureID" id="editTreatmentProcedure" class="form-select" required><option value="">-- Select --</option><?php if($allProcedures) foreach ($allProcedures as $proc): ?><option value="<?php echo $proc['ProcedureID']; ?>"><?php echo htmlspecialchars($proc['ProcedureName']); ?></option><?php endforeach; ?></select></div><div class="col-md-6 mb-3"><label class="form-label">Tooth Number</label><input type="text" name="ToothNumber" id="editTreatmentTooth" class="form-control"></div><div class="col-md-6 mb-3"><label class="form-label">Next Appointment</label><input type="date" name="NextAppointment" id="editTreatmentNextAppt" class="form-control"></div><div class="col-md-4 mb-3"><label class="form-label">Amount Charged</label><input type="number" step="0.01" name="AmountCharged" id="editAmountCharged" class="form-control balance-calc-edit"></div><div class="col-md-4 mb-3"><label class="form-label">Amount Paid</label><input type="number" step="0.01" name="AmountPaid" id="editAmountPaid" class="form-control balance-calc-edit"></div><div class="col-md-4 mb-3"><label class="form-label">Balance</label><input type="number" step="0.01" name="Balance" id="editBalance" class="form-control" readonly></div><div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="Notes" id="editTreatmentNotes" class="form-control"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Update Treatment</button></div></form></div></div></div></div>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Main form edit/save/cancel toggle
            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const formSections = document.querySelectorAll('fieldset');
            if(editBtn) {
                editBtn.addEventListener('click', () => {
                    formSections.forEach(section => section.disabled = false);
                    editBtn.style.display = 'none';
                    saveBtn.style.display = 'inline-block';
                    cancelBtn.style.display = 'inline-block';
                });
                cancelBtn.addEventListener('click', () => window.location.reload());
            }

            // Function to handle balance calculation
            function setupBalanceCalculation(containerSelector, inputsClass) {
                const container = document.querySelector(containerSelector);
                if (!container) return;
                
                const chargedInput = container.querySelector(`[name="AmountCharged"].${inputsClass}`);
                const paidInput = container.querySelector(`[name="AmountPaid"].${inputsClass}`);
                const balanceInput = container.querySelector(`[name="Balance"]`);

                const calculate = () => {
                    const charged = parseFloat(chargedInput.value) || 0;
                    const paid = parseFloat(paidInput.value) || 0;
                    balanceInput.value = (charged - paid).toFixed(2);
                };

                chargedInput.addEventListener('input', calculate);
                paidInput.addEventListener('input', calculate);
            }

            // Setup calculation for both modals
            setupBalanceCalculation('#addTreatmentModal', 'balance-calc-add');
            setupBalanceCalculation('#editTreatmentModal', 'balance-calc-edit');
            
            // Populate EDIT Finding Modal
            const editFindingModal = document.getElementById('editFindingModal');
            if(editFindingModal) {
                editFindingModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const finding = JSON.parse(button.getAttribute('data-finding'));
                    document.getElementById('editFindingId').value = finding.ClinicalFindingsID;
                    document.getElementById('editFindingDate').value = finding.DateObserved;
                    document.getElementById('editFindingTooth').value = finding.ToothNumber;
                    document.getElementById('editFindingDiagnosis').value = finding.Diagnosis;
                    document.getElementById('editFindingProposed').value = finding.ProposedTreatment;
                    document.getElementById('editFindingRemarks').value = finding.Remarks;
                });
            }
            
            // Populate EDIT Treatment Modal
            const editTreatmentModal = document.getElementById('editTreatmentModal');
            if(editTreatmentModal) {
                editTreatmentModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const treatment = JSON.parse(button.getAttribute('data-treatment'));
                    document.getElementById('editTreatmentId').value = treatment.TreatmentID;
                    document.getElementById('editTreatmentDate').value = treatment.Date;
                    document.getElementById('editTreatmentProcedure').value = treatment.ProcedureID;
                    document.getElementById('editTreatmentTooth').value = treatment.ToothNumber;
                    document.getElementById('editTreatmentNextAppt').value = treatment.NextAppointment;
                    document.getElementById('editAmountCharged').value = treatment.AmountCharged;
                    document.getElementById('editAmountPaid').value = treatment.AmountPaid;
                    document.getElementById('editBalance').value = treatment.Balance;
                    document.getElementById('editTreatmentNotes').value = treatment.Notes;
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>