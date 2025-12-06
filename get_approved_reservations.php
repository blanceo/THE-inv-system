<?php
require_once 'check_session.php';
require_once 'config/db_connect.php';

header('Content-Type: application/json');

try {
    // First, let's see what columns your reservations table has
    $sql = "SELECT * FROM reservations WHERE status = 'approved' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $sample = $result->fetch_assoc();
        // Log the column names
        error_log("Reservation columns: " . print_r(array_keys($sample), true));
    }
    
    // Now get all approved reservations
    $sql = "SELECT 
                r.*,
                u.full_name as teacher_name,
                u.username
            FROM reservations r
            LEFT JOIN users u ON r.teacher_id = u.id
            WHERE r.status = 'approved'
            ORDER BY r.date_needed ASC";
    
    $result = $conn->query($sql);
    
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    
    // Debug output
    error_log("Total approved reservations: " . count($reservations));
    
    echo json_encode($reservations);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>