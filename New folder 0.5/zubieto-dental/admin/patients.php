<?php
// FILE: admin/patients.php (FINAL CORRECTED VERSION FOR SCRIPT LOADING)

require 'includes/header.php';
require '../includes/db_connect.php';

// --- 1. DETERMINE THE BRANCH TO DISPLAY ---
$valid_branches = ['Lucban', 'Sta. Rosa'];
$selected_branch = 'All';

if (isset($_GET['branch'])) {
    $requested_branch = $_GET['branch'];
} else {
    $requested_branch = ($_SESSION['role'] === 'Admin') ? 'All' : $_SESSION['branch'];
}

// --- 2. ENFORCE SECURITY AND ROLE-BASED ACCESS ---
if ($_SESSION['role'] !== 'Admin') {
    $selected_branch = $_SESSION['branch'];
} else {
    if (in_array($requested_branch, $valid_branches) || $requested_branch === 'All') {
        $selected_branch = $requested_branch;
    } else {
        $selected_branch = 'All';
    }
}

// --- 3. BUILD AND EXECUTE THE SQL QUERY ---
$sql = "SELECT patient_id, last_name, first_name, mobile_no, branch FROM patients";
$params = [];
$types = "";

if ($selected_branch !== 'All') {
    $sql .= " WHERE branch = ?";
    $params[] = $selected_branch;
    $types .= "s";
}

$sql .= " ORDER BY last_name, first_name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>
<!-- Page-specific styles -->
<style>
    .filter-controls .btn { margin-right: 5px; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Patient Records</h1>
    <a href="patient_add.php" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i> Add New Patient</a>
</div>

<?php
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">' . $_SESSION['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']); unset($_SESSION['message_type']);
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Displaying Patients from: <span class="text-primary fw-bold"><?php echo htmlspecialchars($selected_branch); ?></span>
        </h5>
        
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <div class="filter-controls">
                <div class="btn-group" role="group" aria-label="Branch Filter">
                    <a href="?branch=All" class="btn <?php echo ($selected_branch === 'All') ? 'btn-primary' : 'btn-outline-primary'; ?>">All Branches</a>
                    <?php foreach ($valid_branches as $branch_name): ?>
                        <a href="?branch=<?php echo $branch_name; ?>" class="btn <?php echo ($selected_branch === $branch_name) ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo $branch_name; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table id="patientsTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Mobile Number</th>
                    <?php if ($selected_branch === 'All'): ?>
                        <th>Branch</th>
                    <?php endif; ?>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($patient = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patient['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($patient['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($patient['mobile_no']); ?></td>
                            <?php if ($selected_branch === 'All'): ?>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($patient['branch']); ?></span></td>
                            <?php endif; ?>
                            <td class="text-center">
                                <a href="patient_view.php?id=<?php echo $patient['patient_id']; ?>&branch=<?php echo urlencode($selected_branch); ?>" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                <a href="patient_edit.php?id=<?php echo $patient['patient_id']; ?>&branch=<?php echo urlencode($selected_branch); ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-danger btn-sm deleteBtn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deletePatientModal" 
                                        data-patient-id="<?php echo $patient['patient_id']; ?>"
                                        data-patient-name="<?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePatientModal" tabindex="-1" aria-labelledby="deletePatientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deletePatientModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to permanently delete the record for <strong id="patientNameToDelete"></strong>?</p>
        <p class="text-danger"><strong>This action cannot be undone.</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete Patient</button>
      </div>
    </div>
  </div>
</div>


<?php
// Close PHP connections before the footer is included
$stmt->close();
$conn->close();

// ========================================================== -->
// THE FIX IS HERE: The footer is now called BEFORE the script -->
// ========================================================== -->
require 'includes/footer.php';
?>

<!-- All page-specific JavaScript goes here, after the libraries are loaded -->
<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#patientsTable').DataTable({
        "pageLength": 10,
        "order": [] 
    });

    // A variable to hold the patient ID to be deleted
    var patientIdToDelete;

    // 1. When a trash can icon is clicked, store its data
    $('#patientsTable tbody').on('click', '.deleteBtn', function() {
        var patientName = $(this).data('patient-name');
        patientIdToDelete = $(this).data('patient-id');
        $('#patientNameToDelete').text(patientName);
    });

    // 2. When the final "Yes, Delete Patient" button is clicked, perform the action
    $('#confirmDeleteBtn').on('click', function() {
    if (patientIdToDelete) {
        // THE FIX IS HERE: We add the current branch to the redirect URL
        var currentBranch = '<?php echo urlencode($selected_branch); ?>';
        var deleteUrl = 'patient_delete_process.php?id=' + patientIdToDelete + '&branch=' + currentBranch;
        window.location.href = deleteUrl;
    }
});
});
</script>