<?php
session_start();
include('config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only teachers can submit reservations
    if ($_SESSION['user_type'] !== 'teacher') {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    $item_name = trim($_POST['item_name']);
    $date_needed = $_POST['date_needed'];
    $purpose = trim($_POST['purpose'] ?? '');
    
    if (empty($item_name) || empty($date_needed)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO reservations (teacher_id, teacher_name, item_name, date_needed, purpose) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $_SESSION['user_id'], $_SESSION['full_name'], $item_name, $date_needed, $purpose);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reservation request submitted!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit reservation']);
    }
    
    $stmt->close();
    $conn->close();
}
?>