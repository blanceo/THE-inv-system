<?php
header("Content-Type: application/json");
include('../config/db_connect.php');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "error" => "No item ID provided"]);
    exit;
}

// Prepare and execute delete statement
$stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Item deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Item not found or already deleted"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>