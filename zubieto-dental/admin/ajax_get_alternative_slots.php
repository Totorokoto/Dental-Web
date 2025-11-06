<?php
// FILE: admin/ajax_get_alternative_slots.php (REVISED LOGIC)

session_start();
require '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit; 
}

// --- CONFIGURATION ---
// Set the maximum number of appointments a branch can handle per day.
const DAILY_APPOINTMENT_LIMIT = 10;

// --- INPUT VALIDATION ---
if (empty($_GET['start_date']) || empty($_GET['branch'])) {
    echo json_encode(['success' => false, 'message' => 'Start date and branch are required.']);
    exit;
}

$start_date = new DateTime($_GET['start_date']);
$branch = $_GET['branch'];
$suggestions = [];
$days_to_check = 21; // Check over the next 3 weeks

// --- LOGIC ---

for ($day = 0; $day < $days_to_check && count($suggestions) < 3; $day++) {
    $current_date = clone $start_date;
    $current_date->modify("+$day day");
    $date_str = $current_date->format('Y-m-d');

    // Skip Sundays
    if ($current_date->format('w') == 0) {
        continue;
    }

    // 1. Check if the day is already fully booked for the entire branch.
    $sql_daily_count = "
        SELECT COUNT(a.appointment_id) as total_appts
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE p.branch = ? 
          AND DATE(a.appointment_date) = ? 
          AND a.status IN ('Scheduled', 'Pending Approval')
    ";
    $stmt_count = $conn->prepare($sql_daily_count);
    $stmt_count->bind_param("ss", $branch, $date_str);
    $stmt_count->execute();
    $total_appts_today = $stmt_count->get_result()->fetch_assoc()['total_appts'];
    $stmt_count->close();

    // If the day is at or over the limit, skip to the next day.
    if ($total_appts_today >= DAILY_APPOINTMENT_LIMIT) {
        continue;
    }

    // 2. The day has open capacity. Find which dentists are available.
    $sql_dentists = "
        SELECT full_name 
        FROM users 
        WHERE is_active = 1 
          AND availability_status = 'Available' 
          AND (branch = ? OR role = 'Admin')
    ";
    $stmt_dentists = $conn->prepare($sql_dentists);
    $stmt_dentists->bind_param("s", $branch);
    $stmt_dentists->execute();
    $dentists_result = $stmt_dentists->get_result();
    $dentist_names = [];
    while ($row = $dentists_result->fetch_assoc()) {
        // Extracting just the name part (e.g., "Dr. Juan Dela Cruz" -> "Dr. Dela Cruz")
        $parts = explode(' ', $row['full_name']);
        $last_name = end($parts);
        $prefix = str_contains($row['full_name'], 'Dr.') ? 'Dr.' : '';
        $dentist_names[] = $prefix . ' ' . $last_name;
    }
    $stmt_dentists->close();

    // 3. If there are available dentists, format the suggestion string.
    if (!empty($dentist_names)) {
        $suggestion_string = $current_date->format('l, F j, Y') . " (Has open slots)";
        $suggestion_string .= "<br><small class='text-muted'>Available Doctors: " . implode(', ', $dentist_names) . "</small>";
        $suggestions[] = $suggestion_string;
    }
}

echo json_encode(['success' => true, 'slots' => $suggestions]);
$conn->close();
?>