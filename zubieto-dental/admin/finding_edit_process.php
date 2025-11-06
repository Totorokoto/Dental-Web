<?php
// FILE: admin/finding_edit_process.php (CORRECTED)
session_start();
require '../includes/db_connect.php';

// Security check: Ensure it's a POST request from a logged-in user
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

// --- 1. GET AND SANITIZE ALL INCOMING DATA ---
$finding_id = intval($_POST['finding_id']);
$patient_id = intval($_POST['patient_id']); // For redirection

// If finding_id is invalid, we cannot proceed.
if ($finding_id <= 0) {
    $_SESSION['message'] = "Invalid finding ID. Update failed.";
    $_SESSION['message_type'] = 'danger';
    header("Location: patient_view.php?id=" . $patient_id . "&tab=findings");
    exit();
}

$finding_date = $_POST['finding_date'];
$custom_findings_notes = trim($_POST['custom_findings_notes']);
$diagnosis = trim($_POST['diagnosis']);
$custom_treatment_notes = trim($_POST['custom_treatment_notes']);
$remarks = trim($_POST['remarks']);
$lookup_findings = $_POST['lookup_findings'] ?? [];
$lookup_treatments = $_POST['lookup_treatments'] ?? [];

// Start a transaction to ensure all updates succeed or fail together
$conn->begin_transaction();

try {
    // --- 2. UPDATE THE MAIN clinical_findings RECORD ---
    $sql_update_main = "UPDATE clinical_findings SET 
        finding_date = ?, 
        custom_findings_notes = ?, 
        diagnosis = ?, 
        custom_treatment_notes = ?, 
        remarks = ? 
        WHERE finding_id = ?";
    
    $stmt_main = $conn->prepare($sql_update_main);
    $stmt_main->bind_param("sssssi", $finding_date, $custom_findings_notes, $diagnosis, $custom_treatment_notes, $remarks, $finding_id);
    $stmt_main->execute();
    $stmt_main->close();

    // --- 3. UPDATE THE LINKED FINDINGS (DELETE THEN RE-INSERT) ---
    // This is the simplest and most reliable way to handle changes in multi-selects.
    $stmt_delete_links = $conn->prepare("DELETE FROM clinical_finding_links WHERE finding_id = ?");
    $stmt_delete_links->bind_param("i", $finding_id);
    $stmt_delete_links->execute();
    $stmt_delete_links->close();

    if (!empty($lookup_findings)) {
        $stmt_insert_links = $conn->prepare("INSERT INTO clinical_finding_links (finding_id, lookup_finding_id) VALUES (?, ?)");
        foreach ($lookup_findings as $lookup_id) {
            $stmt_insert_links->bind_param("ii", $finding_id, $lookup_id);
            $stmt_insert_links->execute();
        }
        $stmt_insert_links->close();
    }

    // --- 4. UPDATE THE LINKED TREATMENTS (DELETE THEN RE-INSERT) ---
    $stmt_delete_treats = $conn->prepare("DELETE FROM clinical_treatment_links WHERE finding_id = ?");
    $stmt_delete_treats->bind_param("i", $finding_id);
    $stmt_delete_treats->execute();
    $stmt_delete_treats->close();

    if (!empty($lookup_treatments)) {
        $stmt_insert_treats = $conn->prepare("INSERT INTO clinical_treatment_links (finding_id, lookup_treatment_id) VALUES (?, ?)");
        foreach ($lookup_treatments as $lookup_id) {
            $stmt_insert_treats->bind_param("ii", $finding_id, $lookup_id);
            $stmt_insert_treats->execute();
        }
        $stmt_insert_treats->close();
    }

    // --- 5. FINALIZE PENDING ATTACHMENT DELETIONS ---
    // This part processes the files marked for deletion via AJAX.
    $session_id = session_id();
    $sql_get_pending = "SELECT attachment_id FROM pending_attachment_deletions WHERE session_id = ?";
    $stmt_get_pending = $conn->prepare($sql_get_pending);
    $stmt_get_pending->bind_param("s", $session_id);
    $stmt_get_pending->execute();
    $pending_result = $stmt_get_pending->get_result();
    
    $ids_to_delete = [];
    while ($row = $pending_result->fetch_assoc()) {
        $ids_to_delete[] = $row['attachment_id'];
    }
    $stmt_get_pending->close();

    if (!empty($ids_to_delete)) {
        $in_clause = implode(',', array_fill(0, count($ids_to_delete), '?'));
        $types = str_repeat('i', count($ids_to_delete));
        
        // Mark as deleted in the main attachments table
        $sql_mark_deleted = "UPDATE finding_attachments SET is_deleted = 1 WHERE attachment_id IN ($in_clause)";
        $stmt_mark_deleted = $conn->prepare($sql_mark_deleted);
        $stmt_mark_deleted->bind_param($types, ...$ids_to_delete);
        $stmt_mark_deleted->execute();
        $stmt_mark_deleted->close();

        // Clean up the pending deletions table
        $sql_clear_pending = "DELETE FROM pending_attachment_deletions WHERE session_id = ?";
        $stmt_clear_pending = $conn->prepare($sql_clear_pending);
        $stmt_clear_pending->bind_param("s", $session_id);
        $stmt_clear_pending->execute();
        $stmt_clear_pending->close();
    }


    // --- 6. HANDLE NEW FILE UPLOADS ---
    if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        $upload_dir = '../uploads/';
        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['attachments']['error'][$key] !== UPLOAD_ERR_OK) continue;
            
            $file_name = basename($_FILES['attachments']['name'][$key]);
            $unique_file_name = uniqid() . '-' . $file_name;
            $target_path = $upload_dir . $unique_file_name;
            $db_path = 'uploads/' . $unique_file_name;
            
            if (move_uploaded_file($tmp_name, $target_path)) {
                $file_type = mime_content_type($target_path);
                $sql_attach = "INSERT INTO finding_attachments (finding_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)";
                $stmt_attach = $conn->prepare($sql_attach);
                $stmt_attach->bind_param("isss", $finding_id, $file_name, $db_path, $file_type);
                $stmt_attach->execute();
                $stmt_attach->close();
            } else {
                 throw new Exception("Failed to move uploaded file: " . htmlspecialchars($file_name));
            }
        }
    }

    // If everything was successful, commit the changes
    $conn->commit();
    $_SESSION['message'] = "Clinical finding updated successfully.";
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    // If any error occurred, roll back all changes
    $conn->rollback();
    $_SESSION['message'] = "Error updating finding: " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

$conn->close();
// Get the active tab from the form submission, default to 'profile'
$active_tab = isset($_POST['tab_redirect']) ? htmlspecialchars($_POST['tab_redirect']) : 'profile';

// Build the redirect URL
$redirect_url = "patient_view.php?id=" . $patient_id . "&tab=" . $active_tab;

// Redirect
header("Location: " . $redirect_url);
exit();
?>