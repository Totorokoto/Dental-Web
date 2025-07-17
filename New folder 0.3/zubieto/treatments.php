<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once 'config.php';

// --- FILTERING LOGIC ---
$where_clauses = [];
$params = [];

// Patient Name Search
if (!empty($_GET['search'])) {
    $where_clauses[] = "(p.FirstName LIKE :search OR p.LastName LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

// Date Range Filter
if (!empty($_GET['start_date'])) {
    $where_clauses[] = "t.Date >= :start_date";
    $params[':start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $where_clauses[] = "t.Date <= :end_date";
    $params[':end_date'] = $_GET['end_date'];
}

// Procedure Filter
if (!empty($_GET['procedure_id'])) {
    $where_clauses[] = "t.ProcedureID = :procedure_id";
    $params[':procedure_id'] = $_GET['procedure_id'];
}

// --- DATABASE QUERIES ---
$treatments = [];
$summary = ['total_charged' => 0, 'total_paid' => 0, 'total_balance' => 0];

try {
    // Fetch all procedures for the filter dropdown
    $allProcedures = $pdo->query("SELECT ProcedureID, ProcedureName FROM procedures ORDER BY ProcedureName")->fetchAll(PDO::FETCH_ASSOC);

    // Base SQL for fetching treatments
    $sql_base = "FROM Treatments t 
                 JOIN Patients p ON t.PatientID = p.PatientID 
                 LEFT JOIN procedures pr ON t.ProcedureID = pr.ProcedureID";
    
    $sql_where = "";
    if (!empty($where_clauses)) {
        $sql_where = " WHERE " . implode(" AND ", $where_clauses);
    }

    // Query for the main table
    $sql_treatments = "SELECT t.*, p.FirstName, p.LastName, pr.ProcedureName " . $sql_base . $sql_where . " ORDER BY t.Date DESC, p.LastName";
    $stmt_treatments = $pdo->prepare($sql_treatments);
    $stmt_treatments->execute($params);
    $treatments = $stmt_treatments->fetchAll(PDO::FETCH_ASSOC);

    // Query for the financial summary
    $sql_summary = "SELECT SUM(t.AmountCharged) as total_charged, SUM(t.AmountPaid) as total_paid, SUM(t.Balance) as total_balance " . $sql_base . $sql_where;
    $stmt_summary = $pdo->prepare($sql_summary);
    $stmt_summary->execute($params);
    $summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>
<?php include 'topbar.php'; ?>

<main class="main-content-area">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Master Treatment Log</h2>
        <!-- The "Add" button will trigger the same modal as on patients.php -->
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTreatmentModal"><i class="fas fa-plus"></i> Add Treatment Record</button>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Records</h5>
            <form method="GET" action="treatments.php" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Patient Name</label>
                    <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="procedure_id" class="form-label">Procedure</label>
                    <select name="procedure_id" id="procedure_id" class="form-select">
                        <option value="">All Procedures</option>
                        <?php foreach($allProcedures as $proc): ?>
                            <option value="<?php echo $proc['ProcedureID']; ?>" <?php if(isset($_GET['procedure_id']) && $_GET['procedure_id'] == $proc['ProcedureID']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($proc['ProcedureName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <a href="treatments.php" class="btn btn-secondary">Clear Filters</a>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="stat-card p-3"><div class="info"><h5>Total Charged</h5><h3 class="fw-bold">₱ <?php echo number_format($summary['total_charged'] ?? 0, 2); ?></h3></div></div></div>
        <div class="col-md-4"><div class="stat-card p-3"><div class="info"><h5>Total Paid</h5><h3 class="fw-bold text-success">₱ <?php echo number_format($summary['total_paid'] ?? 0, 2); ?></h3></div></div></div>
        <div class="col-md-4"><div class="stat-card p-3"><div class="info"><h5>Outstanding Balance</h5><h3 class="fw-bold text-danger">₱ <?php echo number_format($summary['total_balance'] ?? 0, 2); ?></h3></div></div></div>
    </div>

    <!-- Master Log Table -->
    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover">
            <thead>
                <tr><th>Date</th><th>Patient</th><th>Procedure</th><th>Tooth#</th><th>Charged</th><th>Paid</th><th>Balance</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if ($treatments): foreach ($treatments as $treatment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($treatment['Date']); ?></td>
                        <td><a href="patients.php?patientID=<?php echo $treatment['PatientID']; ?>"><?php echo htmlspecialchars($treatment['LastName'] . ', ' . $treatment['FirstName']); ?></a></td>
                        <td><?php echo htmlspecialchars($treatment['ProcedureName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($treatment['ToothNumber']); ?></td>
                        <td><?php echo number_format((float)$treatment['AmountCharged'], 2); ?></td>
                        <td><?php echo number_format((float)$treatment['AmountPaid'], 2); ?></td>
                        <td><?php echo number_format((float)$treatment['Balance'], 2); ?></td>
                        <td>
                             <button class="btn btn-sm btn-primary edit-treatment-btn" data-bs-toggle="modal" data-bs-target="#editTreatmentModal" data-treatment='<?php echo json_encode($treatment); ?>'><i class="fas fa-edit"></i></button>
                             <a href="delete_treatment.php?id=<?php echo $treatment['TreatmentID']; ?>&return_to=treatments" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record? This cannot be undone.')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center">No records found matching your criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- ========================= -->
<!-- MODALS (COPIED FROM PATIENTS.PHP) -->
<!-- ========================= -->
<!-- NOTE: You need to create a version of the Add Modal form that allows selecting a patient -->
<!-- For now, we'll reuse the edit modal structure, which is more complete. -->

<!-- ================================== -->
<!-- ALL MODALS FOR TREATMENTS PAGE     -->
<!-- ================================== -->

<!-- Add Treatment Modal (Master Version) -->
<div class="modal fade" id="addTreatmentModal" tabindex="-1" aria-labelledby="addTreatmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTreatmentModalLabel">Add New Treatment Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="create_treatment.php" method="POST">
                    <!-- This hidden field will tell the script to return here -->
                    <input type="hidden" name="return_to" value="treatments">
                    <div class="row">
                        <!-- NEW: Patient Selection Dropdown -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Patient</label>
                            <select name="PatientID" class="form-select" required>
                                <option value="">-- Select a Patient --</option>
                                <?php 
                                    // We need to fetch all patients for this modal
                                    $allPatients = $pdo->query("SELECT PatientID, FirstName, LastName FROM patients ORDER BY LastName, FirstName")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($allPatients as $p): 
                                ?>
                                    <option value="<?php echo $p['PatientID']; ?>">
                                        <?php echo htmlspecialchars($p['LastName'] . ', ' . $p['FirstName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3"><label class="form-label">Date</label><input type="date" name="Date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Procedure Done</label><select name="ProcedureID" class="form-select" required><option value="">-- Select --</option><?php if($allProcedures) foreach ($allProcedures as $proc): ?><option value="<?php echo $proc['ProcedureID']; ?>"><?php echo htmlspecialchars($proc['ProcedureName']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Tooth Number</label><input type="text" name="ToothNumber" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Next Appointment</label><input type="date" name="NextAppointment" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Amount Charged</label><input type="number" step="0.01" name="AmountCharged" class="form-control balance-calc-add"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Amount Paid</label><input type="number" step="0.01" name="AmountPaid" class="form-control balance-calc-add"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Balance</label><input type="number" step="0.01" name="Balance" class="form-control balance-calc-add" readonly></div>
                        <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="Notes" class="form-control"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Treatment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Treatment Record Modal -->
<div class="modal fade" id="editTreatmentModal" tabindex="-1" aria-labelledby="editTreatmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTreatmentModalLabel">Edit Treatment Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTreatmentForm" action="update_treatment.php" method="POST">
                     <!-- This hidden field will tell the script to return here -->
                    <input type="hidden" name="return_to" value="treatments">
                    <!-- The PatientID is part of the treatment data, but we pass it explicitly for the form action -->
                    <input type="hidden" name="patientID" id="editTreatmentPatientId">
                    <input type="hidden" name="TreatmentID" id="editTreatmentId">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Patient</label>
                            <input type="text" id="editTreatmentPatientName" class="form-control" readonly disabled>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Date</label><input type="date" name="Date" id="editTreatmentDate" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Procedure Done</label><select name="ProcedureID" id="editTreatmentProcedure" class="form-select" required><option value="">-- Select --</option><?php if($allProcedures) foreach ($allProcedures as $proc): ?><option value="<?php echo $proc['ProcedureID']; ?>"><?php echo htmlspecialchars($proc['ProcedureName']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Tooth Number</label><input type="text" name="ToothNumber" id="editTreatmentTooth" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Next Appointment</label><input type="date" name="NextAppointment" id="editTreatmentNextAppt" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Amount Charged</label><input type="number" step="0.01" name="AmountCharged" id="editAmountCharged" class="form-control balance-calc-edit"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Amount Paid</label><input type="number" step="0.01" name="AmountPaid" id="editAmountPaid" class="form-control balance-calc-edit"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Balance</label><input type="number" step="0.01" name="Balance" id="editBalance" class="form-control" readonly></div>
                        <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="Notes" id="editTreatmentNotes" class="form-control"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Treatment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to handle balance calculation
    function setupBalanceCalculation(containerSelector, inputsClass) {
        const container = document.querySelector(containerSelector);
        if (!container) return;
        
        const chargedInput = container.querySelector(`[name="AmountCharged"].${inputsClass}`);
        const paidInput = container.querySelector(`[name="AmountPaid"].${inputsClass}`);
        const balanceInput = container.querySelector(`[name="Balance"]`);

        const calculate = () => {
            if(!chargedInput || !paidInput || !balanceInput) return;
            const charged = parseFloat(chargedInput.value) || 0;
            const paid = parseFloat(paidInput.value) || 0;
            balanceInput.value = (charged - paid).toFixed(2);
        };

        if(chargedInput && paidInput) {
            chargedInput.addEventListener('input', calculate);
            paidInput.addEventListener('input', calculate);
        }
    }

    // Setup calculation for both modals
    setupBalanceCalculation('#addTreatmentModal', 'balance-calc-add');
    setupBalanceCalculation('#editTreatmentModal', 'balance-calc-edit');
    
    // Populate EDIT Treatment Modal
    const editTreatmentModal = document.getElementById('editTreatmentModal');
    if(editTreatmentModal) {
        editTreatmentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const treatment = JSON.parse(button.getAttribute('data-treatment'));
            
            // Populate all the fields
            document.getElementById('editTreatmentPatientName').value = treatment.LastName + ', ' + treatment.FirstName;
            document.getElementById('editTreatmentPatientId').value = treatment.PatientID; // Set hidden patientID
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