<?php
// FILE: admin/includes/header.php (FINAL, SECURE VERSION)
require 'session_check.php'; // This starts the session and ensures the user is logged in.

// =========================================================================
// THE FIX IS HERE: CENTRALIZED PAGE ACCESS CONTROL
// Define which pages are admin-only.
// =========================================================================
$current_page = basename($_SERVER['PHP_SELF']);
$admin_only_pages = ['dashboard.php', 'users.php', 'user_add.php', 'user_edit.php'];

// Check if the user is trying to access an admin-only page
if (in_array($current_page, $admin_only_pages)) {
    // If the page is admin-only AND the user is NOT an Admin...
    if ($_SESSION['role'] !== 'Admin') {
        // ...set an error message and redirect them.
        $_SESSION['message'] = "Access Denied: You do not have permission to view that page.";
        $_SESSION['message_type'] = 'danger';
        header("Location: appointments.php"); // Redirect to a safe default page
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Zubieto Dental Clinic</title>
    <!-- CSS Includes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 56px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .sidebar-sticky { height: calc(100vh - 56px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        main { margin-top: 56px; padding-top: 1.5rem; }
        .nav-link.active { font-weight: bold; color: #0d6efd !important; }
        .card { border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="<?php echo ($_SESSION['role'] == 'Admin') ? 'dashboard.php' : 'appointments.php'; ?>">Zubieto Dental Clinic</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav ms-auto">
        <div class="nav-item text-nowrap d-flex align-items-center">
            <span class="navbar-text text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
            <a class="nav-link px-3" href="../logout.php">Logout <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</header>
<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
             <div class="sidebar-sticky pt-3">
                <ul class="nav flex-column">
                    
                    <!-- Role-based links will now work correctly -->
                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link <?php echo (strpos($current_page, 'patient') !== false) ? 'active' : ''; ?>" href="patients.php"><i class="fas fa-users fa-fw me-2"></i> Patients</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'appointments.php') ? 'active' : ''; ?>" href="appointments.php"><i class="fas fa-calendar-alt fa-fw me-2"></i> Appointments</a></li>
                    
                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <li class="nav-item"><a class="nav-link <?php echo (strpos($current_page, 'user') !== false) ? 'active' : ''; ?>" href="users.php"><i class="fas fa-user-cog fa-fw me-2"></i> Manage Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-chart-bar fa-fw me-2"></i> Reports</a></li>
                    <?php endif; ?>

                </ul>
            </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">