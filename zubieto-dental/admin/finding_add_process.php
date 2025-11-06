<?php
// FILE: admin/finding_add_process.php (REVISED)
session_start();
require '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); exit();
}

$patient_id = intval($_POST['patient_id']);
$conn->begin_transaction();

try {
    // 1. Insert the main finding record (with custom notes)
    $stmt = $conn->prepare("INSERT INTO clinical_findings (patient_id, dentist_id, finding_date, custom_findings_notes, diagnosis, custom_treatment_notes, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $patient_id, $_SESSION['user_id'], $_POST['finding_date'], $_POST['custom_findings_notes'], $_POST['diagnosis'], $_POST['custom_treatment_notes'], $_POST['remarks']);
    $stmt->execute();
    $new_finding_id = $conn->insert_id;
    if ($new_finding_id == 0) {
        throw new Exception("Failed to create the clinical finding record.");
    }
    $stmt->close();

    // 2. Insert selected clinical findings from dropdown
    if (!empty($_POST['lookup_findings']) && is_array($_POST['lookup_findings'])) {
        $stmt_link = $conn->prepare("INSERT INTO clinical_finding_links (finding_id, lookup_finding_id) VALUES (?, ?)");
        foreach ($_POST['lookup_findings'] as $lookup_id) {
            $stmt_link->bind_param("ii", $new_finding_id, $lookup_id);
            $stmt_link->execute();
        }
        $stmt_link->close();
    }

    // 3. Insert selected proposed treatments from dropdown
    if (!empty($_POST['lookup_treatments']) && is_array($_POST['lookup_treatments'])) {
        $stmt_link = $conn->prepare("INSERT INTO clinical_treatment_links (finding_id, lookup_treatment_id) VALUES (?, ?)");
        foreach ($_POST['lookup_treatments'] as $lookup_id) {
            $stmt_link->bind_param("ii", $new_finding_id, $lookup_id);
            $stmt_link->execute();
        }
        $stmt_link->close();
    }
    
    // 4. Handle File Uploads (This logic remains the same)
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
                $stmt_attach->bind_param("isss", $new_finding_id, $file_name, $db_path, $file_type);
                $stmt_attach->execute();
                $stmt_attach->close();
            } else {
                 throw new Exception("Failed to move uploaded file: " . htmlspecialchars($file_name));
            }
        }
    }

    $conn->commit();
    $_SESSION['message'] = "New clinical finding added successfully.";
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = "Error: " . $e->getMessage();
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