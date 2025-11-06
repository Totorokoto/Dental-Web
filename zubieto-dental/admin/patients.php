<?php
// FILE: admin/patients.php 

require 'includes/header.php';
// The database connection is now open from header.php

// --- 1. DETERMINE THE BRANCH TO DISPLAY ---
$valid_branches = ['Lucban', 'Sta. Rosa'];
$selected_branch = 'All';

// Determine the requested branch from URL or session role
if (isset($_GET['branch'])) {
    $requested_branch = $_GET['branch'];
} else {
    // Default to 'All' for Admin, or their specific branch for other roles
    $requested_branch = ($_SESSION['role'] === 'Admin') ? 'All' : $_SESSION['branch'];
}

// --- 2. ENFORCE SECURITY AND ROLE-BASED ACCESS ---
if ($_SESSION['role'] !== 'Admin') {
    // Force non-admins to only see their own branch
    $selected_branch = $_SESSION['branch'];
} else {
    // Admin can view 'All' or a specific valid branch
    if (in_array($requested_branch, $valid_branches) || $requested_branch === 'All') {
        $selected_branch = $requested_branch;
    } else {
        $selected_branch = 'All'; // Default to 'All' if an invalid branch is requested
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
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .btn-primary:hover {
        background-color: #00695C;
        border-color: #00695C;
    }
    .btn-outline-primary {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    .btn-outline-primary:hover, .btn-outline-primary.active {
        background-color: var(--primary-color);
        color: white;
    }
    .alert-custom {
        border-radius: 0.75rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: none;
        padding: 1rem 1.5rem;
    }
    #patientsTable_wrapper .form-control,
    #patientsTable_wrapper .form-select {
        border-radius: 0.5rem;
    }

    /* Styling for action buttons to ensure they have consistent spacing and transitions */
    #patientsTable .btn {
        margin: 0 2px;
        transition: all 0.2s ease-in-out;
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Patient Records</h1>
    <a href="patient_add.php" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i> Add New Patient
    </a>
</div>

<!-- Session Message Display -->
<?php
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message_type'] ?? 'success';
    $icon = ($message_type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';
    echo '
    <div class="alert alert-' . $message_type . ' d-flex align-items-center alert-custom alert-dismissible fade show" role="alert">
        <i class="fas ' . $icon . ' me-3"></i>
        <div>' . $_SESSION['message'] . '</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    unset($_SESSION['message']); unset($_SESSION['message_type']);
}
?>

<!-- Main Content Card -->
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
        <h5 class="mb-0">
            <i class="fas fa-users me-2" style="color: var(--primary-color);"></i>
            Displaying Patients from: <span class="fw-bold" style="color: var(--primary-color);"><?php echo htmlspecialchars($selected_branch); ?></span>
        </h5>
        
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <div class="filter-controls mt-2 mt-md-0">
                <div class="btn-group" role="group" aria-label="Branch Filter">
                    <a href="?branch=All" class="btn btn-sm <?php echo ($selected_branch === 'All') ? 'btn-primary' : 'btn-outline-primary'; ?>">All Branches</a>
                    <?php foreach ($valid_branches as $branch_name): ?>
                        <a href="?branch=<?php echo $branch_name; ?>" class="btn btn-sm <?php echo ($selected_branch === $branch_name) ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo $branch_name; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table id="patientsTable" class="table table-striped table-hover" style="width:100%">
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
                                <td>
                                    <?php 
                                        $branch_bg = ($patient['branch'] === 'Sta. Rosa') ? 'bg-info-subtle' : 'bg-success-subtle';
                                        $branch_text = ($patient['branch'] === 'Sta. Rosa') ? 'text-info-emphasis' : 'text-success-emphasis';
                                        echo '<span class="badge rounded-pill ' . $branch_bg . ' ' . $branch_text . '">' . htmlspecialchars($patient['branch']) . '</span>';
                                    ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-center">
                                <a href="patient_view.php?id=<?php echo $patient['patient_id']; ?>&branch=<?php echo urlencode($selected_branch); ?>" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="patient_edit.php?id=<?php echo $patient['patient_id']; ?>&branch=<?php echo urlencode($selected_branch); ?>" class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm deleteBtn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deletePatientModal" 
                                        data-patient-id="<?php echo $patient['patient_id']; ?>"
                                        data-patient-name="<?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>"
                                        data-bs-toggle="tooltip"
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 1rem;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deletePatientModalLabel">
            <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to permanently delete the record for <strong id="patientNameToDelete" class="text-danger"></strong>?</p>
        <p class="text-muted small">This action cannot be undone and will remove all associated patient history.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete Patient</button>
      </div>
    </div>
  </div>
</div>


<?php
$stmt->close();
// The connection will be closed by footer.php
require 'includes/footer.php';
?>

<!-- All page-specific JavaScript goes here, after the libraries are loaded -->
<script>
$(document).ready(function() {
    // Initialize DataTables for sorting, searching, and pagination
    $('#patientsTable').DataTable({
        "pageLength": 10,
        "order": [], // Disable initial sorting
        "language": {
            "search": "Search Patients:"
        }
    });

    // Initialize Bootstrap tooltips for action buttons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // --- Delete Modal Logic ---
    var patientIdToDelete;

    $('#patientsTable tbody').on('click', '.deleteBtn', function() {
        var patientName = $(this).data('patient-name');
        patientIdToDelete = $(this).data('patient-id');
        $('#patientNameToDelete').text(patientName);
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (patientIdToDelete) {
            var currentBranch = '<?php echo urlencode($selected_branch); ?>';
            var deleteUrl = 'patient_delete_process.php?id=' + patientIdToDelete + '&branch=' + currentBranch;
            window.location.href = deleteUrl;
        }
    });
});
</script>