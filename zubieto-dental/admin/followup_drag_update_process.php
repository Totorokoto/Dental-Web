<?php
// FILE: admin/followup_drag_update_process.php 

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Security: Ensure it's a POST request from a logged-in user
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get and validate the input from the AJAX call
$followup_id_str = $_POST['id'] ?? null;
$new_date = $_POST['new_date'] ?? null;

// The ID from the calendar is in the format "followup-123". We need to extract the number.
if (is_null($followup_id_str) || sscanf($followup_id_str, 'followup-%d', $record_id) !== 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid follow-up ID provided.']);
    exit;
}

if ($record_id <= 0 || is_null($new_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

// Prepare the SQL statement to update the 'next_appt' date in the 'treatment_records' table
$sql = "UPDATE treatment_records SET next_appt = ? WHERE record_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

// Bind parameters and execute
$stmt->bind_param("si", $new_date, $record_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Follow-up rescheduled successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database Error: Could not update the follow-up. ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>