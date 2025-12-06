<?php
require_once 'check_session.php';
require_once 'config/db_connect.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$reservation_id = $data['id'] ?? null;

if (!$reservation_id) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID required']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE reservations SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Reservation removed from calendar and marked as rejected'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update reservation']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>