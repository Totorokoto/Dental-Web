<?php
// FILE: admin/includes/header.php

// Start the session to access session variables.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is not logged in.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "You must be logged in to view this page.";
    // Adjust the path to go up two directories to the root index.php
    header("Location: ../../index.php");
    exit();
}

// --- DATABASE CONNECTION AND DATA FETCHING (MERGED FOR EFFICIENCY) ---
// The connection is opened here and will remain open for the main page script to use.
require_once __DIR__ . '/../../includes/db_connect.php';

// Fetch user's current availability status for the session if not set
if (!isset($_SESSION['availability_status'])) {
    $stmt_status = $conn->prepare("SELECT availability_status FROM users WHERE user_id = ?");
    $stmt_status->bind_param("i", $_SESSION['user_id']);
    $stmt_status->execute();
    $status_result = $stmt_status->get_result()->fetch_assoc();
    $_SESSION['availability_status'] = $status_result['availability_status'] ?? 'Available';
    $stmt_status->close();
}

// --- LOGIC FOR PENDING APPOINTMENT COUNT ---
$pending_count = 0;
$user_role_for_header = $_SESSION['role'];
$user_branch_for_header = $_SESSION['branch'];

$sql_pending_count = "SELECT COUNT(a.appointment_id) as count 
                      FROM appointments a
                      JOIN patients p ON a.patient_id = p.patient_id
                      WHERE a.status = 'Pending Approval'";

// If the user is not an Admin, filter the count by their branch
if ($user_role_for_header !== 'Admin') {
    $sql_pending_count .= " AND p.branch = ?";
    $stmt_count = $conn->prepare($sql_pending_count);
    $stmt_count->bind_param("s", $user_branch_for_header);
} else {
    $stmt_count = $conn->prepare($sql_pending_count);
}

if ($stmt_count) {
    $stmt_count->execute();
    $result_count = $stmt_count->get_result()->fetch_assoc();
    $pending_count = $result_count['count'] ?? 0;
    $stmt_count->close();
}

// --- CRITICAL FIX: The connection is intentionally left open for the main page script to use. ---
// It will be closed in footer.php.


// --- PAGE ACCESS CONTROL (RBAC) ---
$current_page = basename($_SERVER['PHP_SELF']);

// Define pages accessible only by Admins
$admin_only_pages = [
    'dashboard.php',
    'users.php',
    'user_add.php',
    'user_edit.php',
    'user_delete_process.php',
    'reports.php',
    'logs.php',
    'clinic_data_management.php'
];

// Define pages accessible by Dentists (and Admins)
$dentist_allowed_pages = [
    'dentist_dashboard.php'
];

// Redirect if a non-Admin tries to access an admin-only page
if (in_array($current_page, $admin_only_pages) && $_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "Access Denied: You do not have permission to view that page.";
    $_SESSION['message_type'] = 'danger';
    // Redirect to a default page appropriate for their role
    header("Location: " . ($_SESSION['role'] === 'Dentist' ? 'dentist_dashboard.php' : 'patients.php'));
    exit();
}

// Redirect if a non-Dentist/non-Admin tries to access a dentist page
if (in_array($current_page, $dentist_allowed_pages) && !in_array($_SESSION['role'], ['Admin', 'Dentist'])) {
    $_SESSION['message'] = "Access Denied.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patients.php"); // Assistants are redirected to the patients list
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Zubieto Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00796B;
            --secondary-color: #B2DFDB;
            --background-color: #f4f7f6;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            --sidebar-width: 250px;
            --sidebar-bg-color: #2c3e50;
            --sidebar-link-color: #ecf0f1;
            --sidebar-hover-bg: #34495e;
            --sidebar-active-bg: var(--primary-color);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            background-image: linear-gradient(to top, #e6f0f3 0%, #f4f7f6 100%);
        }
        .main-header {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            height: 60px;
            z-index: 1030;
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
            padding-top: 60px;
            background-color: var(--sidebar-bg-color);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1020;
        }
        .sidebar-sticky {
            height: calc(100vh - 60px);
            overflow-y: auto;
        }
        .sidebar-custom .nav-link {
            color: var(--sidebar-link-color);
            padding: 12px 20px;
            font-weight: 500;
            transition: background-color 0.2s, color 0.2s;
            border-left: 4px solid transparent;
            font-size: 0.95rem;
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
            font-weight: 600;
            border-left-color: var(--secondary-color);
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
            margin-top: 60px;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }
        @media (max-width: 767.98px) {
            .sidebar-custom {
                padding-top: 60px;
                transform: translateX(calc(-1 * var(--sidebar-width)));
                transition: transform 0.3s ease-in-out;
            }
            .sidebar-custom.show {
                transform: translateX(0);
            }
            main {
                margin-left: 0;
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
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
        }
        .availability-badge {
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<header class="navbar sticky-top flex-md-nowrap p-0 shadow-sm main-header">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="<?php
        if ($_SESSION['role'] == 'Admin') echo 'dashboard.php';
        elseif ($_SESSION['role'] == 'Dentist') echo 'dentist_dashboard.php';
        else echo 'patients.php';
    ?>">
        <i class="fas fa-tooth me-2"></i>Zubieto Dental Clinic
    </a>

    <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-nav ms-auto">
        <div class="nav-item dropdown user-dropdown me-3">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-2x me-2 text-secondary"></i>
                <div>
                    <span class="fw-bold d-block"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <small class="text-muted"><?php echo htmlspecialchars($_SESSION['role']) . ' - ' . htmlspecialchars($_SESSION['branch']); ?></small>
                    <?php 
                        $status = $_SESSION['availability_status'];
                        $badge_class = 'bg-success';
                        if ($status == 'On Leave') $badge_class = 'bg-secondary';
                        if ($status == 'Training') $badge_class = 'bg-info';
                        if ($status == 'Sick Day') $badge_class = 'bg-warning text-dark';
                    ?>
                    <span id="user-status-badge" class="badge rounded-pill <?php echo $badge_class; ?> availability-badge ms-1"><?php echo htmlspecialchars($status); ?></span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#availabilityModal"><i class="fas fa-clock fa-fw me-2"></i>Set My Status</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar-custom collapse">
             <div class="sidebar-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-heading">Main</li>
                    <?php
                    if ($_SESSION['role'] == 'Admin') {
                        $dashboard_page = 'dashboard.php';
                        $dashboard_icon = 'fa-tachometer-alt';
                        $dashboard_label = 'Dashboard';
                        $is_active = ($current_page == $dashboard_page);
                        echo '<li class="nav-item"><a class="nav-link ' . ($is_active ? 'active' : '') . '" href="' . $dashboard_page . '"><i class="fas ' . $dashboard_icon . ' fa-fw me-2"></i> ' . $dashboard_label . '</a></li>';
                    } elseif ($_SESSION['role'] == 'Dentist') {
                        $dashboard_page = 'dentist_dashboard.php';
                        $dashboard_icon = 'fa-user-md';
                        $dashboard_label = 'My Dashboard';
                        $is_active = ($current_page == $dashboard_page);
                        echo '<li class="nav-item"><a class="nav-link ' . ($is_active ? 'active' : '') . '" href="' . $dashboard_page . '"><i class="fas ' . $dashboard_icon . ' fa-fw me-2"></i> ' . $dashboard_label . '</a></li>';
                    }
                    ?>

                    <li class="nav-heading">Management</li>
                    <?php 
                        $patient_pages = ['patients.php', 'patient_add.php', 'patient_view.php', 'patient_edit.php'];
                        $is_patient_active = in_array($current_page, $patient_pages) ? 'active' : '';
                    ?>
                    <li class="nav-item"><a class="nav-link <?php echo $is_patient_active; ?>" href="patients.php"><i class="fas fa-users fa-fw me-2"></i> Patients</a></li>
                    
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center <?php echo ($current_page == 'appointments.php') ? 'active' : ''; ?>" href="appointments.php">
                            <span><i class="fas fa-calendar-alt fa-fw me-2"></i> Appointments</span>
                            <?php if ($pending_count > 0): ?>
                                <span class="badge bg-warning rounded-pill"><?php echo $pending_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <li class="nav-heading">Administration</li>
                        <?php 
                            $user_pages = ['users.php', 'user_add.php', 'user_edit.php'];
                            $is_user_active = in_array($current_page, $user_pages) ? 'active' : '';
                        ?>
                        <li class="nav-item"><a class="nav-link <?php echo $is_user_active; ?>" href="users.php"><i class="fas fa-user-cog fa-fw me-2"></i> Manage Users</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php"><i class="fas fa-chart-line fa-fw me-2"></i> Reports</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'clinic_data_management.php') ? 'active' : ''; ?>" href="clinic_data_management.php"><i class="fas fa-cogs fa-fw me-2"></i> Clinic Data</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>" href="logs.php"><i class="fas fa-history fa-fw me-2"></i> Activity Logs</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10">
<!-- NEW: Availability Status Modal -->
<div class="modal fade" id="availabilityModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update My Availability</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="availabilityForm">
        <div class="modal-body">
            <p class="text-muted">Setting your status will notify administrators and affect appointment scheduling.</p>
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <div class="form-group">
                <label for="availability_status" class="form-label">New Status</label>
                <select name="status" id="availability_status" class="form-select" required>
                    <option value="Available" <?php if ($_SESSION['availability_status'] == 'Available') echo 'selected'; ?>>Available</option>
                    <option value="On Leave" <?php if ($_SESSION['availability_status'] == 'On Leave') echo 'selected'; ?>>On Leave</option>
                    <option value="Training" <?php if ($_SESSION['availability_status'] == 'Training') echo 'selected'; ?>>Training</option>
                    <option value="Sick Day" <?php if ($_SESSION['availability_status'] == 'Sick Day') echo 'selected'; ?>>Sick Day</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>