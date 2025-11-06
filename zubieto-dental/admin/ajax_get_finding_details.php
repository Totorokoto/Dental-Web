<?php
// FILE: admin/ajax_get_finding_details.php (REVISED)
session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$finding_id = intval($_GET['id']);
$response = [
    'details' => null, 
    'attachments' => [], 
    'selected_findings' => [], 
    'selected_treatments' => []
];

// Get main details
$stmt = $conn->prepare("SELECT * FROM clinical_findings WHERE finding_id = ?");
$stmt->bind_param("i", $finding_id);
$stmt->execute();
$response['details'] = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get attachments
$stmt_attach = $conn->prepare("SELECT attachment_id, file_name, file_path, file_type FROM finding_attachments WHERE finding_id = ? AND is_deleted = 0");
$stmt_attach->bind_param("i", $finding_id);
$stmt_attach->execute();
$result_attach = $stmt_attach->get_result();
while ($row = $result_attach->fetch_assoc()) {
    $response['attachments'][] = $row;
}
$stmt_attach->close();

// Get SELECTED findings
$stmt_find = $conn->prepare("SELECT lookup_finding_id FROM clinical_finding_links WHERE finding_id = ?");
$stmt_find->bind_param("i", $finding_id);
$stmt_find->execute();
$result_find = $stmt_find->get_result();
while ($row = $result_find->fetch_assoc()) {
    $response['selected_findings'][] = $row['lookup_finding_id'];
}
$stmt_find->close();

// Get SELECTED treatments
$stmt_treat = $conn->prepare("SELECT lookup_treatment_id FROM clinical_treatment_links WHERE finding_id = ?");
$stmt_treat->bind_param("i", $finding_id);
$stmt_treat->execute();
$result_treat = $stmt_treat->get_result();
while ($row = $result_treat->fetch_assoc()) {
    $response['selected_treatments'][] = $row['lookup_treatment_id'];
}
$stmt_treat->close();

echo json_encode($response);
$conn->close();
?>