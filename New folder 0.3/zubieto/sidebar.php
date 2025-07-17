<?php
// Get the filename of the currently executing script
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <img src="dental_clinic_logo.png" alt="Dental Clinic Logo" class="img-fluid" style="max-width: 80px;">
        <h5 class="mt-2 mb-0">ZUBIETO</h5>
        <p class="text-muted small mb-0">Dental Clinic <br>Adress.....</p>
    </div>

    <!-- Main Navigation Links -->
    <ul class="nav flex-column flex-grow-1">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <span class="nav-link text-uppercase">CLINIC</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'reservations.php') ? 'active' : ''; ?>" href="reservations.php">
                <i class="fas fa-calendar-alt"></i> Reservations
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'daily_log.php') ? 'active' : ''; ?>" href="daily_log.php">
                <i class="fas fa-history"></i> Daily Log
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo in_array($current_page, ['patient_list.php', 'patients.php', 'add_patient.php']) ? 'active' : ''; ?>" href="patient_list.php">
                <i class="fas fa-user-injured"></i> Patients
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'treatments.php') ? 'active' : ''; ?>" href="treatments.php">
                <i class="fas fa-tooth"></i> Treatments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'staff_list.php') ? 'active' : ''; ?>" href="staff_list.php">
                <i class="fas fa-users"></i> Staff List
            </a>
        </li>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <li class="nav-item">
                <span class="nav-link text-uppercase">ADMINISTRATION</span>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'user_management.php') ? 'active' : ''; ?>" href="user_management.php">
                    <i class="fas fa-user-shield"></i> User Management
                </a>
            </li>
        <?php endif; ?>

        <li class="nav-item">
            <span class="nav-link text-uppercase">FINANCE</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'accounts.php') ? 'active' : ''; ?>" href="accounts.php">
                <i class="fas fa-file-invoice-dollar"></i> Accounts
            </a>
        </li>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'sales.php') ? 'active' : ''; ?>" href="sales.php">
                <i class="fas fa-chart-line"></i> Sales
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'purchases.php') ? 'active' : ''; ?>" href="purchases.php">
                <i class="fas fa-shopping-cart"></i> Purchases
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'payment_methods.php') ? 'active' : ''; ?>" href="payment_methods.php">
                <i class="fas fa-credit-card"></i> Payment Method
            </a>
        </li>

        <li class="nav-item">
            <span class="nav-link text-uppercase">PHYSICAL ASSET</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'stocks.php') ? 'active' : ''; ?>" href="stocks.php">
                <i class="fas fa-box-open"></i> Stocks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'peripherals.php') ? 'active' : ''; ?>" href="peripherals.php">
                <i class="fas fa-keyboard"></i> Peripherals
            </a>
        </li>
    </ul>
    <div class="sidebar-footer p-3">
        <hr>
        <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
</div>