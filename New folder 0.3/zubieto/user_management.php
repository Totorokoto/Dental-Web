<!-- user_management.php -->
<?php
session_start();

// Gatekeeper: Check if user is logged in and is an Admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'Admin') {
    header("location: login.php?error=Access denied.");
    exit;
}

require_once 'config.php';

$users = [];
$error_message = '';

try {
    // Fetch all existing users to display in a list
    $stmt = $pdo->query("SELECT UserID, Username, FirstName, LastName, Role FROM users ORDER BY LastName, FirstName");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("User Management Error: " . $e->getMessage());
    $error_message = "A database error occurred while fetching the user list.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content-area">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">User Management</h2>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">New user created successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Side: Add New User Form -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Add New User</h4>
                    </div>
                    <div class="card-body">
                        <form action="create_user.php" method="POST">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName">
                            </div>
                             <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="Admin">Admin</option>
                                    <option value="Dentist">Dentist</option>
                                    <option value="Assistant" selected>Assistant</option>
                                    <option value="Hygienist">Hygienist</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create User</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Side: User List -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Existing Users</h4>
                    </div>
                    <div class="card-body p-0">
                         <?php if ($error_message): ?>
                            <div class="alert alert-danger m-3"><?php echo $error_message; ?></div>
                        <?php elseif (empty($users)): ?>
                            <div class="alert alert-info m-3">No users found.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Username']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['Role']); ?></span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>