<?php
// ========================================
// FILE 2: delete_image.php
// ========================================
require_once 'check_session.php';
require_once 'config/db_connect.php';

// Only admin can delete images
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$item_id = $data['item_id'] ?? null;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

try {
    // Get current image path
    $stmt = $conn->prepare("SELECT image_path FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['image_path'] && file_exists($row['image_path'])) {
            unlink($row['image_path']); // Delete file
        }
    }
    $stmt->close();
    
    // Remove image path from database
    $stmt = $conn->prepare("UPDATE inventory SET image_path = NULL WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>