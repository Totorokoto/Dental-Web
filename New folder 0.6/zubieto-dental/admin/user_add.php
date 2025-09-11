<?php
// FILE: admin/user_add.php
require 'includes/header.php';

// RBAC Check
if ($_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "Access Denied."; $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit();
}
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New User</h1>
    <a href="users.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to User List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="user_add_process.php" method="POST" id="addUserForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="col-md-6">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>
                <div class="col-md-4">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select id="role" name="role" class="form-select" required>
                        <option selected disabled value="">Choose...</option>
                        <option value="Admin">Admin</option>
                        <option value="Dentist">Dentist</option>
                        <option value="Assistant">Assistant</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                    <select id="branch" name="branch" class="form-select" required>
                        <option selected disabled value="">Choose...</option>
                        <option value="Lucban">Lucban</option>
                        <option value="Sta. Rosa">Sta. Rosa</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active" id="active" value="1" checked>
                        <label class="form-check-label" for="active">Active</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0">
                        <label class="form-check-label" for="inactive">Inactive</label>
                    </div>
                </div>
                <div class="col-12 mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">Create User Account</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// UX Enhancement: Client-side password match validation
document.getElementById('addUserForm').addEventListener('submit', function(event) {
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirm_password');
    if (password.value !== confirmPassword.value) {
        confirmPassword.classList.add('is-invalid');
        event.preventDefault(); // Stop form submission
    } else {
        confirmPassword.classList.remove('is-invalid');
    }
});
</script>

<?php require 'includes/footer.php'; ?>