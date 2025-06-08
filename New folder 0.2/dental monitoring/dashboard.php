<?php
// 1. START THE SESSION & GATEKEEPER
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 2. REQUIRE CONFIG
require_once 'config.php';

// 3. PAGE-SPECIFIC LOGIC: Fetching dashboard data
$total_patients = 0;
$total_staff = 0;
$recent_patients = [];
$error_message = '';

try {
    // Get total number of patients
    $stmt_patients = $pdo->query("SELECT COUNT(PatientID) FROM Patients");
    $total_patients = $stmt_patients->fetchColumn();

    // Get total number of staff/users
    $stmt_staff = $pdo->query("SELECT COUNT(UserID) FROM users");
    $total_staff = $stmt_staff->fetchColumn();

    // Get the 5 most recently added patients
    $stmt_recent = $pdo->query("SELECT PatientID, FirstName, LastName, MobileNumber FROM Patients ORDER BY PatientID DESC LIMIT 5");
    $recent_patients = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $error_message = "A database error occurred while fetching dashboard data.";
}

// --- Placeholder Data for features not yet built ---
// In the future, you would query your 'appointments' and 'treatments' tables here.
$todays_appointments_count = 0; // Placeholder
$total_revenue = 0.00; // Placeholder
$upcoming_appointments = [
    ['time' => '09:00 AM', 'patient_name' => 'John Doe', 'procedure' => 'Cleaning'],
    ['time' => '10:30 AM', 'patient_name' => 'Jane Smith', 'procedure' => 'Filling Consultation'],
    ['time' => '02:00 PM', 'patient_name' => 'Peter Jones', 'procedure' => 'Check-up']
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <!-- REQUIRED CSS LINKS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    
    <style>
        /* Dashboard-specific styles */
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }
        .stat-card .icon {
            font-size: 2.5rem;
            padding: 1rem;
            border-radius: 50%;
            margin-right: 1.5rem;
            color: #fff;
        }
        .stat-card .info h5 {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        .stat-card .info .display-4 {
            font-weight: 700;
        }
        .list-group-item {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content-area">
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- ============================ -->
        <!--      STATISTICS CARDS        -->
        <!-- ============================ -->
        <div class="row g-4 mb-4">
            <!-- Total Patients Card -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="icon" style="background-color: #cfe2ff;">
                        <i class="fas fa-users" style="color: #0d6efd;"></i>
                    </div>
                    <div class="info">
                        <h5>Total Patients</h5>
                        <h2 class="display-4"><?php echo $total_patients; ?></h2>
                    </div>
                </div>
            </div>
            <!-- Today's Appointments Card -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="icon" style="background-color: #d1e7dd;">
                        <i class="fas fa-calendar-check" style="color: #198754;"></i>
                    </div>
                    <div class="info">
                        <h5>Today's Appointments</h5>
                        <h2 class="display-4"><?php echo $todays_appointments_count; ?></h2>
                    </div>
                </div>
            </div>
            <!-- Total Staff Card -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="icon" style="background-color: #fff3cd;">
                        <i class="fas fa-user-shield" style="color: #ffc107;"></i>
                    </div>
                    <div class="info">
                        <h5>Staff Members</h5>
                        <h2 class="display-4"><?php echo $total_staff; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================ -->
        <!--  RECENT PATIENTS & SCHEDULE  -->
        <!-- ============================ -->
        <div class="row g-4">
            <!-- Recently Added Patients -->
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Recently Added Patients</h4>
                        <a href="patient_list.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($recent_patients)): ?>
                                <div class="p-3 text-center text-muted">No recent patients.</div>
                            <?php else: ?>
                                <?php foreach ($recent_patients as $patient): ?>
                                    <a href="patients.php?patientID=<?php echo $patient['PatientID']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($patient['FirstName'] . ' ' . $patient['LastName']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($patient['MobileNumber']); ?></small>
                                        </div>
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                         <h4 class="mb-0">Today's Schedule</h4>
                         <a href="reservations.php" class="btn btn-sm btn-outline-primary">View Calendar</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                             <?php foreach ($upcoming_appointments as $appt): ?>
                                <div class="list-group-item d-flex">
                                    <div class="me-3">
                                        <span class="badge bg-primary rounded-pill p-2"><?php echo $appt['time']; ?></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($appt['patient_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($appt['procedure']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>