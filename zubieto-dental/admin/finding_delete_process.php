<?php
// FILE: admin/finding_delete_process.php (MODIFIED)
session_start();
require '../includes/db_connect.php';

// ... (keep your existing security checks) ...

$finding_id = intval($_GET['id']);
$patient_id = intval($_GET['patient_id']);

$conn->begin_transaction();

try {
    // 1. Get all file paths for this finding BEFORE deleting the record
    $sql_get_files = "SELECT file_path FROM finding_attachments WHERE finding_id = ?";
    $stmt_get_files = $conn->prepare($sql_get_files);
    $stmt_get_files->bind_param("i", $finding_id);
    $stmt_get_files->execute();
    $result = $stmt_get_files->get_result();
    
    // 2. Delete the files from the server
    while ($row = $result->fetch_assoc()) {
        $file_on_server = '../' . $row['file_path'];
        if (file_exists($file_on_server)) {
            unlink($file_on_server); // Deletes the file
        }
    }
    $stmt_get_files->close();

    // 3. Delete the finding from the database.
    // The ON DELETE CASCADE will handle deleting from `finding_attachments` table.
    $sql_delete = "DELETE FROM clinical_findings WHERE finding_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $finding_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    
    $conn->commit();
    $_SESSION['message'] = "Clinical finding and all associated files deleted successfully.";
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = "Error deleting finding: " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

$conn->close();
// Get the active tab from the form submission, default to 'profile'
$active_tab = isset($_POST['tab_redirect']) ? htmlspecialchars($_POST['tab_redirect']) : 'profile';

// Build the redirect URL
$redirect_url = "patient_view.php?id=" . $patient_id . "&tab=" . $active_tab;

// Redirect
$_SESSION['message'] = "Clinical finding deleted successfully.";
$_SESSION['message_type'] = 'success';

// Get the patient ID from the URL
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Get the tab to redirect back to from the URL, default to 'findings'
$active_tab = isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'findings';

// Build the final redirect URL
$redirect_url = "patient_view.php?id=" . $patient_id . "&tab=" . $active_tab;

// Redirect
header("Location: " . $redirect_url);
exit();
?>