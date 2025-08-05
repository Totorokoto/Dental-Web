<?php
// FILE: admin/patients.php (ENHANCED)
require 'includes/header.php';
require '../includes/db_connect.php';

$sql = "SELECT patient_id, last_name, first_name, birthdate, mobile_no FROM patients ORDER BY last_name, first_name";
$result = $conn->query($sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Patient Records</h1>
    <a href="patient_add.php" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i> Add New Patient</a>
</div>

<?php
// Enhanced Feedback Alert System
if (isset($_SESSION['message'])) {
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
    echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<div class="card">
    <div class="card-body">
        <table id="patientsTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Birthdate</th>
                    <th>Mobile Number</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($patient = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patient['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($patient['first_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($patient['birthdate'])); ?></td>
                            <td><?php echo htmlspecialchars($patient['mobile_no']); ?></td>
                            <td class="text-center">
                                <a href="patient_view.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                <a href="patient_edit.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                <!-- UX ENHANCEMENT: Trigger modal instead of direct link -->
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

<!-- UX ENHANCEMENT: Delete Confirmation Modal -->
<div class="modal fade" id="deletePatientModal" tabindex="-1" aria-labelledby="deletePatientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deletePatientModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you absolutely sure you want to permanently delete the record for <strong id="patientNameToDelete"></strong>?</p>
        <p class="text-danger"><strong>This action cannot be undone.</strong> All associated medical records, treatments, and findings will be lost forever.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a id="confirmDeleteLink" href="#" class="btn btn-danger">Yes, Delete Patient</a>
      </div>
    </div>
  </div>
</div>

<?php
$conn->close();
// Add JavaScript at the end of the file
?>
<script>
$(document).ready(function() {
    // Initialize DataTables for powerful search and pagination
    $('#patientsTable').DataTable();

    // JavaScript to handle the delete modal
    $('.deleteBtn').on('click', function() {
        // Get data from the button that was clicked
        var patientId = $(this).data('patient-id');
        var patientName = $(this).data('patient-name');

        // Populate the modal with the specific patient's info
        $('#patientNameToDelete').text(patientName);
        
        // Create the correct delete link for the modal's confirm button
        var deleteUrl = 'patient_delete_process.php?id=' + patientId;
        $('#confirmDeleteLink').attr('href', deleteUrl);
    });
});
</script>
<?php
require 'includes/footer.php';
?>