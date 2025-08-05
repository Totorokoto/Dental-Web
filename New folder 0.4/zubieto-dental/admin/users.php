<?php
// FILE: admin/users.php

require 'includes/header.php';
require '../includes/db_connect.php';

// --- RBAC (Role-Based Access Control) ---
// This is the most important part of this file. Only Admins can access this page.
if ($_SESSION['role'] !== 'Admin') {
    // If the user is not an admin, set an error message and redirect to the dashboard.
    $_SESSION['message'] = "Access Denied: You do not have permission to manage users.";
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit();
}

// Fetch all users from the database
$sql = "SELECT user_id, username, full_name, role, branch, is_active FROM users ORDER BY full_name";
$result = $conn->query($sql);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <a href="user_add.php" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i> Add New User</a>
</div>

<?php
// Display feedback messages
if (isset($_SESSION['message'])) {
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
    echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">' . $_SESSION['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
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
                    <th>Username</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
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
                                <a href="user_edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php
                                // --- UX Enhancement: Prevent an admin from deleting their own account ---
                                if ($user['user_id'] == $_SESSION['user_id']): ?>
                                    <button class="btn btn-danger btn-sm" title="Cannot delete yourself" disabled><i class="fas fa-trash"></i></button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-danger btn-sm deleteBtn" 
                                            data-bs-toggle="modal" data-bs-target="#deleteUserModal" 
                                            data-user-id="<?php echo $user['user_id']; ?>"
                                            data-user-name="<?php echo htmlspecialchars($user['full_name']); ?>"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirm User Deletion</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p>Are you sure you want to delete the user <strong id="userNameToDelete"></strong>?</p>
        <p class="text-danger">This action is irreversible.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a id="confirmUserDeleteLink" href="#" class="btn btn-danger">Yes, Delete User</a>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable();

    $('.deleteBtn').on('click', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        $('#userNameToDelete').text(userName);
        $('#confirmUserDeleteLink').attr('href', 'user_delete_process.php?id=' + userId);
    });
});
</script>

<?php
$conn->close();
require 'includes/footer.php';
?>