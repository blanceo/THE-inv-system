<?php
session_start();
include('config/db_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $user_type = $_SESSION['user_type'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;
    
    // Validate inputs
    if (empty($reservation_id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    // Check if user is logged in
    if (empty($user_type) || empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
    
    try {
        // Get current reservation details
        $check_stmt = $conn->prepare("SELECT teacher_id, status FROM reservations WHERE id = ?");
        $check_stmt->bind_param("i", $reservation_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Reservation not found']);
            exit;
        }
        
        $reservation = $result->fetch_assoc();
        $current_status = $reservation['status'];
        $teacher_id = $reservation['teacher_id'];
        
        // ADMIN ACTIONS (approved/rejected)
        if ($user_type === 'admin') {
            // Admin can approve or reject pending reservations
            if (!in_array($status, ['approved', 'rejected'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid admin action']);
                exit;
            }
            
            $stmt = $conn->prepare("UPDATE reservations SET status = ?, approved_at = NOW(), approved_by = ? WHERE id = ?");
            $stmt->bind_param("sii", $status, $user_id, $reservation_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => "Reservation {$status}!"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update reservation']);
            }
            
            $stmt->close();
        } 
        // TEACHER ACTIONS (cancelled/borrowed/returned)
        else {
            // Verify this reservation belongs to the logged-in teacher
            if ($teacher_id != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Access denied - not your reservation']);
                exit;
            }
            
            // Validate allowed status transitions for teachers
            $allowed_statuses = ['cancelled', 'borrowed', 'returned'];
            if (!in_array($status, $allowed_statuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                exit;
            }
            
            // Validate status transitions
            if ($status === 'cancelled' && $current_status !== 'pending') {
                echo json_encode(['success' => false, 'message' => 'Only pending reservations can be cancelled']);
                exit;
            }
            
            if ($status === 'borrowed' && $current_status !== 'approved') {
                echo json_encode(['success' => false, 'message' => 'Only approved reservations can be marked as borrowed']);
                exit;
            }
            
            if ($status === 'returned' && $current_status !== 'borrowed') {
                echo json_encode(['success' => false, 'message' => 'Only borrowed items can be marked as returned']);
                exit;
            }
            
            // Update the status
            $update_stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ? AND teacher_id = ?");
            $update_stmt->bind_param("sii", $status, $reservation_id, $teacher_id);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Status updated to ' . ucfirst($status) . ' successfully!'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
            
            $update_stmt->close();
        }
        
        $check_stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>