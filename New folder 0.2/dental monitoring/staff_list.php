<?php
// 1. START THE SESSION & GATEKEEPER
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 2. REQUIRE CONFIG
require_once 'config.php';

// 3. PAGE-SPECIFIC LOGIC
$staff_members = [];
$error_message = '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Base SQL query to get all users (staff)
    $sql = "SELECT UserID, Username, FirstName, LastName, Role FROM users";
    
    // Add a WHERE clause if a search term is provided
    if (!empty($search_term)) {
        // Search by name, username, or role
        $sql .= " WHERE FirstName LIKE :search 
                  OR LastName LIKE :search 
                  OR Username LIKE :search 
                  OR Role LIKE :search";
    }
    
    $sql .= " ORDER BY Role, LastName, FirstName";
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($search_term)) {
        $stmt->bindValue(':search', '%' . $search_term . '%');
    }
    
    $stmt->execute();
    $staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Staff List Error: " . $e->getMessage());
    $error_message = "A database error occurred while fetching the staff list.";
}

// Helper function to determine badge color based on role
function getRoleBadgeClass($role) {
    switch (strtolower($role)) {
        case 'admin':
            return 'bg-danger';
        case 'dentist':
            return 'bg-primary';
        case 'hygienist':
            return 'bg-info text-dark';
        case 'assistant':
            return 'bg-secondary';
        default:
            return 'bg-light text-dark';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff List</title>
    
    <!-- REQUIRED CSS LINKS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    
    <style>
        /* Re-using patient-table styles for consistency */
        .staff-table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .staff-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        .staff-table tbody td {
            vertical-align: middle;
        }
        .action-icon {
            color: #6c757d;
            text-decoration: none;
            margin: 0 5px;
        }
        .action-icon:hover {
            color: #0d6efd;
        }
        .action-icon.delete:hover {
            color: #dc3545;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content-area">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Staff List</h2>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="user_management.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i> Add / Manage Users
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Feedback Messages -->
        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                User action completed successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Main Content: Table or Error Messages -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php elseif (empty($staff_members)): ?>
            <?php if (!empty($search_term)): ?>
                <div class="alert alert-warning">No staff found matching your search for "<strong><?php echo htmlspecialchars($search_term); ?></strong>".</div>
            <?php else: ?>
                <div class="alert alert-info">No staff members found in the database.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="table-responsive staff-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($staff['LastName'] . ', ' . $staff['FirstName']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($staff['Username']); ?></td>
                                <td>
                                    <span class="badge <?php echo getRoleBadgeClass($staff['Role']); ?>">
                                        <?php echo htmlspecialchars($staff['Role']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="#" class="action-icon" title="View Profile"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="action-icon" title="Edit User"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-icon delete" title="Delete User"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>