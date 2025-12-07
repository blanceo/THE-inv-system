<?php
// ========================================
// FILE 1: upload_image.php
// ========================================
require_once 'check_session.php';
require_once 'config/db_connect.php';

// Only admin can upload images
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$item_id = $_POST['item_id'] ?? null;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// Get file extension
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

// Validate file
if (!in_array($fileExt, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP are allowed.']);
    exit;
}

if ($fileSize > 2097152) { // 2MB in bytes
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 2MB.']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/inventory/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$newFileName = uniqid('item_', true) . '.' . $fileExt;
$uploadPath = $uploadDir . $newFileName;

// Move uploaded file
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Check if item already has an image and delete old one
    $stmt = $conn->prepare("SELECT image_path FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['image_path'] && file_exists($row['image_path'])) {
            unlink($row['image_path']); // Delete old image
        }
    }
    $stmt->close();
    
    // Update database with new image path
    $stmt = $conn->prepare("UPDATE inventory SET image_path = ? WHERE id = ?");
    $stmt->bind_param("si", $uploadPath, $item_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Image uploaded successfully',
            'image_path' => $uploadPath
        ]);
    } else {
        // If database update fails, delete the uploaded file
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}

$conn->close();
?>