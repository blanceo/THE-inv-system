<?php
session_start();
include('config/db_connect.php');

// Debug output
error_log("=== GET_RESERVATIONS.PHP START ===");
error_log("Session user_type: " . ($_SESSION['user_type'] ?? 'NOT SET'));
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

header('Content-Type: application/json');

$user_type = $_SESSION['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

try {
    if ($user_type === 'admin') {
        error_log("Fetching all reservations for admin");
        $stmt = $conn->prepare("SELECT r.*, u.username as teacher_username 
                               FROM reservations r 
                               JOIN users u ON r.teacher_id = u.id 
                               ORDER BY r.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        error_log("Fetching reservations for teacher ID: $user_id");
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE teacher_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $reservations = [];
    $count = 0;

    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
        $count++;
    }

    error_log("Found $count reservations");
    
    // Debug: Print first reservation if exists
    if ($count > 0) {
        error_log("First reservation: " . json_encode($reservations[0]));
    }

    $stmt->close();
    $conn->close();

    echo json_encode($reservations);
    
} catch (Exception $e) {
    error_log("Error in get_reservations.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

error_log("=== GET_RESERVATIONS.PHP END ===");
?>