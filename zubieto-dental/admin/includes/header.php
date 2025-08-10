<?php
// FILE: admin/includes/header.php (FINAL - WITH PERFECT LAYOUT)
require 'session_check.php'; 

// PAGE ACCESS CONTROL
$current_page = basename($_SERVER['PHP_SELF']);
$admin_only_pages = ['dashboard.php', 'users.php', 'user_add.php', 'user_edit.php', 'user_delete_process.php', 'reports.php'];
$dentist_allowed_pages = ['dentist_dashboard.php'];

if (in_array($current_page, $admin_only_pages) && $_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "Access Denied: You do not have permission to view that page.";
    $_SESSION['message_type'] = 'danger';
    header("Location: appointments.php");
    exit();
}

if (in_array($current_page, $dentist_allowed_pages) && !in_array($_SESSION['role'], ['Admin', 'Dentist'])) {
    $_SESSION['message'] = "Access Denied.";
    $_SESSION['message_type'] = 'danger';
    header("Location: appointments.php");
    exit();
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
    
    <!-- CUSTOM CSS FOR ENHANCED HEADER & SIDEBAR -->
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg-color: #2c3e50;
            --sidebar-link-color: #ecf0f1;
            --sidebar-hover-bg: #34495e;
            --sidebar-active-bg: #0d6efd;
        }
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        }
        .main-header {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            height: 56px;
        }
        .navbar-brand {
            color: #2c3e50 !important;
            font-weight: 600;
            width: var(--sidebar-width);
        }
        .sidebar-custom {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            padding-top: 56px;
            background-color: var(--sidebar-bg-color);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 100; /* Ensure sidebar is above content */
        }
        .sidebar-sticky {
            height: calc(100vh - 56px);
            overflow-y: auto;
        }
        .sidebar-custom .nav-link {
            color: var(--sidebar-link-color);
            padding: 12px 20px;
            font-weight: 500;
            transition: background-color 0.2s, color 0.2s;
            border-left: 4px solid transparent;
        }
        .sidebar-custom .nav-link .fa-fw {
            width: 1.5em; 
        }
        .sidebar-custom .nav-link:hover:not(.active) {
            background-color: var(--sidebar-hover-bg);
        }
        .sidebar-custom .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: #fff;
            font-weight: 700;
            border-left-color: #a0c7ff;
        }
        .nav-heading {
            font-size: 0.75rem;
            padding: 10px 20px;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1rem;
        }
        main {
            margin-top: 56px;
            margin-left: var(--sidebar-width); /* This is the key for side-by-side layout */
            padding: 0 1.5rem 1.5rem 1.5rem;
        }
        @media (max-width: 767.98px) {
            .sidebar-custom {
                position: relative; /* On mobile, it's not fixed */
                width: 100%;
                padding-top: 0;
            }
            main {
                margin-left: 0; /* No margin on mobile */
            }
            .navbar-brand {
                width: auto;
            }
        }
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        .user-dropdown .dropdown-menu {
            border-radius: .5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<!-- ENHANCED HEADER STRUCTURE -->
<header class="navbar sticky-top flex-md-nowrap p-0 shadow-sm main-header">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="<?php 
        if ($_SESSION['role'] == 'Admin') echo 'dashboard.php';
        elseif ($_SESSION['role'] == 'Dentist') echo 'dentist_dashboard.php';
        else echo 'patients.php';
    ?>">
        <i class="fas fa-tooth me-2"></i>Zubieto Dental Clinic
    </a>
    
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="navbar-nav ms-auto">
        <div class="nav-item dropdown user-dropdown me-3">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-2x me-2 text-secondary"></i>
                <div>
                    <span class="fw-bold d-block"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <small class="text-muted"><?php echo htmlspecialchars($_SESSION['role']); ?></small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<div class="container-fluid">
    <!-- =================================================================
         THE FIX IS HERE: The <div class="row"> has been removed.
         The <nav> and <main> elements are now direct children of container-fluid.
         ================================================================= -->
    
    <!-- ENHANCED SIDEBAR STRUCTURE -->
    <nav id="sidebarMenu" class="d-md-block sidebar-custom collapse">
         <div class="sidebar-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-heading">Main</li>
                <?php
                if ($_SESSION['role'] == 'Admin') {
                    echo '<li class="nav-item"><a class="nav-link ' . ($current_page == 'dashboard.php' ? 'active' : '') . '" href="dashboard.php"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a></li>';
                } elseif ($_SESSION['role'] == 'Dentist') {
                    echo '<li class="nav-item"><a class="nav-link ' . ($current_page == 'dentist_dashboard.php' ? 'active' : '') . '" href="dentist_dashboard.php"><i class="fas fa-user-md fa-fw me-2"></i> My Dashboard</a></li>';
                }
                ?>
                
                <li class="nav-heading">Management</li>
                <li class="nav-item"><a class="nav-link <?php echo (strpos($current_page, 'patient') !== false) ? 'active' : ''; ?>" href="patients.php"><i class="fas fa-users fa-fw me-2"></i> Patients</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'appointments.php') ? 'active' : ''; ?>" href="appointments.php"><i class="fas fa-calendar-alt fa-fw me-2"></i> Appointments</a></li>
                
                <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <li class="nav-heading">Administration</li>
                    <li class="nav-item"><a class="nav-link <?php echo (strpos($current_page, 'user') !== false) ? 'active' : ''; ?>" href="users.php"><i class="fas fa-user-cog fa-fw me-2"></i> Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php"><i class="fas fa-chart-bar fa-fw me-2"></i> Reports</a></li>
                <?php endif; ?>

            </ul>
        </div>
    </nav>
    
    <!-- This main tag now correctly follows the sidebar -->
    <main>