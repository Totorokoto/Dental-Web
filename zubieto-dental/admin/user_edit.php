<?php
// FILE: admin/user_edit.php
require 'includes/header.php';
// The database connection is now open from header.php

// RBAC and ID Validation
if ($_SESSION['role'] !== 'Admin') { header("Location: dashboard.php"); exit(); }
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) { header("Location: users.php"); exit(); }

// Fetch user data
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { header("Location: users.php"); exit(); }
$user = $result->fetch_assoc();
$stmt->close();
?>

<style>
    /* Custom styles for this page */
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25);
    }
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .btn-primary:hover {
        background-color: #00695C;
        border-color: #00695C;
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit User: <?php echo htmlspecialchars($user['full_name']); ?></h1>
    <a href="users.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to User List</a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="user_edit_process.php" method="POST" id="editUserForm">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></div>
                <div class="col-md-6"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></div>
                
                <div class="col-12"><hr class="my-3"><p class="text-muted small mb-0">Leave the password fields blank below to keep the user's current password.</p></div>

                <div class="col-md-6"><label class="form-label">New Password</label><input type="password" class="form-control" id="password" name="password"></div>
                <div class="col-md-6"><label class="form-label">Confirm New Password</label><input type="password" class="form-control" id="confirm_password" name="confirm_password"><div class="invalid-feedback">Passwords do not match.</div></div>
                
                <div class="col-md-3"><label class="form-label">Role <span class="text-danger">*</span></label><select name="role" class="form-select" required>
                    <option value="Admin" <?php if($user['role']=='Admin') echo 'selected'; ?>>Admin</option>
                    <option value="Dentist" <?php if($user['role']=='Dentist') echo 'selected'; ?>>Dentist</option>
                    <option value="Assistant" <?php if($user['role']=='Assistant') echo 'selected'; ?>>Assistant</option>
                </select></div>
                <div class="col-md-3"><label class="form-label">Branch <span class="text-danger">*</span></label><select name="branch" class="form-select" required>
                    <option value="Lucban" <?php if($user['branch']=='Lucban') echo 'selected'; ?>>Lucban</option>
                    <option value="Sta. Rosa" <?php if($user['branch']=='Sta. Rosa') echo 'selected'; ?>>Sta. Rosa</option>
                </select></div>
                <div class="col-md-3"><label class="form-label">Availability Status</label><select name="availability_status" class="form-select" <?php if ($user['role'] === 'Assistant') echo 'disabled'; ?>>
                    <option value="Available" <?php if($user['availability_status']=='Available') echo 'selected'; ?>>Available</option>
                    <option value="On Leave" <?php if($user['availability_status']=='On Leave') echo 'selected'; ?>>On Leave</option>
                    <option value="Training" <?php if($user['availability_status']=='Training') echo 'selected'; ?>>Training</option>
                    <option value="Sick Day" <?php if($user['availability_status']=='Sick Day') echo 'selected'; ?>>Sick Day</option>
                </select></div>
                <div class="col-md-3 align-self-center"><label class="form-label">Account Status</label>
                    <div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_active" value="1" <?php if($user['is_active']) echo 'checked'; ?>><label class="form-check-label">Active</label></div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_active" value="0" <?php if(!$user['is_active']) echo 'checked'; ?>><label class="form-check-label">Inactive</label></div>
                    </div>
                </div>
                <div class="col-12 mt-4 text-center"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Update User Account</button></div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editUserForm').addEventListener('submit', function(event) {
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirm_password');
    if (password.value !== "" && password.value !== confirmPassword.value) {
        confirmPassword.classList.add('is-invalid');
        event.preventDefault();
    } else {
        confirmPassword.classList.remove('is-invalid');
    }
});
</script>

<?php 
// The connection will be closed by footer.php
require 'includes/footer.php'; 
?>