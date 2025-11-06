<?php
// FILE: admin/ajax_update_availability.php

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Security check: Must be a logged-in user making a POST request.
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$user_id_to_update = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$new_status = $_POST['status'] ?? '';
$valid_statuses = ['Available', 'On Leave', 'Training', 'Sick Day'];

// --- Validation ---
if ($user_id_to_update <= 0 || !in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

// Security: Admins can update anyone. Others can only update themselves.
$is_self_update = ($user_id_to_update == $_SESSION['user_id']);
if ($_SESSION['role'] !== 'Admin' && !$is_self_update) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to change this user\'s status.']);
    exit;
}

// --- Database Update ---
$conn->begin_transaction();
try {
    // Update the user's status in the database
    $stmt = $conn->prepare("UPDATE users SET availability_status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_status, $user_id_to_update);
    $stmt->execute();

    // --- Activity Logging ---
    $actor_id = $_SESSION['user_id'];
    $action_type = 'User Status Changed';
    
    $user_info_stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $user_info_stmt->bind_param("i", $user_id_to_update);
    $user_info_stmt->execute();
    $user_name = $user_info_stmt->get_result()->fetch_assoc()['full_name'] ?? 'Unknown User';
    $user_info_stmt->close();

    $details = "Status for user '" . htmlspecialchars($user_name) . "' was changed to '" . htmlspecialchars($new_status) . "'.";

    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $log_stmt->bind_param("iss", $actor_id, $action_type, $details);
    $log_stmt->execute();
    $log_stmt->close();

    $conn->commit();
    
    // If it's a self-update, also update the session variable.
    if ($is_self_update) {
        $_SESSION['availability_status'] = $new_status;
    }
    
    // --- NEW: Determine badge class for the response ---
    $badge_class = 'bg-success'; // Default for Available
    if ($new_status == 'On Leave') $badge_class = 'bg-secondary';
    if ($new_status == 'Training') $badge_class = 'bg-info';
    if ($new_status == 'Sick Day') $badge_class = 'bg-warning text-dark';

    // --- NEW: Send a more detailed success response ---
    echo json_encode([
        'success' => true, 
        'message' => 'Status updated successfully.',
        'is_self_update' => $is_self_update,
        'new_status' => $new_status,
        'badge_class' => $badge_class
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

if (isset($stmt)) $stmt->close();
// The connection is closed by the footer include on the calling page
?>