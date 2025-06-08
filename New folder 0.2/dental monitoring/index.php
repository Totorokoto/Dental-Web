<?php
require_once 'config.php'; // Database connection

// Assuming you have a PatientID (e.g., from a GET request)
$patientID = isset($_GET['patientID']) ? $_GET['patientID'] : 1; // Default to PatientID 1

// Fetch Patient Data
try {
    $stmt = $pdo->prepare("SELECT FirstName, LastName, Age, Gender, Email, MobileNumber, Address FROM Patients WHERE PatientID = ?");
    $stmt->execute([$patientID]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT BloodPressure, OtherConditions FROM MedicalHistories WHERE PatientID = ? ORDER BY DateTaken DESC LIMIT 1");
    $stmt2->execute([$patientID]);
    $medical = $stmt2->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    $patient = null;
    $medical = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

            <!-- Sidebar -->

            <?php include 'sidebar.php'; ?>
            <?php include 'topbar.php'; ?>

            <!-- Main Content -->
           <div class="main-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="patient-details-header">
          <div class="patient-avatar">
            <img src="patient_avatar.png" alt="Patient Avatar" class="rounded-circle">
          </div>
          <div class="patient-info">
            <h2>Patient's Name</h2>
            <div class="event-info">
              <i class="far fa-calendar-alt"></i> Event......
              <a href="#" class="btn btn-sm btn-link">Edit</a>
            </div>
          </div>
          <div class="patient-actions">
            <button class="btn btn-primary">Change Data</button>
            <div class="dropdown">
              <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="#">Action 1</a>
                <a class="dropdown-item" href="#">Action 2</a>
                <a class="dropdown-item" href="#">Action 3</a>
              </div>
            </div>
          </div>
        </div>
        <div class="patient-details-tabs">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="patient-info-tab" data-toggle="tab" href="#patient-info" role="tab" aria-controls="patient-info" aria-selected="true">Patient Information</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="appointment-history-tab" data-toggle="tab" href="#appointment-history" role="tab" aria-controls="appointment-history" aria-selected="false">Appointment History</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="next-treatment-tab" data-toggle="tab" href="#next-treatment" role="tab" aria-controls="next-treatment" aria-selected="false">Next Treatment</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="medical-record-tab" data-toggle="tab" href="#medical-record" role="tab" aria-controls="medical-record" aria-selected="false">Medical Record</a>
            </li>
          </ul>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="patient-info" role="tabpanel" aria-labelledby="patient-info-tab">
              <div class="patient-data-section">
                <h4>PATIENT DATA</h4>
                <div class="row">
                  <div class="col-md-4">
                    <div class="data-field">
                      <h6>Age</h6>
                      <div class="placeholder-box"></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="data-field">
                      <h6>Gender</h6>
                      <div class="placeholder-box"></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="data-field">
                      <h6>Email Address</h6>
                      <div class="placeholder-box"></div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="data-field">
                      <h6>Mobile number</h6>
                      <div class="placeholder-box"></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="data-field">
                      <h6>Address</h6>
                      <div class="placeholder-box"></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                  </div>
                </div>
              </div>
              <div class="medical-data-section">
                <h4>MEDICAL DATA <span class="last-updated">Last Update DD/MM/YY</span></h4>
                <div class="row">
                  <div class="col-md-4">
                    <div class="data-field">
                      <h6>Blood pressure</h6>
                      <div class="placeholder-box"></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="data-field">
                      <h6>Particular sickness</h6>
                      <div class="placeholder-box"></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="appointment-history" role="tabpanel" aria-labelledby="appointment-history-tab">
              <p>Appointment History content here...</p>
            </div>
            <div class="tab-pane fade" id="next-treatment" role="tabpanel" aria-labelledby="next-treatment-tab">
              <p>Next Treatment content here...</p>
            </div>
            <div class="tab-pane fade" id="medical-record" role="tabpanel" aria-labelledby="medical-record-tab">
              <p>Medical Record content here...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    </div>
  </div>

  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const patientID = <?php echo isset($patientID) ? json_encode(intval($patientID)) : 0; ?>;
    </script>
    <script src="script.js"></script>
</body>

</html>
