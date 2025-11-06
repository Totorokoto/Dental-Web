<?php
// FILE: admin/dashboard.php

require 'includes/header.php';
// The database connection is now open from header.php

// --- A) FETCH DATA FOR KPI & SUMMARY CARDS ---
$current_month = date('m');
$current_year = date('Y');

$revenue_sql = "SELECT SUM(amount_paid) as total_revenue FROM treatment_records WHERE MONTH(procedure_date) = ? AND YEAR(procedure_date) = ?";
$stmt = $conn->prepare($revenue_sql);
$stmt->bind_param("is", $current_month, $current_year);
$stmt->execute();
$revenue_result = $stmt->get_result()->fetch_assoc();
$monthly_revenue = $revenue_result['total_revenue'] ?? 0;
$stmt->close();

$new_patients_sql = "SELECT COUNT(patient_id) as total_new FROM patients WHERE MONTH(registration_date) = ? AND YEAR(registration_date) = ?";
$stmt = $conn->prepare($new_patients_sql);
$stmt->bind_param("is", $current_month, $current_year);
$stmt->execute();
$new_patients_result = $stmt->get_result()->fetch_assoc();
$new_patients_month = $new_patients_result['total_new'] ?? 0;
$stmt->close();

$appts_completed_sql = "SELECT COUNT(appointment_id) as total_completed FROM appointments WHERE status = 'Completed' AND MONTH(appointment_date) = ? AND YEAR(appointment_date) = ?";
$stmt = $conn->prepare($appts_completed_sql);
$stmt->bind_param("is", $current_month, $current_year);
$stmt->execute();
$appts_completed_result = $stmt->get_result()->fetch_assoc();
$appts_completed_month = $appts_completed_result['total_completed'] ?? 0;
$stmt->close();

$sql_todays_schedule = "
    SELECT a.appointment_date, a.service_description, a.status, p.patient_id, p.first_name, p.last_name, u.full_name as dentist_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN users u ON a.dentist_id = u.user_id
    WHERE DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_date ASC";
$todays_schedule_result = $conn->query($sql_todays_schedule);

$outstanding_sql = "
    SELECT p.patient_id, p.first_name, p.last_name, SUM(tr.balance) as total_balance
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    GROUP BY tr.patient_id
    HAVING total_balance > 0
    ORDER BY total_balance DESC
    LIMIT 5";
$outstanding_result = $conn->query($outstanding_sql);

// --- NEW QUERY FOR PENDING APPOINTMENTS ---
$sql_pending_appts = "
    SELECT a.appointment_id, a.appointment_date, a.service_description, p.patient_id, p.first_name, p.last_name, p.branch
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE a.status = 'Pending Approval'
    ORDER BY a.appointment_date ASC";
$pending_appts_result = $conn->query($sql_pending_appts);
?>

<!-- Custom Styles for Dashboard -->
<style>
    .kpi-card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); color: #ffffff; transition: transform 0.2s ease-in-out; }
    .kpi-card:hover { transform: translateY(-5px); }
    .kpi-card .card-body { display: flex; justify-content: space-between; align-items: center; }
    .kpi-card i { font-size: 3rem; opacity: 0.3; }
    .kpi-card .card-text { font-size: 2.25rem; font-weight: 700; margin-bottom: 0; }
    .kpi-card .card-title { font-weight: 500; font-size: 0.95rem; }
    .table-card { border-radius: 1rem; box-shadow: var(--card-shadow); overflow: hidden; }
    .table-card .card-header { background-color: #ffffff; border-bottom: 1px solid #f0f0f0; font-weight: 600; font-size: 1.1rem; padding: 1rem 1.5rem; }
    .table-card .card-header i { color: var(--primary-color); }
    .table .btn { transition: all 0.2s ease-in-out; }
</style>

<!-- Page Header & Welcome Message -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>
<div class="alert alert-light border-0 d-flex align-items-center" style="background-color: #e6f0f3;" role="alert">
    <i class="fas fa-sun fa-2x me-3 text-warning"></i>
    <div> Welcome back, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>! Here is a summary of your clinic's activity.</div>
</div>

<!-- KPI CARDS -->
<div class="row">
    <div class="col-lg-4 mb-4"><div class="card bg-success kpi-card"><div class="card-body"><div><h6 class="card-title">Revenue (This Month)</h6><p class="card-text">₱<?php echo number_format($monthly_revenue, 2); ?></p></div><i class="fas fa-peso-sign"></i></div></div></div>
    <div class="col-lg-4 mb-4"><div class="card bg-primary kpi-card"><div class="card-body"><div><h6 class="card-title">New Patients (This Month)</h6><p class="card-text"><?php echo $new_patients_month; ?></p></div><i class="fas fa-user-plus"></i></div></div></div>
    <div class="col-lg-4 mb-4"><div class="card kpi-card" style="background-color: var(--primary-color);"><div class="card-body"><div><h6 class="card-title">Appointments Completed</h6><p class="card-text"><?php echo $appts_completed_month; ?></p></div><i class="fas fa-check-circle"></i></div></div></div>
</div>

<!-- Today's Priorities Row -->
<div class="row">
    <!-- Today's Schedule Column -->
    <div class="col-lg-7 mb-4">
        <div class="card table-card">
            <div class="card-header"><i class="fas fa-calendar-day me-2"></i>Today's Schedule (<?php echo $todays_schedule_result->num_rows; ?>)</div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th class="ps-4">Time</th><th>Patient</th><th>Service</th><th class="text-center">Status</th><th class="text-center pe-4">Actions</th></tr></thead>
                <tbody>
                    <?php if ($todays_schedule_result && $todays_schedule_result->num_rows > 0): ?>
                        <?php while($appt = $todays_schedule_result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 align-middle"><strong><?php echo date('g:i A', strtotime($appt['appointment_date'])); ?></strong></td>
                                <td class="align-middle"><?php echo htmlspecialchars($appt['last_name'] . ', ' . $appt['first_name']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($appt['service_description']); ?></td>
                                <td class="text-center align-middle"><?php
                                    $status = htmlspecialchars($appt['status']);
                                    $badge_class = 'bg-secondary';
                                    if ($status == 'Scheduled') $badge_class = 'bg-primary'; if ($status == 'Completed') $badge_class = 'bg-success'; if ($status == 'Cancelled') $badge_class = 'bg-secondary'; if ($status == 'No-Show') $badge_class = 'bg-danger'; if ($status == 'Pending Approval') $badge_class = 'bg-warning text-dark';
                                    echo "<span class=\"badge $badge_class\">$status</span>";
                                ?></td>
                                <td class="text-center pe-4 align-middle">
                                    <a href="patient_view.php?id=<?php echo $appt['patient_id']; ?>" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View Patient Record"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5"><div class="text-center p-4 text-muted">No appointments scheduled for today.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>
    
    <!-- Outstanding Balances Column -->
    <div class="col-lg-5 mb-4">
        <div class="card table-card">
            <div class="card-header"><i class="fas fa-file-invoice-dollar me-2"></i>Top Outstanding Balances</div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th class="ps-4">Patient</th><th class="text-end">Balance</th><th class="text-center pe-4">Actions</th></tr></thead>
                <tbody>
                    <?php if ($outstanding_result && $outstanding_result->num_rows > 0): ?>
                        <?php while($bal = $outstanding_result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 align-middle"><?php echo htmlspecialchars($bal['last_name'] . ', ' . $bal['first_name']); ?></td>
                                <td class="text-end text-danger fw-bold align-middle">₱<?php echo number_format($bal['total_balance'], 2); ?></td>
                                <td class="text-center pe-4 align-middle">
                                    <a href="patient_view.php?id=<?php echo $bal['patient_id']; ?>" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View Patient Record"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3"><div class="text-center p-4 text-muted">No outstanding balances found.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>

    <!-- NEW PENDING APPOINTMENTS SECTION -->
    <div class="col-12 mb-4">
        <div class="card table-card">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-dark"><i class="fas fa-hourglass-half me-2"></i>Pending Appointment Requests (<?php echo $pending_appts_result->num_rows; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Requested Date</th>
                                <th>Patient</th>
                                <th>Branch</th>
                                <th>Reason</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pending_appts_result && $pending_appts_result->num_rows > 0): ?>
                                <?php while($appt = $pending_appts_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 align-middle"><strong><?php echo date('M d, Y @ g:i A', strtotime($appt['appointment_date'])); ?></strong></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($appt['last_name'] . ', ' . $appt['first_name']); ?></td>
                                        <td class="align-middle"><span class="badge bg-secondary"><?php echo htmlspecialchars($appt['branch']); ?></span></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($appt['service_description']); ?></td>
                                        <td class="text-center pe-4 align-middle">
                                            <a href="appointments.php" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Calendar to Review"><i class="fas fa-calendar-alt me-2"></i>Review</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5"><div class="text-center p-4 text-muted">No pending appointment requests.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
// The connection will be closed by footer.php
require 'includes/footer.php';
?>

<!-- SCRIPT TO INITIALIZE TOOLTIPS -->
<script>
$(document).ready(function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>