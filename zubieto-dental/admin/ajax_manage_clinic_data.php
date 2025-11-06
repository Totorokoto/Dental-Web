<?php
// FILE: admin/ajax_manage_clinic_data.php
session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Security & Validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

// Determine table name from type
$table_map = [
    'finding' => 'lookup_findings',
    'treatment' => 'lookup_treatments'
];

if (!isset($table_map[$type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data type specified.']);
    exit;
}
$table_name = $table_map[$type];

switch ($action) {
    case 'add':
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name cannot be empty.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO $table_name (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            echo json_encode(['success' => true, 'id' => $new_id, 'name' => htmlspecialchars($name)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        break;

    case 'update':
        if ($id <= 0 || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE $table_name SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        break;

    case 'delete':
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM $table_name WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        break;
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>