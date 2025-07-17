<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once 'config.php';

// --- Get the selected date, default to today ---
$selected_date = $_GET['date'] ?? date('Y-m-d');

// --- Initialize variables ---
$daily_summary = [
    'patients_seen' => 0,
    'new_patients' => 0,
    'revenue' => 0.00
];
$daily_treatments = [];
$error_message = '';

try {
    // --- Query 1: Get total patients seen on this day ---
    $stmt_seen = $pdo->prepare("SELECT COUNT(DISTINCT PatientID) FROM Treatments WHERE Date = ?");
    $stmt_seen->execute([$selected_date]);
    $daily_summary['patients_seen'] = $stmt_seen->fetchColumn();

    // --- Query 2: Get total revenue for this day ---
    $stmt_revenue = $pdo->prepare("SELECT SUM(AmountPaid) FROM Treatments WHERE Date = ?");
    $stmt_revenue->execute([$selected_date]);
    $daily_summary['revenue'] = $stmt_revenue->fetchColumn() ?? 0;

    // --- Query 3: Get the count of new patients seen on this day ---
    $stmt_new = $pdo->prepare("
        SELECT COUNT(PatientID) 
        FROM (SELECT PatientID, MIN(Date) as first_visit_date FROM Treatments GROUP BY PatientID) as first_visits
        WHERE first_visit_date = ?
    ");
    $stmt_new->execute([$selected_date]);
    $daily_summary['new_patients'] = $stmt_new->fetchColumn();

    // --- Query 4 (ENHANCED): Get the detailed log with a flag for new patients ---
    $stmt_log = $pdo->prepare("
        SELECT 
            t.*, p.FirstName, p.LastName, pr.ProcedureName,
            (SELECT MIN(Date) FROM Treatments WHERE PatientID = t.PatientID) = t.Date AS is_new_patient
        FROM Treatments t
        JOIN Patients p ON t.PatientID = p.PatientID
        LEFT JOIN procedures pr ON t.ProcedureID = pr.ProcedureID
        WHERE t.Date = ?
        ORDER BY p.LastName, p.FirstName
    ");
    $stmt_log->execute([$selected_date]);
    $daily_treatments = $stmt_log->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Activity Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>
<?php include 'topbar.php'; ?>

<main class="main-content-area">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Daily Activity Log</h2>
    </div>

    <!-- ENHANCED Date Picker Form with Quick Navigation -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-center g-3">
                <div class="col-md-auto">
                    <label for="date" class="form-label">Select a Date to View:</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($selected_date); ?>">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary mt-4">View History</button>
                </div>
                <div class="col-md-auto ms-md-auto text-end">
                    <a href="daily_log.php?date=<?php echo date('Y-m-d', strtotime('-1 day', strtotime($selected_date))); ?>" class="btn btn-outline-secondary">← Previous Day</a>
                    <a href="daily_log.php?date=<?php echo date('Y-m-d', strtotime('+1 day', strtotime($selected_date))); ?>" class="btn btn-outline-secondary">Next Day →</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php else: ?>
        <!-- Daily Summary Cards -->
        <h3 class="mb-3">Summary for <?php echo date("F j, Y", strtotime($selected_date)); ?></h3>
        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="stat-card p-3"><div class="info w-100"><h5>Patients Seen</h5><h3 class="fw-bold"><?php echo $daily_summary['patients_seen']; ?></h3></div></div></div>
            <div class="col-md-4"><div class="stat-card p-3"><div class="info w-100"><h5>New Patients</h5><h3 class="fw-bold text-primary"><?php echo $daily_summary['new_patients']; ?></h3></div></div></div>
            <div class="col-md-4"><div class="stat-card p-3"><div class="info w-100"><h5>Revenue Collected</h5><h3 class="fw-bold text-success">₱ <?php echo number_format($daily_summary['revenue'], 2); ?></h3></div></div></div>
        </div>

        <!-- ENHANCED Daily Log Table -->
        <div class="table-responsive bg-white p-3 rounded shadow-sm">
            <table class="table table-hover">
                <thead>
                    <tr><th>Patient</th><th>Procedure</th><th>Tooth#</th><th>Charged</th><th>Paid</th><th>Notes</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if ($daily_treatments): foreach ($daily_treatments as $treatment): ?>
                        <tr>
                            <td>
                                <a href="patients.php?patientID=<?php echo $treatment['PatientID']; ?>"><?php echo htmlspecialchars($treatment['LastName'] . ', ' . $treatment['FirstName']); ?></a>
                                <?php if ($treatment['is_new_patient']): ?>
                                    <span class="badge bg-primary ms-2">New</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($treatment['ProcedureName'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($treatment['ToothNumber']); ?></td>
                            <td><?php echo number_format((float)$treatment['AmountCharged'], 2); ?></td>
                            <td><?php echo number_format((float)$treatment['AmountPaid'], 2); ?></td>
                            <td><?php echo htmlspecialchars($treatment['Notes']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-treatment-btn" data-bs-toggle="modal" data-bs-target="#editTreatmentModal" data-treatment='<?php echo json_encode($treatment); ?>'><i class="fas fa-edit"></i></button>
                                <a href="delete_treatment.php?id=<?php echo $treatment['TreatmentID']; ?>&return_to=daily_log&date=<?php echo $selected_date; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record? This cannot be undone.')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <!-- Enhanced Empty State -->
                        <tr>
                            <td colspan="7" class="text-center p-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Activity Recorded</h5>
                                <p>There were no treatments logged for this day.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<!-- Edit Treatment Modal -->
<div class="modal fade" id="editTreatmentModal" tabindex="-1" aria-labelledby="editTreatmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="editTreatmentModalLabel">Edit Treatment Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editTreatmentForm" action="update_treatment.php" method="POST">
                    <input type="hidden" name="return_to" value="daily_log">
                    <input type="hidden" name="return_date" value="<?php echo $selected_date; ?>">
                    <input type="hidden" name="patientID" id="editTreatmentPatientId">
                    <input type="hidden" name="TreatmentID" id="editTreatmentId">
                    <div class="row">
                        <div class="col-12 mb-3"><label class="form-label">Patient</label><input type="text" id="editTreatmentPatientName" class="form-control" readonly disabled></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Date</label><input type="date" name="Date" id="editTreatmentDate" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Procedure Done</label><select name="ProcedureID" id="editTreatmentProcedure" class="form-select" required><option value="">-- Select --</option><?php $allProcedures = $pdo->query("SELECT ProcedureID, ProcedureName FROM procedures ORDER BY ProcedureName")->fetchAll(PDO::FETCH_ASSOC); if($allProcedures) foreach ($allProcedures as $proc): ?><option value="<?php echo $proc['ProcedureID']; ?>"><?php echo htmlspecialchars($proc['ProcedureName']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Tooth Number</label><input type="text" name="ToothNumber" id="editTreatmentTooth" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Next Appointment</label><input type="date" name="NextAppointment" id="editTreatmentNextAppt" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Amount Charged</label><input type="number" step="0.01" name="AmountCharged" id="editAmountCharged" class="form-control balance-calc-edit"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Amount Paid</label><input type="number" step="0.01" name="AmountPaid" id="editAmountPaid" class="form-control balance-calc-edit"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Balance</label><input type="number" step="0.01" name="Balance" id="editBalance" class="form-control" readonly></div>
                        <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="Notes" id="editTreatmentNotes" class="form-control"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Update Treatment</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Balance calculation for EDIT modal
    const editChargedInput = document.getElementById('editAmountCharged');
    const editPaidInput = document.getElementById('editAmountPaid');
    const editBalanceInput = document.getElementById('editBalance');
    
    function calculateEditBalance() {
        const charged = parseFloat(editChargedInput.value) || 0;
        const paid = parseFloat(editPaidInput.value) || 0;
        editBalanceInput.value = (charged - paid).toFixed(2);
    }

    if (editChargedInput && editPaidInput) {
        editChargedInput.addEventListener('input', calculateEditBalance);
        editPaidInput.addEventListener('input', calculateEditBalance);
    }

    // Populate EDIT Treatment Modal
    const editTreatmentModal = document.getElementById('editTreatmentModal');
    if(editTreatmentModal) {
        editTreatmentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const treatment = JSON.parse(button.getAttribute('data-treatment'));
            document.getElementById('editTreatmentPatientName').value = treatment.LastName + ', ' + treatment.FirstName;
            document.getElementById('editTreatmentPatientId').value = treatment.PatientID;
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
</body>
</html>