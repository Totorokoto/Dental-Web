<?php
// Always start the session at the very top of any page that needs login info
session_start();

// This is the "gatekeeper" to protect the page.
// If the user is not logged in, it redirects them to the login page.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Clinic Application</title>
    
    <!-- ================================== -->
    <!--          REQUIRED LINKS            -->
    <!-- ================================== -->
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome 5 CSS for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Your Custom Stylesheet (must be last) -->
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

    <!-- Include the sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- ================================== -->
    <!--     INTEGRATED TOPBAR HTML         -->
    <!-- ================================== -->
    <header class="topbar">
    <!-- Left side: Title -->
    <div class="d-flex align-items-center">
        <a href="javascript:history.back()" class="btn btn-light me-3" title="Go Back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <!-- The dynamic page title code can stay as is -->
        <h4 class="page-title mb-0 me-3">
            <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                switch ($current_page) {
                    case 'patient_list.php': echo 'Patient List'; break;
                    case 'patients.php': echo 'Patient Detail'; break;
                    case 'add_patient.php': echo 'Add New Patient'; break;
                    case 'user_management.php': echo 'User Management'; break;
                    default: echo 'Dashboard';
                }
            ?>
        </h4>

        <!-- ================================== -->
        <!--  DYNAMIC BREADCRUMB (THE FIX)    -->
        <!-- ================================== -->
        <nav aria-label="breadcrumb">
            <?php generate_breadcrumbs(); ?>
        </nav>

    </div>

    <!-- Right side: Actions and User Menu -->
    <div class="ms-auto d-flex align-items-center">
            
            <!-- Search form that submits to the patient list -->
            <form class="d-flex me-2" action="patient_list.php" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
                    <input class="form-control border-start-0" type="search" name="search" placeholder="Search..." aria-label="Search">
                </div>
            </form>

            <!-- Add New Patient Button (circular) -->
            <a href="add_patient.php" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
               style="width: 40px; height: 40px;" title="Add New Patient">
               <i class="fas fa-plus"></i>
            </a>

            <!-- Icon Links -->
            <div class="d-flex align-items-center">
                <a href="#" class="text-muted px-2" title="Help"><i class="fas fa-question-circle"></i></a>
                <a href="#" class="text-muted px-2" title="Settings"><i class="fas fa-cog"></i></a>
                <a href="#" class="text-muted px-2" title="Notifications"><i class="fas fa-flag"></i> 1/4</a>
                <div class="vr mx-2"></div>

                <!-- User Dropdown Menu with Correct Bootstrap 5 Attributes -->
                <!-- Simplified Dropdown in topbar.php -->
<div class="dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="avatar.png" alt="Admin" class="rounded-circle" width="32" height="32">
        <span class="ms-2 d-none d-sm-inline">
            <?php 
                echo isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : 'User'; 
            ?>
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
        <li><a class="dropdown-item" href="#">Profile</a></li>
        <li><a class="dropdown-item" href="#">Settings</a></li>
    </ul>
</div>
            </div>
        </div>
    </header>
    <!-- ================================== -->
    <!--         END OF TOPBAR HTML         -->
    <!-- ================================== -->

    <!-- ================================== -->
    <!-- REQUIRED JAVASCRIPT (at the end) -->
    <!-- ================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

</body>
</html>