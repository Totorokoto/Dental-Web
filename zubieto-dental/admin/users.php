<?php
// FILE: admin/users.php

require 'includes/header.php';
// The database connection is now open from header.php

// --- RBAC (Role-Based Access Control) ---
if ($_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "Access Denied: You do not have permission to manage users.";
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit();
}

// Fetch all users from the database, including availability status
$sql = "SELECT user_id, username, full_name, role, branch, is_active, availability_status FROM users ORDER BY full_name";
$result = $conn->query($sql);
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
    .alert-custom {
        border-radius: 0.75rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: none;
        padding: 1rem 1.5rem;
    }
    
    #usersTable .btn {
        transition: all 0.2s ease-in-out;
    }
    .availability-select {
        min-width: 120px;
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <a href="user_add.php" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i> Add New User</a>
</div>

<?php
// Display feedback messages
if (isset($_SESSION['message'])) {
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
    $icon = ($message_type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';
    echo '
    <div class="alert alert-' . $message_type . ' d-flex align-items-center alert-custom alert-dismissible fade show" role="alert">
        <i class="fas ' . $icon . ' me-3 fa-lg"></i>
        <div>' . $_SESSION['message'] . '</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<div class="card">
    <div class="card-body">
        <table id="usersTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th class="text-center">Account Status</th>
                    <th class="text-center">Availability Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($user = $result->fetch_assoc()): ?>
                        <tr data-user-row-id="<?php echo $user['user_id']; ?>">
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['branch']); ?></td>
                            <td class="text-center">
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($user['role'] !== 'Assistant'): ?>
                                    <select class="form-select form-select-sm availability-select" 
                                            data-user-id="<?php echo $user['user_id']; ?>" 
                                            data-original-status="<?php echo $user['availability_status']; ?>">
                                        <option value="Available" <?php if ($user['availability_status'] == 'Available') echo 'selected'; ?>>Available</option>
                                        <option value="On Leave" <?php if ($user['availability_status'] == 'On Leave') echo 'selected'; ?>>On Leave</option>
                                        <option value="Training" <?php if ($user['availability_status'] == 'Training') echo 'selected'; ?>>Training</option>
                                        <option value="Sick Day" <?php if ($user['availability_status'] == 'Sick Day') echo 'selected'; ?>>Sick Day</option>
                                    </select>
                                <?php else: ?>
                                    <span class="text-muted small">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="user_edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                                
                                <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                    <span data-bs-toggle="tooltip" title="Cannot delete yourself">
                                        <button class="btn btn-outline-danger btn-sm" disabled><i class="fas fa-trash"></i></button>
                                    </span>
                                <?php else: ?>
                                    <span data-bs-toggle="tooltip" title="Delete">
                                        <button type="button" class="btn btn-outline-danger btn-sm deleteBtn" 
                                                data-bs-toggle="modal" data-bs-target="#deleteUserModal" 
                                                data-user-id="<?php echo $user['user_id']; ?>"
                                                data-user-name="<?php echo htmlspecialchars($user['full_name']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm User Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to permanently delete the account for <strong id="userNameToDelete"></strong>?</p>
        <p class="text-danger"><strong>This action cannot be undone.</strong> Consider deactivating the user instead if they have associated records.</p>
      </div>
      <div class="modal-footer" style="border-top: none;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete User</button>
      </div>
    </div>
  </div>
</div>

<?php
// The connection will be closed by footer.php
require 'includes/footer.php';
?>

<script>
$(document).ready(function() {
    // Storing the current user's ID from PHP session into a JS variable
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;

    // Initialize Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize DataTable
    $('#usersTable').DataTable();

    // Modal Logic for deletion
    var userIdToDelete; 
    $('.deleteBtn').on('click', function() {
        $('.tooltip').remove(); 
        userIdToDelete = $(this).data('user-id');
        $('#userNameToDelete').text($(this).data('user-name'));
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (userIdToDelete) {
            window.location.href = 'user_delete_process.php?id=' + userIdToDelete;
        }
    });

    // AJAX for quick status update
    $('.availability-select').on('change', function() {
        var dropdown = $(this);
        var userId = dropdown.data('user-id');
        var newStatus = dropdown.val();

        $.ajax({
            url: 'ajax_update_availability.php',
            type: 'POST',
            data: {
                user_id: userId,
                status: newStatus
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Show a success message for any update
                    toastr.success(response.message);
                    
                    // --- NEW: REAL-TIME HEADER UPDATE LOGIC ---
                    // Check if the update was for the currently logged-in user
                    if (response.is_self_update) {
                        // Find the badge in the header by its ID
                        var headerBadge = $('#user-status-badge');
                        
                        // Update its text
                        headerBadge.text(response.new_status);
                        
                        // Remove all existing color classes and add the new one from the server
                        headerBadge.removeClass('bg-success bg-secondary bg-info bg-warning text-dark')
                                   .addClass(response.badge_class);
                    }
                    
                    // Update the 'original-status' attribute to prevent accidental reverts on error
                    dropdown.data('original-status', newStatus);

                } else {
                    toastr.error('Error: ' + response.message);
                    // On failure, revert the dropdown to its original state
                    dropdown.val(dropdown.data('original-status'));
                }
            },
            error: function() {
                toastr.error('A server error occurred. Please try again.');
                // On failure, revert the dropdown to its original state
                dropdown.val(dropdown.data('original-status'));
            }
        });
    });
});
</script>