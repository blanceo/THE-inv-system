<?php
require_once 'config/db_connect.php';
require_once 'check_session.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Get form data
$item_names_json = $_POST['item_names'] ?? '';
$date_needed = $_POST['date_needed'] ?? '';
$purpose = $_POST['purpose'] ?? '';

// Validate inputs
if (empty($item_names_json) || empty($date_needed)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Decode item names array
$item_names = json_decode($item_names_json, true);

if (!is_array($item_names) || count($item_names) === 0) {
    echo json_encode(['success' => false, 'message' => 'No items specified']);
    exit;
}

// Connect to database
require_once 'config/db_connect.php';

// Insert each item as a separate reservation
$success_count = 0;
$failed_count = 0;

foreach ($item_names as $item_name) {
    $item_name = trim($item_name);
    
    if (empty($item_name)) {
        continue;
    }
    
    $stmt = $conn->prepare("INSERT INTO reservations (teacher_id, item_name, date_needed, purpose, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("isss", $teacher_id, $item_name, $date_needed, $purpose);
    
    if ($stmt->execute()) {
        $success_count++;
    } else {
        $failed_count++;
    }
    
    $stmt->close();
}

$conn->close();

if ($success_count > 0) {
    $message = $success_count === 1 
        ? "Reservation request submitted successfully!" 
        : "$success_count reservation requests submitted successfully!";
    
    if ($failed_count > 0) {
        $message .= " ($failed_count failed)";
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit reservations']);
}
?>