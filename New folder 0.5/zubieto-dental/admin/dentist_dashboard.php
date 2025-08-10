<?php
// FILE: admin/dentist_dashboard.php (CORRECTED FOLLOW-UP LOGIC)

require 'includes/header.php';
require '../includes/db_connect.php';

// Get session variables
$dentist_id = $_SESSION['user_id'];
$user_branch = $_SESSION['branch'];
$user_role = $_SESSION['role'];
$current_date = date('Y-m-d');
$current_month = date('m');
$current_year = date('Y');

// --- A) FETCH DATA FOR KPI CARDS ---

// 1. Today's Appointments (This logic is correct and remains the same)
$stmt_today = $conn->prepare("SELECT COUNT(appointment_id) as total FROM appointments WHERE dentist_id = ? AND DATE(appointment_date) = ?");
$stmt_today->bind_param("is", $dentist_id, $current_date);
$stmt_today->execute();
$today_count = $stmt_today->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_today->close();

// 2. Upcoming Follow-ups Count (This is the corrected logic)
$sql_followup_count = "
    SELECT COUNT(tr.record_id) as total 
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    WHERE tr.next_appt BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY)
";
$params_followup = [$current_date, $current_date];
$types_followup = "ss";

// If user is not an Admin, filter by their branch
if ($user_role !== 'Admin') {
    $sql_followup_count .= " AND p.branch = ?";
    $params_followup[] = $user_branch;
    $types_followup .= "s";
}

$stmt_followup = $conn->prepare($sql_followup_count);
$stmt_followup->bind_param($types_followup, ...$params_followup);
$stmt_followup->execute();
$followup_count = $stmt_followup->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_followup->close();


// 3. Appointments Completed This Month (This logic is correct and remains the same)
$stmt_completed = $conn->prepare("SELECT COUNT(appointment_id) as total FROM appointments WHERE dentist_id = ? AND status = 'Completed' AND MONTH(appointment_date) = ? AND YEAR(appointment_date) = ?");
$stmt_completed->bind_param("iss", $dentist_id, $current_month, $current_year);
$stmt_completed->execute();
$completed_count = $stmt_completed->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_completed->close();


// --- B) FETCH DATA FOR DETAILED LISTS ---

// 1. Today's Schedule for the Dentist (This logic is correct and remains the same)
$stmt_schedule = $conn->prepare("SELECT a.appointment_date, a.service_description, p.patient_id, p.first_name, p.last_name FROM appointments a JOIN patients p ON a.patient_id = p.patient_id WHERE a.dentist_id = ? AND DATE(a.appointment_date) = ? ORDER BY a.appointment_date ASC");
$stmt_schedule->bind_param("is", $dentist_id, $current_date);
$stmt_schedule->execute();
$todays_schedule_result = $stmt_schedule->get_result();
$stmt_schedule->close();

// 2. Pending Follow-ups for the Dentist's Branch (This is the corrected logic)
$sql_pending_followups = "
    SELECT tr.next_appt, tr.procedure_done, p.patient_id, p.first_name, p.last_name
    FROM treatment_records tr
    JOIN patients p ON tr.patient_id = p.patient_id
    WHERE tr.next_appt IS NOT NULL AND tr.next_appt >= ?
";
$params_pending = [$current_date];
$types_pending = "s";

// If user is not an Admin, filter by their branch
if ($user_role !== 'Admin') {
    $sql_pending_followups .= " AND p.branch = ?";
    $params_pending[] = $user_branch;
    $types_pending .= "s";
}

$sql_pending_followups .= " ORDER BY tr.next_appt ASC LIMIT 7";

$stmt_pending_followups = $conn->prepare($sql_pending_followups);
$stmt_pending_followups->bind_param($types_pending, ...$params_pending);
$stmt_pending_followups->execute();
$pending_followups_result = $stmt_pending_followups->get_result();
$stmt_pending_followups->close();

?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dentist Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="appointments.php" class="btn btn-sm btn-outline-secondary">View Full Calendar</a>
    </div>
</div>

<!-- Welcome Message -->
<div class="alert alert-info">
    Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>! Here is your personal schedule and a summary of your branch's upcoming follow-ups.
</div>


<!-- KPI Cards for the Dentist -->
<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">My Appointments Today</h5>
                    <h2 class="card-text"><?php echo $today_count; ?></h2>
                </div>
                <i class="fas fa-calendar-day fa-3x opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">Branch Follow-ups</h5>
                    <h2 class="card-text"><?php echo $followup_count; ?></h2>
                </div>
                <i class="fas fa-user-clock fa-3x opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                 <div>
                    <h5 class="card-title">My Completed (Month)</h5>
                    <h2 class="card-text"><?php echo $completed_count; ?></h2>
                </div>
                <i class="fas fa-check-circle fa-3x opacity-75"></i>
            </div>
        </div>
    </div>
</div>


<!-- Main Content Row -->
<div class="row">
    <!-- Today's Schedule Column -->
    <div class="col-lg-7">
        <h3 class="mt-4">My Schedule Today</h3>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>Time</th><th>Patient</th><th>Service / Reason</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($todays_schedule_result && $todays_schedule_result->num_rows > 0): ?>
                                <?php while($appt = $todays_schedule_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo date('g:i A', strtotime($appt['appointment_date'])); ?></strong></td>
                                        <td><?php echo htmlspecialchars($appt['last_name'] . ', ' . $appt['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appt['service_description']); ?></td>
                                        <td class="text-center"><a href="patient_view.php?id=<?php echo $appt['patient_id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4"><div class="alert alert-light text-center mb-0">No appointments scheduled for you today.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Follow-ups Column -->
    <div class="col-lg-5">
        <h3 class="mt-4">Branch's Pending Follow-ups</h3>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Patient</th><th>Reason</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($pending_followups_result && $pending_followups_result->num_rows > 0): ?>
                                <?php while($followup = $pending_followups_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($followup['next_appt'])); ?></td>
                                        <td><a href="patient_view.php?id=<?php echo $followup['patient_id']; ?>"><?php echo htmlspecialchars($followup['last_name'] . ', ' . $followup['first_name']); ?></a></td>
                                        <td><?php echo htmlspecialchars($followup['procedure_done']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3"><div class="alert alert-light text-center mb-0">No pending follow-ups for this branch.</div></td></tr>
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