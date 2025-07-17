<?php
// 1. START THE SESSION. This must be the very first line.
session_start();
 
// 2. GATEKEEPER. Check if the user is logged in.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 3. REQUIRE CONFIG.
require_once 'config.php';

// 4. PAGE-SPECIFIC LOGIC (WITH SEARCH)
$patients = [];
$error_message = '';
// Get the search term from the URL, if it exists, and trim whitespace
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Start with the base SQL query
    $sql = "SELECT PatientID, FirstName, LastName, Birthdate, MobileNumber, Email FROM Patients";
    
    // If a search term is provided, add a WHERE clause
    if (!empty($search_term)) {
        // The WHERE clause searches across multiple relevant fields
        $sql .= " WHERE FirstName LIKE :search 
                  OR LastName LIKE :search 
                  OR MobileNumber LIKE :search 
                  OR Email LIKE :search";
    }
    
    $sql .= " ORDER BY LastName, FirstName";
    
    $stmt = $pdo->prepare($sql);
    
    // If we are searching, we need to bind the search term to the placeholder
    if (!empty($search_term)) {
        // The '%' are wildcards, so the query finds the term anywhere in the string
        $stmt->bindValue(':search', '%' . $search_term . '%');
    }
    
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error on Patient List: " . $e->getMessage());
    $error_message = "A database error occurred while fetching the patient list.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient List</title>
    
    <!-- REQUIRED CSS LINKS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    
    <style>
        /* Page-specific styles for the patient table */
        .patient-table { background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .patient-table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; font-weight: 600; }
        .patient-table tbody tr { cursor: pointer; transition: background-color 0.2s ease; }
        .patient-table tbody tr:hover { background-color: #f1f1f1; }
        .patient-table tbody td { vertical-align: middle; }
        .action-icon { color: #6c757d; text-decoration: none; margin: 0 5px; cursor: default; }
        .action-icon:hover { color: #0d6efd; }
        .action-icon.delete:hover { color: #dc3545; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; // The topbar search form will now work with this page's logic ?>

    <main class="main-content-area">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Patient List</h2>
            <a href="add_patient.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Patient
            </a>
        </div>
        
        <!-- Feedback Messages for delete actions, etc. -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Patient record deleted successfully.
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
        <?php elseif (empty($patients)): ?>
            <?php if (!empty($search_term)): ?>
                <div class="alert alert-warning">No patients found matching your search for "<strong><?php echo htmlspecialchars($search_term); ?></strong>". <a href="patient_list.php">Clear search</a>.</div>
            <?php else: ?>
                <div class="alert alert-info">No patients found in the database. <a href="add_patient.php">Add one now!</a></div>
            <?php endif; ?>
        <?php else: ?>
            <div class="table-responsive patient-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Birthdate</th>
                            <th>Mobile Number</th>
                            <th>Email Address</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                            <tr onclick="viewPatient(<?php echo $patient['PatientID']; ?>)">
                                <td><strong><?php echo htmlspecialchars($patient['LastName'] . ', ' . $patient['FirstName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($patient['Birthdate']); ?></td>
                                <td><?php echo htmlspecialchars($patient['MobileNumber']); ?></td>
                                <td><?php echo htmlspecialchars($patient['Email']); ?></td>
                                <td class="text-center">
                                    <a href="#" class="action-icon" title="Send Message" onclick="event.stopPropagation(); alert('Send message functionality not implemented yet.');"><i class="fas fa-envelope"></i></a>
                                    <a href="#" class="action-icon delete" title="Delete Patient" 
                                       onclick="confirmDelete(event, <?php echo $patient['PatientID']; ?>, '<?php echo htmlspecialchars(addslashes($patient['FirstName'] . ' ' . $patient['LastName']), ENT_QUOTES); ?>')">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        function viewPatient(patientID) {
            window.location.href = 'patients.php?patientID=' + patientID;
        }

        function confirmDelete(event, patientID, patientName) {
            event.stopPropagation(); 
            if (confirm('Are you sure you want to delete the record for ' + patientName + '? This action cannot be undone.')) {
                window.location.href = 'delete_patient.php?patientID=' + patientID;
            }
        }
    </script>
</body>
</html>