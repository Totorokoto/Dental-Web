<?php
// FILE: admin/logs.php 

require 'includes/header.php';
require '../includes/db_connect.php';

// RBAC: This page is strictly for Admins.
if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

// --- Filter Logic (remains the same) ---
$where_clauses = []; $params = []; $types = "";
$start_date = $_GET['start_date'] ?? ''; $end_date = $_GET['end_date'] ?? '';
if ($start_date && $end_date) {
    $where_clauses[] = "al.log_timestamp BETWEEN ? AND ?";
    $params[] = $start_date . " 00:00:00"; $params[] = $end_date . " 23:59:59"; $types .= "ss";
}
$user_filter_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_filter_id > 0) {
    $where_clauses[] = "al.user_id = ?"; $params[] = $user_filter_id; $types .= "i";
}
$users_result = $conn->query("SELECT user_id, full_name FROM users ORDER BY full_name");
$sql = "SELECT al.log_id, al.action_type, al.details, al.log_timestamp, u.full_name 
        FROM activity_logs al LEFT JOIN users u ON al.user_id = u.user_id";
if (!empty($where_clauses)) { $sql .= " WHERE " . implode(" AND ", $where_clauses); }
$sql .= " ORDER BY al.log_timestamp DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$logs_result = $stmt->get_result();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Activity History & Logs</h1>
</div>

<!-- Filter Card (remains the same) -->
<div class="card mb-4">
    <div class="card-header">Filter Logs</div>
    <div class="card-body">
        <form method="GET" action="logs.php">
            <div class="row align-items-end">
                <div class="col-md-4"><label for="start_date" class="form-label">Start Date</label><input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>"></div>
                <div class="col-md-4"><label for="end_date" class="form-label">End Date</label><input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"></div>
                <div class="col-md-3"><label for="user_id" class="form-label">User</label><select class="form-select" name="user_id" id="user_id"><option value="0">All Users</option><?php while($user = $users_result->fetch_assoc()): ?><option value="<?php echo $user['user_id']; ?>" <?php if($user_filter_id == $user['user_id']) echo 'selected'; ?>><?php echo htmlspecialchars($user['full_name']); ?></option><?php endwhile; ?></select></div>
                <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
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
                        // ---  Logic for Icons, Colors, and Links ---
                        $action = htmlspecialchars($log['action_type']);
                        $details = htmlspecialchars($log['details']);
                        $icon_html = '<i class="fas fa-info-circle text-secondary"></i>'; // Default
                        $badge_class = 'bg-light text-dark'; // Default

                        switch ($action) {
                            case 'Login Success':
                                $icon_html = '<i class="fas fa-sign-in-alt text-success"></i>';
                                $badge_class = 'bg-success';
                                break;
                            case 'Login Failed':
                                $icon_html = '<i class="fas fa-exclamation-triangle text-danger"></i>';
                                $badge_class = 'bg-danger';
                                break;
                            case 'Patient Created':
                            case 'Appointment Completed':
                                $icon_html = '<i class="fas fa-plus-circle text-success"></i>';
                                $badge_class = 'bg-success';
                                break;
                            case 'Patient Record Viewed':
                                $icon_html = '<i class="fas fa-eye text-primary"></i>';
                                $badge_class = 'bg-primary';
                                break;
                            case 'Appointment Edited':
                                $icon_html = '<i class="fas fa-pencil-alt text-warning"></i>';
                                $badge_class = 'bg-warning text-dark';
                                break;
                            case 'Patient Deleted':
                            case 'Appointment Deleted':
                                $icon_html = '<i class="fas fa-trash-alt text-danger"></i>';
                                $badge_class = 'bg-danger';
                                break;
                        }

                        // Create clickable links by finding IDs in the details string
                        $details_html = $details;
                        if (preg_match('/Patient ID: (\d+)/', $details, $matches)) {
                            $patient_id = $matches[1];
                            $details_html = preg_replace('/(Patient ID: \d+)/', '<a href="patient_view.php?id='.$patient_id.'">$1</a>', $details);
                        }
                        if (preg_match('/Appt ID: (\d+)/', $details, $matches)) {
                            $appt_id = $matches[1];
                             $details_html = preg_replace('/(Appt ID: \d+)/', '<a href="appointments.php">$1</a>', $details); // Link to calendar
                        }
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y, h:i A', strtotime($log['log_timestamp'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($log['full_name'] ?? 'System/Unknown'); ?></strong></td>
                            <td><span class="badge <?php echo $badge_class; ?> p-2"><?php echo $icon_html . ' ' . $action; ?></span></td>
                            <td><?php echo $details_html; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require 'includes/footer.php';
?>

<script>
$(document).ready(function() {
    $('#logsTable').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        //  Re-enabled search. It will now work with the filtered results.
        "searching": true, 
        "language": {
            "emptyTable": "No logs found matching the selected criteria.",
            "search": "Search within results:"
        }
    });
});
</script>