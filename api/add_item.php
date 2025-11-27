<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include('../config/db_connect.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

// Validate required fields
if (empty($data['item'])) {
    echo json_encode(['success' => false, 'error' => 'Item name is required']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO inventory 
    (room, category, item, description, beginning, acquisition, ending, pullout, remarks) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssssssss",
    $data['room'],
    $data['category'],
    $data['item'],
    $data['description'],
    $data['beginning'],
    $data['acquisition'],
    $data['ending'],
    $data['pullout'],
    $data['remarks']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>