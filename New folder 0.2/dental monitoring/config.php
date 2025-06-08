<?php
$host = 'localhost';  // Your database host
$dbname = 'zubieto'; // Your database name
$username = 'root'; // Your database username
$password = ''; // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ===============================================
//   NEW BREADCRUMB HELPER FUNCTION
// ===============================================
function generate_breadcrumbs() {
    // Get the current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Start with the home link
    echo '<ol class="breadcrumb bg-transparent mb-0 p-0">';
    echo '<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>';
    
    // Add page-specific links
    switch ($current_page) {
        case 'patient_list.php':
            echo '<li class="breadcrumb-item active" aria-current="page">Patient List</li>';
            break;
            
        case 'patients.php':
            echo '<li class="breadcrumb-item"><a href="patient_list.php">Patient List</a></li>';
            // Try to get the patient's name for a richer breadcrumb
            if (isset($_GET['patientID'])) {
                // In a real app, you might fetch the name here, but for simplicity we'll just show 'Detail'
                echo '<li class="breadcrumb-item active" aria-current="page">Patient Detail</li>';
            }
            break;
            
        case 'add_patient.php':
            echo '<li class="breadcrumb-item"><a href="patient_list.php">Patient List</a></li>';
            echo '<li class="breadcrumb-item active" aria-current="page">Add New</li>';
            break;

        case 'user_management.php':
            echo '<li class="breadcrumb-item active" aria-current="page">User Management</li>';
            break;
            
        // You can add more cases for other pages here
    }
    
    echo '</ol>';
}

?>