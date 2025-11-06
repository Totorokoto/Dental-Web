<?php
// FILE: admin/ajax_delete_attachment.php
session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
if ($attachment_id > 0) {
    // Instead of deleting, we'll mark it for deletion in the temporary table
    $session_id = session_id();
    $sql = "INSERT INTO pending_attachment_deletions (attachment_id, session_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $attachment_id, $session_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
}
$conn->close();
?>