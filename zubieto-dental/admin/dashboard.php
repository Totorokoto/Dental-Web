<?php
// FILE: admin/dashboard.php (UPGRADED WITH KPIs AND FINANCIALS)

require 'includes/header.php';
require '../includes/db_connect.php';

// --- A) FETCH DATA FOR KPI & SUMMARY CARDS ---

// Get current month and year for queries
$current_month = date('m');
$current_year = date('Y');

// 1. Revenue This Month (from completed treatments)
$revenue_sql = "SELECT SUM(amount_paid) as total_revenue FROM treatment_records WHERE MONTH(procedure_date) = ? AND YEAR(procedure_date) = ?";
$stmt = $conn->prepare($revenue_sql);
$stmt->bind_param("is", $current_month, $current_year);
$stmt->execute();
$revenue_result = $stmt->get_result()->fetch_assoc();
$monthly_revenue = $revenue_result['total_revenue'] ?? 0;

// 2. New Patients This Month
$new_patients_sql = "SELECT COUNT(patient_id) as total_new FROM patients WHERE MONTH(registration_date) = ? AND YEAR(registration_date) = ?";
$stmt = $conn->prepare($new_patients_sql);
$stmt->bind_param("is", $current_month, $current_year);
$stmt->execute();
$new_patients_result = $stmt->get_result()->fetch_assoc();
$new_patients_month = $new_patients_result['total_new'] ?? 0;

// 3. Appointments Completed This Month
$appts_completed_sql = "SELECT COUNT(appointment_id) as total_completed FROM appointments WHERE status = 'Completed' AND MONTH(appointment_date) = ? AND YEAR(appointment_date) = ?";
$stmt = $conn->prepare($appts_completed_sql);
$stmt->bind_param("is", $current_month, $current_year);
$stmt->execute();
$appts_completed_result = $stmt->get_result()->fetch_assoc();
$appts_completed_month = $appts_completed_result['total_completed'] ?? 0;


// --- B) FETCH DATA FOR TODAY'S SCHEDULE ---
$sql_todays_schedule = "
    SELECT a.appointment_date, a.service_description, a.status, p.patient_id, p.first_name, p.last_name, u.full_name as dentist_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN users u ON a.dentist_id = u.user_id
    WHERE DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_date ASC";
$todays_schedule_result = $conn->query($sql_todays_schedule);


// --- C) FETCH DATA FOR OUTSTANDING BALANCES ---
$outstanding_sql = "
    SELECT p.patient_id, p.first_name, p.last_name, SUM(tr.balance) as total_balance
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    GROUP BY tr.patient_id
    HAVING total_balance > 0
    ORDER BY total_balance DESC
    LIMIT 5"; // Limit to top 5 for the dashboard
$outstanding_result = $conn->query($outstanding_sql);

?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- =================================================================
     NEW: KEY PERFORMANCE INDICATOR (KPI) CARDS
     ================================================================= -->
<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Revenue (This Month)</h5>
                        <h2 class="card-text">₱<?php echo number_format($monthly_revenue, 2); ?></h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">New Patients (This Month)</h5>
                        <h2 class="card-text"><?php echo $new_patients_month; ?></h2>
                    </div>
                    <i class="fas fa-user-plus fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Appointments Completed</h5>
                        <h2 class="card-text"><?php echo $appts_completed_month; ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Today's Priorities Row -->
<div class="row">
    <!-- Today's Schedule Column -->
    <div class="col-lg-7">
        <h2 class="mt-4">Today's Schedule (<?php echo $todays_schedule_result->num_rows; ?>)</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>Time</th><th>Patient</th><th>Service</th><th class="text-center">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($todays_schedule_result && $todays_schedule_result->num_rows > 0): ?>
                                <?php while($appt = $todays_schedule_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo date('g:i A', strtotime($appt['appointment_date'])); ?></strong></td>
                                        <td><?php echo htmlspecialchars($appt['last_name'] . ', ' . $appt['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appt['service_description']); ?></td>
                                        <td class="text-center"><a href="patient_view.php?id=<?php echo $appt['patient_id']; ?>" class="btn btn-info btn-sm" title="View Patient Record"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4"><div class="alert alert-light text-center mb-0">No appointments scheduled for today.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- =================================================================
         NEW: OUTSTANDING BALANCES COLUMN
         ================================================================= -->
    <div class="col-lg-5">
        <h2 class="mt-4">Top Outstanding Balances</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>Patient</th><th class="text-end">Balance</th><th class="text-center">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($outstanding_result && $outstanding_result->num_rows > 0): ?>
                                <?php while($bal = $outstanding_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bal['last_name'] . ', ' . $bal['first_name']); ?></td>
                                        <td class="text-end text-danger fw-bold">₱<?php echo number_format($bal['total_balance'], 2); ?></td>
                                        <td class="text-center"><a href="patient_view.php?id=<?php echo $bal['patient_id']; ?>" class="btn btn-info btn-sm" title="View Patient Record"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3"><div class="alert alert-light text-center mb-0">No outstanding balances. Great work!</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require 'includes/footer.php';
?>