<?php
session_start();
include('config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only admin can update status
    if ($_SESSION['user_type'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    $reservation_id = $_POST['reservation_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE reservations SET status = ?, approved_at = NOW(), approved_by = ? WHERE id = ?");
    $stmt->bind_param("sii", $status, $_SESSION['user_id'], $reservation_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Reservation {$status}!"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update reservation']);
    }
    
    $stmt->close();
    $conn->close();
}
?>