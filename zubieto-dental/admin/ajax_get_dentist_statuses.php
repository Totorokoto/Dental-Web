<?php
// FILE: admin/ajax_get_dentist_statuses.php

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Security check: ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Fetch all active users who are either a Dentist or an Admin to get their latest status
$sql = "SELECT user_id, full_name, availability_status FROM users WHERE role IN ('Dentist', 'Admin') AND is_active = 1";
$result = $conn->query($sql);

$statuses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $statuses[] = $row;
    }
}

$conn->close();

// Return the data as a JSON object for the JavaScript to read
echo json_encode(['success' => true, 'dentists' => $statuses]);
?>