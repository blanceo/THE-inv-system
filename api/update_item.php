<?php
header("Content-Type: application/json");

// Use the same DB connection as your other files
include('../config/db_connect.php');

$data = json_decode(file_get_contents("php://input"), true);

$id     = $data["id"] ?? null;
$column = $data["column"] ?? null;
$value  = $data["value"] ?? null;

if (!$id || !$column) {
    echo json_encode(["success" => false, "error" => "Missing fields"]);
    exit;
}

// Map header name to DB column name
$colMap = [
    "Item" => "item",
    "Room" => "room",
    "Description" => "description",
    "Quantity (Beginning)" => "beginning",
    "Acquisition/Transfer" => "acquisition",
    "Quantity (End)" => "ending",
    "Pull-out" => "pullout",
    "Remarks" => "remarks"
];

if (!isset($colMap[$column])) {
    echo json_encode(["success" => false, "error" => "Invalid column"]);
    exit;
}

$dbColumn = $colMap[$column];

// Prevent SQL Injection
$stmt = $conn->prepare("UPDATE inventory SET $dbColumn = ? WHERE id = ?");
$stmt->bind_param("si", $value, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>