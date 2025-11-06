<?php
require 'includes/db_connect.php';
header('Content-Type: application/json');

if (empty($_GET['date'])) {
    echo json_encode(['success' => false, 'slots' => []]);
    exit;
}

$date = $_GET['date'];

// Get all appointments that are either scheduled or pending for this day
$sql = "SELECT appointment_date FROM appointments WHERE DATE(appointment_date) = ? AND status IN ('Scheduled', 'Pending Approval')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
$booked_slots = [];
while($row = $result->fetch_assoc()){
    $booked_slots[] = date('H:i', strtotime($row['appointment_date']));
}
$stmt->close();

// Define clinic hours and intervals (e.g., every 30 minutes)
$available_slots = [];
$start_time = strtotime('08:00');
$end_time = strtotime('17:00');
$interval = 30 * 60; // 30 minutes

for ($i = $start_time; $i < $end_time; $i += $interval) {
    $current_slot_24hr = date('H:i', $i);
    // If the slot is NOT in the list of booked slots, add it to our available list
    if (!in_array($current_slot_24hr, $booked_slots)) {
        $available_slots[] = [
            'value' => date('H:i:s', $i),      // e.g., "09:30:00"
            'display' => date('h:i A', $i)     // e.g., "09:30 AM"
        ];
    }
}

echo json_encode(['success' => true, 'slots' => $available_slots]);
$conn->close();
?>