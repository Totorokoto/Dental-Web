<?php
// FILE: admin/logs.php 

require 'includes/header.php';
// The database connection is now open from header.php

// RBAC: This page is strictly for Admins.
if ($_SESSION['role'] !== 'Admin') {
    $redirect_page = 'dashboard.php'; 
    if(isset($_SESSION['role'])) {
        switch($_SESSION['role']) {
            case 'Dentist': $redirect_page = 'dentist_dashboard.php'; break;
            case 'Assistant': $redirect_page = 'patients.php'; break;
        }
    }
    header("Location: $redirect_page");
    exit();
}

// --- Filter Logic ---
$where_clauses = []; 
$params = []; 
$types = "";

$start_date = $_GET['start_date'] ?? ''; 
$end_date = $_GET['end_date'] ?? '';

if ($start_date && $end_date) {
    $where_clauses[] = "al.log_timestamp BETWEEN ? AND ?";
    $params[] = $start_date . " 00:00:00"; 
    $params[] = $end_date . " 23:59:59"; 
    $types .= "ss";
}

$user_filter_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_filter_id > 0) {
    $where_clauses[] = "al.user_id = ?"; 
    $params[] = $user_filter_id; 
    $types .= "i";
}

// Fetch users for the filter dropdown
$users_result = $conn->query("SELECT user_id, full_name FROM users ORDER BY full_name");

// Build and execute the main query
$sql = "SELECT al.log_id, al.action_type, al.details, al.log_timestamp, u.full_name 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.user_id";

if (!empty($where_clauses)) { 
    $sql .= " WHERE " . implode(" AND ", $where_clauses); 
}

$sql .= " ORDER BY al.log_timestamp DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) { 
    $stmt->bind_param($types, ...$params); 
}
$stmt->execute();
$logs_result = $stmt->get_result();
?>

<style>
    /* Custom styles for this page */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .btn-primary:hover {
        background-color: #00695C;
        border-color: #00695C;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25);
    }
    .table-dark {
        --bs-table-bg: var(--primary-color);
        --bs-table-border-color: #00695C;
    }
    .badge {
        font-weight: 500;
        padding: 0.5em 0.75em;
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Activity History & Logs</h1>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Logs</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="logs.php">
            <div class="row align-items-end g-3">
                <div class="col-md-4"><label for="start_date" class="form-label">Start Date</label><input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>"></div>
                <div class="col-md-4"><label for="end_date" class="form-label">End Date</label><input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"></div>
                <div class="col-md-3"><label for="user_id" class="form-label">User</label><select class="form-select" name="user_id" id="user_id"><option value="0">All Users</option><?php while($user = $users_result->fetch_assoc()): ?><option value="<?php echo $user['user_id']; ?>" <?php if($user_filter_id == $user['user_id']) echo 'selected'; ?>><?php echo htmlspecialchars($user['full_name']); ?></option><?php endwhile; ?></select></div>
                <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary">Filter</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Log Entries</h5>
    </div>
    <div class="card-body">
        <table id="logsTable" class="table table-striped table-hover" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th style="width: 15%;">Date & Time</th>
                    <th style="width: 15%;">Performed By</th>
                    <th style="width: 15%;">Action</th>
                    <th style="width: 55%;">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs_result && $logs_result->num_rows > 0): ?>
                    <?php while($log = $logs_result->fetch_assoc()): ?>
                        <?php
                        $action = htmlspecialchars($log['action_type']);
                        $details = htmlspecialchars($log['details']);
                        $icon_html = '<i class="fas fa-info-circle me-2"></i>';
                        $badge_class = 'bg-secondary';

                        switch ($action) {
                            case 'Login Success': case 'User Created': case 'Patient Created': case 'Appointment Completed':
                                $icon_html = '<i class="fas fa-check-circle me-2"></i>'; $badge_class = 'bg-success'; break;
                            case 'User Edited': case 'Appointment Edited':
                                $icon_html = '<i class="fas fa-pencil-alt me-2"></i>'; $badge_class = 'bg-warning text-dark'; break;
                            case 'Login Failed':
                                $icon_html = '<i class="fas fa-exclamation-triangle me-2"></i>'; $badge_class = 'bg-danger'; break;
                            case 'User Deleted': case 'Appointment Deleted': case 'Patient Deleted':
                                $icon_html = '<i class="fas fa-trash-alt me-2"></i>'; $badge_class = 'bg-danger'; break;
                            case 'Patient Record Viewed':
                                $icon_html = '<i class="fas fa-eye me-2"></i>'; $badge_class = 'bg-primary'; break;
                            case 'User Deactivated':
                                $icon_html = '<i class="fas fa-user-slash me-2"></i>'; $badge_class = 'bg-secondary'; break;
                        }

                        $details_html = $details;
                        if (preg_match('/\(Patient ID: (\d+)\)/', $details, $matches)) {
                            $patient_id = $matches[1];
                            $details_html = preg_replace('/(\(Patient ID: \d+\))/', '<a href="patient_view.php?id='.$patient_id.'">$1</a>', $details);
                        }
                        if (preg_match('/\(Appt ID: (\d+)\)/', $details, $matches)) {
                            $details_html = preg_replace('/(\(Appt ID: \d+\))/', '<a href="appointments.php" class="text-decoration-underline">$1</a>', $details_html);
                        }
                        ?>
                        <tr>
                            <td class="align-middle"><?php echo date('M d, Y, h:i A', strtotime($log['log_timestamp'])); ?></td>
                            <td class="align-middle"><strong><?php echo htmlspecialchars($log['full_name'] ?? 'System/Unknown'); ?></strong></td>
                            <td class="align-middle"><span class="badge <?php echo $badge_class; ?> w-100"><?php echo $icon_html . ' ' . $action; ?></span></td>
                            <td class="align-middle"><?php echo $details_html; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// The connection will be closed by footer.php
require 'includes/footer.php';
?>

<script>
$(document).ready(function() {
    $('#logsTable').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "searching": true,
        "language": {
            "emptyTable": "No logs found matching the selected criteria.",
            "search": "Search within results:"
        }
    });
});
</script>