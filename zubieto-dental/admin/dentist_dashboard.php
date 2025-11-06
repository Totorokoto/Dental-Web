<?php
// FILE: admin/dentist_dashboard.php 

require 'includes/header.php';
// The database connection is now open from header.php

// --- PHP DATA FETCHING LOGIC ---
$dentist_id = $_SESSION['user_id'];
$user_branch = $_SESSION['branch'];
$user_role = $_SESSION['role'];
$current_date = date('Y-m-d');
$current_month = date('m');
$current_year = date('Y');

$stmt_today = $conn->prepare("SELECT COUNT(appointment_id) as total FROM appointments WHERE dentist_id = ? AND DATE(appointment_date) = ?");
$stmt_today->bind_param("is", $dentist_id, $current_date);
$stmt_today->execute();
$today_count = $stmt_today->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_today->close();

$sql_followup_count = "SELECT COUNT(tr.record_id) as total FROM treatment_records tr JOIN patients p ON tr.patient_id = p.patient_id WHERE tr.next_appt BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY)";
$params_followup = [$current_date, $current_date]; $types_followup = "ss";
if ($user_role !== 'Admin') { $sql_followup_count .= " AND p.branch = ?"; $params_followup[] = $user_branch; $types_followup .= "s"; }
$stmt_followup = $conn->prepare($sql_followup_count);
$stmt_followup->bind_param($types_followup, ...$params_followup);
$stmt_followup->execute();
$followup_count = $stmt_followup->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_followup->close();

$stmt_completed = $conn->prepare("SELECT COUNT(appointment_id) as total FROM appointments WHERE dentist_id = ? AND status = 'Completed' AND MONTH(appointment_date) = ? AND YEAR(appointment_date) = ?");
$stmt_completed->bind_param("iss", $dentist_id, $current_month, $current_year);
$stmt_completed->execute();
$completed_count = $stmt_completed->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_completed->close();

$stmt_schedule = $conn->prepare("
    SELECT a.appointment_date, a.status, a.service_description, p.patient_id, p.first_name, p.last_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.patient_id 
    WHERE a.dentist_id = ? AND DATE(a.appointment_date) = ? 
    ORDER BY a.appointment_date ASC
");
$stmt_schedule->bind_param("is", $dentist_id, $current_date);
$stmt_schedule->execute();
$todays_schedule_result = $stmt_schedule->get_result();
$stmt_schedule->close();

$sql_pending_followups = "SELECT tr.next_appt, tr.procedure_done, p.patient_id, p.first_name, p.last_name FROM treatment_records tr JOIN patients p ON tr.patient_id = p.patient_id WHERE tr.next_appt IS NOT NULL AND tr.next_appt >= ?";
$params_pending = [$current_date]; $types_pending = "s";
if ($user_role !== 'Admin') { $sql_pending_followups .= " AND p.branch = ?"; $params_pending[] = $user_branch; $types_pending .= "s"; }
$sql_pending_followups .= " ORDER BY tr.next_appt ASC LIMIT 7";
$stmt_pending_followups = $conn->prepare($sql_pending_followups);
$stmt_pending_followups->bind_param($types_pending, ...$params_pending);
$stmt_pending_followups->execute();
$pending_followups_result = $stmt_pending_followups->get_result();
$stmt_pending_followups->close();

// --- NEW QUERY FOR PENDING APPOINTMENTS (BRANCH-SPECIFIC) ---
$sql_pending_appts = "
    SELECT a.appointment_id, a.appointment_date, a.service_description, p.patient_id, p.first_name, p.last_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE a.status = 'Pending Approval' AND p.branch = ?
    ORDER BY a.appointment_date ASC";
$stmt_pending = $conn->prepare($sql_pending_appts);
$stmt_pending->bind_param("s", $user_branch);
$stmt_pending->execute();
$pending_appts_result = $stmt_pending->get_result();
$stmt_pending->close();
?>

<!-- Custom Styles for Dentist Dashboard -->
<style>
    .kpi-card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); color: #ffffff; transition: transform 0.2s ease-in-out; }
    .kpi-card:hover { transform: translateY(-5px); }
    .kpi-card .card-body { display: flex; justify-content: space-between; align-items: center; }
    .kpi-card i { font-size: 3rem; opacity: 0.3; }
    .kpi-card .card-text { font-size: 2.25rem; font-weight: 700; margin-bottom: 0; }
    .kpi-card .card-title { font-weight: 500; font-size: 0.95rem; }
    .table-card .card-header { background-color: #ffffff; border-bottom: 1px solid #f0f0f0; font-weight: 600; font-size: 1.1rem; padding: 1rem 1.5rem; }
    .table-card .card-header i { color: var(--primary-color); }
    .table .btn { transition: all 0.2s ease-in-out; }
</style>

<!-- Page Header & Welcome Message -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dentist Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0"><a href="appointments.php" class="btn btn-outline-secondary"><i class="fas fa-calendar-alt me-2"></i>View Full Calendar</a></div>
</div>
<div class="alert alert-light border-0 d-flex align-items-center" style="background-color: #e6f0f3;" role="alert">
    <i class="fas fa-sun fa-2x me-3 text-warning"></i>
    <div>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>! Here's what's happening today.</div>
</div>

<!-- KPI Cards -->
<div class="row">
    <div class="col-lg-4 mb-4"><div class="card kpi-card" style="background-color: var(--primary-color);"><div class="card-body"><div><h6 class="card-title">My Appointments Today</h6><p class="card-text"><?php echo $today_count; ?></p></div><i class="fas fa-calendar-day"></i></div></div></div>
    <div class="col-lg-4 mb-4"><div class="card bg-warning kpi-card"><div class="card-body text-dark"><div><h6 class="card-title">Branch Follow-ups (7 days)</h6><p class="card-text"><?php echo $followup_count; ?></p></div><i class="fas fa-user-clock"></i></div></div></div>
    <div class="col-lg-4 mb-4"><div class="card bg-success kpi-card"><div class="card-body"><div><h6 class="card-title">My Completed (This Month)</h6><p class="card-text"><?php echo $completed_count; ?></p></div><i class="fas fa-check-circle"></i></div></div></div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Today's Schedule Column -->
    <div class="col-lg-7 mb-4">
        <div class="card table-card">
            <div class="card-header"><i class="fas fa-clipboard-list me-2"></i>My Schedule Today</div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th class="ps-4">Time</th><th>Patient</th><th>Service / Reason</th><th class="text-center">Status</th><th class="text-center pe-4">Action</th></tr></thead>
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
                        <tr><td colspan="5"><div class="text-center p-4 text-muted">No appointments scheduled for you today.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>
    
    <!-- Pending Follow-ups Column -->
    <div class="col-lg-5 mb-4">
        <div class="card table-card">
            <div class="card-header"><i class="fas fa-bell me-2"></i>Upcoming Branch Follow-ups</div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th class="ps-4">Date</th><th>Patient</th><th>Reason</th></tr></thead>
                <tbody>
                    <?php if ($pending_followups_result && $pending_followups_result->num_rows > 0): ?>
                        <?php while($followup = $pending_followups_result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 align-middle"><?php echo date('M d, Y', strtotime($followup['next_appt'])); ?></td>
                                <td class="align-middle"><a href="patient_view.php?id=<?php echo $followup['patient_id']; ?>"><?php echo htmlspecialchars($followup['last_name'] . ', ' . $followup['first_name']); ?></a></td>
                                <td class="pe-4 align-middle"><?php echo htmlspecialchars($followup['procedure_done']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3"><div class="text-center p-4 text-muted">No pending follow-ups for this branch.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>

    <!-- PENDING APPOINTMENTS SECTION -->
    <div class="col-12 mb-4">
        <div class="card table-card">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-dark"><i class="fas fa-hourglass-half me-2"></i>Pending Requests for Your Branch (<?php echo $pending_appts_result->num_rows; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Requested Date</th>
                                <th>Patient</th>
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
                                        <td class="align-middle"><?php echo htmlspecialchars($appt['service_description']); ?></td>
                                        <td class="text-center pe-4 align-middle">
                                            <a href="appointments.php" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to Calendar to Review"><i class="fas fa-calendar-alt me-2"></i>Review</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4"><div class="text-center p-4 text-muted">No pending appointment requests for this branch.</div></td></tr>
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