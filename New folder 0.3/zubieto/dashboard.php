<?php
// 1. START THE SESSION & GATEKEEPER
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 2. REQUIRE CONFIG
require_once 'config.php';

// 3. UPGRADED: Fetching all dashboard and analytics data
$error_message = '';
try {
    // --- CORE STATS ---
    $total_patients = $pdo->query("SELECT COUNT(PatientID) FROM Patients")->fetchColumn();
    $total_staff = $pdo->query("SELECT COUNT(UserID) FROM users")->fetchColumn();
    $todays_appointments_count = $pdo->query("SELECT COUNT(TreatmentID) FROM Treatments WHERE DATE(NextAppointment) = CURDATE()")->fetchColumn();
    $revenue_this_month = $pdo->query("SELECT SUM(AmountPaid) FROM Treatments WHERE MONTH(Date) = MONTH(CURDATE()) AND YEAR(Date) = YEAR(CURDATE())")->fetchColumn();

    // --- DEMOGRAPHIC STATS ---
    $gender_counts = $pdo->query("SELECT SUM(CASE WHEN Gender = 'M' THEN 1 ELSE 0 END) as male_count, SUM(CASE WHEN Gender = 'F' THEN 1 ELSE 0 END) as female_count FROM Patients")->fetch(PDO::FETCH_ASSOC);
    $child_count = $pdo->query("SELECT COUNT(*) FROM Patients WHERE Age < 18")->fetchColumn();

    // --- DATA FOR CHARTS ---
    $age_distribution = $pdo->query("SELECT SUM(CASE WHEN Age BETWEEN 0 AND 17 THEN 1 ELSE 0 END) AS '0-17', SUM(CASE WHEN Age BETWEEN 18 AND 30 THEN 1 ELSE 0 END) AS '18-30', SUM(CASE WHEN Age BETWEEN 31 AND 50 THEN 1 ELSE 0 END) AS '31-50', SUM(CASE WHEN Age > 50 THEN 1 ELSE 0 END) AS '51+' FROM Patients")->fetch(PDO::FETCH_ASSOC);
    $age_labels = json_encode(array_keys($age_distribution));
    $age_data = json_encode(array_values($age_distribution));

    $popular_procedures_stmt = $pdo->query("SELECT pr.ProcedureName, COUNT(t.TreatmentID) as count FROM Treatments t JOIN procedures pr ON t.ProcedureID = pr.ProcedureID WHERE t.ProcedureID IS NOT NULL GROUP BY pr.ProcedureName ORDER BY count DESC LIMIT 5");
    $popular_procedures = $popular_procedures_stmt->fetchAll(PDO::FETCH_ASSOC);
    $proc_labels = json_encode(array_column($popular_procedures, 'ProcedureName'));
    $proc_data = json_encode(array_column($popular_procedures, 'count'));

    // --- DATA FOR LISTS (RECENT PATIENTS & TODAY'S SCHEDULE) ---
    $recent_patients = $pdo->query("SELECT PatientID, FirstName, LastName, MobileNumber FROM Patients ORDER BY PatientID DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_today_schedule = $pdo->prepare("
        SELECT t.NextAppointment, p.PatientID, p.FirstName, p.LastName, pr.ProcedureName
        FROM Treatments t
        JOIN Patients p ON t.PatientID = p.PatientID
        LEFT JOIN procedures pr ON t.ProcedureID = pr.ProcedureID
        WHERE DATE(t.NextAppointment) = CURDATE()
        ORDER BY t.NextAppointment ASC
    ");

    $stmt_today_schedule->execute();
    $upcoming_appointments_db = $stmt_today_schedule->fetchAll(PDO::FETCH_ASSOC);

    $upcoming_appointments = [];
    foreach($upcoming_appointments_db as $appt) {
        $upcoming_appointments[] = [
            'patient_name' => htmlspecialchars($appt['FirstName'] . ' ' . $appt['LastName']),
            'procedure' => htmlspecialchars($appt['ProcedureName'] ?? 'Check-up'),
            'patient_id' => $appt['PatientID'],
            'time' => date('g:i A', strtotime($appt['NextAppointment']))
        ];
    }

} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $error_message = "A database error occurred while fetching dashboard data.";
    $total_patients = $total_staff = $todays_appointments_count = $revenue_this_month = $child_count = 0;
    $gender_counts = ['male_count' => 0, 'female_count' => 0];
    $recent_patients = $upcoming_appointments = [];
    $age_labels = $age_data = $proc_labels = $proc_data = '[]';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .stat-card { background-color: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 8px rgba(0,0,0,0.05); display: flex; align-items: center; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .stat-card .icon { font-size: 2.5rem; padding: 1rem; border-radius: 50%; margin-right: 1.5rem; color: #fff; }
        .stat-card .info h5 { font-size: 1rem; color: #6c757d; margin-bottom: 0.25rem; }
        .stat-card .info .display-4, .stat-card .info h3 { font-weight: 700; }
        .list-group-item { cursor: pointer; transition: background-color 0.2s ease; }
        .list-group-item:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content-area">
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3"><a href="patient_list.php" class="text-decoration-none"><div class="stat-card"><div class="icon" style="background-color: #cfe2ff;"><i class="fas fa-users" style="color: #0d6efd;"></i></div><div class="info"><h5>Total Patients</h5><h2 class="display-4"><?php echo $total_patients; ?></h2></div></div></a></div>
            <div class="col-md-6 col-lg-3"><a href="reservations.php" class="text-decoration-none"><div class="stat-card"><div class="icon" style="background-color: #d1e7dd;"><i class="fas fa-calendar-check" style="color: #198754;"></i></div><div class="info"><h5>Today's Appointments</h5><h2 class="display-4"><?php echo $todays_appointments_count ?? 0; ?></h2></div></div></a></div>
            <div class="col-md-6 col-lg-3"><a href="staff_list.php" class="text-decoration-none"><div class="stat-card"><div class="icon" style="background-color: #fff3cd;"><i class="fas fa-user-shield" style="color: #ffc107;"></i></div><div class="info"><h5>Staff Members</h5><h2 class="display-4"><?php echo $total_staff; ?></h2></div></div></a></div>
            <div class="col-md-6 col-lg-3"><a href="treatments.php?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-t'); ?>" class="text-decoration-none"><div class="stat-card"><div class="icon" style="background-color: #d1e7dd;"><i class="fas fa-dollar-sign" style="color: #198754;"></i></div><div class="info"><h5>Revenue This Month</h5><h2 class="display-4">₱<?php echo number_format($revenue_this_month ?? 0, 2); ?></h2></div></div></a></div>
        </div>

        <div class="row g-4 mb-4">
             <div class="col-md-4"><div class="stat-card p-3"><div class="info w-100 text-center"><h5>Male Patients</h5><h3><?php echo $gender_counts['male_count'] ?? 0; ?></h3></div></div></div>
             <div class="col-md-4"><div class="stat-card p-3"><div class="info w-100 text-center"><h5>Female Patients</h5><h3><?php echo $gender_counts['female_count'] ?? 0; ?></h3></div></div></div>
             <div class="col-md-4"><div class="stat-card p-3"><div class="info w-100 text-center"><h5>Child Patients (<18)</h5><h3><?php echo $child_count ?? 0; ?></h3></div></div></div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-lg-7"><div class="card h-100"><div class="card-header"><h4 class="mb-0">Patient Age Distribution</h4></div><div class="card-body d-flex justify-content-center align-items-center"><?php if (empty(array_filter(json_decode($age_data, true)))): ?><p class="text-muted">Not enough data to generate age distribution chart.</p><?php else: ?><canvas id="ageDistributionChart"></canvas><?php endif; ?></div></div></div>
            <div class="col-lg-5"><div class="card h-100"><div class="card-header"><h4 class="mb-0">Most Common Procedures</h4></div><div class="card-body d-flex justify-content-center align-items-center"><?php if (empty(json_decode($proc_data, true))): ?><p class="text-muted">No procedure data available to generate chart.</p><?php else: ?><canvas id="topProceduresChart"></canvas><?php endif; ?></div></div></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h4 class="mb-0">Recently Added Patients</h4><a href="patient_list.php" class="btn btn-sm btn-outline-primary">View All</a></div><div class="card-body p-0"><div class="list-group list-group-flush"><?php if (empty($recent_patients)): ?><div class="p-3 text-center text-muted">No recent patients.</div><?php else: foreach ($recent_patients as $patient): ?><a href="patients.php?patientID=<?php echo $patient['PatientID']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"><div><h6 class="mb-0"><?php echo htmlspecialchars($patient['FirstName'] . ' ' . $patient['LastName']); ?></h6><small class="text-muted"><?php echo htmlspecialchars($patient['MobileNumber']); ?></small></div><i class="fas fa-chevron-right text-muted"></i></a><?php endforeach; endif; ?></div></div></div></div>
            <div class="col-lg-5"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h4 class="mb-0">Today's Schedule</h4><a href="reservations.php" class="btn btn-sm btn-outline-primary">View Calendar</a></div><div class="card-body p-0"><div class="list-group list-group-flush">
                <?php if (empty($upcoming_appointments)): ?>
                    <div class="p-4 text-center text-muted"><i class="fas fa-calendar-day fa-2x mb-2"></i><p>No appointments scheduled for today.</p></div>
                <?php else: ?>
                    <?php foreach ($upcoming_appointments as $appt): ?>
                        <!-- ============================================= -->
                        <!-- START OF UI FIX FOR TODAY'S SCHEDULE        -->
                        <!-- ============================================= -->
                        <a href="patients.php?patientID=<?php echo $appt['patient_id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <!-- Div for the time badge -->
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-pill p-2 fs-6">
                                        <?php echo $appt['time']; ?>
                                    </span>
                                </div>
                                <!-- Div for the text content (Name and Procedure) -->
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo $appt['patient_name']; ?></h6>
                                    <small class="text-muted"><?php echo $appt['procedure']; ?></small>
                                </div>
                            </div>
                            <!-- Chevron icon on the far right -->
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                        <!-- ============================================= -->
                        <!-- END OF UI FIX                               -->
                        <!-- ============================================= -->
                    <?php endforeach; ?>
                <?php endif; ?>
            </div></div></div></div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ageCtx = document.getElementById('ageDistributionChart');
        if (ageCtx) {
            new Chart(ageCtx, {
                type: 'bar',
                data: { labels: <?php echo $age_labels; ?>, datasets: [{ label: 'Number of Patients', data: <?php echo $age_data; ?>, backgroundColor: 'rgba(54, 162, 235, 0.6)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 }] },
                options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, responsive: true, maintainAspectRatio: false }
            });
        }
        const procCtx = document.getElementById('topProceduresChart');
        if (procCtx) {
            new Chart(procCtx, {
                type: 'pie',
                data: { labels: <?php echo $proc_labels; ?>, datasets: [{ label: 'Procedures Done', data: <?php echo $proc_data; ?>, backgroundColor: ['rgba(255, 99, 132, 0.7)','rgba(54, 162, 235, 0.7)','rgba(255, 206, 86, 0.7)','rgba(75, 192, 192, 0.7)','rgba(153, 102, 255, 0.7)'] }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    });
    </script>
</body>
</html>